<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useDisplay } from 'vuetify';
import { ConflictError, ValidationError } from '@/api/bookings';
import { dateKey, formatDayKeyLong, formatTimeRange } from '@/lib/datetime';
import type { BookingFieldErrors, BookingPayload, Slot } from '@/types';

const props = defineProps<{
    slot: Slot | null;
    timeZone: string;
    submit: (payload: BookingPayload) => Promise<{ id: number }>;
}>();

const emit = defineEmits<{
    booked: [bookingId: number];
}>();

const isOpen = defineModel<boolean>({ required: true });

const { xs } = useDisplay();

type VuetifyForm = { validate: () => Promise<{ valid: boolean }> };

const form = ref<VuetifyForm | null>(null);
const customerName = ref('');
const customerEmail = ref('');
const isSubmitting = ref(false);
const fieldErrors = ref<BookingFieldErrors>({});
const conflictMessage = ref<string | null>(null);
const requestErrorMessage = ref<string | null>(null);

const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

const nameRules = [
    (value: string) => !!value.trim() || 'Укажите имя.',
    (value: string) =>
        value.trim().length <= 100 ||
        'Имя должно быть не длиннее 100 символов.',
];
const emailRules = [
    (value: string) => !!value.trim() || 'Укажите email.',
    (value: string) =>
        EMAIL_PATTERN.test(value.trim()) || 'Укажите корректный email.',
];

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
        fieldErrors.value = {};
        conflictMessage.value = null;
        requestErrorMessage.value = null;
    }
});

async function onSubmit(): Promise<void> {
    if (!props.slot || isSubmitting.value || !form.value) {
        return;
    }

    const { valid } = await form.value.validate();

    if (!valid) {
        return;
    }

    isSubmitting.value = true;
    fieldErrors.value = {};
    requestErrorMessage.value = null;

    try {
        const booking = await props.submit({
            slot_id: props.slot.id,
            customer_name: customerName.value.trim(),
            customer_email: customerEmail.value.trim(),
        });

        isOpen.value = false;
        emit('booked', booking.id);
    } catch (error) {
        if (error instanceof ValidationError) {
            fieldErrors.value = error.errors;

            if (error.errors.slot_id) {
                conflictMessage.value = error.errors.slot_id.join(' ');
            }
        } else if (error instanceof ConflictError) {
            conflictMessage.value = error.message;
        } else {
            requestErrorMessage.value =
                error instanceof Error
                    ? error.message
                    : 'Не удалось создать бронирование.';
        }
    } finally {
        isSubmitting.value = false;
    }
}
</script>

<template>
    <v-dialog
        v-model="isOpen"
        :fullscreen="xs"
        :persistent="isSubmitting"
        max-width="480"
    >
        <v-card :loading="isSubmitting">
            <v-card-item>
                <v-card-title>Бронирование переговорной</v-card-title>
                <v-card-subtitle class="text-wrap">
                    {{ slotLabel }}
                </v-card-subtitle>
            </v-card-item>

            <v-form
                ref="form"
                :disabled="isSubmitting"
                @submit.prevent="onSubmit"
            >
                <v-card-text class="d-flex flex-column ga-2">
                    <v-alert
                        v-if="conflictMessage"
                        type="warning"
                        variant="tonal"
                        title="Слот недоступен"
                        :text="`${conflictMessage} Расписание обновлено.`"
                    />
                    <v-alert
                        v-if="requestErrorMessage"
                        type="error"
                        variant="tonal"
                        :text="requestErrorMessage"
                    />

                    <v-text-field
                        v-model="customerName"
                        label="Имя"
                        autocomplete="name"
                        prepend-inner-icon="mdi-account-outline"
                        :rules="nameRules"
                        :error-messages="fieldErrors.customer_name"
                        :disabled="!!conflictMessage"
                        required
                        @update:model-value="
                            fieldErrors.customer_name = undefined
                        "
                    />
                    <v-text-field
                        v-model="customerEmail"
                        label="Email"
                        type="email"
                        autocomplete="email"
                        inputmode="email"
                        prepend-inner-icon="mdi-email-outline"
                        :rules="emailRules"
                        :error-messages="fieldErrors.customer_email"
                        :disabled="!!conflictMessage"
                        required
                        @update:model-value="
                            fieldErrors.customer_email = undefined
                        "
                    />
                </v-card-text>

                <v-card-actions class="px-6 pb-4">
                    <v-spacer />
                    <v-btn
                        variant="text"
                        :disabled="isSubmitting"
                        @click="isOpen = false"
                    >
                        {{ conflictMessage ? 'Выбрать другой слот' : 'Отмена' }}
                    </v-btn>
                    <v-btn
                        v-if="!conflictMessage"
                        type="submit"
                        color="primary"
                        variant="flat"
                        :loading="isSubmitting"
                        :prepend-icon="
                            requestErrorMessage ? 'mdi-refresh' : undefined
                        "
                    >
                        {{
                            requestErrorMessage ? 'Повторить' : 'Забронировать'
                        }}
                    </v-btn>
                </v-card-actions>
            </v-form>
        </v-card>
    </v-dialog>
</template>
