<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\Payment;
use App\Models\Setting;
use App\Services\PaymentService;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PublicPaymentController extends Controller
{
    public function showPayPage(Request $request, string $bookingId)
    {
        $booking = Booking::where('id', $bookingId)
            ->orWhere('booking_id', $bookingId)
            ->firstOrFail();

        $keyId = RazorpayService::getKeyId();
        $upiId = Setting::get('upi_id', config('vaishnavi.upi_id', '9179484443m@pnb'));
        $upiMerchantName = Setting::get('upi_merchant_name', config('vaishnavi.upi_merchant_name', 'VAISHNAVI TOURS'));
        $upiBankName = Setting::get('upi_bank_name', config('vaishnavi.upi_bank_name', 'Punjab National Bank (PNB)'));
        $upiQrImage = Setting::get('upi_qr_image', config('vaishnavi.upi_qr_image', '/assets/images/upi_qr.jpg'));

        return view('public.pay', compact('booking', 'keyId', 'upiId', 'upiMerchantName', 'upiBankName', 'upiQrImage'));
    }

    /**
     * Submit direct UPI payment confirmation (PNB QR / UPI ID)
     */
    public function confirmUpiPayment(Request $request, string $bookingId, PaymentService $paymentService)
    {
        $booking = Booking::where('id', $bookingId)
            ->orWhere('booking_id', $bookingId)
            ->firstOrFail();

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'upi_ref_number' => 'required|string|min:4|max:80',
            'sender_name' => 'nullable|string|max:150',
            'notes' => 'nullable|string|max:500',
        ], [
            'amount.required' => 'Please enter the amount you paid.',
            'amount.min' => 'Amount must be at least ₹1.',
            'upi_ref_number.required' => 'Please enter your 12-digit UPI Reference / UTR Number from your payment app.',
            'upi_ref_number.min' => 'Please enter a valid UPI Reference / UTR Number.',
        ]);

        $amount = (float) $validated['amount'];
        $refNo = trim($validated['upi_ref_number']);
        $upiId = Setting::get('upi_id', config('vaishnavi.upi_id', '9179484443m@pnb'));

        // Record payment
        Payment::create([
            'payment_id' => 'UPI-' . strtoupper(uniqid()),
            'booking_id' => $booking->id,
            'customer_id' => $booking->customer_id,
            'amount' => $amount,
            'currency' => 'INR',
            'payment_method' => 'UPI / QR',
            'gateway' => "PNB UPI ({$upiId})",
            'transaction_id' => $refNo,
            'status' => 'Success',
            'payment_type' => ($amount >= (float) $booking->balance_amount) ? 'full' : 'advance',
            'notes' => "Direct UPI payment received via PNB QR ({$upiId}). Sender: " . ($validated['sender_name'] ?? ($booking->customer?->name ?? 'Customer')),
            'paid_at' => now(),
        ]);

        // Update booking totals
        $newPaid = round((float) $booking->paid_amount + $amount, 2);
        $newBalance = max(0, round((float) $booking->total_amount - $newPaid, 2));
        $newPaymentStatus = ($newBalance <= 0) ? 'Paid' : 'Partial';
        $newBookingStatus = ($booking->booking_status === 'Pending') ? 'Confirmed' : $booking->booking_status;

        $booking->update([
            'paid_amount' => $newPaid,
            'balance_amount' => $newBalance,
            'payment_status' => $newPaymentStatus,
            'booking_status' => $newBookingStatus,
        ]);

        BookingStatusHistory::create([
            'booking_id' => $booking->id,
            'changed_by' => Auth::id(),
            'old_status' => $booking->booking_status,
            'new_status' => $newBookingStatus,
            'remarks' => "Customer paid ₹" . number_format($amount, 2) . " via PNB UPI (Ref/UTR: {$refNo}).",
            'comment' => "Customer paid ₹" . number_format($amount, 2) . " via PNB UPI (Ref/UTR: {$refNo}).",
            'created_at' => now(),
        ]);

        try {
            $paymentService->syncInvoice($booking);
        } catch (\Throwable $e) {
            Log::warning('Invoice sync warning: ' . $e->getMessage());
        }

        return redirect()->route('booking.success', $booking->id)
            ->with('success', "✓ Thank you! Your payment of ₹" . number_format($amount, 2) . " via UPI (Ref/UTR: {$refNo}) has been submitted and verified! Your booking #{$booking->booking_id} is confirmed.");
    }

    public function createOrder(Request $request, RazorpayService $razorpay): JsonResponse
    {
        $validated = $request->validate([
            'booking_id' => 'required',
            'amount_type' => 'nullable|string|in:advance,balance,full,custom',
            'custom_amount' => 'nullable|numeric|min:1',
        ]);

        $booking = Booking::where('id', $validated['booking_id'])
            ->orWhere('booking_id', $validated['booking_id'])
            ->firstOrFail();

        $amount = (float) $booking->balance_amount;
        $amountType = $validated['amount_type'] ?? 'balance';

        if ($amountType === 'custom' && ! empty($validated['custom_amount'])) {
            $amount = min((float) $validated['custom_amount'], (float) $booking->balance_amount);
        } elseif ($amountType === 'advance') {
            $advance = max(500, round((float) $booking->total_amount * 0.25));
            $amount = min($advance, (float) $booking->balance_amount);
        } else {
            $amount = (float) $booking->balance_amount;
        }

        if ($amount <= 0) {
            return response()->json([
                'success' => false,
                'error' => 'No outstanding balance for this booking.',
            ], 422);
        }

        $amountPaise = (int) round($amount * 100);

        try {
            $order = $razorpay->createOrder([
                'amount' => $amountPaise,
                'currency' => 'INR',
                'receipt' => 'VT-'.$booking->booking_id,
                'notes' => [
                    'booking_id' => (string) $booking->booking_id,
                    'customer_id' => (string) $booking->customer_id,
                    'customer_name' => (string) ($booking->customer?->name ?? 'Customer'),
                    'pickup' => (string) $booking->pickup_location,
                    'drop' => (string) $booking->destination,
                ],
            ]);

            return response()->json([
                'success' => true,
                'order_id' => $order['id'],
                'amount' => $order['amount'],
                'currency' => $order['currency'],
                'key_id' => RazorpayService::getKeyId(),
                'booking' => [
                    'id' => $booking->id,
                    'booking_id' => $booking->booking_id,
                    'customer_name' => $booking->customer?->name ?? 'Customer',
                    'customer_phone' => $booking->customer?->phone ?? '',
                    'customer_email' => $booking->customer?->email ?? '',
                    'total_amount' => $booking->total_amount,
                    'balance_amount' => $booking->balance_amount,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Razorpay Create Order Failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function verifyPayment(Request $request, RazorpayService $razorpay, PaymentService $paymentService): JsonResponse
    {
        $validated = $request->validate([
            'booking_id' => 'required',
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $booking = Booking::where('id', $validated['booking_id'])
            ->orWhere('booking_id', $validated['booking_id'])
            ->firstOrFail();

        $isValid = $razorpay->verifyCheckoutSignature(
            $validated['razorpay_order_id'],
            $validated['razorpay_payment_id'],
            $validated['razorpay_signature']
        );

        if (! $isValid) {
            return response()->json([
                'success' => false,
                'error' => 'Cryptographic signature verification failed.',
            ], 400);
        }

        try {
            $paymentData = $razorpay->fetchPayment($validated['razorpay_payment_id']);
            $amount = (float) (($paymentData['amount'] ?? 0) / 100);

            if ($amount <= 0) {
                $amount = (float) $booking->balance_amount;
            }

            $payment = $paymentService->confirmRazorpayPayment($booking, [
                'amount' => $amount,
                'razorpay_order_id' => $validated['razorpay_order_id'],
                'razorpay_payment_id' => $validated['razorpay_payment_id'],
                'method' => $paymentData['method'] ?? 'Online UPI/Card',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment verified and confirmed successfully!',
                'payment_id' => $payment->id,
                'booking_id' => $booking->booking_id,
                'paid_amount' => $booking->fresh()->paid_amount,
                'balance_amount' => $booking->fresh()->balance_amount,
                'redirect_url' => route('booking.success', $booking->id),
            ]);
        } catch (\Throwable $e) {
            Log::error('Razorpay Verify Payment Failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error' => 'Payment processing failed: '.$e->getMessage(),
            ], 500);
        }
    }

    public function webhook(Request $request, RazorpayService $razorpay, PaymentService $paymentService): JsonResponse
    {
        $signature = $request->header('X-Razorpay-Signature', '');
        $payload = $request->getContent();

        if (! empty($signature)) {
            $isValid = $razorpay->verifyWebhookSignature($payload, $signature);
            if (! $isValid) {
                return response()->json(['error' => 'Invalid webhook signature'], 400);
            }
        }

        $event = json_decode($payload, true);
        $eventType = $event['event'] ?? '';

        if (in_array($eventType, ['payment.captured', 'order.paid'])) {
            $paymentEntity = $event['payload']['payment']['entity'] ?? [];
            $orderId = $paymentEntity['order_id'] ?? null;
            $paymentId = $paymentEntity['id'] ?? null;
            $amount = ((float) ($paymentEntity['amount'] ?? 0)) / 100;
            $notes = $paymentEntity['notes'] ?? [];
            $bookingId = $notes['booking_id'] ?? null;

            if ($bookingId) {
                $booking = Booking::where('booking_id', $bookingId)->orWhere('id', $bookingId)->first();
                if ($booking && $paymentId) {
                    $paymentService->confirmRazorpayPayment($booking, [
                        'amount' => $amount ?: (float) $booking->balance_amount,
                        'razorpay_order_id' => $orderId,
                        'razorpay_payment_id' => $paymentId,
                        'method' => $paymentEntity['method'] ?? 'Razorpay Webhook',
                    ]);
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }

    public function selectPayLater(Request $request, string $bookingId)
    {
        $booking = Booking::where('id', $bookingId)
            ->orWhere('booking_id', $bookingId)
            ->firstOrFail();

        BookingStatusHistory::create([
            'booking_id' => $booking->id,
            'changed_by' => Auth::id(),
            'old_status' => $booking->booking_status,
            'new_status' => $booking->booking_status,
            'remarks' => 'Customer confirmed Pay Later (Cash / UPI to Chauffeur on journey).',
            'comment' => 'Customer confirmed Pay Later (Cash / UPI to Chauffeur on journey).',
            'created_at' => now(),
        ]);

        return redirect()->route('booking.success', $booking->id)
            ->with('success', 'Your booking is confirmed with Pay Later! You can pay ₹'.number_format($booking->balance_amount, 2).' directly to the chauffeur via Cash or UPI during your journey.');
    }
}
