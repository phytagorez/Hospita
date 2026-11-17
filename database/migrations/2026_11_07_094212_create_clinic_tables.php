<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('patient');
            $table->string('google_id')->nullable()->unique();
            $table->string('avatar')->nullable();
        });
        Schema::create('patient_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->restrictOnDelete();
            $table->string('no_rm')->unique();
            $table->string('nik', 16)->unique();
            $table->date('birth_date');
            $table->string('gender');
            $table->string('blood_type')->nullable();
            $table->string('phone', 20);
            $table->text('address');
            $table->timestamps();
        });
        Schema::create('polyclinics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 10)->unique();
            $table->string('room_location');
            $table->unsignedInteger('base_admin_fee')->default(25000);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('polyclinic_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('sip_number')->unique();
            $table->string('photo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('doctor_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('max_quota');
            $table->boolean('is_active')->default(true);
            $table->date('closed_date')->nullable();
            $table->timestamps();
            $table->index(['day_of_week', 'is_active']);
        });
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 16)->unique();
            $table->foreignId('patient_id')->constrained('patient_profiles')->restrictOnDelete();
            $table->foreignId('schedule_id')->constrained('doctor_schedules')->restrictOnDelete();
            $table->date('reservation_date');
            $table->unsignedSmallInteger('queue_number');
            $table->text('complaint');
            $table->string('booking_type')->default('online');
            $table->string('status')->default('booked');
            $table->unsignedInteger('admin_fee');
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamps();
            $table->unique(['schedule_id', 'reservation_date', 'queue_number'], 'session_queue_unique');
            $table->index(['patient_id', 'reservation_date', 'status']);
            $table->index(['reservation_date', 'status']);
        });
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->unique()->constrained()->restrictOnDelete();
            $table->unsignedInteger('admin_fee');
            $table->unsignedInteger('consultation_fee');
            $table->unsignedInteger('total_amount');
            $table->string('payment_method');
            $table->string('payment_status')->default('paid');
            $table->timestamp('paid_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['transactions', 'reservations', 'doctor_schedules', 'doctors', 'polyclinics', 'patient_profiles'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'google_id', 'avatar']);
        });
    }
};
