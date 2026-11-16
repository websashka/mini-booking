export type SlotStatus = 'available' | 'booked' | 'past';

export type Slot = {
    id: number;
    starts_at: string;
    ends_at: string;
    status: SlotStatus;
    active_booking_id: number | null;
};

export type BookingPayload = {
    slot_id: number;
    customer_name: string;
    customer_email: string;
};

export type BookingFieldErrors = Partial<
    Record<keyof BookingPayload, string[]>
>;
