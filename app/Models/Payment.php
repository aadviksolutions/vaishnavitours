<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'booking_id',
        'customer_id',
        'gateway',
        'transaction_id',
        'amount',
        'currency',
        'payment_method',
        'status',
        'gateway_response',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->payment_id)) {
                $payment->payment_id = 'PAY-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
            }
            if (empty($payment->paid_at) && $payment->status === 'Success') {
                $payment->paid_at = now();
            }
        });
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
