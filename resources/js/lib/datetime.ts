const LOCALE = 'ru-RU';

/** Calendar day key (YYYY-MM-DD) of an ISO timestamp in the given timezone. */
export function dateKey(iso: string, timeZone: string): string {
    return new Intl.DateTimeFormat('en-CA', {
        timeZone,
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    }).format(new Date(iso));
}

/** "09:00" in the given timezone. */
export function formatTime(iso: string, timeZone: string): string {
    return new Intl.DateTimeFormat(LOCALE, {
        timeZone,
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(iso));
}

/** "пн, 16 нояб." for a YYYY-MM-DD key; noon UTC avoids day shifts across timezones. */
export function formatDayKey(key: string): string {
    return new Intl.DateTimeFormat(LOCALE, {
        timeZone: 'UTC',
        weekday: 'short',
        day: 'numeric',
        month: 'short',
    }).format(new Date(`${key}T12:00:00Z`));
}

/** "понедельник, 16 ноября" for a YYYY-MM-DD key. */
export function formatDayKeyLong(key: string): string {
    return new Intl.DateTimeFormat(LOCALE, {
        timeZone: 'UTC',
        weekday: 'long',
        day: 'numeric',
        month: 'long',
    }).format(new Date(`${key}T12:00:00Z`));
}

/** "09:00–10:00" */
export function formatTimeRange(
    startsAt: string,
    endsAt: string,
    timeZone: string,
): string {
    return `${formatTime(startsAt, timeZone)}–${formatTime(endsAt, timeZone)}`;
}
