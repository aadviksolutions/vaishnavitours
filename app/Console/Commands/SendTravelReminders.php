<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\WhatsappService;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Sends WhatsApp travel reminder to customers whose trip is tomorrow.
 * Schedule in console.php: Schedule::command('whatsapp:travel-reminders')->dailyAt('09:00');
 */
class SendTravelReminders extends Command
{
    protected $signature = 'whatsapp:travel-reminders
                            {--dry-run : Preview without sending}
                            {--date= : Override date (Y-m-d) for testing}';

    protected $description = 'Send WhatsApp travel reminder to customers with trips tomorrow';

    public function handle(WhatsappService $whatsapp): int
    {
        $targetDate = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::tomorrow();

        $this->info("Sending travel reminders for: {$targetDate->toDateString()}");

        $bookings = Booking::with(['customer', 'driver', 'vehicle'])
            ->whereDate('travel_date', $targetDate->toDateString())
            ->whereNotIn('booking_status', ['Cancelled', 'Completed'])
            ->get();

        if ($bookings->isEmpty()) {
            $this->info('No upcoming bookings found for that date.');

            return self::SUCCESS;
        }

        $sent = 0;
        $skipped = 0;

        foreach ($bookings as $booking) {
            $phone = $booking->customer?->phone;

            if (! $phone) {
                $this->warn("  Skip #{$booking->booking_id}: no customer phone");
                $skipped++;

                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("  [DRY-RUN] Would send reminder to #{$booking->booking_id} → {$phone}");
                $sent++;

                continue;
            }

            $whatsapp->sendEvent(
                'travel_reminder',
                $whatsapp->bookingVariables($booking),
                $phone,
                $booking
            );

            $this->info("  ✅ Reminder sent: #{$booking->booking_id} → {$phone}");
            $sent++;
        }

        $this->info("Done. Sent: {$sent}, Skipped: {$skipped}");

        return self::SUCCESS;
    }
}
