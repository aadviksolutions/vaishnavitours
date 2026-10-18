<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'travel_partner_name')) {
                $table->string('travel_partner_name', 255)->nullable()->after('corporate_agreed_rate');
                $table->string('travel_partner_phone', 50)->nullable()->after('travel_partner_name');
                $table->string('travel_partner_email', 255)->nullable()->after('travel_partner_phone');
                $table->text('travel_partner_address')->nullable()->after('travel_partner_email');
                $table->decimal('travel_partner_commission', 10, 2)->nullable()->after('travel_partner_address');
                $table->text('travel_partner_notes')->nullable()->after('travel_partner_commission');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'travel_partner_name',
                'travel_partner_phone',
                'travel_partner_email',
                'travel_partner_address',
                'travel_partner_commission',
                'travel_partner_notes',
            ]);
        });
    }
};
