<?php

use App\Models\Reservation;
use App\Models\User;

test('login rejects requests with missing or incorrect CSRF tokens', function (?string $token) {
    $admin = User::factory()->admin()->create();
    $this->app['env'] = 'local';

    $this->withSession(['_token' => 'valid-session-token'])->post(route('login.store'), [
        'email' => $admin->email, 'password' => 'password', '_token' => $token,
    ])->assertStatus(419)->assertSee('Sesi perlu diperbarui');
    $this->assertGuest();
})->with([null, 'incorrect-token']);

test('login accepts a matching form or header CSRF token', function (bool $header) {
    $admin = User::factory()->admin()->create();
    $this->app['env'] = 'local';
    $data = ['email' => $admin->email, 'password' => 'password'];
    if ($header) {
        $this->withHeader('X-CSRF-TOKEN', 'valid-session-token');
    } else {
        $data['_token'] = 'valid-session-token';
    }

    $this->withSession(['_token' => 'valid-session-token'])->post(route('login.store'), $data)->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($admin);
})->with([false, true]);

test('forged cancellation cannot change a reservation even for its authenticated owner', function () {
    $reservation = Reservation::factory()->create();
    $this->app['env'] = 'local';

    $this->actingAs($reservation->patient->user)->withSession(['_token' => 'valid-session-token'])
        ->delete(route('reservations.destroy', $reservation), ['_token' => 'incorrect-token'])
        ->assertStatus(419)->assertSee('name="_token"', false);
    $this->assertDatabaseHas('reservations', ['id' => $reservation->id, 'status' => 'booked']);
});

test('the initial response issues the XSRF cookie used by Inertia requests', function () {
    $this->get(route('home'))->assertOk()->assertCookie('XSRF-TOKEN');
});
