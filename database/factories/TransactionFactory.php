<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Transaction> */
class TransactionFactory extends Factory
{
    public function definition(): array
    {
        return ['reservation_id' => Reservation::factory()->state(['status' => 'completed']), 'admin_fee' => 25000, 'consultation_fee' => 75000, 'total_amount' => 100000, 'payment_method' => 'cash', 'payment_status' => 'paid', 'paid_at' => now()];
    }
}
