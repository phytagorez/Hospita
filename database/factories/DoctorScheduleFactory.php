<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DoctorSchedule> */
class DoctorScheduleFactory extends Factory
{
    public function definition(): array
    {
        return ['doctor_id' => Doctor::factory(), 'day_of_week' => 1, 'start_time' => '08:00', 'end_time' => '12:00', 'max_quota' => 20, 'is_active' => true];
    }
}
