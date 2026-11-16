<?php

namespace App\Http\Controllers\Api;

use App\Actions\CreateBooking;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class BookingController extends Controller
{
    /**
     * Book a free future slot.
     */
    public function store(StoreBookingRequest $request, CreateBooking $createBooking): JsonResponse
    {
        /** @var array{slot_id: int, customer_name: string, customer_email: string} $attributes */
        $attributes = $request->validated();

        $booking = $createBooking->handle($attributes);

        return response()->json(['id' => $booking->id], Response::HTTP_CREATED);
    }

    /**
     * Cancel a booking by its ID. Repeated cancellation is idempotent.
     */
    public function destroy(Booking $booking): Response
    {
        $booking->cancel();

        return response()->noContent();
    }
}
