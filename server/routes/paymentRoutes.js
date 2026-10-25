import express from 'express';
import db, { getOne, getAll, run } from '../db.js';
import PaymentService from '../services/PaymentService.js';
import RazorpayService from '../services/RazorpayService.js';
import { ensureAuthenticated, ensureAdmin } from '../middleware/auth.js';

const router = express.Router();

/**
 * 1. Create Razorpay Order
 * Secure: Fetches amount from server-side database records. Never trusts browser amount.
 */
router.post('/razorpay/create-order', async (req, res) => {
    try {
        const { booking_id, amount } = req.body;
        if (!booking_id) {
            return res.status(400).json({
                success: false,
                message: 'booking_id is required'
            });
        }

        const orderData = await PaymentService.createRazorpayOrder(booking_id, amount);
        res.json(orderData);
    } catch (err) {
        console.error('Create Razorpay Order Error:', err);
        res.status(400).json({
            success: false,
            message: err.message || 'Unable to create payment order'
        });
    }
});

/**
 * 2. Verify Payment Signature & Confirm Booking
 * Invoked by Checkout success callback. Verification happens strictly server-side.
 */
router.post('/razorpay/verify', async (req, res) => {
    try {
        const { razorpay_order_id, razorpay_payment_id, razorpay_signature, booking_id } = req.body;

        if (!razorpay_order_id || !razorpay_payment_id || !razorpay_signature) {
            return res.status(400).json({
                success: false,
                message: 'Missing Razorpay signature verification parameters'
            });
        }

        const result = await PaymentService.verifyAndConfirmRazorpayPayment({
            razorpay_order_id,
            razorpay_payment_id,
            razorpay_signature,
            booking_id
        });

        res.json({
            success: true,
            message: 'Payment verified and booking confirmed successfully',
            booking_id: result.booking?.booking_id,
            id: result.booking?.id,
            payment_status: result.booking?.payment_status,
            booking_status: result.booking?.booking_status,
            paid_amount: result.booking?.paid_amount,
            redirect_url: `/booking-success/${result.booking?.id}?paid=1`
        });
    } catch (err) {
        console.error('Payment Verification Error:', err);
        res.status(400).json({
            success: false,
            message: err.message || 'Payment signature verification failed'
        });
    }
});

/**
 * 3. Server-side Razorpay Webhooks
 * Verifies raw request body with HMAC-SHA256 and handles events idempotently
 */
router.post('/razorpay/webhook', async (req, res) => {
    try {
        const signature = req.headers['x-razorpay-signature'];
        const rawBody = req.rawBody || JSON.stringify(req.body);
        const event = req.body;

        const result = await PaymentService.processRazorpayWebhook({
            rawBody,
            signature,
            event
        });

        res.status(200).json({ status: 'ok', result });
    } catch (err) {
        console.error('Webhook Processing Error:', err);
        // Razorpay expects 200/400
        res.status(400).json({ status: 'error', message: err.message });
    }
});

/**
 * 4. Dedicated Public Pay Page for any Booking
 * Usable for customer portal, corporate bookings, manual bookings, or WhatsApp payment links
 */
router.get('/pay/:bookingId', (req, res) => {
    const bId = req.params.bookingId;
    const booking = getOne("SELECT * FROM bookings WHERE id = ? OR booking_id = ?", [bId, bId]);

    if (!booking) {
        res.setFlash('error', 'Booking record not found.');
        return res.redirect('/');
    }

    const customer = getOne("SELECT * FROM users WHERE id = ?", [booking.customer_id]) || {};
    const vehicle = booking.vehicle_id ? getOne("SELECT * FROM vehicles WHERE id = ?", [booking.vehicle_id]) : null;
    const payments = getAll("SELECT * FROM payments WHERE booking_id = ? ORDER BY id DESC", [booking.id]);

    res.render('public/pay', {
        booking,
        customer,
        vehicle,
        payments,
        razorpayKeyId: RazorpayService.keyId,
        pageTitle: `Complete Payment for Booking #${booking.booking_id} | Vaishnavi Tours`
    });
});

/**
 * 5. Admin Payment Reconciliation Endpoint
 */
router.post('/admin/payments/:id/reconcile', ensureAuthenticated, ensureAdmin, async (req, res) => {
    try {
        const result = await PaymentService.reconcilePayment(req.params.id);
        res.json({
            success: true,
            message: result.updated ? 'Transaction reconciled & updated successfully' : 'Transaction is in sync with gateway',
            data: result
        });
    } catch (err) {
        console.error('Reconciliation error:', err);
        res.status(400).json({
            success: false,
            message: err.message || 'Reconciliation failed'
        });
    }
});

/**
 * 6. Admin Refund Endpoint
 */
router.post('/admin/payments/:id/refund', ensureAuthenticated, ensureAdmin, async (req, res) => {
    try {
        const { amount, reason } = req.body;
        const payment = getOne("SELECT * FROM payments WHERE id = ?", [req.params.id]);
        if (!payment) return res.status(404).json({ success: false, message: 'Payment record not found' });

        const refundResult = await PaymentService.recordRefund(
            payment.booking_id,
            amount || payment.amount,
            reason || 'Refund requested by administrator',
            req.user?.id
        );

        res.json({
            success: true,
            message: 'Refund recorded and processed successfully',
            refund: refundResult
        });
    } catch (err) {
        console.error('Refund error:', err);
        res.status(400).json({
            success: false,
            message: err.message || 'Unable to process refund'
        });
    }
});

export default router;
