<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'customer_type')) {
                $table->string('customer_type', 50)->default('individual')->after('user_id')->index();
            }
            if (! Schema::hasColumn('customers', 'company_name')) {
                $table->string('company_name', 255)->nullable()->after('customer_type');
            }
            if (! Schema::hasColumn('customers', 'contact_person')) {
                $table->string('contact_person', 255)->nullable()->after('company_name');
            }
            if (! Schema::hasColumn('customers', 'gstin')) {
                $table->string('gstin', 30)->nullable()->after('contact_person');
            }
            if (! Schema::hasColumn('customers', 'billing_address')) {
                $table->text('billing_address')->nullable()->after('gstin');
            }
            if (! Schema::hasColumn('customers', 'partner_name')) {
                $table->string('partner_name', 255)->nullable()->after('billing_address');
            }
            if (! Schema::hasColumn('customers', 'partner_commission')) {
                $table->string('partner_commission', 100)->nullable()->after('partner_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'customer_type',
                'company_name',
                'contact_person',
                'gstin',
                'billing_address',
                'partner_name',
                'partner_commission',
            ]);
        });
    }
};
