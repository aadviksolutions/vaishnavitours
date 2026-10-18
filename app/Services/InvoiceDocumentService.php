<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Throwable;

class InvoiceDocumentService
{
    private const SETTING_KEYS = [
        'company_name',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'pincode',
        'phone_primary',
        'contact_email',
        'gstin',
        'bank_account_name',
        'bank_ifsc_code',
        'bank_account_number',
        'bank_name',
        'booking_terms',
    ];

    /**
     * Generate or sync invoice model for a booking with 5% GST and legal standards.
     */
    public function generateForBooking(Booking $booking): Invoice
    {
        return app(PaymentService::class)->syncInvoice($booking);
    }

    /**
     * @return array<string, mixed>
     */
    public function forBooking(Booking $booking): array
    {
        $booking->loadMissing([
            'invoice',
            'payments',
            'customer.customer',
            'vehicle',
            'driver',
            'rateVehiclePrice',
        ]);

        $settings = $this->settings();
        $invoice = $booking->invoice;
        $customer = $booking->customer;
        $customerProfile = $customer?->customer;
        $totalAmount = round((float) ($invoice?->total_amount ?? $booking->total_amount ?? 0), 2);
        $receivedAmount = $this->receivedAmount($booking, $invoice?->paid_amount);
        $tax = $this->taxDetails($booking, $totalAmount, $customerProfile?->state);

        $customerAddress = array_filter([
            $booking->corporate_billing_address ?: $customerProfile?->address,
            $customerProfile?->city,
            $customerProfile?->state,
            $customerProfile?->pincode,
        ], static fn (?string $value): bool => filled($value));

        $viaText = $booking->via_location ? 'via '.$booking->via_location : null;

        $serviceDescription = trim(implode(' ', array_filter([
            $booking->pickup_location,
            $viaText,
            'to',
            $booking->destination,
            str($booking->trip_type)->replace('_', ' ')->title()->toString(),
            'Taxi Service',
            $booking->vehicle?->name ? 'with '.$booking->vehicle->name : null,
        ], static fn (?string $value): bool => filled($value))));

        $companyName = $this->setting($settings, 'company_name', config('vaishnavi.business_name', 'Vaishnavi Tours'));

        $category = $booking->booking_category ?: 'WEB';
        $categoryLabel = match ($category) {
            'CORP' => 'Corporate Booking',
            'WEB' => 'Website Booking',
            'MAN' => 'Manual Booking',
            'TP' => 'Third-Party Partner Booking',
            default => $category,
        };

        $customerGstin = $booking->corporate_gstin ?: $customerProfile?->getAttribute('gstin');

        return [
            'company' => [
                'name' => $companyName,
                'address' => $this->companyAddress($settings),
                'phone' => $this->setting($settings, 'phone_primary', config('vaishnavi.phone_primary', '+91 92447 84443')),
                'email' => $this->setting($settings, 'contact_email', config('vaishnavi.contact_email', 'info@vaishnavitours.in')),
                'gstin' => $this->setting($settings, 'gstin', config('vaishnavi.gstin', '22AALPV8963D1Z1')),
            ],
            'invoice' => [
                'number' => $invoice?->invoice_number ?? ('INV-'.date('Y').'-'.str_pad((string) $booking->id, 5, '0', STR_PAD_LEFT)),
                'date' => $invoice?->issued_at ?? $invoice?->created_at ?? now(),
                'category' => $category,
                'category_label' => $categoryLabel,
            ],
            'booking' => [
                'id' => $booking->booking_id,
                'category' => $category,
                'category_label' => $categoryLabel,
                'pickup' => $booking->pickup_location,
                'via' => $booking->via_location,
                'destination' => $booking->destination,
                'travel_date' => $booking->travel_date,
                'return_date' => $booking->return_date,
                'trip_type' => $booking->trip_type,
            ],
            'customer' => [
                'name' => $booking->corporate_company_name ? "{$booking->corporate_company_name} (Attn: {$booking->corporate_contact_person})" : ($customer?->name ?? 'Valued Customer'),
                'company_name' => $booking->corporate_company_name,
                'contact_person' => $booking->corporate_contact_person,
                'address' => implode(', ', $customerAddress),
                'mobile' => $booking->corporate_phone ?: $customer?->phone,
                'gstin' => $customerGstin,
                'pan' => $customerProfile?->getAttribute('pan'),
                'place_of_supply' => $customerProfile?->state ?: 'Chhattisgarh',
            ],
            'driver' => [
                'name' => $booking->driver?->name ?? 'Assigned Chauffeur',
                'mobile' => $booking->driver?->mobile,
            ],
            'vehicle' => [
                'name' => $booking->vehicle?->name,
                'number' => $booking->vehicle?->registration_number ?? 'Confirmed Vehicle',
                'category' => $booking->vehicle?->vehicle_type,
            ],
            'service' => [
                'description' => $serviceDescription,
                'travel_date' => $booking->travel_date,
                'quantity' => '1 TRIP',
                'rate' => $tax['taxable_amount'],
                'tax' => $tax,
                'amount' => $totalAmount,
            ],
            'totals' => [
                'taxable_amount' => $tax['taxable_amount'],
                'gst_rate' => $tax['gst_rate'],
                'cgst_amount' => $tax['cgst_amount'],
                'sgst_amount' => $tax['sgst_amount'],
                'igst_amount' => $tax['igst_amount'],
                'gst_amount' => $tax['gst_amount'],
                'tax_recorded' => $tax['tax_recorded'],
                'total_amount' => $totalAmount,
                'received_amount' => $receivedAmount,
                'balance_amount' => round($totalAmount - $receivedAmount, 2),
                'amount_in_words' => $this->amountInWords($totalAmount),
            ],
            'bank' => [
                'account_name' => $this->setting($settings, 'bank_account_name', 'Vaishnavi Tours'),
                'ifsc_code' => $this->setting($settings, 'bank_ifsc_code', 'SBIN0000336'),
                'account_number' => $this->setting($settings, 'bank_account_number', '38491029384'),
                'bank_name' => $this->setting($settings, 'bank_name', 'State Bank of India'),
            ],
            'notes' => array_filter([
                'Booking Reference: '.$booking->booking_id.' ['.$category.']',
                $booking->via_location ? 'Via Location: '.$booking->via_location : null,
                $booking->corporate_company_name ? 'Corporate Billing Account: '.$booking->corporate_company_name : null,
                $booking->travel_partner_name ? 'Travel Partner Referral: '.$booking->travel_partner_name.($booking->travel_partner_phone ? ' (Ph: '.$booking->travel_partner_phone.')' : '') : null,
                $booking->notes,
            ], static fn (?string $value): bool => filled($value)),
            'terms' => $this->setting($settings, 'booking_terms', '1. Toll tax, state border tax, and parking charges are extra where applicable unless specifically included. 2. Night driving charges applicable between 10:00 PM and 06:00 AM. 3. System generated tax invoice with standard 5% GST under GTA / passenger transport service.'),
        ];
    }

