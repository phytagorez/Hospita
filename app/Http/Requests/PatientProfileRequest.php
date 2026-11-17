<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PatientProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'patient';
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        $profile = $this->user()->patientProfile;

        return [
            'nik' => [$profile ? 'prohibited' : 'required', 'digits:16', Rule::unique('patient_profiles', 'nik')],
            'birth_date' => [$profile ? 'prohibited' : 'required', 'date', 'before_or_equal:today', 'after:1900-01-01'],
            'gender' => [$profile ? 'prohibited' : 'required', Rule::in(['male', 'female'])],
            'blood_type' => [$profile ? 'prohibited' : 'nullable', Rule::in(['A', 'B', 'AB', 'O', 'unknown'])],
            'phone' => ['required', 'regex:/^\\+?[0-9]{9,15}$/'],
            'address' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }
}
