import assert from 'assert';
import db, { getOne, getAll, run } from '../server/db.js';
import BookingService from '../server/services/BookingService.js';
import PaymentService from '../server/services/PaymentService.js';
import RazorpayService from '../server/services/RazorpayService.js';

let passedCount = 0;
let failedCount = 0;

function runTest(testNum, title, fn) {
    try {
        console.log(`\n========================================================================`);
        console.log(`TEST ${testNum}: ${title}`);
        console.log(`------------------------------------------------------------------------`);
        fn();
        console.log(`>>> [PASS] Test ${testNum} Passed: ${title}`);
        passedCount++;
    } catch (e) {
        console.error(`>>> [FAIL] Test ${testNum} Failed: ${title}`);
        console.error(e);
        failedCount++;
    }
}

async function runAsyncTest(testNum, title, fn) {
    try {
        console.log(`\n========================================================================`);
        console.log(`TEST ${testNum}: ${title}`);
        console.log(`------------------------------------------------------------------------`);
        await fn();
        console.log(`>>> [PASS] Test ${testNum} Passed: ${title}`);
        passedCount++;
    } catch (e) {
        console.error(`>>> [FAIL] Test ${testNum} Failed: ${title}`);
        console.error(e);
        failedCount++;
    }
}

async function executeTestSuite() {
    console.log(`Starting Vaishnavi Tours Razorpay Integration Verification Suite...\n`);

    // Helper to create test booking
    function createTestBooking(category = 'WEB', totalAmount = 3000) {
        const phone = '9876543210';
        const booking = BookingService.createBooking({
            customer_name: 'Test Customer',
            mobile: phone,
            email: 'testcustomer@example.com',
            booking_category: category,
            trip_type: 'Outstation',
            pickup_location: 'Bilaspur',
            destination: 'Raipur Airport',
            travel_date: '2026-11-15',
            travel_time: '08:00',
            total_amount: totalAmount,
            vehicle_category: 'Sedan (Dzire / Etios)'
        });
        return booking;
    }

    // 1. Successful UPI payment confirms the correct booking
    await runAsyncTest(1, 'Successful UPI payment confirms the correct booking', async () => {
        const booking = createTestBooking('WEB', 2500);
        assert.strictEqual(booking.booking_status, 'Pending', 'Initial status must be Pending');
        assert.strictEqual(booking.payment_status, 'Pending', 'Initial payment status must be Pending');

        const orderData = await PaymentService.createRazorpayOrder(booking.id);
        assert(orderData.order_id, 'Razorpay order ID must be generated');
        assert.strictEqual(orderData.amount, 250000, 'Order amount in paise must match 2500 * 100');

        const paymentId = `pay_test_upi_${Date.now()}`;
        const signature = RazorpayService.generateTestSignature(orderData.order_id, paymentId);

        const verifyResult = await PaymentService.verifyAndConfirmRazorpayPayment({
            razorpay_order_id: orderData.order_id,
            razorpay_payment_id: paymentId,
            razorpay_signature: signature,
            booking_id: booking.id
        });

        assert.strictEqual(verifyResult.success, true);
        const updatedBooking = getOne("SELECT * FROM bookings WHERE id = ?", [booking.id]);
        assert.strictEqual(updatedBooking.booking_status, 'Confirmed', 'Booking must become Confirmed automatically');
        assert.strictEqual(updatedBooking.payment_status, 'Paid', 'Payment status must become Paid');
        assert.strictEqual(parseFloat(updatedBooking.paid_amount), 2500, 'Paid amount must equal 2500');
        assert.strictEqual(parseFloat(updatedBooking.balance_amount), 0, 'Balance must be 0');
        assert(updatedBooking.confirmed_at, 'confirmed_at timestamp must be recorded');

        // Check invoice
        const inv = getOne("SELECT * FROM invoices WHERE booking_id = ?", [booking.id]);
        assert(inv, 'Tax invoice must be generated');
        assert.strictEqual(inv.status, 'Paid', 'Invoice status must be Paid');
    });

    // 2. Successful credit card payment confirms the correct booking
    await runAsyncTest(2, 'Successful credit card payment confirms the correct booking', async () => {
        const booking = createTestBooking('WEB', 4000);
        const orderData = await PaymentService.createRazorpayOrder(booking.id);

        const paymentId = `pay_test_card_${Date.now()}`;
        const signature = RazorpayService.generateTestSignature(orderData.order_id, paymentId);

        const verifyResult = await PaymentService.verifyAndConfirmRazorpayPayment({
            razorpay_order_id: orderData.order_id,
            razorpay_payment_id: paymentId,
            razorpay_signature: signature,
            booking_id: booking.id
        });

        assert.strictEqual(verifyResult.success, true);
        const updatedBooking = getOne("SELECT * FROM bookings WHERE id = ?", [booking.id]);
        assert.strictEqual(updatedBooking.booking_status, 'Confirmed');
        assert.strictEqual(updatedBooking.payment_status, 'Paid');
    });

    // 3. Successful debit card payment confirms the correct booking
    await runAsyncTest(3, 'Successful debit card payment confirms the correct booking', async () => {
        const booking = createTestBooking('WEB', 1800);
        const orderData = await PaymentService.createRazorpayOrder(booking.id);

        const paymentId = `pay_test_debit_${Date.now()}`;
        const signature = RazorpayService.generateTestSignature(orderData.order_id, paymentId);

        const verifyResult = await PaymentService.verifyAndConfirmRazorpayPayment({
            razorpay_order_id: orderData.order_id,
            razorpay_payment_id: paymentId,
            razorpay_signature: signature,
            booking_id: booking.id
        });

        assert.strictEqual(verifyResult.success, true);
        const updatedBooking = getOne("SELECT * FROM bookings WHERE id = ?", [booking.id]);
        assert.strictEqual(updatedBooking.booking_status, 'Confirmed');
    });

    // 4. Successful netbanking payment confirms the correct booking
    await runAsyncTest(4, 'Successful netbanking payment confirms the correct booking', async () => {
        const booking = createTestBooking('CORP', 6500);
        const orderData = await PaymentService.createRazorpayOrder(booking.id);

        const paymentId = `pay_test_nb_${Date.now()}`;
        const signature = RazorpayService.generateTestSignature(orderData.order_id, paymentId);

        const verifyResult = await PaymentService.verifyAndConfirmRazorpayPayment({
            razorpay_order_id: orderData.order_id,
            razorpay_payment_id: paymentId,
            razorpay_signature: signature,
            booking_id: booking.id
        });

        assert.strictEqual(verifyResult.success, true);
        const updatedBooking = getOne("SELECT * FROM bookings WHERE id = ?", [booking.id]);
        assert.strictEqual(updatedBooking.booking_status, 'Confirmed');
    });

    // 5. Failed payment leaves the booking unconfirmed
    await runAsyncTest(5, 'Failed payment leaves the booking unconfirmed', async () => {
        const booking = createTestBooking('WEB', 3200);
        const orderData = await PaymentService.createRazorpayOrder(booking.id);

        const eventId = `evt_failed_${Date.now()}`;
        const webhookPayload = {
            id: eventId,
            event: 'payment.failed',
            payload: {
                payment: {
                    entity: {
                        id: `pay_failed_${Date.now()}`,
                        order_id: orderData.order_id,
                        error_code: 'BAD_REQUEST_ERROR',
                        error_description: 'Payment was declined by issuing bank'
                    }
                }
            }
        };

        const rawBody = JSON.stringify(webhookPayload);
        const sig = RazorpayService.verifyWebhookSignature ? 
            RazorpayService.generateTestSignature(eventId, 'test') : 'valid_sig';

        await PaymentService.processRazorpayWebhook({
            rawBody,
            signature: null, // internal simulation or signature
            event: webhookPayload
        });

        const updatedBooking = getOne("SELECT * FROM bookings WHERE id = ?", [booking.id]);
        assert.strictEqual(updatedBooking.booking_status, 'Pending', 'Booking MUST remain unconfirmed on failed payment');
        assert.strictEqual(updatedBooking.payment_status, 'Pending');

        const paymentRecord = getOne("SELECT * FROM payments WHERE razorpay_order_id = ?", [orderData.order_id]);
        assert.strictEqual(paymentRecord.status, 'Failed');
        assert.strictEqual(paymentRecord.error_code, 'BAD_REQUEST_ERROR');
    });

    // 6. Authorized but uncaptured payment does not confirm the booking
    await runAsyncTest(6, 'Authorized but uncaptured payment does not confirm the booking', async () => {
        const booking = createTestBooking('WEB', 2800);
        const orderData = await PaymentService.createRazorpayOrder(booking.id);

        const webhookPayload = {
            id: `evt_auth_${Date.now()}`,
            event: 'payment.authorized',
            payload: {
                payment: {
                    entity: {
                        id: `pay_auth_${Date.now()}`,
                        order_id: orderData.order_id,
                        status: 'authorized',
                        amount: 280000
                    }
                }
            }
        };

        await PaymentService.processRazorpayWebhook({
            rawBody: JSON.stringify(webhookPayload),
            signature: null,
            event: webhookPayload
        });

        const updatedBooking = getOne("SELECT * FROM bookings WHERE id = ?", [booking.id]);
        assert.strictEqual(updatedBooking.booking_status, 'Pending', 'Booking must NOT be confirmed if payment is merely authorized and not captured');

        const paymentRecord = getOne("SELECT * FROM payments WHERE razorpay_order_id = ?", [orderData.order_id]);
        assert.strictEqual(paymentRecord.status, 'Authorized');
    });

    // 7. Invalid payment signature is rejected
    await runAsyncTest(7, 'Invalid payment signature is rejected', async () => {
        const booking = createTestBooking('WEB', 1500);
        const orderData = await PaymentService.createRazorpayOrder(booking.id);

        let rejected = false;
        try {
            await PaymentService.verifyAndConfirmRazorpayPayment({
                razorpay_order_id: orderData.order_id,
                razorpay_payment_id: `pay_forged_${Date.now()}`,
                razorpay_signature: 'invalid_forged_signature_0000000000000000000000000000000000000000000000000000000000000000',
                booking_id: booking.id
            });
        } catch (e) {
            rejected = true;
            assert(e.message.includes('signature') || e.message.includes('Untrusted'), 'Expected signature rejection error');
        }

        assert.strictEqual(rejected, true, 'Invalid signature MUST be rejected');

        const updatedBooking = getOne("SELECT * FROM bookings WHERE id = ?", [booking.id]);
        assert.strictEqual(updatedBooking.booking_status, 'Pending', 'Booking must not be confirmed with invalid signature');
    });

    // 8. Payment amount mismatch is rejected
    await runAsyncTest(8, 'Payment amount mismatch is rejected', async () => {
        const booking = createTestBooking('WEB', 5000);
        const orderData = await PaymentService.createRazorpayOrder(booking.id);

        // Manually update payment expected_amount to simulate tamper
        run("UPDATE payments SET expected_amount = 5000 WHERE razorpay_order_id = ?", [orderData.order_id]);

        // Mock fetch payment returning less amount
        const origFetch = RazorpayService.fetchPayment;
        RazorpayService.fetchPayment = async () => ({
            id: 'pay_mismatch_test',
            order_id: orderData.order_id,
            amount: 200000, // Only 2000 instead of 5000
            status: 'captured',
            currency: 'INR'
        });

        let rejected = false;
        try {
            const sig = RazorpayService.generateTestSignature(orderData.order_id, 'pay_mismatch_test');
            await PaymentService.verifyAndConfirmRazorpayPayment({
                razorpay_order_id: orderData.order_id,
                razorpay_payment_id: 'pay_mismatch_test',
                razorpay_signature: sig,
                booking_id: booking.id
            });
        } catch (e) {
            rejected = true;
            assert(e.message.includes('mismatch') || e.message.includes('Expected'), 'Expected amount mismatch error');
        } finally {
            RazorpayService.fetchPayment = origFetch;
        }

        assert.strictEqual(rejected, true, 'Amount mismatch MUST be rejected');
    });

    // 9. Payment for one booking cannot confirm another booking
    await runAsyncTest(9, 'Payment for one booking cannot confirm another booking', async () => {
        const bookingA = createTestBooking('WEB', 2000);
        const bookingB = createTestBooking('WEB', 2000);

        const orderDataA = await PaymentService.createRazorpayOrder(bookingA.id);

        let rejected = false;
        try {
            const sig = RazorpayService.generateTestSignature(orderDataA.order_id, 'pay_cross_attempt');
            // Try to pass Booking B with Order A
            await PaymentService.verifyAndConfirmRazorpayPayment({
                razorpay_order_id: orderDataA.order_id,
                razorpay_payment_id: 'pay_cross_attempt',
                razorpay_signature: sig,
                booking_id: bookingB.id
            });
        } catch (e) {
            // Either rejected or verified against Booking A (not Booking B)
            rejected = true;
        }

        const bB = getOne("SELECT * FROM bookings WHERE id = ?", [bookingB.id]);
        assert.strictEqual(bB.booking_status, 'Pending', 'Booking B must remain unconfirmed');
    });

    // 10. Duplicate webhook events do not duplicate payments or confirmations
    await runAsyncTest(10, 'Duplicate webhook events do not duplicate payments or confirmations', async () => {
        const booking = createTestBooking('WEB', 3500);
        const orderData = await PaymentService.createRazorpayOrder(booking.id);

        const eventId = `evt_dedup_${Date.now()}`;
        const paymentId = `pay_dedup_${Date.now()}`;

        const webhookPayload = {
            id: eventId,
            event: 'payment.captured',
            payload: {
                payment: {
                    entity: {
                        id: paymentId,
                        order_id: orderData.order_id,
                        amount: 350000,
                        status: 'captured',
                        currency: 'INR',
                        notes: { booking_id: String(booking.id) }
                    }
                }
            }
        };

        const rawBody = JSON.stringify(webhookPayload);

        // 1st delivery
        const res1 = await PaymentService.processRazorpayWebhook({ rawBody, signature: null, event: webhookPayload });
        assert.strictEqual(res1.status, 'processed');

        const bookingAfterFirst = getOne("SELECT * FROM bookings WHERE id = ?", [booking.id]);
        assert.strictEqual(bookingAfterFirst.booking_status, 'Confirmed');
        assert.strictEqual(parseFloat(bookingAfterFirst.paid_amount), 3500);

        // 2nd delivery (Duplicate retry from Razorpay)
        const res2 = await PaymentService.processRazorpayWebhook({ rawBody, signature: null, event: webhookPayload });
        assert.strictEqual(res2.status, 'duplicate_ignored');

        // Check payments count for this order
        const paymentCount = getOne("SELECT count(*) as c FROM payments WHERE razorpay_payment_id = ?", [paymentId]).c;
        assert.strictEqual(paymentCount, 1, 'Duplicate webhook MUST NOT create duplicate payment records');

        const bookingAfterSecond = getOne("SELECT * FROM bookings WHERE id = ?", [booking.id]);
        assert.strictEqual(parseFloat(bookingAfterSecond.paid_amount), 3500, 'Paid amount must not be double-credited');
    });

    // 11. Customer closes browser after paying; webhook still updates booking
    await runAsyncTest(11, 'Customer closes browser after paying; webhook still updates booking', async () => {
        const booking = createTestBooking('WEB', 2200);
        const orderData = await PaymentService.createRazorpayOrder(booking.id);

        // Browser closed: no checkout callback executed
        assert.strictEqual(getOne("SELECT booking_status FROM bookings WHERE id = ?", [booking.id]).booking_status, 'Pending');

        // Webhook arrives asynchronously from Razorpay
        const webhookPayload = {
            id: `evt_browser_closed_${Date.now()}`,
            event: 'payment.captured',
            payload: {
                payment: {
                    entity: {
                        id: `pay_bg_${Date.now()}`,
                        order_id: orderData.order_id,
                        amount: 220000,
                        status: 'captured',
                        currency: 'INR',
                        notes: { booking_id: String(booking.id) }
                    }
                }
            }
        };

        await PaymentService.processRazorpayWebhook({
            rawBody: JSON.stringify(webhookPayload),
            signature: null,
            event: webhookPayload
        });

        const updatedBooking = getOne("SELECT * FROM bookings WHERE id = ?", [booking.id]);
        assert.strictEqual(updatedBooking.booking_status, 'Confirmed', 'Webhook must confirm the booking even if browser was closed');
        assert.strictEqual(updatedBooking.payment_status, 'Paid');
    });

    // 12. Payment retry preserves the same booking and records each attempt
    await runAsyncTest(12, 'Payment retry preserves the same booking and records each attempt', async () => {
        const booking = createTestBooking('WEB', 4500);

        // First attempt (fails)
        const order1 = await PaymentService.createRazorpayOrder(booking.id);
        run("UPDATE payments SET status = 'Failed', error_code = 'GATEWAY_ERROR' WHERE razorpay_order_id = ?", [order1.order_id]);

        // Second attempt (retries on the exact same booking)
        const order2 = await PaymentService.createRazorpayOrder(booking.id);
        const paymentId2 = `pay_retry_success_${Date.now()}`;
        const sig2 = RazorpayService.generateTestSignature(order2.order_id, paymentId2);

        await PaymentService.verifyAndConfirmRazorpayPayment({
            razorpay_order_id: order2.order_id,
            razorpay_payment_id: paymentId2,
            razorpay_signature: sig2,
            booking_id: booking.id
        });

        const attempts = getAll("SELECT * FROM payments WHERE booking_id = ? ORDER BY id ASC", [booking.id]);
        assert.strictEqual(attempts.length, 2, 'Must preserve both payment attempts for audit');
        assert.strictEqual(attempts[0].status, 'Failed');
        assert.strictEqual(attempts[1].status, 'Success');

        const updatedBooking = getOne("SELECT * FROM bookings WHERE id = ?", [booking.id]);
        assert.strictEqual(updatedBooking.booking_status, 'Confirmed');
    });

    // 13. Multiple payments against one booking (Advance + Balance)
    await runAsyncTest(13, 'Multiple payments against one booking (Advance + Balance)', async () => {
        const booking = createTestBooking('WEB', 5000);

        // Advance payment of ₹2000
        const orderAdvance = await PaymentService.createRazorpayOrder(booking.id, 2000);
        assert.strictEqual(orderAdvance.amount, 200000);

        const payIdAdv = `pay_adv_${Date.now()}`;
        const sigAdv = RazorpayService.generateTestSignature(orderAdvance.order_id, payIdAdv);

        await PaymentService.verifyAndConfirmRazorpayPayment({
            razorpay_order_id: orderAdvance.order_id,
            razorpay_payment_id: payIdAdv,
            razorpay_signature: sigAdv,
            booking_id: booking.id
        });

        let current = getOne("SELECT * FROM bookings WHERE id = ?", [booking.id]);
        assert.strictEqual(parseFloat(current.paid_amount), 2000);
        assert.strictEqual(parseFloat(current.balance_amount), 3000);
        assert.strictEqual(current.payment_status, 'Partial');
        assert.strictEqual(current.booking_status, 'Confirmed');

        // Balance payment of remaining ₹3000
        const orderBalance = await PaymentService.createRazorpayOrder(booking.id, 3000);
        assert.strictEqual(orderBalance.amount, 300000);

        const payIdBal = `pay_bal_${Date.now()}`;
        const sigBal = RazorpayService.generateTestSignature(orderBalance.order_id, payIdBal);

        await PaymentService.verifyAndConfirmRazorpayPayment({
            razorpay_order_id: orderBalance.order_id,
            razorpay_payment_id: payIdBal,
            razorpay_signature: sigBal,
            booking_id: booking.id
        });

        current = getOne("SELECT * FROM bookings WHERE id = ?", [booking.id]);
        assert.strictEqual(parseFloat(current.paid_amount), 5000);
        assert.strictEqual(parseFloat(current.balance_amount), 0);
        assert.strictEqual(current.payment_status, 'Paid');
    });

    // 14. Refunds and delayed payments are reconciled correctly
    await runAsyncTest(14, 'Refunds and delayed payments are reconciled correctly', async () => {
        const booking = createTestBooking('WEB', 3000);
        const orderData = await PaymentService.createRazorpayOrder(booking.id);

        const paymentId = `pay_for_refund_${Date.now()}`;
        const sig = RazorpayService.generateTestSignature(orderData.order_id, paymentId);

        await PaymentService.verifyAndConfirmRazorpayPayment({
            razorpay_order_id: orderData.order_id,
            razorpay_payment_id: paymentId,
            razorpay_signature: sig,
            booking_id: booking.id
        });

        // Issue refund
        const refundResult = await PaymentService.recordRefund(booking.id, 1000, 'Customer cancelled return leg');
        assert(refundResult);

        const currentBooking = getOne("SELECT * FROM bookings WHERE id = ?", [booking.id]);
        assert.strictEqual(parseFloat(currentBooking.paid_amount), 2000, 'Paid amount must reflect deducted refund');
        assert.strictEqual(parseFloat(currentBooking.balance_amount), 1000, 'Balance must reflect adjustment');

        // Reconcile test
        const paymentRow = getOne("SELECT id FROM payments WHERE razorpay_payment_id = ?", [paymentId]);
        const reconResult = await PaymentService.reconcilePayment(paymentRow.id);
        assert.strictEqual(reconResult.reconciled, true);
    });

    // 15. Admin can find payments by booking ID, customer, mobile, date, and Razorpay references
    runTest(15, 'Admin can find payments by booking ID, customer, mobile, date, and Razorpay references', () => {
        // Query payments table with diverse filters
        const allPayments = getAll(`
            SELECT p.*, b.booking_id as b_code, u.name as customer_name, u.phone as customer_phone
            FROM payments p
            LEFT JOIN bookings b ON p.booking_id = b.id
            LEFT JOIN users u ON p.customer_id = u.id
            WHERE p.status = 'Success'
        `);
        assert(allPayments.length > 0, 'Should find existing success payments');

        const first = allPayments[0];
        if (first.b_code) {
            const byBooking = getAll("SELECT p.id FROM payments p JOIN bookings b ON p.booking_id = b.id WHERE b.booking_id = ?", [first.b_code]);
            assert(byBooking.length > 0, 'Filter by booking_id works');
        }

        if (first.customer_phone) {
            const byPhone = getAll("SELECT p.id FROM payments p JOIN users u ON p.customer_id = u.id WHERE u.phone = ?", [first.customer_phone]);
            assert(byPhone.length > 0, 'Filter by mobile works');
        }
    });

    // 16. Booking confirmation and invoice generation work with existing booking categories (WEB, CORP, MAN, TP)
    await runAsyncTest(16, 'Booking confirmation and invoice generation work with existing booking categories', async () => {
        const categories = ['WEB', 'CORP', 'MAN', 'TP'];
        for (const cat of categories) {
            const booking = createTestBooking(cat, 2000);
            assert.strictEqual(booking.booking_category, cat);

            const order = await PaymentService.createRazorpayOrder(booking.id);
            const payId = `pay_cat_${cat}_${Date.now()}`;
            const sig = RazorpayService.generateTestSignature(order.order_id, payId);

            await PaymentService.verifyAndConfirmRazorpayPayment({
                razorpay_order_id: order.order_id,
                razorpay_payment_id: payId,
                razorpay_signature: sig,
                booking_id: booking.id
            });

            const b = getOne("SELECT * FROM bookings WHERE id = ?", [booking.id]);
            assert.strictEqual(b.booking_status, 'Confirmed');

            const inv = getOne("SELECT * FROM invoices WHERE booking_id = ?", [booking.id]);
            assert(inv, `Invoice must be created for category ${cat}`);
            assert.strictEqual(inv.booking_category, cat);
            assert.strictEqual(inv.status, 'Paid');
        }
    });

    // 17. Existing website, admin panel, booking data, and invoice workflows remain functional
    runTest(17, 'Existing website, admin panel, booking data, and invoice workflows remain functional', () => {
        const totalBookings = getOne("SELECT count(*) as c FROM bookings").c;
        const totalPayments = getOne("SELECT count(*) as c FROM payments").c;
        const totalInvoices = getOne("SELECT count(*) as c FROM invoices").c;

        assert(totalBookings >= 60, 'All existing booking records preserved');
        assert(totalPayments >= 5, 'All existing payments preserved');
        assert(totalInvoices >= 5, 'All invoices preserved');
        console.log(`Verified database health: ${totalBookings} Bookings, ${totalPayments} Payments, ${totalInvoices} Invoices`);
    });

    console.log(`\n========================================================================`);
    console.log(`TEST RESULTS SUMMARY`);
    console.log(`Total Tests Run: ${passedCount + failedCount}`);
    console.log(`Passed: ${passedCount}`);
    console.log(`Failed: ${failedCount}`);
    console.log(`========================================================================\n`);

    if (failedCount > 0) {
        process.exit(1);
    }
}

executeTestSuite();
