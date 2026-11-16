import BookingController from '@/actions/App/Http/Controllers/Api/BookingController';
import SlotController from '@/actions/App/Http/Controllers/Api/SlotController';
import type { BookingFieldErrors, BookingPayload, Slot } from '@/types';

/** 422: the server rejected one or more fields. */
export class ValidationError extends Error {
    constructor(
        message: string,
        public readonly errors: BookingFieldErrors,
    ) {
        super(message);
        this.name = 'ValidationError';
    }
}

/** 409: the slot is already booked or already past. */
export class ConflictError extends Error {
    constructor(message: string) {
        super(message);
        this.name = 'ConflictError';
    }
}

/** 404: the booking no longer exists. */
export class NotFoundError extends Error {
    constructor(message: string) {
        super(message);
        this.name = 'NotFoundError';
    }
}

/** Network failure or an unexpected server error (5xx etc.); the action can be retried. */
export class RequestError extends Error {
    constructor(message: string) {
        super(message);
        this.name = 'RequestError';
    }
}

const NETWORK_ERROR_MESSAGE =
    'Не удалось связаться с сервером. Проверьте подключение и повторите попытку.';
const SERVER_ERROR_MESSAGE =
    'Сервер не смог выполнить запрос. Повторите попытку чуть позже.';

async function request(url: string, init: RequestInit = {}): Promise<Response> {
    let response: Response;

    try {
        response = await fetch(url, {
            ...init,
            headers: {
                Accept: 'application/json',
                ...(init.body ? { 'Content-Type': 'application/json' } : {}),
            },
        });
    } catch {
        throw new RequestError(NETWORK_ERROR_MESSAGE);
    }

    if (response.ok) {
        return response;
    }

    const body = (await response.json().catch(() => ({}))) as {
        message?: string;
        errors?: BookingFieldErrors;
    };

    switch (response.status) {
        case 422:
            throw new ValidationError(
                body.message ?? 'Проверьте введённые данные.',
                body.errors ?? {},
            );
        case 409:
            throw new ConflictError(
                body.message ?? 'Слот недоступен для бронирования.',
            );
        case 404:
            throw new NotFoundError('Бронирование не найдено.');
        case 429:
            throw new RequestError(
                'Слишком много запросов. Подождите минуту и повторите.',
            );
        default:
            throw new RequestError(SERVER_ERROR_MESSAGE);
    }
}

export async function fetchSlots(): Promise<Slot[]> {
    const response = await request(SlotController.index.url());
    const body = (await response.json()) as { data: Slot[] };

    return body.data;
}

export async function createBooking(
    payload: BookingPayload,
): Promise<{ id: number }> {
    const response = await request(BookingController.store.url(), {
        method: 'POST',
        body: JSON.stringify(payload),
    });

    return (await response.json()) as { id: number };
}

export async function cancelBooking(bookingId: number): Promise<void> {
    await request(BookingController.destroy.url(bookingId), {
        method: 'DELETE',
    });
}
