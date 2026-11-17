<?php

namespace App\Models;

use Database\Factories\ReservationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $patient_id
 * @property int $schedule_id
 * @property string $booking_code
 * @property Carbon $reservation_date
 * @property int $queue_number
 * @property string $complaint
 * @property string $booking_type
 * @property string $status
 * @property int $admin_fee
 * @property Carbon|null $checked_in_at
 * @property-read PatientProfile $patient
 * @property-read DoctorSchedule $schedule
 * @property-read Transaction|null $transaction
 */
#[Fillable(['booking_code', 'patient_id', 'schedule_id', 'reservation_date', 'queue_number', 'complaint', 'booking_type', 'status', 'admin_fee', 'checked_in_at'])]
class Reservation extends Model
{
    /** @use HasFactory<ReservationFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['reservation_date' => 'date:Y-m-d', 'checked_in_at' => 'datetime'];
    }

    public const ACTIVE = ['booked', 'waiting', 'examining', 'completed', 'skipped'];

    /** @return BelongsTo<PatientProfile, $this> */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id');
    }

    /** @return BelongsTo<DoctorSchedule, $this> */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(DoctorSchedule::class, 'schedule_id');
    }

    /** @return HasOne<Transaction, $this> */
    public function transaction(): HasOne
    {
        return $this->hasOne(Transaction::class);
    }
}
