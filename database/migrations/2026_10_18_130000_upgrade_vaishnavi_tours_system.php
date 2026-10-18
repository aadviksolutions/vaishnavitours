<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Booking Categories Master Table
        if (! Schema::hasTable('booking_categories')) {
            Schema::create('booking_categories', function (Blueprint $table) {
                $table->id();
                $table->string('code', 30)->unique();
                $table->string('name', 100);
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_system')->default(false);
                $table->timestamps();
            });

            // Seed required 4 core categories
            $categories = [
                ['code' => 'CORP', 'name' => 'Corporate Booking', 'description' => 'Dedicated corporate booking with company billing', 'is_active' => true, 'is_system' => true],
                ['code' => 'WEB', 'name' => 'Website Booking', 'description' => 'Online booking created via website booking intake', 'is_active' => true, 'is_system' => true],
                ['code' => 'MAN', 'name' => 'Manually Created Booking', 'description' => 'Admin desk phone / walk-in booking', 'is_active' => true, 'is_system' => true],
                ['code' => 'TP', 'name' => 'Third-Party / Travel Partner', 'description' => 'Bookings referred by travel partners and agents', 'is_active' => true, 'is_system' => true],
            ];
            foreach ($categories as $cat) {
                DB::table('booking_categories')->updateOrInsert(['code' => $cat['code']], array_merge($cat, ['created_at' => now(), 'updated_at' => now()]));
            }
        }

        // 2. Pickup Locations Master Table
        if (! Schema::hasTable('pickup_locations')) {
            Schema::create('pickup_locations', function (Blueprint $table) {
                $table->id();
                $table->string('name', 255)->unique();
                $table->string('address', 255)->nullable();
                $table->string('city', 100)->default('Bilaspur');
                $table->string('state', 100)->default('Chhattisgarh');
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });

            // Pre-seed common regional pickup points
            $locations = [
                ['name' => 'Bilaspur Railway Station', 'city' => 'Bilaspur'],
                ['name' => 'Bilaspur City Center (Vyapar Vihar)', 'city' => 'Bilaspur'],
                ['name' => 'Raipur Swami Vivekananda Airport (RPR)', 'city' => 'Raipur'],
                ['name' => 'Raipur Railway Station', 'city' => 'Raipur'],
                ['name' => 'Korba Bus Stand', 'city' => 'Korba'],
                ['name' => 'Raigarh Railway Station', 'city' => 'Raigarh'],
                ['name' => 'Ambikapur Clock Tower', 'city' => 'Ambikapur'],
                ['name' => 'Bhilai Power House', 'city' => 'Bhilai'],
            ];
            foreach ($locations as $loc) {
                DB::table('pickup_locations')->updateOrInsert(['name' => $loc['name']], array_merge($loc, ['state' => 'Chhattisgarh', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]));
            }
        }

        // 3. Tour Packages Master Table
        if (! Schema::hasTable('tour_packages')) {
            Schema::create('tour_packages', function (Blueprint $table) {
                $table->id();
                $table->string('name', 255);
                $table->string('slug', 255)->unique();
                $table->text('description')->nullable();
                $table->string('route', 255)->nullable();
                $table->string('duration', 100)->default('1 Day');
                $table->integer('duration_days')->default(1);
                $table->text('destinations')->nullable();
                $table->text('applicable_vehicles')->nullable();
                $table->decimal('sedan_price', 10, 2)->nullable();
                $table->decimal('ertiga_price', 10, 2)->nullable();
                $table->decimal('innova_price', 10, 2)->nullable();
                $table->text('terms')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Seed sample tour packages
            $packages = [
                [
                    'name' => 'Amarkantak Sacred Pilgrimage Tour',
                    'slug' => 'amarkantak-pilgrimage-tour',
                    'description' => 'Holy pilgrimage to Narmada Udgam, Mai ki Bagiya, Kapildhara, and ancient Kalachuri temples.',
                    'route' => 'Bilaspur - Pendra - Amarkantak - Bilaspur',
                    'duration' => '2 Days / 1 Night',
                    'duration_days' => 2,
                    'destinations' => 'Narmada Udgam, Kapildhara Falls, Dudhdhara, Kabir Chabutra',
                    'applicable_vehicles' => 'Sedan, Ertiga, Innova Crysta',
                    'sedan_price' => 5500.00,
                    'ertiga_price' => 7500.00,
                    'innova_price' => 10500.00,
                    'terms' => 'Driver food extra. Toll and parking included on highway route.',
                    'is_active' => true,
                ],
                [
                    'name' => 'Mainpat - Shimla of Chhattisgarh Tour',
                    'slug' => 'mainpat-tibetan-highland-tour',
                    'description' => 'Explore the scenic Tibetan settlement, Tiger Point waterfall, Fish Point, and Buddhist monastery in Mainpat.',
                    'route' => 'Bilaspur - Ambikapur - Mainpat - Bilaspur',
                    'duration' => '3 Days / 2 Nights',
                    'duration_days' => 3,
                    'destinations' => 'Tiger Point, Fish Point, Dhakpo Monastery, Jaljali Bouncing Land',
                    'applicable_vehicles' => 'Sedan, Ertiga, Innova Crysta',
                    'sedan_price' => 9500.00,
                    'ertiga_price' => 12500.00,
                    'innova_price' => 16500.00,
                    'terms' => 'Hill terrain rates apply. Driver night allowance ₹400/night.',
                    'is_active' => true,
                ],
            ];
            foreach ($packages as $pkg) {
                DB::table('tour_packages')->updateOrInsert(['slug' => $pkg['slug']], array_merge($pkg, ['created_at' => now(), 'updated_at' => now()]));
            }
        }

        // 4. Update Bookings Table
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'booking_category')) {
                $table->string('booking_category', 30)->default('WEB')->after('booking_id')->index();
            }
            if (! Schema::hasColumn('bookings', 'via_location')) {
                $table->string('via_location', 255)->nullable()->after('pickup_location');
            }
            if (! Schema::hasColumn('bookings', 'tour_package_id')) {
                $table->foreignId('tour_package_id')->nullable()->after('driver_id')->constrained('tour_packages')->nullOnDelete();
            }
            if (! Schema::hasColumn('bookings', 'corporate_company_name')) {
                $table->string('corporate_company_name', 255)->nullable()->after('tour_package_id');
                $table->string('corporate_contact_person', 255)->nullable()->after('corporate_company_name');
                $table->string('corporate_phone', 30)->nullable()->after('corporate_contact_person');
                $table->string('corporate_email', 255)->nullable()->after('corporate_phone');
                $table->string('corporate_gstin', 30)->nullable()->after('corporate_email');
                $table->text('corporate_billing_address')->nullable()->after('corporate_gstin');
                $table->string('corporate_payment_terms', 100)->nullable()->after('corporate_billing_address');
                $table->decimal('corporate_agreed_rate', 10, 2)->nullable()->after('corporate_payment_terms');
            }
            if (! Schema::hasColumn('bookings', 'duration_days')) {
                $table->integer('duration_days')->default(1)->after('return_date');
                $table->decimal('duration_hours', 8, 2)->nullable()->after('duration_days');
                $table->text('duration_notes')->nullable()->after('duration_hours');
                $table->time('return_time')->nullable()->after('duration_notes');
            }
            if (! Schema::hasColumn('bookings', 'taxable_amount')) {
                $table->decimal('taxable_amount', 10, 2)->nullable()->after('pricing_details');
                $table->decimal('gst_rate', 5, 2)->default(5.00)->after('taxable_amount');
                $table->decimal('cgst_amount', 10, 2)->default(0.00)->after('gst_rate');
                $table->decimal('sgst_amount', 10, 2)->default(0.00)->after('cgst_amount');
                $table->decimal('igst_amount', 10, 2)->default(0.00)->after('sgst_amount');
                $table->decimal('gst_amount', 10, 2)->default(0.00)->after('igst_amount');
                $table->boolean('is_interstate')->default(false)->after('gst_amount');
            }
        });

        // 5. Update Vehicles Table (Documents & Third-Party support)
        Schema::table('vehicles', function (Blueprint $table) {
            if (! Schema::hasColumn('vehicles', 'is_third_party')) {
                $table->boolean('is_third_party')->default(false)->after('status');
                $table->string('vendor_name', 255)->nullable()->after('is_third_party');
                $table->string('vendor_phone', 30)->nullable()->after('vendor_name');
                $table->string('vendor_email', 255)->nullable()->after('vendor_phone');
                $table->decimal('vendor_agreed_amount', 10, 2)->nullable()->after('vendor_email');
                $table->decimal('vendor_paid_amount', 10, 2)->default(0.00)->after('vendor_agreed_amount');
                $table->decimal('vendor_balance_amount', 10, 2)->default(0.00)->after('vendor_paid_amount');
                $table->string('vendor_payment_status', 30)->default('Pending')->after('vendor_balance_amount');
            }
        });

        // 6. Update Trips Table (Separate Journey Completion vs Payment Completion)
        Schema::table('trips', function (Blueprint $table) {
            if (! Schema::hasColumn('trips', 'journey_status')) {
                $table->string('journey_status', 40)->default('Scheduled')->after('status')->index();
            }
            if (! Schema::hasColumn('trips', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('completed_at');
                $table->foreignId('closed_by')->nullable()->after('closed_at')->constrained('users')->nullOnDelete();
                $table->text('closure_reason')->nullable()->after('closed_by');
            }
        });

        // 7. Third-Party Vehicle Payments Table
        if (! Schema::hasTable('third_party_payments')) {
            Schema::create('third_party_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
                $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
                $table->decimal('amount', 10, 2);
                $table->date('payment_date');
                $table->string('payment_method', 50)->default('UPI');
                $table->string('reference_number', 100)->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 8. Trip & Booking Expenses Table
        if (! Schema::hasTable('expenses')) {
            Schema::create('expenses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
                $table->foreignId('trip_id')->nullable()->constrained('trips')->nullOnDelete();
                $table->string('category', 50)->default('Fuel'); // Fuel, Toll, Other
                $table->decimal('amount', 10, 2);
                $table->date('expense_date');
                $table->text('description')->nullable();
                $table->string('receipt_number', 100)->nullable();
                $table->string('receipt_path', 255)->nullable();
                $table->foreignId('entered_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 9. Complaints & Reviews System (Separate Driver vs Vehicle)
        if (! Schema::hasTable('complaints')) {
            Schema::create('complaints', function (Blueprint $table) {
                $table->id();
                $table->string('type', 30); // Driver Complaint / Driver Review OR Vehicle Complaint / Vehicle Review
                $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
                $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
                $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
                $table->string('customer_name', 255);
                $table->string('customer_mobile', 30)->nullable();
                $table->string('customer_email', 255)->nullable();
                $table->text('description');
                $table->integer('rating')->nullable(); // 1 to 5
                $table->date('complaint_date');
                $table->string('status', 30)->default('Open'); // Open, In Progress, Resolved, Dismissed
                $table->text('resolution_notes')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 10. Update Enquiries Table
        Schema::table('enquiries', function (Blueprint $table) {
            if (! Schema::hasColumn('enquiries', 'enquiry_id')) {
                $table->string('enquiry_id', 50)->nullable()->after('id')->index();
                $table->string('source', 50)->default('Website')->after('enquiry_id')->index(); // Website, Manual, Corporate, Third-Party
                $table->string('booking_type', 50)->nullable()->after('source');
                $table->string('pickup_location', 255)->nullable()->after('phone');
                $table->string('via_location', 255)->nullable()->after('pickup_location');
                $table->string('destination', 255)->nullable()->after('via_location');
                $table->date('travel_date')->nullable()->after('destination');
                $table->date('return_date')->nullable()->after('travel_date');
                $table->integer('duration_days')->nullable()->after('return_date');
                $table->string('vehicle_preference', 100)->nullable()->after('duration_days');
                $table->decimal('quotation_amount', 10, 2)->nullable()->after('vehicle_preference');
                $table->foreignId('assigned_to')->nullable()->after('quotation_amount')->constrained('users')->nullOnDelete();
                $table->text('notes')->nullable()->after('message');
                $table->foreignId('converted_booking_id')->nullable()->after('notes')->constrained('bookings')->nullOnDelete();
            }
        });

        // 11. Update Invoices Table
        Schema::table('invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('invoices', 'taxable_amount')) {
                $table->decimal('taxable_amount', 10, 2)->nullable()->after('total_amount');
                $table->decimal('gst_rate', 5, 2)->default(5.00)->after('taxable_amount');
                $table->decimal('cgst_amount', 10, 2)->default(0.00)->after('gst_rate');
                $table->decimal('sgst_amount', 10, 2)->default(0.00)->after('cgst_amount');
                $table->decimal('igst_amount', 10, 2)->default(0.00)->after('sgst_amount');
                $table->decimal('gst_amount', 10, 2)->default(0.00)->after('igst_amount');
                $table->string('customer_gstin', 30)->nullable()->after('gst_amount');
                $table->string('booking_category', 30)->default('WEB')->after('customer_gstin');
                $table->integer('version')->default(1)->after('booking_category');
            }
        });
    }

    public function down(): void
    {
        // Safe rollback
        Schema::dropIfExists('complaints');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('third_party_payments');
        Schema::dropIfExists('tour_packages');
        Schema::dropIfExists('pickup_locations');
        Schema::dropIfExists('booking_categories');
    }
};
