<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SlotUnavailableException extends Exception
{
    public static function alreadyBooked(): self
    {
        return new self('Этот слот уже забронирован. Выберите другое время.');
    }

    public static function alreadyPast(): self
    {
        return new self('Этот слот уже прошёл. Выберите будущее время.');
    }

    /**
     * Render the exception as an HTTP 409 Conflict response.
     */
    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], Response::HTTP_CONFLICT);
    }
}
