<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rates', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_name');
            $table->string('vehicle_type')->nullable(); // Sedan, SUV, MUV, Traveller
            $table->string('trip_type'); // Outstation, Local 8hr/80km, Airport Transfer
            $table->decimal('rate', 10, 2);
            $table->decimal('extra_km_rate', 8, 2)->nullable();
            $table->decimal('waiting_charge', 8, 2)->nullable();
            $table->decimal('night_charge', 8, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rates');
    }
};
