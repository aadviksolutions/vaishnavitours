<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Record a payment against a booking, update balances and generate/update invoice.
     */
    public function recordPayment(Booking $booking, array $data, ?User $recordedBy = null): Payment
    {
        return DB::transaction(function () use ($booking, $data) {
            $amount = (float)$data['amount'];

            $payment = Payment::create([
                'booking_id' => $booking->id,
                'customer_id' => $booking->customer_id,
                'gateway' => $data['gateway'] ?? 'Manual / Direct',
                'transaction_id' => $data['transaction_id'] ?? ('TXN-' . strtoupper(uniqid())),
                'amount' => $amount,
                'currency' => 'INR',
                'payment_method' => $data['payment_method'] ?? 'Cash',
                'status' => $data['status'] ?? 'Success',
                'paid_at' => now(),
                'notes' => $data['notes'] ?? null,
            ]);

            // If payment succeeded, update booking amounts
            if ($payment->status === 'Success') {
                $newPaid = (float)$booking->paid_amount + $amount;
                $newBalance = max(0, (float)$booking->total_amount - $newPaid);

                $paymentStatus = 'Partial';
                if ($newBalance <= 0) {
                    $paymentStatus = 'Paid';
                }

                $booking->update([
                    'paid_amount' => $newPaid,
                    'balance_amount' => $newBalance,
                    'payment_status' => $paymentStatus,
                ]);

                // Create or update invoice
                $this->syncInvoice($booking);

                // Send payment notification
                Notification::create([
                    'user_id' => $booking->customer_id,
                    'title' => 'Payment Received: ₹' . number_format($amount, 2),
                    'message' => 'We received ₹' . number_format($amount, 2) . ' for booking #' . $booking->booking_id . '. Remaining balance: ₹' . number_format($newBalance, 2),
                    'type' => 'payment',
                    'action_url' => '/customer/bookings/' . $booking->id,
                ]);

                Notification::create([
                    'user_id' => null,
                    'title' => 'Payment Received: ' . $booking->booking_id,
                    'message' => 'Payment received for ' . $booking->booking_id . '.',
                    'type' => 'payment',
                    'action_url' => '/admin/bookings/' . $booking->id,
                ]);
            }

            return $payment;
        });
    }

    /**
     * Create or refresh invoice for a booking.
     */
    public function syncInvoice(Booking $booking): Invoice
    {
        $status = 'Unpaid';
        if ($booking->paid_amount >= $booking->total_amount && $booking->total_amount > 0) {
            $status = 'Paid';
        } elseif ($booking->paid_amount > 0) {
            $status = 'Partially Paid';
        }

        return Invoice::updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'customer_id' => $booking->customer_id,
                'total_amount' => $booking->total_amount,
                'paid_amount' => $booking->paid_amount,
                'balance_amount' => $booking->balance_amount,
                'status' => $status,
                'issued_at' => now(),
            ]
        );
    }
}
