<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'booking_id',
        'customer_id',
        'taxable_amount',
        'gst_rate',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'gst_amount',
        'customer_gstin',
        'booking_category',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'status',
        'version',
        'issued_at',
    ];

    protected $casts = [
        'taxable_amount' => 'decimal:2',
        'gst_rate' => 'decimal:2',
        'cgst_amount' => 'decimal:2',
        'sgst_amount' => 'decimal:2',
        'igst_amount' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'version' => 'integer',
        'issued_at' => 'datetime',
    ];

    public const SYSTEM_GENERATED_NOTE = 'THIS IS A SYSTEM-GENERATED INVOICE. SIGNATURE NOT REQUIRED.';

    public function getFooterNoteAttribute(): string
    {
        return self::SYSTEM_GENERATED_NOTE;
    }

    public function getGstPercentageAttribute(): float
    {
        return (float) ($this->gst_rate ?? 5.00);
    }

    public static function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $prefix = "INV-{$year}-";

        $latest = static::where('invoice_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->pluck('invoice_number')
            ->first();

        $maxSeq = 0;
        if ($latest && preg_match('/^INV-\d{4}-(\d+)$/', (string) $latest, $m)) {
            $maxSeq = (int) $m[1];
        }

        do {
            $maxSeq++;
            $candidate = sprintf('%s%05d', $prefix, $maxSeq);
        } while (static::where('invoice_number', $candidate)->exists());

        return $candidate;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateInvoiceNumber();
            }
            if (empty($invoice->issued_at)) {
                $invoice->issued_at = now();
            }
            if (empty($invoice->gst_rate)) {
                $invoice->gst_rate = 5.00;
            }
        });
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
