<?php

namespace App\Services;

use App\Models\Booking;
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
        $tax = $this->taxDetails($booking, $totalAmount);

        $customerAddress = array_filter([
            $customerProfile?->address,
            $customerProfile?->city,
            $customerProfile?->state,
            $customerProfile?->pincode,
        ], static fn (?string $value): bool => filled($value));

        $serviceDescription = trim(implode(' ', array_filter([
            $booking->pickup_location,
            'to',
            $booking->destination,
            str($booking->trip_type)->replace('_', ' ')->title()->toString(),
            'Taxi Service',
            $booking->vehicle?->name ? 'with '.$booking->vehicle->name : null,
        ], static fn (?string $value): bool => filled($value))));

        $companyName = $this->setting($settings, 'company_name', config('vaishnavi.business_name'));

        return [
            'company' => [
                'name' => $companyName,
                'address' => $this->companyAddress($settings),
                'phone' => $this->setting($settings, 'phone_primary', config('vaishnavi.phone_primary')),
                'email' => $this->setting($settings, 'contact_email', config('vaishnavi.contact_email')),
                'gstin' => $this->setting($settings, 'gstin', config('vaishnavi.gstin')),
            ],
            'invoice' => [
                'number' => $invoice?->invoice_number,
                'date' => $invoice?->issued_at ?? $invoice?->created_at,
            ],
            'customer' => [
                'name' => $customer?->name,
                'address' => implode(', ', $customerAddress),
                'mobile' => $customer?->phone,
                'gstin' => $customerProfile?->getAttribute('gstin'),
                'pan' => $customerProfile?->getAttribute('pan'),
                'place_of_supply' => $customerProfile?->state,
            ],
            'driver' => [
                'name' => $booking->driver?->name,
            ],
            'vehicle' => [
                'number' => $booking->vehicle?->registration_number,
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
                'cgst_amount' => $tax['cgst_amount'],
                'sgst_amount' => $tax['sgst_amount'],
                'tax_recorded' => $tax['tax_recorded'],
                'total_amount' => $totalAmount,
                'received_amount' => $receivedAmount,
                'balance_amount' => round($totalAmount - $receivedAmount, 2),
                'amount_in_words' => $this->amountInWords($totalAmount),
            ],
            'bank' => [
                'account_name' => $this->setting($settings, 'bank_account_name', config('vaishnavi.bank_account_name')),
                'ifsc_code' => $this->setting($settings, 'bank_ifsc_code', config('vaishnavi.bank_ifsc_code')),
                'account_number' => $this->setting($settings, 'bank_account_number', config('vaishnavi.bank_account_number')),
                'bank_name' => $this->setting($settings, 'bank_name', config('vaishnavi.bank_name')),
            ],
            'notes' => array_filter([
                'Customer / Guest Name: '.$customer?->name,
                $booking->notes,
                $customerProfile?->notes,
            ], static fn (?string $value): bool => filled($value)),
            'terms' => $this->setting($settings, 'booking_terms', config('vaishnavi.booking_terms')),
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

    /**
     * @return Collection<string, string>
     */
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

    private function receivedAmount(Booking $booking, mixed $invoicePaidAmount): float
    {
        if ($booking->payments->isEmpty()) {
            return round((float) ($invoicePaidAmount ?? $booking->paid_amount ?? 0), 2);
        }

        return round((float) $booking->payments
            ->where('status', 'Success')
            ->sum('amount'), 2);
    }

    private function companyAddress(Collection $settings): ?string
    {
        $addressLine1 = $settings->get('address_line_1');

        if (! filled($addressLine1)) {
            return config('vaishnavi.address');
        }

        $addressParts = array_filter([
            $addressLine1,
            $settings->get('address_line_2'),
            $settings->get('city'),
            $settings->get('state'),
            $settings->get('pincode'),
        ], static fn (?string $value): bool => filled($value));

        return implode(', ', array_unique($addressParts));
    }

    /**
     * @return array{taxable_amount: float, cgst_amount: ?float, sgst_amount: ?float, tax_recorded: bool}
     */
    private function taxDetails(Booking $booking, float $totalAmount): array
    {
        $pricingDetails = $booking->pricing_details ?? [];
        $gstApplicable = (bool) data_get(
            $pricingDetails,
            'gst_applicable',
            $booking->rateVehiclePrice?->gst_applicable ?? false
        );

        if (! $gstApplicable) {
            return [
                'taxable_amount' => $totalAmount,
                'cgst_amount' => 0.0,
                'sgst_amount' => 0.0,
                'tax_recorded' => true,
                'gst_applicable' => false,
            ];
        }

        $cgst = data_get($pricingDetails, 'cgst_amount');
        $sgst = data_get($pricingDetails, 'sgst_amount');
        $gstAmount = data_get($pricingDetails, 'gst_amount');

        if (is_numeric($cgst) && is_numeric($sgst)) {
            $cgstAmount = round((float) $cgst, 2);
            $sgstAmount = round((float) $sgst, 2);
        } elseif (is_numeric($gstAmount)) {
            $cgstAmount = round((float) $gstAmount / 2, 2);
            $sgstAmount = round((float) $gstAmount - $cgstAmount, 2);
        } else {
            return [
                'taxable_amount' => $totalAmount,
                'cgst_amount' => null,
                'sgst_amount' => null,
                'tax_recorded' => false,
                'gst_applicable' => true,
            ];
        }

        $taxAmount = $cgstAmount + $sgstAmount;

        return [
            'taxable_amount' => max(0, round($totalAmount - $taxAmount, 2)),
            'cgst_amount' => $cgstAmount,
            'sgst_amount' => $sgstAmount,
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
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        if ($number < 20) {
            return $ones[$number];
        }

        if ($number < 100) {
            return trim($tens[intdiv($number, 10)].' '.$ones[$number % 10]);
        }

        if ($number < 1000) {
            return trim($ones[intdiv($number, 100)].' Hundred '.($number % 100 > 0 ? $this->numberInWords($number % 100) : ''));
        }

        foreach ([10000000 => 'Crore', 100000 => 'Lakh', 1000 => 'Thousand'] as $unit => $label) {
            if ($number >= $unit) {
                return trim($this->numberInWords(intdiv($number, $unit)).' '.$label.' '.($number % $unit > 0 ? $this->numberInWords($number % $unit) : ''));
            }
        }

        return '';
    }
}
