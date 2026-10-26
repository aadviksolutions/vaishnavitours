<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('vehicle_categories')) {
            return;
        }

        $ambulanceCategories = [
            [
                'code' => 'ambulance_body_freezer',
                'name' => 'Ambulance with Body Freezer',
                'display_name' => 'Ambulance with Body Freezer',
                'description' => 'Dedicated ambulance transportation with body freezer facility for safe and respectful transportation of deceased persons.',
                'default_seating_capacity' => 2,
                'min_seating_capacity' => 1,
                'max_seating_capacity' => 4,
                'is_capacity_configurable' => false,
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'code' => 'ambulance_ventilator',
                'name' => 'Ambulance with Ventilator',
                'display_name' => 'Ambulance with Ventilator',
                'description' => 'Ambulance transportation equipped with ventilator support for critical patient transfers.',
                'default_seating_capacity' => 2,
                'min_seating_capacity' => 1,
                'max_seating_capacity' => 4,
                'is_capacity_configurable' => false,
                'is_active' => true,
                'sort_order' => 11,
            ],
            [
                'code' => 'ambulance_icu',
                'name' => 'ICU Ambulance',
                'display_name' => 'ICU Ambulance',
                'description' => 'Advanced Cardiac Life Support (ACLS / ICU) ambulance for emergency critical care transportation.',
                'default_seating_capacity' => 2,
                'min_seating_capacity' => 1,
                'max_seating_capacity' => 4,
                'is_capacity_configurable' => false,
                'is_active' => true,
                'sort_order' => 12,
            ],
            [
                'code' => 'ambulance_dead_body',
                'name' => 'Dead Body Transfer',
                'display_name' => 'Dead Body Transfer Ambulance',
                'description' => 'Specialized hearse / mortuary ambulance for safe, hygienic and dignified deceased person transfer.',
                'default_seating_capacity' => 2,
                'min_seating_capacity' => 1,
                'max_seating_capacity' => 4,
                'is_capacity_configurable' => false,
                'is_active' => true,
                'sort_order' => 13,
            ],
        ];

        foreach ($ambulanceCategories as $cat) {
            $existing = DB::table('vehicle_categories')->where('code', $cat['code'])->first();
            if ($existing) {
                DB::table('vehicle_categories')->where('id', $existing->id)->update(array_merge($cat, [
                    'updated_at' => now(),
                ]));
            } else {
                DB::table('vehicle_categories')->insert(array_merge($cat, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('vehicle_categories')) {
            return;
        }

        DB::table('vehicle_categories')->whereIn('code', [
            'ambulance_body_freezer',
            'ambulance_ventilator',
            'ambulance_icu',
            'ambulance_dead_body',
        ])->delete();
    }
};
