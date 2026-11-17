<?php

namespace App\Models;

use Database\Factories\DoctorScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $doctor_id
 * @property int $day_of_week
 * @property string $start_time
 * @property string $end_time
 * @property int $max_quota
 * @property bool $is_active
 * @property Carbon|null $closed_date
 * @property int $booked_count
 * @property-read Doctor $doctor
 * @property-read Collection<int, Reservation> $reservations
 */
#[Fillable(['doctor_id', 'day_of_week', 'start_time', 'end_time', 'max_quota', 'is_active', 'closed_date'])]
class DoctorSchedule extends Model
{
    /** @use HasFactory<DoctorScheduleFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'closed_date' => 'date:Y-m-d', 'day_of_week' => 'integer', 'max_quota' => 'integer'];
    }

    /** @return BelongsTo<Doctor, $this> */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /** @return HasMany<Reservation, $this> */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'schedule_id');
    }
}
