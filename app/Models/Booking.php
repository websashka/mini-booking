<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $slot_id
 * @property string $customer_name
 * @property string $customer_email
 * @property BookingStatus $status
 * @property Carbon|null $cancelled_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['slot_id', 'customer_name', 'customer_email', 'status', 'cancelled_at'])]
class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * Cancel the booking; cancelling an already cancelled booking is a no-op.
     */
    public function cancel(): void
    {
        if ($this->status === BookingStatus::Cancelled) {
            return;
        }

        $this->update([
            'status' => BookingStatus::Cancelled,
            'cancelled_at' => now(),
        ]);
    }
}
