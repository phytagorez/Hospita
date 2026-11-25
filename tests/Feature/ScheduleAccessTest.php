<?php

use App\Models\DoctorSchedule;
use App\Models\PatientProfile;
use App\Models\Polyclinic;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-11-09 07:00:00', 'Asia/Jakarta'));
});

test('the introduction exposes active services without exposing practice schedules', function () {
    $schedule = DoctorSchedule::factory()->create();
    Polyclinic::factory()->create(['is_active' => false]);

    $this->get(route('home'))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Welcome')->has('polyclinics', 1)
        ->where('polyclinics.0.id', $schedule->doctor->polyclinic_id)
        ->missing('schedules')->missing('filters')->missing('activeReservation'));
});

test('guests must log in before viewing any schedule information', function () {
    $this->get(route('schedules', ['date' => '2026-11-09']))->assertRedirect(route('login'));
    $this->getJson(route('schedules'))->assertUnauthorized();
});

test('login returns users to the schedule they requested', function () {
    $admin = User::factory()->admin()->create();
    $url = route('schedules', ['date' => '2026-11-09']);
    $this->get($url)->assertRedirect(route('login'));

    $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'password'])->assertRedirect($url);
    $this->assertAuthenticatedAs($admin);
});

test('available sessions appear before started full and closed sessions', function () {
    $started = DoctorSchedule::factory()->create(['start_time' => '06:00', 'end_time' => '07:00']);
    $full = DoctorSchedule::factory()->create(['start_time' => '08:00', 'max_quota' => 1]);
    Reservation::factory()->for($full, 'schedule')->create();
    $closed = DoctorSchedule::factory()->create(['start_time' => '09:00', 'closed_date' => '2026-11-09']);
    $free = DoctorSchedule::factory()->create(['start_time' => '10:00']);
    $patient = PatientProfile::factory()->create();

    $this->actingAs($patient->user)->get(route('schedules'))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->has('schedules', 4)->where('schedules.0.id', $free->id)->where('schedules.0.available', true)
        ->where('schedules.1.id', $started->id)->where('schedules.1.unavailable_reason', 'Sesi sudah dimulai')
        ->where('schedules.2.id', $full->id)->where('schedules.2.unavailable_reason', 'Kuota penuh')
        ->where('schedules.3.id', $closed->id)->where('schedules.3.unavailable_reason', 'Jadwal ditutup'));
});

test('pending visits prevent a second booking across dates and polyclinics', function (string $status, string $date) {
    $patient = PatientProfile::factory()->create();
    Reservation::factory()->for($patient, 'patient')->create(['status' => $status, 'reservation_date' => $date]);
    $schedule = DoctorSchedule::factory()->create();

    $this->actingAs($patient->user)->post(route('reservations.store'), ['schedule_id' => $schedule->id, 'reservation_date' => '2026-11-09', 'complaint' => 'Pemeriksaan berikutnya'])
        ->assertSessionHasErrors(['schedule_id' => 'Anda masih memiliki satu janji aktif. Selesaikan atau batalkan janji tersebut sebelum membuat janji baru.']);
    $this->assertDatabaseCount('reservations', 1);
})->with([
    'booked today' => ['booked', '2026-11-09'],
    'booked tomorrow' => ['booked', '2026-11-10'],
    'waiting' => ['waiting', '2026-11-09'],
    'examining' => ['examining', '2026-11-09'],
    'skipped' => ['skipped', '2026-11-09'],
]);

test('completed cancelled and past visits allow a new booking', function (string $status, string $date) {
    $patient = PatientProfile::factory()->create();
    $schedule = DoctorSchedule::factory()->create();
    Reservation::factory()->for($patient, 'patient')->for($schedule, 'schedule')->create(['status' => $status, 'reservation_date' => $date]);

    $this->actingAs($patient->user)->post(route('reservations.store'), ['schedule_id' => $schedule->id, 'reservation_date' => '2026-11-09', 'complaint' => 'Pemeriksaan berikutnya'])->assertSessionHasNoErrors();
    $this->assertDatabaseCount('reservations', 2);
})->with([['completed', '2026-11-09'], ['cancelled', '2026-11-09'], ['booked', '2026-11-08']]);

test('an active appointment is visible to its owner and redirects attempts to open another booking form', function () {
    $reservation = Reservation::factory()->create();
    $this->actingAs($reservation->patient->user);

    $this->get(route('schedules'))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->where('activeReservation.id', $reservation->id)->where('activeReservation.booking_code', $reservation->booking_code));
    $this->get(route('reservations.create'))->assertRedirect(route('reservations.show', $reservation))->assertSessionHas('error');
});

test('another patients active visit does not block booking a session with remaining capacity', function () {
    $schedule = DoctorSchedule::factory()->create();
    Reservation::factory()->for($schedule, 'schedule')->create();
    $patient = PatientProfile::factory()->create();

    $this->actingAs($patient->user)->get(route('schedules'))->assertOk()->assertInertia(fn (Assert $page) => $page->where('activeReservation', null));
    $this->post(route('reservations.store'), ['schedule_id' => $schedule->id, 'reservation_date' => '2026-11-09', 'complaint' => 'Pemeriksaan kesehatan'])->assertSessionHasNoErrors();
    $this->assertDatabaseHas('reservations', ['patient_id' => $patient->id, 'queue_number' => 2]);
});

test('walk in registration cannot bypass a patients existing active appointment', function () {
    $reservation = Reservation::factory()->create();
    $schedule = DoctorSchedule::factory()->create();

    $this->actingAs(User::factory()->admin()->create())->post(route('admin.walk-in'), [
        'patient_identifier' => $reservation->patient->no_rm, 'schedule_id' => $schedule->id, 'complaint' => 'Datang ke klinik langsung',
    ])->assertSessionHasErrors(['schedule_id']);
    $this->assertDatabaseCount('reservations', 1);
});

test('booking keeps the chosen date and only offers sessions that still have seats', function () {
    $date = '2026-11-10';
    $morning = DoctorSchedule::factory()->create(['day_of_week' => 2]);
    $afternoon = DoctorSchedule::factory()->for($morning->doctor)->create([
        'day_of_week' => 2, 'start_time' => '16:00', 'end_time' => '20:00',
    ]);
    $full = DoctorSchedule::factory()->create(['day_of_week' => 2, 'max_quota' => 1]);
    Reservation::factory()->for($full, 'schedule')->create(['reservation_date' => $date]);
    DoctorSchedule::factory()->create(['day_of_week' => 2, 'closed_date' => $date]);
    $patient = PatientProfile::factory()->create();

    $this->actingAs($patient->user)->get(route('reservations.create', ['date' => $date, 'schedule' => $afternoon->id]))
        ->assertOk()->assertInertia(fn (Assert $page) => $page->component('Booking')
        ->where('date', $date)->where('scheduleId', $afternoon->id)->has('schedules', 2)
        ->where('schedules.0.id', $morning->id)->where('schedules.1.id', $afternoon->id));

    $this->get(route('reservations.create', ['date' => $date, 'schedule' => $full->id]))
        ->assertOk()->assertInertia(fn (Assert $page) => $page->where('date', $date)->where('scheduleId', 0));
});

test('visitors can open the service policies without an account', function () {
    $this->get(route('policies'))->assertOk()->assertInertia(fn (Assert $page) => $page->component('Policies'));
});
