<?php

use App\Models\DoctorSchedule;
use App\Models\PatientProfile;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('onboarding creates an RM without allowing role escalation', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put(route('patient.update'), ['nik' => '1234567890123456', 'birth_date' => '2000-01-01', 'gender' => 'female', 'blood_type' => 'O', 'phone' => '081234567890', 'address' => 'Jalan Mawar No. 1', 'role' => 'admin', 'no_rm' => 'FORGED'])->assertRedirect(route('dashboard'));
    $this->assertDatabaseHas('patient_profiles', ['user_id' => $user->id, 'nik' => '1234567890123456']);
    expect($user->fresh()->patientProfile->no_rm)->toStartWith('RM-')->not->toBe('FORGED');
    expect($user->fresh()->role)->toBe('patient');
});

test('identity fields are immutable but phone and address are editable', function () {
    $patient = PatientProfile::factory()->create();
    $this->actingAs($patient->user);

    $this->put(route('patient.update'), ['nik' => '1111111111111111', 'phone' => '089876543210', 'address' => 'Jalan Baru nomor 2'])->assertSessionHasErrors(['nik']);
    $this->put(route('patient.update'), ['phone' => '089876543210', 'address' => 'Jalan Baru nomor 2'])->assertSessionHasNoErrors();
    $this->assertDatabaseHas('patient_profiles', ['id' => $patient->id, 'nik' => $patient->nik, 'phone' => '089876543210', 'address' => 'Jalan Baru nomor 2']);
});

test('onboarding rejects duplicate NIK and invalid identity values', function () {
    $patient = PatientProfile::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($user)->put(route('patient.update'), ['nik' => $patient->nik, 'birth_date' => '2999-01-01', 'gender' => 'invalid', 'phone' => 'abc', 'address' => 'x'])->assertSessionHasErrors(['nik', 'birth_date', 'gender', 'phone', 'address']);
    $this->assertDatabaseCount('patient_profiles', 1);
});

test('patient search finds RM NIK and name with history', function (string $field) {
    $patient = PatientProfile::factory()->create();
    $search = $field === 'name' ? $patient->user->name : $patient->{$field};

    $this->actingAs(User::factory()->admin()->create())->get(route('admin.patients.index', ['search' => $search]))->assertOk()->assertInertia(fn (Assert $page) => $page->has('patients.data', 1)->where('patients.data.0.id', $patient->id));
    $this->get(route('admin.patients.show', $patient))->assertOk();
})->with(['name', 'nik', 'no_rm']);

test('onboarding returns the patient to the date and session they chose', function () {
    $user = User::factory()->create();
    $date = today()->addDays(2);
    $schedule = DoctorSchedule::factory()->create(['day_of_week' => $date->dayOfWeek]);
    $booking = route('reservations.create', ['date' => $date->toDateString(), 'schedule' => $schedule->id]);
    $this->actingAs($user)->get($booking)->assertRedirect(route('patient.profile'));

    $this->put(route('patient.update'), [
        'nik' => '1234567890123456', 'birth_date' => '2000-01-01', 'gender' => 'female',
        'phone' => '081234567890', 'address' => 'Jalan Mawar No. 1',
    ])->assertRedirect($booking);

    $this->actingAs($user->fresh())->get($booking)->assertOk()->assertInertia(fn (Assert $page) => $page->component('Booking')
        ->where('date', $date->toDateString())->where('scheduleId', $schedule->id));
});
