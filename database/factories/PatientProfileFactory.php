<?php

namespace Database\Factories;

use App\Models\PatientProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PatientProfile> */
class PatientProfileFactory extends Factory
{
    public function definition(): array
    {
        return ['user_id' => User::factory(), 'no_rm' => 'RM-'.fake()->unique()->numerify('########'), 'nik' => fake()->unique()->numerify('################'), 'birth_date' => '2000-01-01', 'gender' => 'female', 'blood_type' => 'O', 'phone' => '081234567890', 'address' => fake()->address()];
    }
}
