<?php

namespace App\Http\Controllers;

use App\Models\DoctorSchedule;
use App\Models\Polyclinic;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClinicController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Welcome', ['polyclinics' => Polyclinic::where('is_active', true)->orderBy('name')->get()]);
    }

    public function schedules(Request $request): Response
    {
        $activeReservation = $request->user()->patientProfile?->reservations()->pending()->orderBy('reservation_date')->first();

        return Inertia::render('Schedules', [
            'polyclinics' => Polyclinic::where('is_active', true)->orderBy('name')->get(),
            'activeReservation' => $activeReservation?->only(['id', 'booking_code', 'reservation_date']),
            ...$this->scheduleData($request),
        ]);
    }

    public function dashboard(Request $request): Response|RedirectResponse
    {
        if ($request->user()->role === 'patient' && ! $request->user()->patientProfile) {
            return redirect()->route('patient.profile');
        }
        $admin = $request->user()->role === 'admin';
        $query = Reservation::with(['patient.user', 'schedule.doctor.polyclinic', 'transaction']);
        if (! $admin) {
            $query->where('patient_id', $request->user()->patientProfile->id);
        }
        $today = (clone $query)->whereDate('reservation_date', today())->get();
        $week = collect(range(6, 0))->map(function (int $days) {
            $date = today()->subDays($days);

            return ['date' => $date->translatedFormat('D'), 'count' => Reservation::whereDate('reservation_date', $date)->count()];
        });

        return Inertia::render('Dashboard', [
            'stats' => ['total' => $today->count(), 'online' => $today->where('booking_type', 'online')->count(),
                'waiting' => $today->whereIn('status', ['waiting', 'examining'])->count(),
                'walk_in' => $today->where('booking_type', 'walk_in')->count(), 'completed' => $today->where('status', 'completed')->count()],
            'reservations' => (clone $query)->whereDate('reservation_date', '>=', today())->whereIn('status', ['booked', 'waiting', 'examining', 'skipped'])->orderBy('reservation_date')->orderBy('queue_number')->limit(20)->get(),
            'monitor' => $this->monitor(), 'week' => $admin ? $week : [],
            'adminOverview' => $admin ? [
                'schedules' => DoctorSchedule::with('doctor.polyclinic')
                    ->where('day_of_week', today()->dayOfWeek)->where('is_active', true)
                    ->whereHas('doctor', fn ($doctor) => $doctor->where('is_active', true)
                        ->whereHas('polyclinic', fn ($poli) => $poli->where('is_active', true)))
                    ->orderBy('start_time')->get(),
                'services' => $today->groupBy('schedule.doctor.polyclinic.id')
                    ->map(fn ($items) => ['name' => $items->first()->schedule->doctor->polyclinic->name, 'count' => $items->count()])
                    ->sortByDesc('count')->values(),
                'date' => today()->translatedFormat('l, d F Y'),
            ] : null,
        ]);
    }

    public function monitor(): mixed
    {
        return Reservation::with('schedule.doctor.polyclinic')->whereDate('reservation_date', today())->where('status', 'examining')
            ->get()->map(fn (Reservation $item) => ['id' => $item->id, 'number' => $item->queue_number, 'doctor' => $item->schedule->doctor->name, 'polyclinic' => $item->schedule->doctor->polyclinic->name, 'room' => $item->schedule->doctor->polyclinic->room_location]);
    }

    /** @return array<string, mixed> */
    private function scheduleData(Request $request): array
    {
        $data = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today', 'before_or_equal:'.today()->addDays(30)->toDateString()],
            'polyclinic' => ['nullable', 'integer', 'exists:polyclinics,id'],
        ]);
        $date = $data['date'] ?? today()->toDateString();
        $schedules = DoctorSchedule::patientSessions($date)
            ->when($data['polyclinic'] ?? null, fn ($query, $id) => $query
                ->whereHas('doctor', fn ($doctor) => $doctor->where('polyclinic_id', $id)))
            ->get()->map(fn (DoctorSchedule $schedule) => $schedule->availabilityOn($date))
            ->sortByDesc('available')->values();

        return ['schedules' => $schedules, 'filters' => ['date' => $date, 'polyclinic' => $data['polyclinic'] ?? ''], 'today' => today()->toDateString(), 'maxDate' => today()->addDays(30)->toDateString()];
    }
}
