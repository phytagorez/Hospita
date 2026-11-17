<?php

use App\Models\Reservation;
use App\Models\Transaction;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('cashier computes totals from stored admin fee and prevents double payment', function () {
    $reservation = Reservation::factory()->create(['status' => 'completed', 'admin_fee' => 25000]);
    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('admin.billing.store', $reservation), ['consultation_fee' => 75000, 'payment_method' => 'qris', 'admin_fee' => 0, 'total_amount' => 1])->assertSessionHasNoErrors();
    $this->assertDatabaseHas('transactions', ['reservation_id' => $reservation->id, 'admin_fee' => 25000, 'consultation_fee' => 75000, 'total_amount' => 100000, 'payment_method' => 'qris']);
    $this->post(route('admin.billing.store', $reservation), ['consultation_fee' => 75000, 'payment_method' => 'cash'])->assertSessionHasErrors(['payment']);
    $this->assertDatabaseCount('transactions', 1);
});

test('cashier rejects unfinished examinations and invalid payment values', function () {
    $reservation = Reservation::factory()->create();
    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('admin.billing.store', $reservation), ['consultation_fee' => 75000, 'payment_method' => 'cash'])->assertSessionHasErrors(['payment']);
    $this->post(route('admin.billing.store', $reservation), ['consultation_fee' => -1, 'payment_method' => 'invalid'])->assertSessionHasErrors(['consultation_fee', 'payment_method']);
    $this->assertDatabaseCount('transactions', 0);
});

test('paid receipt is available to its owner', function () {
    $transaction = Transaction::factory()->create();
    $reservation = $transaction->reservation;

    $this->actingAs($reservation->patient->user)->get(route('reservations.receipt', $reservation))->assertOk()->assertInertia(fn (Assert $page) => $page->component('Receipt')->where('reservation.transaction.total_amount', 100000));
});

test('reports summarize visits and revenues and export valid containers', function () {
    Transaction::factory()->create();
    $this->actingAs(User::factory()->admin()->create());
    $filters = ['from' => '2026-11-01', 'to' => '2026-11-30'];

    $this->get(route('admin.reports', $filters))->assertOk()->assertInertia(fn (Assert $page) => $page->component('Reports')->where('reservations.total', 1)->where('summary.0.revenue', 100000));
    $pdf = $this->get(route('admin.reports', [...$filters, 'format' => 'pdf']))->assertOk()->assertHeader('Content-Type', 'application/pdf');
    expect($pdf->getContent())->toStartWith('%PDF-1.4')->toContain('100.000');
    $xlsx = $this->get(route('admin.reports', [...$filters, 'format' => 'xlsx']))->assertOk()->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    expect($xlsx->getContent())->toStartWith('PK');
});

test('reports filter out visits outside the selected dates and reject inverted intervals', function () {
    Transaction::factory()->create();
    $this->actingAs(User::factory()->admin()->create());

    $this->get(route('admin.reports', ['from' => '2026-01-01', 'to' => '2026-01-02']))->assertOk()->assertInertia(fn (Assert $page) => $page->where('reservations.total', 0));
    $this->get(route('admin.reports', ['from' => '2026-11-30', 'to' => '2026-11-01']))->assertSessionHasErrors(['to']);
});

test('patients cannot create payments for their visits', function () {
    $reservation = Reservation::factory()->create(['status' => 'completed']);

    $this->actingAs($reservation->patient->user)->post(route('admin.billing.store', $reservation), ['consultation_fee' => 0, 'payment_method' => 'cash'])->assertForbidden();
    $this->assertDatabaseCount('transactions', 0);
});
