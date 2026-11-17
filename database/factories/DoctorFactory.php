<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Polyclinic;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Doctor> */
class DoctorFactory extends Factory
{
    public function definition(): array
    {
        return ['polyclinic_id' => Polyclinic::factory(), 'name' => 'dr. '.fake()->name(), 'sip_number' => fake()->unique()->numerify('SIP-########'), 'is_active' => true];
    }
}
