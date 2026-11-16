<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SlotResource;
use App\Models\Slot;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SlotController extends Controller
{
    /**
     * List all slots ordered by start time.
     */
    public function index(): AnonymousResourceCollection
    {
        $slots = Slot::query()
            ->with('activeBooking:id,slot_id')
            ->orderBy('starts_at')
            ->get();

        return SlotResource::collection($slots);
    }
}
