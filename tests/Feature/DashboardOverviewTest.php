<?php

use App\Models\DoctorSchedule;
use App\Models\PatientProfile;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-11-09 07:00:00', 'Asia/Jakarta'));
});

test('admin overview uses today reservations and active practice schedules', function () {
    $schedule = DoctorSchedule::factory()->create();
    DoctorSchedule::factory()->create(['is_active' => false]);
    DoctorSchedule::factory()->create(['day_of_week' => 2]);
    $inactiveDoctor = DoctorSchedule::factory()->create();
    $inactiveDoctor->doctor->update(['is_active' => false]);
    $inactivePoli = DoctorSchedule::factory()->create();
    $inactivePoli->doctor->polyclinic->update(['is_active' => false]);
    Reservation::factory()->for($schedule, 'schedule')->create(['status' => 'waiting', 'queue_number' => 1]);
    Reservation::factory()->for($schedule, 'schedule')->create(['status' => 'completed', 'queue_number' => 2, 'booking_type' => 'walk_in']);
    Reservation::factory()->for($schedule, 'schedule')->create(['reservation_date' => '2026-11-10']);

    $this->actingAs(User::factory()->admin()->create())->get(route('dashboard'))->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Dashboard')
            ->where('stats.total', 2)->where('stats.waiting', 1)->where('stats.completed', 1)->where('stats.walk_in', 1)
            ->has('adminOverview.schedules', 1)->where('adminOverview.schedules.0.id', $schedule->id)
            ->has('adminOverview.services', 1)->where('adminOverview.services.0.name', $schedule->doctor->polyclinic->name)
            ->where('adminOverview.services.0.count', 2)->has('week', 7)->where('week.6.count', 2));
});

test('patient dashboard does not receive administrative overview', function () {
    $patient = PatientProfile::factory()->create();
    Reservation::factory()->create();
    Reservation::factory()->for($patient, 'patient')->create();

    $this->actingAs($patient->user)->get(route('dashboard'))->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Dashboard')
            ->where('adminOverview', null)->has('week', 0)->has('reservations', 1)
            ->where('reservations.0.patient_id', $patient->id)->where('stats.total', 1));
});

test('empty admin overview remains valid without reservations or doctors', function () {
    $this->actingAs(User::factory()->admin()->create())->get(route('dashboard'))->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('stats.total', 0)
            ->has('adminOverview.schedules', 0)->has('adminOverview.services', 0)->has('reservations', 0));
});
