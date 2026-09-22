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
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'terms_accepted')) {
                $table->boolean('terms_accepted')->default(false)->after('cancellation_status');
            }
            if (! Schema::hasColumn('bookings', 'terms_accepted_at')) {
                $table->timestamp('terms_accepted_at')->nullable()->after('terms_accepted');
            }
            if (! Schema::hasColumn('bookings', 'terms_version')) {
                $table->string('terms_version', 20)->nullable()->after('terms_accepted_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('bookings', 'terms_version')) {
                $cols[] = 'terms_version';
            }
            if (Schema::hasColumn('bookings', 'terms_accepted_at')) {
                $cols[] = 'terms_accepted_at';
            }
            if (Schema::hasColumn('bookings', 'terms_accepted')) {
                $cols[] = 'terms_accepted';
            }
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
