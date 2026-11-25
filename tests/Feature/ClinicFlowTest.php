<?php

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\PatientProfile;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-11-09 07:00:00', 'Asia/Jakarta'));
});

test('patient books a session with a fee snapshot and cannot override protected fields', function () {
    $patient = PatientProfile::factory()->create();
    $schedule = DoctorSchedule::factory()->create();

    $this->actingAs($patient->user)->post(route('reservations.store'), ['schedule_id' => $schedule->id, 'reservation_date' => '2026-11-09', 'complaint' => 'Demam selama dua hari', 'status' => 'completed', 'admin_fee' => 0])
        ->assertRedirect(route('reservations.show', Reservation::first()));
    $this->assertDatabaseHas('reservations', ['patient_id' => $patient->id, 'queue_number' => 1, 'status' => 'booked', 'admin_fee' => 25000]);
});

test('quota exhaustion rejects a second patient', function () {
    $schedule = DoctorSchedule::factory()->create(['max_quota' => 1]);
    Reservation::factory()->for($schedule, 'schedule')->create();
    $patient = PatientProfile::factory()->create();

    $this->actingAs($patient->user)->post(route('reservations.store'), ['schedule_id' => $schedule->id, 'reservation_date' => '2026-11-09', 'complaint' => 'Keluhan pasien berikutnya'])
        ->assertSessionHasErrors(['schedule_id' => 'Kuota sesi sudah penuh. Silakan pilih jadwal lain.']);
    $this->assertDatabaseCount('reservations', 1);
});

test('a patient cannot book two doctors in the same polyclinic and date', function () {
    $patient = PatientProfile::factory()->create();
    $schedule = DoctorSchedule::factory()->create();
    Reservation::factory()->for($patient, 'patient')->for($schedule, 'schedule')->create();
    $doctor = Doctor::factory()->create(['polyclinic_id' => $schedule->doctor->polyclinic_id]);
    $other = DoctorSchedule::factory()->for($doctor, 'doctor')->create();

    $this->actingAs($patient->user)->post(route('reservations.store'), ['schedule_id' => $other->id, 'reservation_date' => '2026-11-09', 'complaint' => 'Pemeriksaan tambahan'])->assertSessionHasErrors(['schedule_id']);
    $this->assertDatabaseCount('reservations', 1);
});

test('unavailable sessions cannot be booked', function (array $attributes, string $date) {
    $patient = PatientProfile::factory()->create();
    $schedule = DoctorSchedule::factory()->create($attributes);

    $this->actingAs($patient->user)->post(route('reservations.store'), ['schedule_id' => $schedule->id, 'reservation_date' => $date, 'complaint' => 'Pemeriksaan kesehatan'])->assertSessionHasErrors(['schedule_id']);
    $this->assertDatabaseCount('reservations', 0);
})->with([
    'inactive' => [['is_active' => false], '2026-11-09'],
    'closed' => [['closed_date' => '2026-11-09'], '2026-11-09'],
    'wrong weekday' => [[], '2026-11-10'],
    'session started' => [['start_time' => '06:00'], '2026-11-09'],
]);

test('inactive doctors and polyclinics cannot receive bookings', function (string $entity) {
    $patient = PatientProfile::factory()->create();
    $schedule = DoctorSchedule::factory()->create();
    ($entity === 'doctor' ? $schedule->doctor : $schedule->doctor->polyclinic)->update(['is_active' => false]);

    $this->actingAs($patient->user)->post(route('reservations.store'), ['schedule_id' => $schedule->id, 'reservation_date' => '2026-11-09', 'complaint' => 'Pemeriksaan kesehatan'])->assertSessionHasErrors(['schedule_id']);
    $this->assertDatabaseCount('reservations', 0);
})->with(['doctor', 'polyclinic']);

test('cancellation frees quota while preserving queue numbering', function () {
    $schedule = DoctorSchedule::factory()->create(['max_quota' => 1]);
    $first = Reservation::factory()->for($schedule, 'schedule')->create();

    $this->actingAs($first->patient->user)->delete(route('reservations.destroy', $first))->assertRedirect();
    $this->assertDatabaseHas('reservations', ['id' => $first->id, 'status' => 'cancelled']);

    $patient = PatientProfile::factory()->create();
    $this->actingAs($patient->user)->post(route('reservations.store'), ['schedule_id' => $schedule->id, 'reservation_date' => '2026-11-09', 'complaint' => 'Pemeriksaan pengganti'])->assertSessionHasNoErrors();
    $this->assertDatabaseHas('reservations', ['patient_id' => $patient->id, 'queue_number' => 2]);
    $this->get(route('reservations.show', Reservation::where('patient_id', $patient->id)->first()))->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('estimate', '08:00'));
});

test('cancellation after check in or at session start is rejected', function (string $status, string $time) {
    $reservation = Reservation::factory()->create(['status' => $status]);
    $this->travelTo(Carbon::parse($time, 'Asia/Jakarta'));

    $this->actingAs($reservation->patient->user)->delete(route('reservations.destroy', $reservation))->assertSessionHasErrors(['reservation']);
    $this->assertDatabaseHas('reservations', ['id' => $reservation->id, 'status' => $status]);
})->with([['waiting', '2026-11-09 07:00:00'], ['booked', '2026-11-09 08:00:00']]);

