<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Database\Factories\SlotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon $starts_at
 * @property Carbon $ends_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Booking|null $activeBooking
 */
class Slot extends Model
{
    /** @use HasFactory<SlotFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * @return HasOne<Booking, $this>
     */
    public function activeBooking(): HasOne
    {
        return $this->hasOne(Booking::class)->where('status', BookingStatus::Active);
    }

    /**
     * A slot is past once its start time has been reached.
     */
    public function isPast(): bool
    {
        return $this->starts_at->lessThanOrEqualTo(now());
    }
}
