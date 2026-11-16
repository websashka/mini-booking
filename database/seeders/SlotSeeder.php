<?php

namespace Database\Seeders;

use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SlotSeeder extends Seeder
{
    public const int DAYS = 7;

    public const int FIRST_HOUR = 9;

    public const int LAST_HOUR = 17;

    /**
     * Create hourly slots for 7 calendar days starting today, in the configured app timezone.
     * Re-running is safe: existing slots are left untouched.
     */
    public function run(): void
    {
        $timezone = config('app.timezone');
        $today = CarbonImmutable::today($timezone);
        $now = CarbonImmutable::now($timezone);
        $rows = [];

        for ($dayOffset = 0; $dayOffset < self::DAYS; $dayOffset++) {
            for ($hour = self::FIRST_HOUR; $hour <= self::LAST_HOUR; $hour++) {
                $startsAt = $today->addDays($dayOffset)->setTime($hour, 0);

                $rows[] = [
                    'starts_at' => $startsAt,
                    'ends_at' => $startsAt->addHour(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('slots')->insertOrIgnore($rows);
    }
}
