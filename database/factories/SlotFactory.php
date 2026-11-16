<?php

namespace Database\Factories;

use App\Models\Slot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Slot>
 */
class SlotFactory extends Factory
{
    /**
     * Define the model's default state: a future slot between 09:00 and 18:00.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = now()
            ->addDays(fake()->unique()->numberBetween(1, 1000))
            ->setTime(fake()->numberBetween(9, 17), 0);

        return [
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addHour(),
        ];
    }
}
