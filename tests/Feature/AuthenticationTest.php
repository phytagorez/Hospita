<?php

use App\Models\PatientProfile;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;

test('public pages load and private pages redirect guests to login', function () {
    $this->get(route('home'))->assertOk();
    $this->get(route('schedules'))->assertOk();
    $this->get(route('login'))->assertOk();
    $this->get(route('dashboard'))->assertRedirect(route('login'));
    $this->get(route('admin.queue.index'))->assertRedirect(route('login'));
});

test('admin can log in and log out', function () {
    $admin = User::factory()->admin()->create();

    $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'password'])->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($admin);
    $this->post(route('logout'))->assertRedirect(route('home'));
    $this->assertGuest();
});

test('invalid credentials are rejected and attempts are throttled', function () {
    $admin = User::factory()->admin()->create();

    for ($i = 0; $i < 5; $i++) {
        $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'wrong'])->assertSessionHasErrors(['email' => 'Email atau kata sandi tidak sesuai.']);
    }
    $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'wrong'])->assertTooManyRequests();
    $this->assertGuest();
});

test('patient password login is restricted to local environments', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors(['email']);
    $this->assertGuest();
    $this->app['env'] = 'local';
    $this->withSession(['_token' => 'local-test-token'])->post(route('login.store'), ['email' => $user->email, 'password' => 'password', '_token' => 'local-test-token'])->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
});

test('patients cannot access administrative pages', function (string $route) {
    $this->actingAs(User::factory()->create())->get(route($route))->assertForbidden();
})->with(['admin.queue.index', 'admin.master.index', 'admin.master.schedules', 'admin.patients.index', 'admin.billing.index', 'admin.reports']);

test('patients cannot mutate master data or queue', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('admin.master.store', 'doctors'), [])->assertForbidden();
    $this->post(route('admin.walk-in'), [])->assertForbidden();
    $this->post(route('admin.queue.check-in'), [])->assertForbidden();
    $this->assertDatabaseCount('doctors', 0);
});

test('admin pages render with empty clinical data', function (string $route, string $component) {
    $this->actingAs(User::factory()->admin()->create())->get(route($route))->assertOk()->assertInertia(fn (Assert $page) => $page->component($component));
})->with([
    ['dashboard', 'Dashboard'], ['admin.queue.index', 'Queue'], ['admin.master.index', 'MasterData'],
    ['admin.master.schedules', 'ScheduleManagement'], ['admin.patients.index', 'Patients'],
    ['admin.billing.index', 'Billing'], ['admin.reports', 'Reports'],
]);

test('incomplete patients see onboarding while completed patients see their dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('patient.profile'));

    $patient = PatientProfile::factory()->create();
    $this->actingAs($patient->user)->get(route('dashboard'))->assertOk()->assertInertia(fn (Assert $page) => $page->component('Dashboard'));
});

test('Google without credentials or with invalid state returns a useful message', function () {
    config(['services.google.client_id' => null, 'services.google.client_secret' => null]);
    Http::preventStrayRequests();

    $this->get(route('google.redirect'))->assertRedirect(route('login'))->assertSessionHas('error');
    $this->get(route('google.callback', ['state' => 'invalid', 'code' => 'invalid']))->assertRedirect(route('login'))->assertSessionHas('error');
    Http::assertNothingSent();
    $this->assertGuest();
});

test('Google redirect includes state and PKCE challenge', function () {
    config(['services.google.client_id' => 'client', 'services.google.client_secret' => 'secret']);

    $response = $this->get(route('google.redirect'))->assertRedirect()->assertSessionHas('oauth_state')->assertSessionHas('oauth_verifier');
    expect($response->headers->get('Location'))->toContain('accounts.google.com', 'code_challenge=', 'code_challenge_method=S256');
});

test('verified Google identity creates a patient', function () {
    config(['services.google.client_id' => 'client', 'services.google.client_secret' => 'secret']);
    Http::preventStrayRequests();
    Http::fake([
        'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'token']),
        'https://openidconnect.googleapis.com/v1/userinfo' => Http::response(['sub' => 'google-123', 'email' => 'google@example.test', 'email_verified' => true, 'name' => 'Google Patient']),
    ]);

    $this->withSession(['oauth_state' => 'valid-state', 'oauth_verifier' => 'valid-verifier'])->get(route('google.callback', ['state' => 'valid-state', 'code' => 'valid-code']))->assertRedirect(route('dashboard'));
    $this->assertDatabaseHas('users', ['email' => 'google@example.test', 'role' => 'patient', 'google_id' => 'google-123']);
    $this->assertAuthenticated();
    Http::assertSentCount(2);
});

test('Google cannot link an identity to an existing internal account', function () {
    $admin = User::factory()->admin()->create();
    Http::preventStrayRequests();
    Http::fake([
        'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'token']),
        'https://openidconnect.googleapis.com/v1/userinfo' => Http::response(['sub' => 'foreign-google', 'email' => $admin->email, 'email_verified' => true]),
    ]);

    $this->withSession(['oauth_state' => 'state', 'oauth_verifier' => 'verifier'])->get(route('google.callback', ['state' => 'state', 'code' => 'code']))->assertRedirect(route('login'))->assertSessionHas('error');
    $this->assertGuest();
    $this->assertDatabaseHas('users', ['id' => $admin->id, 'google_id' => null]);
    Http::assertSentCount(2);
});

test('Google network failure returns to login without creating a user', function () {
    Http::preventStrayRequests();
    Http::fake(['https://oauth2.googleapis.com/token' => Http::failedConnection()]);

    $this->withSession(['oauth_state' => 'state', 'oauth_verifier' => 'verifier'])->get(route('google.callback', ['state' => 'state', 'code' => 'code']))->assertRedirect(route('login'))->assertSessionHas('error');
    $this->assertGuest();
    $this->assertDatabaseCount('users', 0);
    Http::assertSentCount(1);
});

test('malformed dates do not crash schedule or booking pages', function () {
    $this->get(route('schedules', ['date' => 'not-a-date']))->assertSessionHasErrors(['date']);
    $patient = PatientProfile::factory()->create();

    $this->actingAs($patient->user)->get(route('reservations.create', ['date' => 'not-a-date']))->assertSessionHasErrors(['date']);
});

test('patient pages render for a completed profile', function (string $route, string $component) {
    $patient = PatientProfile::factory()->create();

    $this->actingAs($patient->user)->get(route($route))->assertOk()->assertInertia(fn (Assert $page) => $page->component($component));
})->with([['patient.profile', 'Profile'], ['reservations.create', 'Booking'], ['patient.history', 'History']]);