test('patients cannot view cancel or download another patient ticket', function (string $action) {
    $reservation = Reservation::factory()->create();
    $patient = PatientProfile::factory()->create();
    $this->actingAs($patient->user);

    $response = match ($action) {
        'show' => $this->get(route('reservations.show', $reservation)),
        'qr' => $this->get(route('reservations.qr', $reservation)),
        'receipt' => $this->get(route('reservations.receipt', $reservation)),
        'destroy' => $this->delete(route('reservations.destroy', $reservation)),
    };
    $response->assertNotFound();
    $this->assertDatabaseHas('reservations', ['id' => $reservation->id, 'status' => 'booked']);
})->with(['show', 'qr', 'receipt', 'destroy']);

test('check in calling skipping and completion follow the queue state machine', function () {
    $reservation = Reservation::factory()->create();
    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('admin.queue.check-in'), ['booking_code' => $reservation->booking_code])->assertSessionHasNoErrors();
    $this->assertDatabaseHas('reservations', ['id' => $reservation->id, 'status' => 'waiting']);
    $this->patch(route('admin.queue.update', $reservation), ['status' => 'skipped'])->assertSessionHasNoErrors();
    $this->patch(route('admin.queue.update', $reservation), ['status' => 'waiting'])->assertSessionHasNoErrors();
    $this->patch(route('admin.queue.update', $reservation), ['status' => 'examining'])->assertSessionHasNoErrors();
    $this->patch(route('admin.queue.update', $reservation), ['status' => 'completed'])->assertSessionHasNoErrors();
    $this->assertDatabaseHas('reservations', ['id' => $reservation->id, 'status' => 'completed']);
});

test('invalid transitions and visits on another date are rejected', function (string $status, string $date) {
    $reservation = Reservation::factory()->create(['status' => $status, 'reservation_date' => $date]);

    $this->actingAs(User::factory()->admin()->create())->patch(route('admin.queue.update', $reservation), ['status' => 'completed'])->assertSessionHasErrors(['status']);
    $this->assertDatabaseHas('reservations', ['id' => $reservation->id, 'status' => $status]);
})->with([['booked', '2026-11-09'], ['waiting', '2026-11-09'], ['examining', '2026-11-10'], ['cancelled', '2026-11-09']]);

test('only one patient can be examined in a session at once', function () {
    $schedule = DoctorSchedule::factory()->create();
    Reservation::factory()->for($schedule, 'schedule')->create(['status' => 'examining']);
    $waiting = Reservation::factory()->for($schedule, 'schedule')->create(['status' => 'waiting', 'queue_number' => 2]);

    $this->actingAs(User::factory()->admin()->create())->patch(route('admin.queue.update', $waiting), ['status' => 'examining'])->assertSessionHasErrors(['status']);
    $this->assertDatabaseHas('reservations', ['id' => $waiting->id, 'status' => 'waiting']);
});

test('walk in and online patients use the same queue', function () {
    $schedule = DoctorSchedule::factory()->create();
    Reservation::factory()->for($schedule, 'schedule')->create();
    $patient = PatientProfile::factory()->create();

    $this->actingAs(User::factory()->admin()->create())->post(route('admin.walk-in'), ['patient_identifier' => $patient->no_rm, 'schedule_id' => $schedule->id, 'complaint' => 'Pasien datang langsung'])->assertSessionHasNoErrors();
    $this->assertDatabaseHas('reservations', ['patient_id' => $patient->id, 'queue_number' => 2, 'booking_type' => 'walk_in', 'status' => 'waiting']);
});

test('new walk in registration rolls back if the session is full', function () {
    $schedule = DoctorSchedule::factory()->create(['max_quota' => 1]);
    Reservation::factory()->for($schedule, 'schedule')->create();
    $admin = User::factory()->admin()->create();
    $users = User::count();

    $this->actingAs($admin)->post(route('admin.walk-in'), ['name' => 'Pasien Baru', 'nik' => '1234567890123456', 'birth_date' => '1999-01-01', 'gender' => 'male', 'phone' => '081234567890', 'address' => 'Jalan Mawar nomor 1', 'schedule_id' => $schedule->id, 'complaint' => 'Pemeriksaan kesehatan'])->assertSessionHasErrors(['schedule_id']);
    $this->assertDatabaseCount('users', $users);
    $this->assertDatabaseMissing('patient_profiles', ['nik' => '1234567890123456']);
});

test('ticket and QR load for their owner', function () {
    $reservation = Reservation::factory()->create();
    $this->actingAs($reservation->patient->user);

    $this->get(route('reservations.show', $reservation))->assertOk()->assertInertia(fn (Assert $page) => $page->component('Ticket')->where('canCancel', true)->where('estimate', '08:00'));
    $this->get(route('reservations.qr', $reservation))->assertOk()->assertHeader('Content-Type', 'image/svg+xml')->assertSee('<svg', false);
});

test('admins can view the reservation record without receiving a patient check-in QR', function () {
    $reservation = Reservation::factory()->create();
    $this->actingAs(User::factory()->admin()->create());

    $this->get(route('reservations.show', $reservation))->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('ReservationRecord')
            ->where('record.id', $reservation->id)->missing('record.booking_code')->missing('reservation'));
    $this->get(route('reservations.qr', $reservation))->assertNotFound();
});
