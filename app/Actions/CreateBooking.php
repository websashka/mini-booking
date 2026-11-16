<?php

namespace App\Actions;

use App\Enums\BookingStatus;
use App\Exceptions\SlotUnavailableException;
use App\Models\Booking;
use App\Models\Slot;
use Illuminate\Database\UniqueConstraintViolationException;

class CreateBooking
{
    /**
     * Book the slot. The partial unique index on bookings(slot_id) WHERE status = 'active'
     * is the source of truth: when concurrent requests race for one slot, exactly one insert wins.
     *
     * @param  array{slot_id: int, customer_name: string, customer_email: string}  $attributes
     *
     * @throws SlotUnavailableException
     */
    public function handle(array $attributes): Booking
    {
        $slot = Slot::query()->findOrFail($attributes['slot_id']);

        if ($slot->isPast()) {
            throw SlotUnavailableException::alreadyPast();
        }

        try {
            return Booking::query()->create([
                ...$attributes,
                'status' => BookingStatus::Active,
            ]);
        } catch (UniqueConstraintViolationException) {
            throw SlotUnavailableException::alreadyBooked();
        }
    }
}
