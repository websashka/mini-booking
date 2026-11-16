<script setup lang="ts">
import { computed } from 'vue';
import { formatTimeRange } from '@/lib/datetime';
import type { Slot, SlotStatus } from '@/types';

const props = defineProps<{
    slot: Slot;
    timeZone: string;
}>();

const emit = defineEmits<{
    book: [slot: Slot];
    cancel: [slot: Slot];
}>();

const STATUS_VIEW: Record<
    SlotStatus,
    { label: string; color: string; icon: string }
> = {
    available: {
        label: 'Свободен',
        color: 'success',
        icon: 'mdi-check-circle-outline',
    },
    booked: {
        label: 'Занят',
        color: 'error',
        icon: 'mdi-account-clock-outline',
    },
    past: { label: 'Прошёл', color: 'grey', icon: 'mdi-history' },
};

const view = computed(() => STATUS_VIEW[props.slot.status]);
const timeRange = computed(() =>
    formatTimeRange(props.slot.starts_at, props.slot.ends_at, props.timeZone),
);
</script>

<template>
    <v-card
        :variant="slot.status === 'past' ? 'tonal' : 'outlined'"
        :class="{ 'slot-card--past': slot.status === 'past' }"
        class="h-100"
    >
        <v-card-item>
            <v-card-title class="text-title-large">{{
                timeRange
            }}</v-card-title>
            <template #append>
                <v-chip
                    :color="view.color"
                    :prepend-icon="view.icon"
                    size="small"
                    variant="tonal"
                    label
                >
                    {{ view.label }}
                </v-chip>
            </template>
            <v-card-subtitle v-if="slot.active_booking_id">
                Бронь #{{ slot.active_booking_id }}
            </v-card-subtitle>
        </v-card-item>

        <v-card-actions v-if="slot.status !== 'past'">
            <v-btn
                v-if="slot.status === 'available'"
                color="primary"
                variant="flat"
                prepend-icon="mdi-calendar-plus"
                block
                @click="emit('book', slot)"
            >
                Забронировать
            </v-btn>
            <v-btn
                v-else
                color="error"
                variant="text"
                prepend-icon="mdi-close-circle-outline"
                block
                @click="emit('cancel', slot)"
            >
                Отменить бронь
            </v-btn>
        </v-card-actions>
    </v-card>
</template>

<style scoped>
.slot-card--past {
    opacity: 0.6;
}
</style>
