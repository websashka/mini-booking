<?php

namespace App\Http\Resources;

use App\Models\Slot;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Slot
 */
class SlotResource extends JsonResource
{
    /**
     * Customer name and email are intentionally not exposed.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'starts_at' => $this->starts_at->toIso8601String(),
            'ends_at' => $this->ends_at->toIso8601String(),
            'status' => $this->status(),
            'active_booking_id' => $this->activeBooking?->id,
        ];
    }

    private function status(): string
    {
        if ($this->isPast()) {
            return 'past';
        }

        return $this->activeBooking ? 'booked' : 'available';
    }
}
