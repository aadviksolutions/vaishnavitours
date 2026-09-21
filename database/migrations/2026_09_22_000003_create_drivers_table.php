<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mobile', 20)->unique()->index();
            $table->string('alternate_mobile', 20)->nullable();
            $table->string('license_number', 50)->unique();
            $table->date('license_expiry')->nullable();
            $table->text('address')->nullable();
            $table->string('status')->default('Available')->index(); // Available, Assigned, On Trip, Inactive
            $table->foreignId('assigned_vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->decimal('rating', 3, 2)->default(5.00);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
