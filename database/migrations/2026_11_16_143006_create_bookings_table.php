<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slot_id')->constrained()->cascadeOnDelete();
            $table->string('customer_name', 100);
            $table->string('customer_email');
            $table->string('status', 20)->default('active');
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });

        /**
         * Guarantees at most one active booking per slot, even under concurrent requests.
         * Partial index syntax is identical in PostgreSQL and SQLite.
         */
        DB::statement("CREATE UNIQUE INDEX bookings_slot_active_unique ON bookings (slot_id) WHERE status = 'active'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
