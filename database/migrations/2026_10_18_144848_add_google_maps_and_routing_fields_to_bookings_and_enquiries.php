<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add Google Maps location coordinates, Place IDs and Road Routing data to Bookings table
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('pickup_latitude', 10, 7)->nullable()->after('pickup_location');
            $table->decimal('pickup_longitude', 10, 7)->nullable()->after('pickup_latitude');
            $table->string('pickup_place_id')->nullable()->after('pickup_longitude');

            $table->decimal('via_latitude', 10, 7)->nullable()->after('via_location');
            $table->decimal('via_longitude', 10, 7)->nullable()->after('via_latitude');
            $table->string('via_place_id')->nullable()->after('via_longitude');

            $table->decimal('destination_latitude', 10, 7)->nullable()->after('destination');
            $table->decimal('destination_longitude', 10, 7)->nullable()->after('destination_latitude');
            $table->string('destination_place_id')->nullable()->after('destination_longitude');

            $table->decimal('route_distance_km', 8, 2)->nullable()->after('duration_notes');
            $table->string('route_duration_text', 100)->nullable()->after('route_distance_km');
            $table->unsignedInteger('route_duration_minutes')->nullable()->after('route_duration_text');
            $table->json('route_legs')->nullable()->after('route_duration_minutes');
            $table->mediumText('route_geometry')->nullable()->after('route_legs');
            $table->timestamp('routed_at')->nullable()->after('route_geometry');
        });

        // 2. Add Google Maps location & routing fields to Enquiries table
        Schema::table('enquiries', function (Blueprint $table) {
            $table->decimal('pickup_latitude', 10, 7)->nullable()->after('pickup_location');
            $table->decimal('pickup_longitude', 10, 7)->nullable()->after('pickup_latitude');
            $table->string('pickup_place_id')->nullable()->after('pickup_longitude');

            $table->decimal('via_latitude', 10, 7)->nullable()->after('via_location');
            $table->decimal('via_longitude', 10, 7)->nullable()->after('via_latitude');
            $table->string('via_place_id')->nullable()->after('via_longitude');

            $table->decimal('destination_latitude', 10, 7)->nullable()->after('destination');
            $table->decimal('destination_longitude', 10, 7)->nullable()->after('destination_latitude');
            $table->string('destination_place_id')->nullable()->after('destination_longitude');

            $table->decimal('route_distance_km', 8, 2)->nullable()->after('duration_days');
            $table->string('route_duration_text', 100)->nullable()->after('route_distance_km');
            $table->unsignedInteger('route_duration_minutes')->nullable()->after('route_duration_text');
            $table->json('route_legs')->nullable()->after('route_duration_minutes');
            $table->mediumText('route_geometry')->nullable()->after('route_legs');
            $table->timestamp('routed_at')->nullable()->after('route_geometry');
        });

        // 3. Add Route Fields to Tour Packages table
        Schema::table('tour_packages', function (Blueprint $table) {
            $table->decimal('route_distance_km', 8, 2)->nullable()->after('duration_days');
            $table->string('route_duration_text', 100)->nullable()->after('route_distance_km');
            $table->json('route_legs')->nullable()->after('route_duration_text');
            $table->mediumText('route_geometry')->nullable()->after('route_legs');
            $table->json('route_locations')->nullable()->after('route_geometry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tour_packages', function (Blueprint $table) {
            $table->dropColumn([
                'route_distance_km',
                'route_duration_text',
                'route_legs',
                'route_geometry',
                'route_locations',
            ]);
        });

        Schema::table('enquiries', function (Blueprint $table) {
            $table->dropColumn([
                'pickup_latitude',
                'pickup_longitude',
                'pickup_place_id',
                'via_latitude',
                'via_longitude',
                'via_place_id',
                'destination_latitude',
                'destination_longitude',
                'destination_place_id',
                'route_distance_km',
                'route_duration_text',
                'route_duration_minutes',
                'route_legs',
                'route_geometry',
                'routed_at',
            ]);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'pickup_latitude',
                'pickup_longitude',
                'pickup_place_id',
                'via_latitude',
                'via_longitude',
                'via_place_id',
                'destination_latitude',
                'destination_longitude',
                'destination_place_id',
                'route_distance_km',
                'route_duration_text',
                'route_duration_minutes',
                'route_legs',
                'route_geometry',
                'routed_at',
            ]);
        });
    }
};