    public function amountInWords(float $amount): string
    {
        $amountInPaise = (int) round($amount * 100);
        $rupees = intdiv($amountInPaise, 100);
        $paise = $amountInPaise % 100;
        $words = $this->numberInWords($rupees).' Rupees';

        if ($paise > 0) {
            $words .= ' and '.$this->numberInWords($paise).' Paise';
        }

        return $words.' Only';
    }

    private function settings(): Collection
    {
        try {
            return Setting::query()
                ->whereIn('key', self::SETTING_KEYS)
                ->pluck('value', 'key');
        } catch (Throwable) {
            return collect();
        }
    }

    private function setting(Collection $settings, string $key, ?string $fallback = null): ?string
    {
        $value = $settings->get($key);

        return filled($value) ? trim((string) $value) : (filled($fallback) ? trim((string) $fallback) : null);
    }

    private function receivedAmount(Booking $booking, ?float $fallback = null): float
    {
        $paid = $booking->payments
            ->where('status', 'Success')
            ->sum('amount');

        if ($paid > 0) {
            return round((float) $paid, 2);
        }

        return round((float) ($fallback ?? $booking->paid_amount ?? 0), 2);
    }

    private function companyAddress(Collection $settings): string
    {
        $addressParts = array_filter([
            $this->setting($settings, 'address_line_1', 'Shop No. 12, Commercial Complex'),
            $this->setting($settings, 'address_line_2', 'Vyapar Vihar Road'),
            $this->setting($settings, 'city', 'Bilaspur'),
            $this->setting($settings, 'state', 'Chhattisgarh'),
            $this->setting($settings, 'pincode', '495001'),
        ], static fn (?string $value): bool => filled($value));

        return implode(', ', array_unique($addressParts));
    }

