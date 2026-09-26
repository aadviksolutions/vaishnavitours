<?php

use Database\Seeders\RateSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_routes', function (Blueprint $table) {
            $table->id();
            $table->string('category', 40)->index();
            $table->string('origin')->nullable();
            $table->string('destination')->nullable();
            $table->unsignedInteger('km_limit')->nullable();
            $table->decimal('included_hours', 6, 2)->nullable();
            $table->boolean('return_same_rate')->default(false);
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
            $table->unique(['category', 'origin', 'destination']);
        });

        Schema::create('rate_vehicle_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rate_route_id')->constrained()->cascadeOnDelete();
            $table->string('vehicle_category', 80);
            $table->decimal('base_fare', 10, 2)->nullable();
            $table->boolean('gst_applicable')->default(false);
            $table->decimal('extra_km_rate', 8, 2)->nullable();
            $table->decimal('extra_hour_rate', 8, 2)->nullable();
            $table->decimal('vehicle_rent', 10, 2)->nullable();
            $table->decimal('per_km_rate', 8, 2)->nullable();
            $table->decimal('night_charge', 8, 2)->nullable();
            $table->string('toll_type', 20)->nullable();
            $table->string('parking_type', 20)->nullable();
            $table->string('border_tax_type', 20)->nullable();
            $table->string('driver_food_type', 20)->nullable();
            $table->timestamps();
            $table->unique(['rate_route_id', 'vehicle_category']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('rate_vehicle_price_id')->nullable()->constrained()->nullOnDelete();
            $table->string('rate_category', 40)->nullable();
            $table->json('pricing_details')->nullable();
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->decimal('per_km_rate', 8, 2)->nullable()->default(null)->change();
            $table->decimal('per_hour_rate', 8, 2)->nullable()->default(null)->change();
        });
        DB::table('vehicles')->update(['per_km_rate' => null, 'per_hour_rate' => null]);

        app(RateSeeder::class)->run();

        Schema::dropIfExists('rates');
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rate_vehicle_price_id');
            $table->dropColumn(['rate_category', 'pricing_details']);
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->decimal('per_km_rate', 8, 2)->default(12)->change();
            $table->decimal('per_hour_rate', 8, 2)->default(200)->change();
        });

        Schema::dropIfExists('rate_vehicle_prices');
        Schema::dropIfExists('rate_routes');

        Schema::create('rates', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_name');
            $table->string('vehicle_type')->nullable();
            $table->string('trip_type');
            $table->decimal('rate', 10, 2);
            $table->decimal('extra_km_rate', 8, 2)->nullable();
            $table->decimal('waiting_charge', 8, 2)->nullable();
            $table->decimal('night_charge', 8, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
};
