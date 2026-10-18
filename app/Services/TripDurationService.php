<?php

namespace App\Services;

use Carbon\Carbon;

class TripDurationService
{
    /**
     * Calculate round-trip journey duration details.
     *
     * @param  string|Carbon  $pickupDate  (Y-m-d)
     * @param  string|null    $pickupTime  (H:i:s or H:i)
     * @param  string|Carbon|null  $returnDate  (Y-m-d)
     * @param  string|null    $returnTime  (H:i:s or H:i)
     * @return array{
     *     days: int,
     *     calendar_days: int,
     *     total_hours: float,
     *     is_same_day: bool,
     *     is_overnight: bool,
     *     pickup_datetime: Carbon,
     *     return_datetime: Carbon,
     *     breakdown: string
     * }
     */
    public function calculate(
        string|Carbon $pickupDate,
        ?string $pickupTime = '06:00',
        string|Carbon|null $returnDate = null,
        ?string $returnTime = '22:00'
    ): array {
        $tz = config('app.timezone', 'Asia/Kolkata');

        $pickupDateStr = $pickupDate instanceof Carbon ? $pickupDate->format('Y-m-d') : trim($pickupDate);
        $pickupTimeStr = $pickupTime ? trim($pickupTime) : '06:00';
        if (strlen($pickupTimeStr) === 5) {
            $pickupTimeStr .= ':00';
        }

        $pickupDt = Carbon::createFromFormat('Y-m-d H:i:s', "{$pickupDateStr} {$pickupTimeStr}", $tz);

        if (! $returnDate) {
            $returnDate = $pickupDate;
        }
        $returnDateStr = $returnDate instanceof Carbon ? $returnDate->format('Y-m-d') : trim($returnDate);
        $returnTimeStr = $returnTime ? trim($returnTime) : '22:00';
        if (strlen($returnTimeStr) === 5) {
            $returnTimeStr .= ':00';
        }

        $returnDt = Carbon::createFromFormat('Y-m-d H:i:s', "{$returnDateStr} {$returnTimeStr}", $tz);

        // If return is recorded before pickup due to invalid inputs, normalize to at least pickup
        if ($returnDt->lt($pickupDt)) {
            $returnDt = $pickupDt->copy()->addHours(4);
        }

        $isSameDay = $pickupDt->toDateString() === $returnDt->toDateString();
        $totalHours = round($pickupDt->diffInMinutes($returnDt) / 60, 2);

        // Indian travel taxi policy:
        // Calendar day count: difference in calendar days + 1
        $startDate = $pickupDt->copy()->startOfDay();
        $endDate = $returnDt->copy()->startOfDay();
        $calendarDays = (int) $startDate->diffInDays($endDate) + 1;

        // Check for overnight trips within short window (e.g. departing 22:00, returning next day at 04:00)
        // If departing late night and returning early morning within 8 hours, it's considered an overnight single block
        $isOvernight = ! $isSameDay && $totalHours <= 12 && $pickupDt->hour >= 20;

        $billableDays = $calendarDays;
        if ($billableDays < 1) {
            $billableDays = 1;
        }

        $breakdown = $isSameDay
            ? "Same-day journey ({$totalHours} hours) = 1 Day"
            : "{$calendarDays} Calendar Days ({$totalHours} Total Hours) from {$pickupDt->format('d M Y, h:i A')} to {$returnDt->format('d M Y, h:i A')}";

        return [
            'days' => $billableDays,
            'calendar_days' => $calendarDays,
            'total_hours' => $totalHours,
            'is_same_day' => $isSameDay,
            'is_overnight' => $isOvernight,
            'pickup_datetime' => $pickupDt,
            'return_datetime' => $returnDt,
            'breakdown' => $breakdown,
        ];
    }
}
