<?php

namespace App\Http\Controllers;

use App\BookingService;
use App\Models\DoctorSchedule;
use App\Models\PatientProfile;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class QueueController extends Controller
{
    public function index(Request $request): Response
    {
        $data = $request->validate(['schedule' => ['nullable', 'integer', 'exists:doctor_schedules,id']]);

        return Inertia::render('Queue', [
            'reservations' => Reservation::with(['patient.user', 'schedule.doctor.polyclinic'])
                ->whereDate('reservation_date', today())->when($data['schedule'] ?? null, fn ($query, $id) => $query->where('schedule_id', $id))
                ->orderBy('schedule_id')->orderBy('queue_number')->get(),
            'schedules' => DoctorSchedule::with('doctor.polyclinic')->where('day_of_week', today()->dayOfWeek)->orderBy('start_time')->get(),
            'selectedSchedule' => $data['schedule'] ?? '', 'today' => today()->toDateString(),
        ]);
    }

    public function checkIn(Request $request): RedirectResponse
    {
        $data = $request->validate(['booking_code' => ['required', 'string', 'max:16']]);
        $reservation = Reservation::where('booking_code', Str::upper(trim($data['booking_code'])))->first();
        if (! $reservation) {
            throw ValidationException::withMessages(['booking_code' => 'Kode booking tidak ditemukan.']);
        }

        return $this->transition($reservation, 'waiting');
    }

    public function update(Request $request, Reservation $reservation): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(['waiting', 'examining', 'completed', 'skipped'])]]);

        return $this->transition($reservation, $data['status']);
    }

    public function walkIn(Request $request, BookingService $booking): RedirectResponse
    {
        $data = $request->validate([
            'patient_identifier' => ['nullable', 'string', 'max:50'],
            'name' => ['required_without:patient_identifier', 'nullable', 'string', 'max:150'],
            'nik' => ['required_without:patient_identifier', 'nullable', 'digits:16', 'unique:patient_profiles,nik'],
            'birth_date' => ['required_without:patient_identifier', 'nullable', 'date', 'before_or_equal:today', 'after:1900-01-01'],
            'gender' => ['required_without:patient_identifier', 'nullable', Rule::in(['male', 'female'])],
            'phone' => ['required_without:patient_identifier', 'nullable', 'regex:/^\\+?[0-9]{9,15}$/'],
            'address' => ['required_without:patient_identifier', 'nullable', 'string', 'min:5', 'max:1000'],
            'schedule_id' => ['required', 'integer', 'exists:doctor_schedules,id'],
            'complaint' => ['required', 'string', 'min:5', 'max:2000'],
        ]);
        DB::transaction(function () use ($data, $booking) {
            if (! empty($data['patient_identifier'])) {
                $patient = PatientProfile::where('no_rm', $data['patient_identifier'])->orWhere('nik', $data['patient_identifier'])->first();
                if (! $patient) {
                    throw ValidationException::withMessages(['patient_identifier' => 'No. RM atau NIK tidak ditemukan.']);
                }
            } else {
                $user = User::create(['name' => $data['name'], 'email' => Str::uuid().'@walkin.invalid', 'password' => Str::random(64), 'role' => 'patient']);
                $patient = PatientProfile::create([
                    'user_id' => $user->id, 'no_rm' => 'RM-'.Str::upper((string) Str::ulid()),
                    ...array_intersect_key($data, array_flip(['nik', 'birth_date', 'gender', 'phone', 'address'])),
                ]);
            }
            $booking->book($patient, $data['schedule_id'], today()->toDateString(), $data['complaint'], 'walk_in');
        }, 5);

        return back()->with('success', 'Pasien walk-in berhasil masuk antrean.');
    }

    private function transition(Reservation $reservation, string $status): RedirectResponse
    {
        DB::transaction(function () use ($reservation, $status) {
            DoctorSchedule::whereKey($reservation->schedule_id)->lockForUpdate()->firstOrFail();
            $reservation = Reservation::lockForUpdate()->findOrFail($reservation->id);
            $allowed = ['booked' => ['waiting'], 'waiting' => ['examining', 'skipped'], 'examining' => ['completed'], 'skipped' => ['waiting']];
            if (! $reservation->reservation_date->isToday() || ! in_array($status, $allowed[$reservation->status] ?? [], true)) {
                throw ValidationException::withMessages(['status' => 'Perubahan status tidak valid atau kunjungan bukan hari ini.']);
            }
            if ($status === 'examining' && Reservation::where('schedule_id', $reservation->schedule_id)->whereDate('reservation_date', today())->where('status', 'examining')->exists()) {
                throw ValidationException::withMessages(['status' => 'Selesaikan pasien yang sedang diperiksa pada sesi ini terlebih dahulu.']);
            }
            $reservation->update(['status' => $status, ...($reservation->status === 'booked' ? ['checked_in_at' => now()] : [])]);
        }, 5);

        return back()->with('success', 'Status antrean berhasil diperbarui.');
    }
}
