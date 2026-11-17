<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatientProfileRequest;
use App\Models\PatientProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PatientController extends Controller
{
    public function profile(Request $request): Response
    {
        return Inertia::render('Profile', ['profile' => $request->user()->patientProfile]);
    }

    public function update(PatientProfileRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $user = User::lockForUpdate()->findOrFail($request->user()->id);
            $profile = $user->patientProfile;
            if ($profile) {
                $profile->update($request->safe()->only(['phone', 'address']));
            } else {
                PatientProfile::create([...$request->validated(), 'user_id' => $user->id, 'no_rm' => 'RM-'.Str::upper((string) Str::ulid())]);
            }
        });

        return redirect()->route('dashboard')->with('success', 'Profil pasien berhasil disimpan.');
    }

    public function index(Request $request): Response
    {
        $data = $request->validate(['search' => ['nullable', 'string', 'max:100']]);
        $search = $data['search'] ?? '';
        $patients = PatientProfile::with('user')->withCount('reservations')
            ->when($search, fn ($query) => $query->where(fn ($q) => $q->where('no_rm', 'like', "%{$search}%")->orWhere('nik', 'like', "%{$search}%")->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"))))
            ->orderByDesc('id')->paginate(15)->withQueryString();

        return Inertia::render('Patients', ['patients' => $patients, 'search' => $search]);
    }

    public function show(PatientProfile $patient): Response
    {
        return Inertia::render('PatientDetail', ['patient' => $patient->load('user'), 'reservations' => $patient->reservations()->with(['schedule.doctor.polyclinic', 'transaction'])->orderByDesc('reservation_date')->paginate(15)]);
    }
}
