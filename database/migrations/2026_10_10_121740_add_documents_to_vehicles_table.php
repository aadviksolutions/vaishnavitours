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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('insurance_number')->nullable()->after('ac_non_ac');
            $table->date('insurance_expiry')->nullable()->after('insurance_number');
            $table->date('fitness_expiry')->nullable()->after('insurance_expiry');
            $table->date('permit_expiry')->nullable()->after('fitness_expiry');
            $table->date('puc_expiry')->nullable()->after('permit_expiry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'insurance_number',
                'insurance_expiry',
                'fitness_expiry',
                'permit_expiry',
                'puc_expiry',
            ]);
        });
    }
};