    /**
     * Calculate 5% GST split (CGST 2.5% + SGST 2.5% or IGST 5%).
     *
     * @return array{
     *     taxable_amount: float,
     *     gst_rate: float,
     *     cgst_amount: float,
     *     sgst_amount: float,
     *     igst_amount: float,
     *     gst_amount: float,
     *     is_interstate: bool,
     *     tax_recorded: bool,
     *     gst_applicable: bool
     * }
     */
    private function taxDetails(Booking $booking, float $totalAmount, ?string $customerState = null): array
    {
        $gstRate = 5.00;

        // Determine if interstate (Default company state is Chhattisgarh)
        $isInterstate = $booking->is_interstate || ($customerState && strcasecmp(trim($customerState), 'Chhattisgarh') !== 0 && strcasecmp(trim($customerState), 'CG') !== 0);

        if ($totalAmount <= 0) {
            return [
                'taxable_amount' => 0.0,
                'gst_rate' => $gstRate,
                'cgst_amount' => 0.0,
                'sgst_amount' => 0.0,
                'igst_amount' => 0.0,
                'gst_amount' => 0.0,
                'is_interstate' => $isInterstate,
                'tax_recorded' => true,
                'gst_applicable' => true,
            ];
        }

        $pricing = (array) ($booking->pricing_details ?? []);
        $gstApplicable = (bool) data_get(
            $pricing,
            'gst_applicable',
            $booking->rateVehiclePrice?->gst_applicable ?? false
        );

        if (! $gstApplicable) {
            return [
                'taxable_amount' => $totalAmount,
                'gst_rate' => 0.0,
                'cgst_amount' => 0.0,
                'sgst_amount' => 0.0,
                'igst_amount' => 0.0,
                'gst_amount' => 0.0,
                'is_interstate' => $isInterstate,
                'tax_recorded' => true,
                'gst_applicable' => false,
            ];
        }

        $cgst = data_get($pricing, 'cgst_amount');
        $sgst = data_get($pricing, 'sgst_amount');
        $igst = data_get($pricing, 'igst_amount');
        $gstAmount = data_get($pricing, 'gst_amount');

        if (is_numeric($cgst) && is_numeric($sgst)) {
            $cgstAmount = round((float) $cgst, 2);
            $sgstAmount = round((float) $sgst, 2);
            $igstAmount = is_numeric($igst) ? round((float) $igst, 2) : 0.0;
            $taxAmount = $cgstAmount + $sgstAmount + $igstAmount;
        } elseif (is_numeric($gstAmount)) {
            if ($isInterstate) {
                $cgstAmount = 0.0;
                $sgstAmount = 0.0;
                $igstAmount = round((float) $gstAmount, 2);
            } else {
                $cgstAmount = round((float) $gstAmount / 2, 2);
                $sgstAmount = round((float) $gstAmount - $cgstAmount, 2);
                $igstAmount = 0.0;
            }
            $taxAmount = round((float) $gstAmount, 2);
        } else {
            return [
                'taxable_amount' => $totalAmount,
                'gst_rate' => $gstRate,
                'cgst_amount' => null,
                'sgst_amount' => null,
                'igst_amount' => null,
                'gst_amount' => 0.0,
                'is_interstate' => $isInterstate,
                'tax_recorded' => false,
                'gst_applicable' => true,
            ];
        }

        return [
            'taxable_amount' => max(0, round($totalAmount - $taxAmount, 2)),
            'gst_rate' => $gstRate,
            'cgst_amount' => $cgstAmount,
            'sgst_amount' => $sgstAmount,
            'igst_amount' => $igstAmount,
            'gst_amount' => $taxAmount,
            'is_interstate' => $isInterstate,
            'tax_recorded' => true,
            'gst_applicable' => true,
        ];
    }

    private function numberInWords(int $number): string
    {
        $ones = [
            'Zero', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
            'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen',
        ];
        $tens = [
            2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
            6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety',
        ];

        if ($number < 20) {
            return $ones[$number];
        }

        if ($number < 100) {
            return $tens[intdiv($number, 10)].($number % 10 !== 0 ? ' '.$ones[$number % 10] : '');
        }

        if ($number < 1000) {
            return $this->numberInWords(intdiv($number, 100)).' Hundred'.($number % 100 !== 0 ? ' '.$this->numberInWords($number % 100) : '');
        }

        if ($number < 100000) {
            return $this->numberInWords(intdiv($number, 1000)).' Thousand'.($number % 1000 !== 0 ? ' '.$this->numberInWords($number % 1000) : '');
        }

        if ($number < 10000000) {
            return $this->numberInWords(intdiv($number, 100000)).' Lakh'.($number % 100000 !== 0 ? ' '.$this->numberInWords($number % 100000) : '');
        }

        return $this->numberInWords(intdiv($number, 10000000)).' Crore'.($number % 10000000 !== 0 ? ' '.$this->numberInWords($number % 10000000) : '');
    }
}
