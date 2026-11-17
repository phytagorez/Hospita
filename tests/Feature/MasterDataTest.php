<?php

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Polyclinic;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Carbon;

test('admin can create update and delete unused master records', function () {
    $this->actingAs(User::factory()->admin()->create());
    $data = ['name' => 'Kulit', 'code' => 'KL', 'room_location' => 'Ruang 05', 'base_admin_fee' => 30000, 'is_active' => true];

    $this->post(route('admin.master.store', 'polyclinics'), $data)->assertSessionHasNoErrors();
    $poli = Polyclinic::first();
    $this->assertDatabaseHas('polyclinics', ['code' => 'KL', 'base_admin_fee' => 30000]);
    $this->put(route('admin.master.update', ['polyclinics', $poli->id]), [...$data, 'name' => 'Kulit dan Kelamin'])->assertSessionHasNoErrors();
    $this->assertDatabaseHas('polyclinics', ['id' => $poli->id, 'name' => 'Kulit dan Kelamin']);
    $this->delete(route('admin.master.destroy', ['polyclinics', $poli->id]))->assertSessionHasNoErrors();
    $this->assertModelMissing($poli);
});

test('master validation rejects duplicate codes and invalid fees', function () {
    $poli = Polyclinic::factory()->create();

    $this->actingAs(User::factory()->admin()->create())->post(route('admin.master.store', 'polyclinics'), ['name' => 'Poli', 'code' => $poli->code, 'room_location' => 'Ruang', 'base_admin_fee' => -1, 'is_active' => true])->assertSessionHasErrors(['code', 'base_admin_fee']);
    $this->assertDatabaseCount('polyclinics', 1);
});

test('admin can create update and delete unused doctors', function () {
    $poli = Polyclinic::factory()->create();
    $data = ['name' => 'dr. Sinta', 'sip_number' => 'SIP-1234', 'polyclinic_id' => $poli->id, 'is_active' => true, 'photo' => null];
    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('admin.master.store', 'doctors'), $data)->assertSessionHasNoErrors();
    $doctor = Doctor::first();
    $this->put(route('admin.master.update', ['doctors', $doctor->id]), [...$data, 'is_active' => false])->assertSessionHasNoErrors();
    $this->assertDatabaseHas('doctors', ['id' => $doctor->id, 'is_active' => false]);
    $this->delete(route('admin.master.destroy', ['doctors', $doctor->id]))->assertSessionHasNoErrors();
    $this->assertModelMissing($doctor);
});

test('admin manages schedules and closes one date', function () {
    $doctor = Doctor::factory()->create();
    $data = ['doctor_id' => $doctor->id, 'day_of_week' => 1, 'start_time' => '08:00', 'end_time' => '12:00', 'max_quota' => 20, 'is_active' => true, 'closed_date' => null];
    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('admin.master.store', 'schedules'), $data)->assertSessionHasNoErrors();
    $schedule = DoctorSchedule::first();
    $date = today()->addWeek()->toDateString();
    $this->put(route('admin.master.update', ['schedules', $schedule->id]), [...$data, 'closed_date' => $date])->assertSessionHasNoErrors();
    $this->assertDatabaseHas('doctor_schedules', ['id' => $schedule->id, 'closed_date' => $date]);
    $this->delete(route('admin.master.destroy', ['schedules', $schedule->id]))->assertSessionHasNoErrors();
    $this->assertModelMissing($schedule);
});

test('schedule validation rejects overlapping sessions and invalid intervals', function () {
    $schedule = DoctorSchedule::factory()->create();
    $data = ['doctor_id' => $schedule->doctor_id, 'day_of_week' => 1, 'start_time' => '10:00', 'end_time' => '13:00', 'max_quota' => 20, 'is_active' => true];
    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('admin.master.store', 'schedules'), $data)->assertSessionHasErrors(['start_time']);
    $this->post(route('admin.master.store', 'schedules'), [...$data, 'end_time' => '09:00', 'max_quota' => 0])->assertSessionHasErrors(['end_time', 'max_quota']);
    $this->assertDatabaseCount('doctor_schedules', 1);
});

test('used master records cannot be deleted', function (string $entity) {
    $reservation = Reservation::factory()->create();
    $model = match ($entity) {
        'schedules' => $reservation->schedule, 'doctors' => $reservation->schedule->doctor, 'polyclinics' => $reservation->schedule->doctor->polyclinic
    };

    $this->actingAs(User::factory()->admin()->create())->delete(route('admin.master.destroy', [$entity, $model->id]))->assertSessionHasErrors(['master']);
    $this->assertModelExists($model);
})->with(['schedules', 'doctors', 'polyclinics']);

test('reserved schedules cannot change times or reduce quota below existing bookings', function () {
    $this->travelTo(Carbon::parse('2026-11-09 07:00:00'));
    $schedule = DoctorSchedule::factory()->create();
    Reservation::factory()->for($schedule, 'schedule')->create();
    Reservation::factory()->for($schedule, 'schedule')->create(['queue_number' => 2]);
    $data = ['doctor_id' => $schedule->doctor_id, 'day_of_week' => 1, 'start_time' => '08:00', 'end_time' => '12:00', 'max_quota' => 20, 'is_active' => true];
    $this->actingAs(User::factory()->admin()->create());

    $this->put(route('admin.master.update', ['schedules', $schedule->id]), [...$data, 'start_time' => '09:00'])->assertSessionHasErrors(['schedule']);
    $this->put(route('admin.master.update', ['schedules', $schedule->id]), [...$data, 'max_quota' => 1])->assertSessionHasErrors(['max_quota']);
    $this->assertDatabaseHas('doctor_schedules', ['id' => $schedule->id, 'max_quota' => 20]);
});

test('unknown master entity is not routable', function () {
    $this->actingAs(User::factory()->admin()->create())->post('/admin/master/users', [])->assertNotFound();
});
