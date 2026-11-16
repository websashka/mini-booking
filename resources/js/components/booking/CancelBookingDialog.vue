<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { NotFoundError } from '@/api/bookings';
import { dateKey, formatDayKeyLong, formatTimeRange } from '@/lib/datetime';
import type { Slot } from '@/types';

const props = defineProps<{
    slot: Slot | null;
    timeZone: string;
    submit: (bookingId: number) => Promise<void>;
}>();

const emit = defineEmits<{
    cancelled: [bookingId: number];
}>();

const isOpen = defineModel<boolean>({ required: true });

const isSubmitting = ref(false);
const errorMessage = ref<string | null>(null);
const isGone = ref(false);

const slotLabel = computed(() => {
    if (!props.slot) {
        return '';
    }

    const day = formatDayKeyLong(dateKey(props.slot.starts_at, props.timeZone));
    const time = formatTimeRange(
        props.slot.starts_at,
        props.slot.ends_at,
        props.timeZone,
    );

    return `${day}, ${time}`;
});

watch(isOpen, (open) => {
    if (open) {
        errorMessage.value = null;
        isGone.value = false;
    }
});

async function onConfirm(): Promise<void> {
    const bookingId = props.slot?.active_booking_id;

    if (!bookingId || isSubmitting.value) {
        return;
    }

    isSubmitting.value = true;
    errorMessage.value = null;

    try {
        await props.submit(bookingId);
        isOpen.value = false;
        emit('cancelled', bookingId);
    } catch (error) {
        if (error instanceof NotFoundError) {
            isGone.value = true;
            errorMessage.value =
                'Бронирование уже не найдено. Расписание обновлено.';
        } else {
            errorMessage.value =
                error instanceof Error
                    ? error.message
                    : 'Не удалось отменить бронирование.';
        }
    } finally {
        isSubmitting.value = false;
    }
}
</script>

<template>
    <v-dialog v-model="isOpen" :persistent="isSubmitting" max-width="420">
        <v-card :loading="isSubmitting">
            <v-card-item>
                <v-card-title>Отменить бронирование?</v-card-title>
            </v-card-item>
            <v-card-text class="d-flex flex-column ga-3">
                <p>
                    Бронь <strong>#{{ slot?.active_booking_id }}</strong> на
                    {{ slotLabel }} будет отменена, и слот станет свободным.
                </p>
                <v-alert
                    v-if="errorMessage"
                    :type="isGone ? 'warning' : 'error'"
                    variant="tonal"
                    :text="errorMessage"
                />
            </v-card-text>
            <v-card-actions class="px-6 pb-4">
                <v-spacer />
                <v-btn
                    variant="text"
                    :disabled="isSubmitting"
                    @click="isOpen = false"
                >
                    {{ isGone ? 'Закрыть' : 'Не отменять' }}
                </v-btn>
                <v-btn
                    v-if="!isGone"
                    color="error"
                    variant="flat"
                    :loading="isSubmitting"
                    :prepend-icon="errorMessage ? 'mdi-refresh' : undefined"
                    @click="onConfirm"
                >
                    {{ errorMessage ? 'Повторить' : 'Отменить бронь' }}
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>
