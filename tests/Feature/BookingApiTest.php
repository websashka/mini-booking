<?php

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Slot;

it('books a free future slot', function () {
    $slot = Slot::factory()->create();

    $response = $this->postJson('/api/bookings', [
        'slot_id' => $slot->id,
        'customer_name' => 'Анна Смирнова',
        'customer_email' => 'anna@example.com',
    ]);

    $response->assertCreated()->assertJsonStructure(['id']);

    $this->assertDatabaseHas('bookings', [
        'id' => $response->json('id'),
        'slot_id' => $slot->id,
        'customer_name' => 'Анна Смирнова',
        'customer_email' => 'anna@example.com',
        'status' => BookingStatus::Active->value,
    ]);
});

it('rejects booking an already booked slot with 409 and keeps a single active booking', function () {
    $slot = Slot::factory()->create();

    $this->postJson('/api/bookings', [
        'slot_id' => $slot->id,
        'customer_name' => 'Анна Смирнова',
        'customer_email' => 'anna@example.com',
    ])->assertCreated();

    $this->postJson('/api/bookings', [
        'slot_id' => $slot->id,
        'customer_name' => 'Борис Иванов',
        'customer_email' => 'boris@example.com',
    ])
        ->assertConflict()
        ->assertJsonPath('message', 'Этот слот уже забронирован. Выберите другое время.');

    expect(Booking::query()->where('slot_id', $slot->id)->where('status', BookingStatus::Active)->count())
        ->toBe(1);
});
