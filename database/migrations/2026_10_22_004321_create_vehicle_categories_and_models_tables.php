<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Vehicle Categories Master Table
        if (! Schema::hasTable('vehicle_categories')) {
            Schema::create('vehicle_categories', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('name', 100)->index();
                $table->string('display_name', 150)->nullable();
                $table->text('description')->nullable();
                $table->integer('default_seating_capacity')->default(4);
                $table->integer('min_seating_capacity')->default(1);
                $table->integer('max_seating_capacity')->default(60);
                $table->boolean('is_capacity_configurable')->default(false);
                $table->boolean('is_active')->default(true)->index();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // 2. Vehicle Models Master Table
        if (! Schema::hasTable('vehicle_models')) {
            Schema::create('vehicle_models', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vehicle_category_id')->constrained('vehicle_categories')->cascadeOnDelete();
                $table->string('name', 100)->index();
                $table->string('brand', 60)->nullable();
                $table->integer('default_seating_capacity')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // 3. Add category & model fields to vehicles table safely
        Schema::table('vehicles', function (Blueprint $table) {
            if (! Schema::hasColumn('vehicles', 'vehicle_category_id')) {
                $table->foreignId('vehicle_category_id')->nullable()->after('vehicle_type')->constrained('vehicle_categories')->nullOnDelete();
            }
            if (! Schema::hasColumn('vehicles', 'vehicle_model_id')) {
                $table->foreignId('vehicle_model_id')->nullable()->after('vehicle_category_id')->constrained('vehicle_models')->nullOnDelete();
            }
        });

        // 4. Add category & model fields to bookings table safely
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'vehicle_category')) {
                $table->string('vehicle_category', 100)->nullable()->after('rate_category')->index();
            }
            if (! Schema::hasColumn('bookings', 'vehicle_model')) {
                $table->string('vehicle_model', 150)->nullable()->after('vehicle_category');
            }
            if (! Schema::hasColumn('bookings', 'vehicle_category_id')) {
                $table->foreignId('vehicle_category_id')->nullable()->after('vehicle_model')->constrained('vehicle_categories')->nullOnDelete();
            }
            if (! Schema::hasColumn('bookings', 'vehicle_model_id')) {
                $table->foreignId('vehicle_model_id')->nullable()->after('vehicle_category_id')->constrained('vehicle_models')->nullOnDelete();
            }
        });

        // 5. Seed initial 6 Vehicle Categories and their Associated Models
        $categoriesData = [
            [
                'code' => 'hatchback',
                'name' => 'Hatchback',
                'display_name' => 'Hatchback (Budget 4-Seater)',
                'description' => 'Compact and economical 4-passenger hatchbacks for quick city trips, daily commutes, and budget outstation travel.',
                'default_seating_capacity' => 4,
                'min_seating_capacity' => 4,
                'max_seating_capacity' => 5,
                'is_capacity_configurable' => false,
                'is_active' => true,
                'sort_order' => 1,
                'models' => [
                    ['name' => 'Maruti Suzuki WagonR', 'brand' => 'Maruti Suzuki', 'default_seating_capacity' => 4, 'sort_order' => 1],
                    ['name' => 'Maruti Suzuki Celerio', 'brand' => 'Maruti Suzuki', 'default_seating_capacity' => 4, 'sort_order' => 2],
                ],
            ],
            [
                'code' => 'sedan',
                'name' => 'Sedan',
                'display_name' => 'Sedan (Comfort 4-Seater)',
                'description' => 'Comfortable 4-passenger sedans with boot space for airport transfers, outstation business, and family travel.',
                'default_seating_capacity' => 4,
                'min_seating_capacity' => 4,
                'max_seating_capacity' => 5,
                'is_capacity_configurable' => false,
                'is_active' => true,
                'sort_order' => 2,
                'models' => [
                    ['name' => 'Maruti Suzuki Swift Dzire', 'brand' => 'Maruti Suzuki', 'default_seating_capacity' => 4, 'sort_order' => 1],
                    ['name' => 'Honda Amaze', 'brand' => 'Honda', 'default_seating_capacity' => 4, 'sort_order' => 2],
                    ['name' => 'Tata Zest', 'brand' => 'Tata', 'default_seating_capacity' => 4, 'sort_order' => 3],
                ],
            ],
            [
                'code' => 'suv_muv_6_1',
                'name' => 'SUV / MUV — 6+1 Seating',
                'display_name' => 'SUV / MUV — 6+1 Seating',
                'description' => 'Spacious 6+1 passenger vehicle with comfortable seating, ample boot space, and smooth highway handling for family trips and delegations.',
                'default_seating_capacity' => 7,
                'min_seating_capacity' => 6,
                'max_seating_capacity' => 7,
                'is_capacity_configurable' => false,
                'is_active' => true,
                'sort_order' => 3,
                'models' => [
                    ['name' => 'Maruti Suzuki Ertiga', 'brand' => 'Maruti Suzuki', 'default_seating_capacity' => 7, 'sort_order' => 1],
                ],
            ],
            [
                'code' => 'traveller',
                'name' => 'Traveller',
                'display_name' => 'Force Traveller (Configurable Seating)',
                'description' => 'Robust Force Traveller minivans for large families, pilgrim groups, wedding guests, and corporate outings with configurable seating capacity.',
                'default_seating_capacity' => 14,
                'min_seating_capacity' => 9,
                'max_seating_capacity' => 26,
                'is_capacity_configurable' => true,
                'is_active' => true,
                'sort_order' => 4,
                'models' => [
                    ['name' => 'Force Traveller', 'brand' => 'Force', 'default_seating_capacity' => 14, 'sort_order' => 1],
                ],
            ],
            [
                'code' => 'urbania',
                'name' => 'Urbania',
                'display_name' => 'Force Urbania (Luxury Executive)',
                'description' => 'Ultra-modern Force Urbania luxury van featuring plush reclining seats, panoramic windows, superior suspension, and customizable seating layout.',
                'default_seating_capacity' => 13,
                'min_seating_capacity' => 10,
                'max_seating_capacity' => 17,
                'is_capacity_configurable' => true,
                'is_active' => true,
                'sort_order' => 5,
                'models' => [
                    ['name' => 'Force Urbania', 'brand' => 'Force', 'default_seating_capacity' => 13, 'sort_order' => 1],
                ],
            ],
            [
                'code' => 'bus',
                'name' => 'Bus',
                'display_name' => 'Bus (Configurable Seating Capacity)',
                'description' => 'Full-size commercial luxury and semi-deluxe AC tourist buses for corporate staff transit, large tour groups, school trips, and wedding events with configurable passenger capacity.',
                'default_seating_capacity' => 32,
                'min_seating_capacity' => 20,
                'max_seating_capacity' => 56,
                'is_capacity_configurable' => true,
                'is_active' => true,
                'sort_order' => 6,
                'models' => [
                    ['name' => 'Bus', 'brand' => 'Commercial', 'default_seating_capacity' => 32, 'sort_order' => 1],
                ],
            ],
        ];

        foreach ($categoriesData as $catData) {
            $models = $catData['models'];
            unset($catData['models']);

            $existing = DB::table('vehicle_categories')->where('code', $catData['code'])->first();
            if ($existing) {
                $categoryId = $existing->id;
                DB::table('vehicle_categories')->where('id', $categoryId)->update(array_merge($catData, ['updated_at' => now()]));
            } else {
                $categoryId = DB::table('vehicle_categories')->insertGetId(array_merge($catData, ['created_at' => now(), 'updated_at' => now()]));
            }

            foreach ($models as $m) {
                $existingModel = DB::table('vehicle_models')
                    ->where('vehicle_category_id', $categoryId)
                    ->where('name', $m['name'])
                    ->first();

                if ($existingModel) {
                    DB::table('vehicle_models')->where('id', $existingModel->id)->update(array_merge($m, ['updated_at' => now()]));
                } else {
                    DB::table('vehicle_models')->insert(array_merge($m, [
                        'vehicle_category_id' => $categoryId,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]));
                }
            }
        }

        // 6. Backfill existing bookings with category values from pricing_details or vehicles
        $bookings = DB::table('bookings')->get();
        foreach ($bookings as $b) {
            $cat = null;
            if ($b->pricing_details) {
                $pd = json_decode($b->pricing_details, true);
                if (is_array($pd) && ! empty($pd['vehicle_category'])) {
                    $cat = $pd['vehicle_category'];
                }
            }
            if (! $cat && $b->vehicle_id) {
                $v = DB::table('vehicles')->where('id', $b->vehicle_id)->first();
                if ($v) {
                    $cat = $v->vehicle_type;
                }
            }
            if (! $cat) {
                $cat = 'Sedan';
            }

            // Normalize category if needed
            $catId = null;
            if (strcasecmp($cat, 'sedan') === 0) {
                $cat = 'Sedan';
                $catId = DB::table('vehicle_categories')->where('code', 'sedan')->value('id');
            } elseif (stripos($cat, 'ertiga') !== false || stripos($cat, 'muv') !== false || stripos($cat, 'suv') !== false) {
                $cat = 'SUV / MUV — 6+1 Seating';
                $catId = DB::table('vehicle_categories')->where('code', 'suv_muv_6_1')->value('id');
            } elseif (stripos($cat, 'hatchback') !== false) {
                $cat = 'Hatchback';
                $catId = DB::table('vehicle_categories')->where('code', 'hatchback')->value('id');
            } elseif (stripos($cat, 'traveller') !== false || stripos($cat, 'tempo') !== false) {
                $cat = 'Traveller';
                $catId = DB::table('vehicle_categories')->where('code', 'traveller')->value('id');
            } elseif (stripos($cat, 'urbania') !== false) {
                $cat = 'Urbania';
                $catId = DB::table('vehicle_categories')->where('code', 'urbania')->value('id');
            } elseif (stripos($cat, 'bus') !== false) {
                $cat = 'Bus';
                $catId = DB::table('vehicle_categories')->where('code', 'bus')->value('id');
            }

            DB::table('bookings')->where('id', $b->id)->update([
                'vehicle_category' => $cat,
                'vehicle_category_id' => $catId,
            ]);
        }

        // 7. Associate existing vehicles with categories and models
        $vehicles = DB::table('vehicles')->get();
        foreach ($vehicles as $v) {
            $name = strtolower($v->name.' '.$v->vehicle_type);
            $catCode = 'sedan';
            $modelName = null;

            if (str_contains($name, 'wagon') || str_contains($name, 'celerio') || str_contains($name, 'hatchback') || str_contains($name, 'tiago')) {
                $catCode = 'hatchback';
                $modelName = str_contains($name, 'celerio') ? 'Maruti Suzuki Celerio' : 'Maruti Suzuki WagonR';
            } elseif (str_contains($name, 'ertiga') || str_contains($name, 'muv') || str_contains($name, 'innova') || str_contains($name, 'suv')) {
                $catCode = 'suv_muv_6_1';
                $modelName = 'Maruti Suzuki Ertiga';
            } elseif (str_contains($name, 'traveller') || str_contains($name, 'tempo')) {
                $catCode = 'traveller';
                $modelName = 'Force Traveller';
            } elseif (str_contains($name, 'urbania')) {
                $catCode = 'urbania';
                $modelName = 'Force Urbania';
            } elseif (str_contains($name, 'bus')) {
                $catCode = 'bus';
                $modelName = 'Bus';
            } else {
                $catCode = 'sedan';
                if (str_contains($name, 'amaze')) {
                    $modelName = 'Honda Amaze';
                } elseif (str_contains($name, 'zest')) {
                    $modelName = 'Tata Zest';
                } else {
                    $modelName = 'Maruti Suzuki Swift Dzire';
                }
            }

            $catId = DB::table('vehicle_categories')->where('code', $catCode)->value('id');
            $modelId = null;
            if ($catId && $modelName) {
                $modelId = DB::table('vehicle_models')
                    ->where('vehicle_category_id', $catId)
                    ->where('name', $modelName)
                    ->value('id');
            }

            DB::table('vehicles')->where('id', $v->id)->update([
                'vehicle_category_id' => $catId,
                'vehicle_model_id' => $modelId,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['vehicle_category_id']);
            $table->dropForeign(['vehicle_model_id']);
            $table->dropColumn(['vehicle_category', 'vehicle_model', 'vehicle_category_id', 'vehicle_model_id']);
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['vehicle_category_id']);
            $table->dropForeign(['vehicle_model_id']);
            $table->dropColumn(['vehicle_category_id', 'vehicle_model_id']);
        });

        Schema::dropIfExists('vehicle_models');
        Schema::dropIfExists('vehicle_categories');
    }
};
