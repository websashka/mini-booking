<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import BookingFormDialog from '@/components/booking/BookingFormDialog.vue';
import CancelBookingDialog from '@/components/booking/CancelBookingDialog.vue';
import SlotCard from '@/components/booking/SlotCard.vue';
import { useSchedule } from '@/composables/useSchedule';
import { formatDayKey, formatDayKeyLong } from '@/lib/datetime';
import type { Slot } from '@/types';

const props = defineProps<{
    timezone: string;
}>();

const {
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
} = useSchedule(props.timezone);

const selectedSlot = ref<Slot | null>(null);
const isBookingDialogOpen = ref(false);
const isCancelDialogOpen = ref(false);
const snackbar = ref({ isVisible: false, text: '' });

function openBooking(slot: Slot): void {
    selectedSlot.value = slot;
    isBookingDialogOpen.value = true;
}

function openCancel(slot: Slot): void {
    selectedSlot.value = slot;
    isCancelDialogOpen.value = true;
}

function notify(text: string): void {
    snackbar.value = { isVisible: true, text };
}

onMounted(load);
</script>

<template>
    <Head title="Бронирование переговорной" />

    <v-app>
        <v-app-bar color="primary" density="comfortable" flat>
            <v-app-bar-title>
                <v-icon icon="mdi-door-open" class="me-2" />
                Переговорная
            </v-app-bar-title>
            <template #append>
                <v-btn
                    icon="mdi-refresh"
                    aria-label="Обновить расписание"
                    :loading="isLoading"
                    @click="load"
                />
            </template>
        </v-app-bar>

        <v-main>
            <v-container class="py-6" style="max-width: 1100px">
                <div class="mb-4">
                    <h1 class="text-headline-small mb-1">
                        Расписание на неделю
                    </h1>
                    <p class="text-body-medium text-medium-emphasis">
                        Слоты по часу с 09:00 до 18:00 каждый день. Время
                        указано в часовом поясе {{ timezone }}.
                    </p>
                </div>

                <v-alert
                    v-if="loadError"
                    type="error"
                    variant="tonal"
                    class="mb-4"
                    title="Не удалось загрузить расписание"
                    :text="loadError"
                >
                    <template #append>
                        <v-btn
                            variant="outlined"
                            prepend-icon="mdi-refresh"
                            :loading="isLoading"
                            @click="load"
                        >
                            Повторить
                        </v-btn>
                    </template>
                </v-alert>

                <template v-if="!hasLoaded && isLoading">
                    <v-skeleton-loader type="chip@4" class="mb-4" />
                    <v-row>
                        <v-col v-for="n in 6" :key="n" cols="12" sm="6" md="4">
                            <v-skeleton-loader type="card" height="130" />
                        </v-col>
                    </v-row>
                </template>

                <template v-else-if="hasLoaded">
                    <v-chip-group
                        v-model="selectedDate"
                        mandatory
                        selected-class="text-primary"
                        class="mb-4"
                        aria-label="Фильтр по дате"
                    >
                        <v-chip
                            v-for="day in availableDates"
                            :key="day"
                            :value="day"
                            variant="outlined"
                            filter
                        >
                            {{
                                day === todayKey ? 'Сегодня' : formatDayKey(day)
                            }}
                        </v-chip>
                    </v-chip-group>

                    <h2
                        v-if="selectedDate"
                        class="selected-day text-title-medium font-weight-medium mb-3"
                    >
                        {{ formatDayKeyLong(selectedDate) }}
                    </h2>

                    <v-row v-if="filteredSlots.length">
                        <v-col
                            v-for="slot in filteredSlots"
                            :key="slot.id"
                            cols="12"
                            sm="6"
                            md="4"
                        >
                            <SlotCard
                                :slot="slot"
                                :time-zone="timezone"
                                @book="openBooking"
                                @cancel="openCancel"
                            />
                        </v-col>
                    </v-row>

                    <v-empty-state
                        v-else
                        icon="mdi-calendar-blank-outline"
                        title="Слотов нет"
                        text="Расписание ещё не сформировано. Запустите SlotSeeder."
                    />
                </template>
            </v-container>
        </v-main>

        <BookingFormDialog
            v-model="isBookingDialogOpen"
            :slot="selectedSlot"
            :time-zone="timezone"
            :submit="book"
            @booked="
                (id) =>
                    notify(
                        `Бронь #${id} создана. Сохраните номер, чтобы отменить её.`,
                    )
            "
        />

        <CancelBookingDialog
            v-model="isCancelDialogOpen"
            :slot="selectedSlot"
            :time-zone="timezone"
            :submit="cancel"
            @cancelled="(id) => notify(`Бронь #${id} отменена.`)"
        />

        <v-snackbar v-model="snackbar.isVisible" color="success" timeout="5000">
            {{ snackbar.text }}
        </v-snackbar>
    </v-app>
</template>

<style scoped>
.selected-day::first-letter {
    text-transform: uppercase;
}
</style>
