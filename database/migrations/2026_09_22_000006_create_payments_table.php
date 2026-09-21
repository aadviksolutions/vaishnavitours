<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_id', 40)->unique()->index();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->string('gateway', 50)->default('Manual / Direct');
            $table->string('transaction_id', 100)->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('INR');
            $table->string('payment_method', 50)->default('Cash'); // Cash, UPI / QR, Bank Transfer, Card, Online Gateway
            $table->string('status', 30)->default('Success')->index(); // Pending, Success, Failed, Refunded
            $table->json('gateway_response')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
