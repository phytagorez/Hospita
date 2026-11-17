<?php

namespace App\Models;

use Database\Factories\PatientProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $no_rm
 * @property string $nik
 * @property Carbon $birth_date
 * @property string $gender
 * @property string|null $blood_type
 * @property string $phone
 * @property string $address
 * @property-read User $user
 * @property-read Collection<int, Reservation> $reservations
 */
#[Fillable(['user_id', 'no_rm', 'nik', 'birth_date', 'gender', 'blood_type', 'phone', 'address'])]
class PatientProfile extends Model
{
    /** @use HasFactory<PatientProfileFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['birth_date' => 'date:Y-m-d'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<Reservation, $this> */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'patient_id');
    }
}
