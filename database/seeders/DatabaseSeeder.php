<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\PatientProfile;
use App\Models\Polyclinic;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (config('services.clinic.admin_password')) {
            User::firstOrCreate(['email' => config('services.clinic.admin_email')], [
                'name' => 'Admin Klinik', 'password' => config('services.clinic.admin_password'), 'role' => 'admin',
            ]);
        }
        if (! app()->environment('local', 'testing')) {
            return;
        }
        if (config('services.clinic.patient_password')) {
            $patient = User::firstOrCreate(['email' => 'pasien@klinik.test'], [
                'name' => 'Nadia Putri', 'password' => config('services.clinic.patient_password'), 'role' => 'patient',
            ]);
            PatientProfile::firstOrCreate(['user_id' => $patient->id], [
                'no_rm' => 'RM-DEMO-000001', 'nik' => '3273010101000001', 'birth_date' => '2000-01-01',
                'gender' => 'female', 'blood_type' => 'O', 'phone' => '081234567890', 'address' => 'Jl. Melati No. 12, Bandung',
            ]);
        }
        $data = [
            ['Umum', 'UM', 'Ruang 01', 25000, 'dr. Aditya Pratama'],
            ['Gigi', 'GG', 'Ruang 02', 35000, 'drg. Maya Lestari'],
            ['Anak', 'AN', 'Ruang 03', 30000, 'dr. Sarah Wijaya, Sp.A'],
            ['Penyakit Dalam', 'PD', 'Ruang 04', 40000, 'dr. Bima Santoso, Sp.PD'],
        ];
        foreach ($data as [$name, $code, $room, $fee, $doctorName]) {
            $polyclinic = Polyclinic::firstOrCreate(['code' => $code], ['name' => $name, 'room_location' => $room, 'base_admin_fee' => $fee, 'is_active' => true]);
            $doctor = Doctor::firstOrCreate(['sip_number' => 'SIP-DEMO-'.$code], ['polyclinic_id' => $polyclinic->id, 'name' => $doctorName, 'is_active' => true]);
            foreach (range(0, 6) as $day) {
                DoctorSchedule::firstOrCreate(['doctor_id' => $doctor->id, 'day_of_week' => $day, 'start_time' => '08:00:00'], ['end_time' => '12:00:00', 'max_quota' => 20, 'is_active' => true]);
                DoctorSchedule::firstOrCreate(['doctor_id' => $doctor->id, 'day_of_week' => $day, 'start_time' => '16:00:00'], ['end_time' => '20:00:00', 'max_quota' => 15, 'is_active' => true]);
            }
        }
    }
}
