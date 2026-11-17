<?php

namespace App\Models;

use Database\Factories\DoctorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $polyclinic_id
 * @property string $name
 * @property string $sip_number
 * @property string|null $photo
 * @property bool $is_active
 * @property-read Polyclinic $polyclinic
 * @property-read Collection<int, DoctorSchedule> $schedules
 */
#[Fillable(['polyclinic_id', 'name', 'sip_number', 'photo', 'is_active'])]
class Doctor extends Model
{
    /** @use HasFactory<DoctorFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** @return BelongsTo<Polyclinic, $this> */
    public function polyclinic(): BelongsTo
    {
        return $this->belongsTo(Polyclinic::class);
    }

    /** @return HasMany<DoctorSchedule, $this> */
    public function schedules(): HasMany
    {
        return $this->hasMany(DoctorSchedule::class);
    }
}
