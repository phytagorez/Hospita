<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'patient';
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'schedule_id' => ['required', 'integer', 'exists:doctor_schedules,id'],
            'reservation_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today', 'before_or_equal:'.today()->addDays(30)->toDateString()],
            'complaint' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }
}
