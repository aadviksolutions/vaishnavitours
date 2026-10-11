<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_id', 30)->unique()->index();
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->string('trip_type', 50)->default('One-Way'); // One-Way, Round-Trip, Local Hourly, Airport Transfer, Emergency
            $table->string('pickup_location');
            $table->string('destination');
            $table->date('travel_date')->index();
            $table->time('travel_time');
            $table->date('return_date')->nullable();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->decimal('paid_amount', 10, 2)->default(0.00);
            $table->decimal('balance_amount', 10, 2)->default(0.00);
            $table->string('payment_status', 30)->default('Pending')->index(); // Pending, Partial, Paid, Failed, Refunded
            $table->string('booking_status', 30)->default('Pending')->index(); // Pending, Confirmed, Vehicle Assigned, Driver Assigned, Trip Started, On The Way, Completed, Cancelled
            $table->text('notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
