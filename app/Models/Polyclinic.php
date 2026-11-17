<?php

namespace App\Models;

use Database\Factories\PolyclinicFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $room_location
 * @property int $base_admin_fee
 * @property bool $is_active
 * @property-read Collection<int, Doctor> $doctors
 */
#[Fillable(['name', 'code', 'room_location', 'base_admin_fee', 'is_active'])]
class Polyclinic extends Model
{
    /** @use HasFactory<PolyclinicFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'base_admin_fee' => 'integer'];
    }

    /** @return HasMany<Doctor, $this> */
    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class);
    }
}
