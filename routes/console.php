<?php

use App\Models\Booking;
use App\Models\Driver;
use App\Models\Notification;
use App\Models\Vehicle;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Module 16: Automated reminders for upcoming bookings, pending payments, and fleet compliance.
 */
Artisan::command('vaishnavi:send-reminders', function () {
    $this->info('Starting automated reminders engine for Vaishnavi Tours...');

    $tomorrow = now()->addDay()->toDateString();

    // 1. Upcoming trips within 24 hours
    $upcomingBookings = Booking::where('travel_date', $tomorrow)
        ->whereIn('booking_status', ['Pending', 'Confirmed', 'Vehicle Assigned', 'Driver Assigned'])
        ->get();

    $this->info("Found {$upcomingBookings->count()} upcoming trip(s) scheduled for tomorrow.");
    foreach ($upcomingBookings as $b) {
        // Customer reminder
        Notification::firstOrCreate([
            'user_id' => $b->customer_id,
            'title' => "Trip Reminder: #{$b->booking_id} Tomorrow",
            'type' => 'booking_reminder',
        ], [
            'message' => "Your trip to {$b->destination} is scheduled for tomorrow at {$b->travel_time}. Please be ready at {$b->pickup_location}.",
            'action_url' => "/customer/bookings/{$b->id}",
        ]);

        // Admin alert
        Notification::firstOrCreate([
            'user_id' => null,
            'title' => "Upcoming Booking Tomorrow: #{$b->booking_id}",
            'type' => 'system',
        ], [
            'message' => "Trip #{$b->booking_id} ({$b->pickup_location} ➔ {$b->destination}) departs tomorrow at {$b->travel_time}.",
            'action_url' => "/admin/bookings/{$b->id}",
        ]);
    }

    // 2. Pending payment balance reminders
    $pendingBookings = Booking::where('balance_amount', '>', 0)
        ->whereIn('booking_status', ['Confirmed', 'Vehicle Assigned', 'Driver Assigned', 'Completed'])
        ->where('travel_date', '<=', now()->toDateString())
        ->get();

    $this->info("Found {$pendingBookings->count()} booking(s) with pending balances.");
    foreach ($pendingBookings as $pb) {
        Notification::firstOrCreate([
            'user_id' => $pb->customer_id,
            'title' => 'Pending Payment Reminder: ₹'.number_format($pb->balance_amount, 2),
            'type' => 'payment_reminder',
        ], [
            'message' => 'You have an outstanding balance of ₹'.number_format($pb->balance_amount, 2)." for booking #{$pb->booking_id}.",
            'action_url' => "/customer/bookings/{$pb->id}",
        ]);
    }

    // 3. Vehicle document expiry compliance check
    $vehicles = Vehicle::where('status', '!=', 'Inactive')->get();
    foreach ($vehicles as $veh) {
        $expiring = $veh->getExpiringDocuments(15);
        if (! empty($expiring)) {
            $docList = implode(', ', array_keys($expiring));
            Notification::firstOrCreate([
                'user_id' => null,
                'title' => "Vehicle Compliance Alert: {$veh->name}",
                'type' => 'compliance',
            ], [
                'message' => "Documents ({$docList}) for vehicle {$veh->name} ({$veh->registration_number}) are expiring or expired.",
                'action_url' => "/admin/vehicles/{$veh->id}",
            ]);
        }
    }

    // 4. Driver licence expiry check
    $drivers = Driver::where('status', '!=', 'Inactive')->get();
    foreach ($drivers as $dr) {
        $lic = $dr->getLicenseStatus();
        if (in_array($lic['status'], ['expired', 'expiring_soon'])) {
            Notification::firstOrCreate([
                'user_id' => null,
                'title' => "Chauffeur Licence Alert: {$dr->name}",
                'type' => 'compliance',
            ], [
                'message' => "Chauffeur {$dr->name} licence ({$dr->license_number}) {$lic['label']}.",
                'action_url' => "/admin/drivers/{$dr->id}",
            ]);
        }
    }

    $this->info('Automated reminders and compliance checks executed successfully.');
})->purpose('Send automated booking, payment, and fleet compliance reminders');

/**
 * Module 18: Secure database backup routine.
 */
Artisan::command('vaishnavi:backup-db', function () {
    $this->info('Starting database backup routine...');

    $backupDir = storage_path('app/backups');
    if (! File::exists($backupDir)) {
        File::makeDirectory($backupDir, 0755, true);
    }

    $timestamp = now()->format('Y_m_d_His');
    $sqliteSource = database_path('database.sqlite');

    if (File::exists($sqliteSource)) {
        $backupFile = "{$backupDir}/backup_{$timestamp}.sqlite";
        File::copy($sqliteSource, $backupFile);
        $this->info("SQLite database backed up securely to: {$backupFile}");
    } else {
        $this->warn('No SQLite source file found at database/database.sqlite.');
    }

    // Rotate backups: keep last 14 copies
    $files = File::files($backupDir);
    if (count($files) > 14) {
        usort($files, fn ($a, $b) => $a->getMTime() <=> $b->getMTime());
        $toDelete = array_slice($files, 0, count($files) - 14);
        foreach ($toDelete as $f) {
            File::delete($f->getRealPath());
        }
        $this->info('Old backups pruned. Retained latest 14 snapshots.');
    }

    $this->info('Database backup completed successfully.');
})->purpose('Create a secure, timestamped backup snapshot of the database');

// Schedule tasks
Schedule::command('vaishnavi:send-reminders')->dailyAt('08:00');
Schedule::command('vaishnavi:backup-db')->dailyAt('02:00');
