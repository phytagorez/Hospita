<?php

namespace App\Http\Controllers;

use App\BookingService;
use App\Http\Requests\ReservationRequest;
use App\Models\DoctorSchedule;
use App\Models\Reservation;
use App\QrCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class ReservationController extends Controller
{
    public function create(Request $request): Response|RedirectResponse
    {
        if (! $request->user()->patientProfile) {
            return redirect()->route('patient.profile');
        }
        $data = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today', 'before_or_equal:'.today()->addDays(30)->toDateString()],
            'schedule' => ['nullable', 'integer', 'exists:doctor_schedules,id'],
        ]);
        $date = $data['date'] ?? today()->toDateString();
        $schedules = DoctorSchedule::with('doctor.polyclinic')->where('day_of_week', Carbon::parse($date)->dayOfWeek)->where('is_active', true)
            ->whereHas('doctor', fn ($query) => $query->where('is_active', true)->whereHas('polyclinic', fn ($poli) => $poli->where('is_active', true)))
            ->where(fn ($query) => $query->whereNull('closed_date')->orWhere('closed_date', '!=', $date))
            ->withCount(['reservations as booked_count' => fn ($query) => $query->whereDate('reservation_date', $date)->where('status', '!=', 'cancelled')])
            ->orderBy('start_time')->get()->filter(fn ($schedule) => Carbon::parse($date.' '.$schedule->start_time)->gt(now()))
            ->map(function ($schedule) {
                $schedule->setAttribute('remaining', max(0, $schedule->max_quota - $schedule->booked_count));

                return $schedule;
            })->values();

        return Inertia::render('Booking', ['scheduleId' => (int) ($data['schedule'] ?? 0), 'date' => $date, 'schedules' => $schedules, 'today' => today()->toDateString(), 'maxDate' => today()->addDays(30)->toDateString()]);
    }

    public function store(ReservationRequest $request, BookingService $booking): RedirectResponse
    {
        if (! $request->user()->patientProfile) {
            return redirect()->route('patient.profile');
        }
        $data = $request->validated();
        $reservation = $booking->book($request->user()->patientProfile, $data['schedule_id'], $data['reservation_date'], $data['complaint']);

        return redirect()->route('reservations.show', $reservation)->with('success', 'Reservasi berhasil. Simpan tiket dan datang sebelum sesi dimulai.');
    }

    public function show(Request $request, Reservation $reservation): Response
    {
        $this->authorizeOwner($request, $reservation);
        $reservation->load(['patient.user', 'schedule.doctor.polyclinic', 'transaction']);
        $start = Carbon::parse($reservation->reservation_date->toDateString().' '.$reservation->schedule->start_time);
        $duration = $start->diffInMinutes(Carbon::parse($reservation->reservation_date->toDateString().' '.$reservation->schedule->end_time));
        $patientsAhead = Reservation::where('schedule_id', $reservation->schedule_id)
            ->whereDate('reservation_date', $reservation->reservation_date)
            ->where('queue_number', '<', $reservation->queue_number)
            ->where('status', '!=', 'cancelled')->count();

        return Inertia::render('Ticket', ['reservation' => $reservation, 'estimate' => $start->copy()->addMinutes((int) ($patientsAhead * $duration / $reservation->schedule->max_quota))->format('H:i'), 'canCancel' => $request->user()->role === 'patient' && $reservation->status === 'booked' && $start->gt(now())]);
    }

    public function destroy(Request $request, Reservation $reservation, BookingService $booking): RedirectResponse
    {
        $this->authorizeOwner($request, $reservation);
        $booking->cancel($reservation);

        return back()->with('success', 'Reservasi dibatalkan. Kuota telah dikembalikan.');
    }

    public function history(Request $request): Response
    {
        return Inertia::render('History', ['reservations' => Reservation::with(['schedule.doctor.polyclinic', 'transaction'])->where('patient_id', $request->user()->patientProfile->id ?? 0)->orderByDesc('reservation_date')->orderByDesc('id')->paginate(12)]);
    }

    public function qr(Request $request, Reservation $reservation, QrCode $qr): HttpResponse
    {
        $this->authorizeOwner($request, $reservation);

        return response($qr->svg($reservation->booking_code), 200, ['Content-Type' => 'image/svg+xml', 'Cache-Control' => 'private, no-store']);
    }

    private function authorizeOwner(Request $request, Reservation $reservation): void
    {
        abort_unless($request->user()->role === 'admin' || $reservation->patient_id === $request->user()->patientProfile?->id, 404);
    }
}
