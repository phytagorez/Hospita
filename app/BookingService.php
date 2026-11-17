<?php

namespace App;

use App\Models\DoctorSchedule;
use App\Models\PatientProfile;
use App\Models\Reservation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function book(PatientProfile $patient, int $scheduleId, string $date, string $complaint, string $type = 'online'): Reservation
    {
        return DB::transaction(function () use ($patient, $scheduleId, $date, $complaint, $type) {
            PatientProfile::whereKey($patient->id)->lockForUpdate()->firstOrFail();
            $schedule = DoctorSchedule::with('doctor.polyclinic')->lockForUpdate()->findOrFail($scheduleId);
            $visit = Carbon::parse($date);
            $start = Carbon::parse($date.' '.$schedule->start_time);
            $end = Carbon::parse($date.' '.$schedule->end_time);
            if (! $schedule->is_active || ! $schedule->doctor->is_active || ! $schedule->doctor->polyclinic->is_active
                || $schedule->closed_date?->toDateString() === $date || $visit->dayOfWeek !== $schedule->day_of_week
                || $visit->lt(today()) || $visit->gt(today()->addDays(30)) || ($type === 'online' ? $start->lte(now()) : $end->lte(now()))) {
                throw ValidationException::withMessages(['schedule_id' => 'Jadwal tidak tersedia atau sesi telah ditutup.']);
            }
            $duplicate = $patient->reservations()->whereDate('reservation_date', $date)->whereIn('status', Reservation::ACTIVE)
                ->whereHas('schedule.doctor', fn ($query) => $query->where('polyclinic_id', $schedule->doctor->polyclinic_id))->exists();
            if ($duplicate) {
                throw ValidationException::withMessages(['schedule_id' => 'Anda sudah memiliki reservasi di poli ini pada tanggal yang sama.']);
            }
            $session = Reservation::where('schedule_id', $scheduleId)->whereDate('reservation_date', $date);
            if ((clone $session)->where('status', '!=', 'cancelled')->count() >= $schedule->max_quota) {
                throw ValidationException::withMessages(['schedule_id' => 'Kuota sesi sudah penuh. Silakan pilih jadwal lain.']);
            }

            return Reservation::create([
                'patient_id' => $patient->id, 'schedule_id' => $scheduleId,
                'booking_code' => 'KS'.Str::upper(Str::random(12)),
                'reservation_date' => $date, 'queue_number' => ((clone $session)->max('queue_number') ?? 0) + 1,
                'complaint' => $complaint, 'booking_type' => $type,
                'status' => $type === 'walk_in' ? 'waiting' : 'booked',
                'checked_in_at' => $type === 'walk_in' ? now() : null,
                'admin_fee' => $schedule->doctor->polyclinic->base_admin_fee,
            ]);
        }, 5);
    }

    public function cancel(Reservation $reservation): void
    {
        DB::transaction(function () use ($reservation) {
            DoctorSchedule::whereKey($reservation->schedule_id)->lockForUpdate()->firstOrFail();
            $reservation = Reservation::with('schedule')->lockForUpdate()->findOrFail($reservation->id);
            if ($reservation->status !== 'booked' || Carbon::parse($reservation->reservation_date->toDateString().' '.$reservation->schedule->start_time)->lte(now())) {
                throw ValidationException::withMessages(['reservation' => 'Reservasi hanya dapat dibatalkan sebelum sesi dimulai dan sebelum check-in.']);
            }
            $reservation->update(['status' => 'cancelled']);
        }, 5);
    }
}
