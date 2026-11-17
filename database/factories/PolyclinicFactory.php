<?php

namespace Database\Factories;

use App\Models\Polyclinic;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Polyclinic> */
class PolyclinicFactory extends Factory
{
    public function definition(): array
    {
        return ['name' => 'Poli '.fake()->unique()->word(), 'code' => fake()->unique()->lexify('??????'), 'room_location' => 'Ruang 01', 'base_admin_fee' => 25000, 'is_active' => true];
    }
}
