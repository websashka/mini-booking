import { computed, ref } from 'vue';
import * as api from '@/api/bookings';
import { dateKey } from '@/lib/datetime';
import type { BookingPayload, Slot } from '@/types';

/**
 * Schedule state: loads all slots once, filters by date in the browser,
 * and reloads after every booking, cancellation or conflict.
 */
export function useSchedule(timeZone: string) {
    const slots = ref<Slot[]>([]);
    const isLoading = ref(false);
    const hasLoaded = ref(false);
    const loadError = ref<string | null>(null);
    const selectedDate = ref<string | null>(null);

    const todayKey = computed(() =>
        dateKey(new Date().toISOString(), timeZone),
    );

    const availableDates = computed(() => [
        ...new Set(
            slots.value.map((slot) => dateKey(slot.starts_at, timeZone)),
        ),
    ]);

    const filteredSlots = computed(() =>
        slots.value.filter(
            (slot) => dateKey(slot.starts_at, timeZone) === selectedDate.value,
        ),
    );

    async function load(): Promise<void> {
        isLoading.value = true;
        loadError.value = null;

        try {
            slots.value = await api.fetchSlots();
            hasLoaded.value = true;

            if (
                !selectedDate.value ||
                !availableDates.value.includes(selectedDate.value)
            ) {
                selectedDate.value = availableDates.value.includes(
                    todayKey.value,
                )
                    ? todayKey.value
                    : (availableDates.value[0] ?? null);
            }
        } catch (error) {
            loadError.value =
                error instanceof Error
                    ? error.message
                    : 'Не удалось загрузить расписание.';
        } finally {
            isLoading.value = false;
        }
    }

    /** Refresh the list in the background; a failure here must not hide the action's own result. */
    function refresh(): void {
        void load();
    }

    async function book(payload: BookingPayload): Promise<{ id: number }> {
        try {
            const booking = await api.createBooking(payload);
            refresh();

            return booking;
        } catch (error) {
            if (error instanceof api.ConflictError) {
                refresh();
            }

            throw error;
        }
    }

    async function cancel(bookingId: number): Promise<void> {
        try {
            await api.cancelBooking(bookingId);
            refresh();
        } catch (error) {
            if (error instanceof api.NotFoundError) {
                refresh();
            }

            throw error;
        }
    }

    return {
        isLoading,
        hasLoaded,
        loadError,
        selectedDate,
        todayKey,
        availableDates,
        filteredSlots,
        load,
        book,
        cancel,
    };
}
