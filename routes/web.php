<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReservationController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

Route::get('/', [ClinicController::class, 'index'])->name('home');
Route::get('/jadwal', [ClinicController::class, 'schedules'])->middleware('auth')->name('schedules');
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:clinic-login')->name('login.store');
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->middleware('throttle:20,1')->name('google.callback');
});
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', [ClinicController::class, 'dashboard'])->name('dashboard');
    Route::get('/reservasi/{reservation}', [ReservationController::class, 'show'])->name('reservations.show');
    Route::get('/reservasi/{reservation}/qr', [ReservationController::class, 'qr'])->name('reservations.qr');
    Route::get('/reservasi/{reservation}/nota', [BillingController::class, 'receipt'])->name('reservations.receipt');
    Route::middleware(EnsureRole::class.':patient')->group(function () {
        Route::get('/profil', [PatientController::class, 'profile'])->name('patient.profile');
        Route::put('/profil', [PatientController::class, 'update'])->name('patient.update');
        Route::get('/buat-janji', [ReservationController::class, 'create'])->name('reservations.create');
        Route::post('/reservasi', [ReservationController::class, 'store'])->middleware('throttle:20,1')->name('reservations.store');
        Route::delete('/reservasi/{reservation}', [ReservationController::class, 'destroy'])->name('reservations.destroy');
        Route::get('/riwayat', [ReservationController::class, 'history'])->name('patient.history');
    });
    Route::prefix('admin')->name('admin.')->middleware(EnsureRole::class.':admin')->group(function () {
        Route::get('/antrean', [QueueController::class, 'index'])->name('queue.index');
        Route::post('/antrean/check-in', [QueueController::class, 'checkIn'])->name('queue.check-in');
        Route::patch('/antrean/{reservation}', [QueueController::class, 'update'])->name('queue.update');
        Route::post('/walk-in', [QueueController::class, 'walkIn'])->name('walk-in');
        Route::get('/master', [MasterDataController::class, 'index'])->name('master.index');
        Route::get('/jadwal', [MasterDataController::class, 'schedules'])->name('master.schedules');
        Route::post('/master/{entity}', [MasterDataController::class, 'store'])->whereIn('entity', ['polyclinics', 'doctors', 'schedules'])->name('master.store');
        Route::put('/master/{entity}/{id}', [MasterDataController::class, 'update'])->whereIn('entity', ['polyclinics', 'doctors', 'schedules'])->whereNumber('id')->name('master.update');
        Route::delete('/master/{entity}/{id}', [MasterDataController::class, 'destroy'])->whereIn('entity', ['polyclinics', 'doctors', 'schedules'])->whereNumber('id')->name('master.destroy');
        Route::get('/pasien', [PatientController::class, 'index'])->name('patients.index');
        Route::get('/pasien/{patient}', [PatientController::class, 'show'])->name('patients.show');
        Route::get('/kasir', [BillingController::class, 'index'])->name('billing.index');
        Route::post('/kasir/{reservation}', [BillingController::class, 'store'])->name('billing.store');
        Route::get('/laporan', [ReportController::class, 'index'])->name('reports');
    });
});
