<?php

namespace Database\Factories;

use App\Models\DoctorSchedule;
use App\Models\PatientProfile;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Reservation> */
class ReservationFactory extends Factory
{
    public function definition(): array
    {
        return ['patient_id' => PatientProfile::factory(), 'schedule_id' => DoctorSchedule::factory(), 'booking_code' => 'KS'.Str::upper(Str::random(12)), 'reservation_date' => '2026-11-09', 'queue_number' => 1, 'complaint' => 'Pemeriksaan kesehatan rutin', 'booking_type' => 'online', 'status' => 'booked', 'admin_fee' => 25000];
    }
}
