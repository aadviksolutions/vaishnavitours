<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancellation_reason');
            }
            if (!Schema::hasColumn('bookings', 'cancelled_by')) {
                $table->foreignId('cancelled_by')->nullable()->after('cancelled_at')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('bookings', 'cancellation_status')) {
                $table->string('cancellation_status', 30)->nullable()->after('cancelled_by');
            }
        });

        Schema::table('booking_status_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_status_histories', 'remarks')) {
                $table->text('remarks')->nullable()->after('new_status');
            }
        });

        Schema::table('trips', function (Blueprint $table) {
            if (!Schema::hasColumn('trips', 'trip_status')) {
                $table->string('trip_status', 40)->default('Scheduled')->after('status')->index();
            }
            if (!Schema::hasColumn('trips', 'notes')) {
                $table->text('notes')->nullable()->after('route_notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'cancellation_status')) {
                $table->dropColumn('cancellation_status');
            }
            if (Schema::hasColumn('bookings', 'cancelled_by')) {
                $table->dropForeign(['cancelled_by']);
                $table->dropColumn('cancelled_by');
            }
            if (Schema::hasColumn('bookings', 'cancelled_at')) {
                $table->dropColumn('cancelled_at');
            }
        });

        Schema::table('booking_status_histories', function (Blueprint $table) {
            if (Schema::hasColumn('booking_status_histories', 'remarks')) {
                $table->dropColumn('remarks');
            }
        });

        Schema::table('trips', function (Blueprint $table) {
            if (Schema::hasColumn('trips', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('trips', 'trip_status')) {
                $table->dropColumn('trip_status');
            }
        });
    }
};
