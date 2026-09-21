<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('registration_number')->unique()->index();
            $table->string('vehicle_type')->index(); // Hatchback, Sedan, SUV, Innova Crysta, Tempo Traveller, Luxury
            $table->integer('seating_capacity')->default(4);
            $table->string('ac_non_ac')->default('AC'); // AC, Non-AC
            $table->decimal('per_km_rate', 8, 2)->default(12.00);
            $table->decimal('per_hour_rate', 8, 2)->default(200.00);
            $table->string('status')->default('Available')->index(); // Available, On Trip, Maintenance, Inactive
            $table->string('image')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
