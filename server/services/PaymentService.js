import crypto from 'crypto';
import db, { getOne, getAll, run, transaction } from '../db.js';
import RazorpayService from './RazorpayService.js';
import WhatsappService from './WhatsappService.js';

export class PaymentService {
    static generatePaymentId() {
        const today = new Date().toISOString().slice(0, 10).replace(/-/g, '');
        const rand = crypto.randomBytes(2).toString('hex').toUpperCase();
        return `PAY-${today}-${rand}`;
    }

    static generateInvoiceNumber() {
        const year = new Date().getFullYear();
        const prefix = `INV-${year}-`;

        const latest = getOne(
            "SELECT invoice_number FROM invoices WHERE invoice_number LIKE ? ORDER BY id DESC LIMIT 1",
            [`${prefix}%`]
        );

        let maxSeq = 0;
        if (latest && latest.invoice_number) {
            const m = String(latest.invoice_number).match(new RegExp(`^INV-\\d{4}-(\\d+)$`));
            if (m) {
                maxSeq = parseInt(m[1], 10);
            }
        }

        let candidate;
        do {
            maxSeq++;
            candidate = `${prefix}${String(maxSeq).padStart(5, '0')}`;
        } while (getOne("SELECT id FROM invoices WHERE invoice_number = ?", [candidate]));

        return candidate;
    }

    /**
     * Legacy & Admin Manual payment recorder (Cash, Direct Bank, etc.)
     */
    static recordPayment(bookingId, data, recordedByUserId = null) {
        return transaction(() => {
            const booking = getOne("SELECT * FROM bookings WHERE id = ?", [bookingId]);
            if (!booking) throw new Error("Booking not found");

            const amount = parseFloat(data.amount);
            const internalPaymentId = this.generatePaymentId();
            const txnId = data.transaction_id || `TXN-${Date.now()}`;
            const gateway = data.gateway || 'Manual / Direct';
            const method = data.payment_method || 'Cash';
            const status = data.status || 'Success';

            const res = run(`
                INSERT INTO payments (
                    payment_id, booking_id, customer_id, gateway, transaction_id,
                    amount, expected_amount, currency, payment_method, status,
                    booking_category, booking_type,
                    paid_at, captured_at, notes, created_at, updated_at
                ) VALUES (
                    ?, ?, ?, ?, ?,
                    ?, ?, 'INR', ?, ?,
                    ?, ?,
                    datetime('now'), datetime('now'), ?, datetime('now'), datetime('now')
                )
            `, [
                internalPaymentId, booking.id, booking.customer_id, gateway, txnId,
                amount, amount, method, status,
                booking.booking_category || 'WEB', booking.trip_type || 'One-Way',
                data.notes || null
            ]);

            if (status === 'Success') {
                const newPaid = parseFloat(booking.paid_amount || 0) + amount;
                const newBalance = Math.max(0, parseFloat(booking.total_amount || 0) - newPaid);
                const paymentStatus = newBalance <= 0 ? 'Paid' : 'Partial';

                // Automatically confirm booking if payment requirement satisfied and booking was pending
                let newBookingStatus = booking.booking_status;
                let confirmedAtClause = '';
                const bookingUpdateParams = [newPaid, newBalance, paymentStatus];

                if (booking.booking_status === 'Pending') {
                    newBookingStatus = 'Confirmed';
                    confirmedAtClause = ', booking_status = ?, confirmed_at = datetime("now")';
                    bookingUpdateParams.push(newBookingStatus);
                }

                bookingUpdateParams.push(booking.id);

                run(`
                    UPDATE bookings 
                    SET paid_amount = ?, balance_amount = ?, payment_status = ? ${confirmedAtClause}, updated_at = datetime('now')
                    WHERE id = ?
                `, bookingUpdateParams);

                if (newBookingStatus === 'Confirmed' && booking.booking_status === 'Pending') {
                    run(`
                        INSERT INTO booking_status_histories (
                            booking_id, old_status, new_status, comment, changed_by, created_at
                        ) VALUES (?, 'Pending', 'Confirmed', ?, ?, datetime('now'))
                    `, [booking.id, `Payment of ₹${amount.toFixed(2)} recorded manually (${method}). Booking automatically confirmed.`, recordedByUserId]);
                }

                // Sync Invoice
                this.syncInvoice(booking.id);

                // Notifications
                run(`
                    INSERT INTO notifications (
                        user_id, title, message, type, is_read, action_url, created_at, updated_at
                    ) VALUES (
                        ?, ?, ?, 'payment', 0, ?, datetime('now'), datetime('now')
                    )
                `, [
                    booking.customer_id,
                    `Payment Received: ₹${amount.toFixed(2)}`,
                    `We received ₹${amount.toFixed(2)} for booking #${booking.booking_id}. Remaining balance: ₹${newBalance.toFixed(2)}`,
                    `/customer/bookings/${booking.id}`
                ]);
            }

            return getOne("SELECT * FROM payments WHERE id = ?", [res.lastInsertRowid]);
        })();
    }

    /**
     * Create Razorpay Order linked to booking
     * Secure: amount is retrieved from server database records, NEVER trusted from browser
     */
    static async createRazorpayOrder(bookingIdOrCode, requestedAmount = null) {
        const booking = getOne(
            "SELECT * FROM bookings WHERE id = ? OR booking_id = ?",
            [bookingIdOrCode, bookingIdOrCode]
        );

        if (!booking) {
            throw new Error('Booking record not found');
        }

        if (booking.booking_status === 'Cancelled') {
            throw new Error('Cannot initiate payment for a cancelled booking');
        }

        const totalAmount = parseFloat(booking.total_amount || 0);
        const paidAmount = parseFloat(booking.paid_amount || 0);
        const currentBalance = Math.max(0, totalAmount - paidAmount);

        if (currentBalance <= 0) {
            throw new Error('This booking is already fully paid');
        }

        // Payable amount: If partial specified, clamp to balance amount, else full balance
        let payableAmount = currentBalance;
        if (requestedAmount && parseFloat(requestedAmount) > 0) {
            payableAmount = Math.min(parseFloat(requestedAmount), currentBalance);
        }

        // Get customer details from users / customers tables
        const customerUser = getOne("SELECT * FROM users WHERE id = ?", [booking.customer_id]) || {};

        // Generate unique internal payment ID
        const internalPaymentId = this.generatePaymentId();

        // Amount in paise
        const amountInPaise = Math.round(payableAmount * 100);

        // Call Razorpay Orders API
        const order = await RazorpayService.createOrder({
            amount: amountInPaise,
            currency: 'INR',
            receipt: internalPaymentId,
            notes: {
                booking_id: String(booking.id),
                booking_code: String(booking.booking_id),
                customer_id: String(booking.customer_id),
                payment_id: internalPaymentId,
                category: booking.booking_category || 'WEB'
            }
        });

        // Atomically create payment attempt record and link to booking
        transaction(() => {
            run(`
                INSERT INTO payments (
                    payment_id, booking_id, customer_id, gateway,
                    razorpay_order_id, amount, expected_amount, currency,
                    payment_method, status, booking_category, booking_type,
                    notes, created_at, updated_at
                ) VALUES (
                    ?, ?, ?, 'Razorpay',
                    ?, ?, ?, 'INR',
                    'Online Gateway', 'Created', ?, ?,
                    ?, datetime('now'), datetime('now')
                )
            `, [
                internalPaymentId, booking.id, booking.customer_id,
                order.id, payableAmount, payableAmount,
                booking.booking_category || 'WEB', booking.trip_type || 'One-Way',
                `Razorpay Order created for Booking #${booking.booking_id}`
            ]);

            // Save order ID against booking
            run(`
                UPDATE bookings 
                SET razorpay_order_id = ?, updated_at = datetime('now') 
                WHERE id = ?
            `, [order.id, booking.id]);
        })();

        return {
            success: true,
            order_id: order.id,
            amount: order.amount, // in paise
            amount_rupees: payableAmount,
            currency: order.currency || 'INR',
            key_id: RazorpayService.keyId,
            payment_id: internalPaymentId,
            booking: {
                id: booking.id,
                booking_id: booking.booking_id,
                trip_type: booking.trip_type,
                pickup_location: booking.pickup_location,
                destination: booking.destination,
                travel_date: booking.travel_date,
                total_amount: totalAmount,
                paid_amount: paidAmount,
                balance_amount: currentBalance
            },
            customer: {
                name: customerUser.name || 'Valued Customer',
                email: customerUser.email || '',
                phone: customerUser.phone || ''
            }
        };
    }

    /**
     * Verify payment signature and automatically confirm booking after successful capture
     */
    static async verifyAndConfirmRazorpayPayment({
        razorpay_order_id,
        razorpay_payment_id,
        razorpay_signature,
        booking_id = null
    }) {
        if (!razorpay_order_id || !razorpay_payment_id || !razorpay_signature) {
            throw new Error('Missing payment verification credentials from checkout callback');
        }

        // Find internal payment attempt record
        let paymentRecord = getOne(
            "SELECT * FROM payments WHERE razorpay_order_id = ? ORDER BY id DESC LIMIT 1",
            [razorpay_order_id]
        );

        // Find booking
        let booking = null;
        if (paymentRecord) {
            booking = getOne("SELECT * FROM bookings WHERE id = ?", [paymentRecord.booking_id]);
        } else if (booking_id) {
            booking = getOne("SELECT * FROM bookings WHERE id = ? OR booking_id = ?", [booking_id, booking_id]);
        }

        if (!booking) {
            throw new Error('No associated booking found for this Razorpay order');
        }

        // If not found by order_id, check if payment attempt was created under this booking
        if (!paymentRecord) {
            paymentRecord = getOne(
                "SELECT * FROM payments WHERE booking_id = ? AND gateway = 'Razorpay' ORDER BY id DESC LIMIT 1",
                [booking.id]
            );
        }

        // Check if this payment ID was already processed successfully (Idempotency)
        const alreadyProcessed = getOne(
            "SELECT * FROM payments WHERE razorpay_payment_id = ? AND status = 'Success'",
            [razorpay_payment_id]
        );

        if (alreadyProcessed) {
            if (alreadyProcessed.booking_id === booking.id) {
                // Idempotent return: already confirmed
                return {
                    success: true,
                    already_processed: true,
                    booking: getOne("SELECT * FROM bookings WHERE id = ?", [booking.id]),
                    payment: alreadyProcessed
                };
            } else {
                throw new Error('This Razorpay payment has already been credited to a different booking record');
            }
        }

        // 1. Verify HMAC-SHA256 signature
        const isSignatureValid = RazorpayService.verifyPaymentSignature({
            order_id: razorpay_order_id,
            payment_id: razorpay_payment_id,
            signature: razorpay_signature
        });

        if (!isSignatureValid) {
            // Record failed attempt for security audit
            if (paymentRecord) {
                run(`
                    UPDATE payments SET
                        razorpay_payment_id = ?,
                        razorpay_signature = ?,
                        signature_verified = 0,
                        status = 'Failed',
                        error_code = 'SIGNATURE_VERIFICATION_FAILED',
                        error_description = 'HMAC SHA256 Signature mismatch',
                        updated_at = datetime('now')
                    WHERE id = ?
                `, [razorpay_payment_id, razorpay_signature, paymentRecord.id]);
            }
            throw new Error('Payment signature verification failed. Untrusted payment submission.');
        }

        // 2. Fetch actual payment from Razorpay API
        const rzpPayment = await RazorpayService.fetchPayment(razorpay_payment_id, razorpay_order_id);

        if (!rzpPayment) {
            throw new Error('Could not retrieve payment details from Razorpay gateway');
        }

        // Verify order ID match
        if (rzpPayment.order_id && rzpPayment.order_id !== razorpay_order_id) {
            throw new Error(`Order ID mismatch: Gateway ${rzpPayment.order_id} vs Local ${razorpay_order_id}`);
        }

        // Verify currency
        if (rzpPayment.currency && rzpPayment.currency !== 'INR') {
            throw new Error(`Unexpected payment currency: ${rzpPayment.currency}`);
        }

        // Ensure payment is captured (if authorized, capture it)
        let capturedPayment = rzpPayment;
        if (rzpPayment.status === 'authorized') {
            console.log(`Capturing authorized payment ${razorpay_payment_id}...`);
            capturedPayment = await RazorpayService.capturePayment(
                razorpay_payment_id,
                rzpPayment.amount,
                rzpPayment.currency || 'INR'
            );
        }

        if (capturedPayment.status !== 'captured' && !capturedPayment.captured) {
            throw new Error(`Payment is not in captured state (Current status: ${capturedPayment.status})`);
        }

        // Verify captured amount
        const amountPaidInRupees = parseFloat(capturedPayment.amount) / 100;
        const expectedAmount = paymentRecord ? parseFloat(paymentRecord.expected_amount || paymentRecord.amount) : amountPaidInRupees;

        // Disallow underpayment mismatch on this transaction
        if (amountPaidInRupees < expectedAmount - 0.01) {
            throw new Error(`Amount mismatch: Expected ₹${expectedAmount.toFixed(2)}, got ₹${amountPaidInRupees.toFixed(2)}`);
        }

        // 3. Atomically update payment, update booking, confirm booking, and sync invoice
        return transaction(() => {
            const currentBooking = getOne("SELECT * FROM bookings WHERE id = ?", [booking.id]);
            if (!currentBooking) throw new Error("Booking vanished during transaction");

            const methodType = (capturedPayment.method || 'Online').toUpperCase();
            const feeRupees = (capturedPayment.fee || 0) / 100;
            const taxRupees = (capturedPayment.tax || 0) / 100;

            const methodDetails = {
                method: capturedPayment.method,
                bank: capturedPayment.bank || null,
                wallet: capturedPayment.wallet || null,
                vpa: capturedPayment.vpa || null,
                card_network: capturedPayment.card?.network || null,
                card_type: capturedPayment.card?.type || null,
                card_last4: capturedPayment.card?.last4 || null
            };

            let paymentRowId = paymentRecord ? paymentRecord.id : null;

            if (paymentRowId) {
                run(`
                    UPDATE payments SET
                        razorpay_payment_id = ?,
                        razorpay_signature = ?,
                        signature_verified = 1,
                        amount = ?,
                        payment_method = ?,
                        method_details = ?,
                        status = 'Success',
                        fee = ?,
                        tax = ?,
                        paid_at = datetime('now'),
                        captured_at = datetime('now'),
                        gateway_response = ?,
                        updated_at = datetime('now')
                    WHERE id = ?
                `, [
                    razorpay_payment_id,
                    razorpay_signature,
                    amountPaidInRupees,
                    methodType,
                    JSON.stringify(methodDetails),
                    feeRupees,
                    taxRupees,
                    JSON.stringify(capturedPayment),
                    paymentRowId
                ]);
            } else {
                const newPaymentId = this.generatePaymentId();
                const insRes = run(`
                    INSERT INTO payments (
                        payment_id, booking_id, customer_id, gateway,
                        transaction_id, razorpay_order_id, razorpay_payment_id,
                        razorpay_signature, signature_verified,
                        amount, expected_amount, currency,
                        payment_method, method_details, status,
                        fee, tax, booking_category, booking_type,
                        paid_at, captured_at, gateway_response,
                        created_at, updated_at
                    ) VALUES (
                        ?, ?, ?, 'Razorpay',
                        ?, ?, ?,
                        ?, 1,
                        ?, ?, 'INR',
                        ?, ?, 'Success',
                        ?, ?, ?, ?,
                        datetime('now'), datetime('now'), ?,
                        datetime('now'), datetime('now')
                    )
                `, [
                    newPaymentId, currentBooking.id, currentBooking.customer_id,
                    razorpay_payment_id, razorpay_order_id, razorpay_payment_id,
                    razorpay_signature,
                    amountPaidInRupees, amountPaidInRupees,
                    methodType, JSON.stringify(methodDetails),
                    feeRupees, taxRupees, currentBooking.booking_category || 'WEB', currentBooking.trip_type || 'One-Way',
                    JSON.stringify(capturedPayment)
                ]);
                paymentRowId = insRes.lastInsertRowid;
            }

            // Update Booking balances
            const newPaid = parseFloat(currentBooking.paid_amount || 0) + amountPaidInRupees;
            const newBalance = Math.max(0, parseFloat(currentBooking.total_amount || 0) - newPaid);
            const paymentStatus = newBalance <= 0 ? 'Paid' : 'Partial';

            // AUTOMATIC CONFIRMATION RULE:
            // A booking must become CONFIRMED automatically only after the payment is authenticated,
            // verified against the correct Razorpay order, and successfully captured.
            let newBookingStatus = currentBooking.booking_status;
            let statusChanged = false;

            if (currentBooking.booking_status === 'Pending') {
                newBookingStatus = 'Confirmed';
                statusChanged = true;
            }

            run(`
                UPDATE bookings SET
                    paid_amount = ?,
                    balance_amount = ?,
                    payment_status = ?,
                    booking_status = ?,
                    confirmed_at = COALESCE(confirmed_at, datetime('now')),
                    updated_at = datetime('now')
                WHERE id = ?
            `, [newPaid, newBalance, paymentStatus, newBookingStatus, currentBooking.id]);

            // Audit history
            if (statusChanged) {
                run(`
                    INSERT INTO booking_status_histories (
                        booking_id, old_status, new_status, comment, changed_by, created_at
                    ) VALUES (
                        ?, 'Pending', 'Confirmed',
                        ?, NULL, datetime('now')
                    )
                `, [
                    currentBooking.id,
                    `Payment of ₹${amountPaidInRupees.toFixed(2)} verified & captured via Razorpay (${methodType} - ID: ${razorpay_payment_id}). Booking automatically confirmed.`
                ]);
            } else {
                run(`
                    INSERT INTO booking_status_histories (
                        booking_id, old_status, new_status, comment, changed_by, created_at
                    ) VALUES (
                        ?, ?, ?,
                        ?, NULL, datetime('now')
                    )
                `, [
                    currentBooking.id, currentBooking.booking_status, currentBooking.booking_status,
                    `Additional payment of ₹${amountPaidInRupees.toFixed(2)} received via Razorpay (${methodType} - ID: ${razorpay_payment_id}).`
                ]);
            }

            // Sync GST Tax Invoice
            this.syncInvoice(currentBooking.id);

            // Customer notification
            run(`
                INSERT INTO notifications (
                    user_id, title, message, type, is_read, action_url, created_at, updated_at
                ) VALUES (
                    ?, ?, ?, 'payment', 0, ?, datetime('now'), datetime('now')
                )
            `, [
                currentBooking.customer_id,
                `Payment Verified & Booking Confirmed!`,
                `Payment of ₹${amountPaidInRupees.toFixed(2)} verified for booking #${currentBooking.booking_id}. Your ride is confirmed.`,
                `/customer/bookings/${currentBooking.id}`
            ]);

            // Admin notification
            run(`
                INSERT INTO notifications (
                    user_id, title, message, type, is_read, action_url, created_at, updated_at
                ) VALUES (
                    1, ?, ?, 'booking_confirmed', 0, ?, datetime('now'), datetime('now')
                )
            `, [
                `Payment Captured #${currentBooking.booking_id}`,
                `₹${amountPaidInRupees.toFixed(2)} captured via Razorpay for Booking #${currentBooking.booking_id}. Booking is confirmed.`,
                `/admin/bookings/${currentBooking.id}`
            ]);

            // Dispatch WhatsApp notification (Async fire-and-forget)
            const customerUser = getOne("SELECT * FROM users WHERE id = ?", [currentBooking.customer_id]);
            const customerPhone = customerUser?.phone || currentBooking.corporate_phone;
            if (customerPhone) {
                WhatsappService.sendEvent('booking_confirmed', {
                    booking_id: currentBooking.booking_id,
                    customer_name: customerUser?.name || 'Customer',
                    pickup_location: currentBooking.pickup_location,
                    destination: currentBooking.destination,
                    travel_date: currentBooking.travel_date,
                    travel_time: currentBooking.travel_time,
                    total_amount: currentBooking.total_amount,
                    paid_amount: newPaid
                }, customerPhone, currentBooking.id).catch(e => console.warn('WhatsApp error on payment confirmation:', e));
            }

            const updatedBooking = getOne("SELECT * FROM bookings WHERE id = ?", [currentBooking.id]);
            const updatedPayment = getOne("SELECT * FROM payments WHERE id = ?", [paymentRowId]);

            return {
                success: true,
                booking: updatedBooking,
                payment: updatedPayment
            };
        })();
    }

    /**
     * Process official Razorpay Webhooks idempotently
     */
    static async processRazorpayWebhook({ rawBody, signature, event }) {
        if (!event || !event.event) {
            throw new Error('Invalid webhook event payload');
        }

        const eventId = event.id || `evt_${Date.now()}_${crypto.randomBytes(3).toString('hex')}`;
        const eventType = event.event;

        // 1. Webhook Signature Verification
        if (signature) {
            const isSignatureValid = RazorpayService.verifyWebhookSignature(rawBody, signature);
            if (!isSignatureValid) {
                // Log failed webhook signature attempt
                run(`
                    INSERT INTO razorpay_webhook_events (
                        event_id, event_type, payload, status, error_message, created_at, processed_at
                    ) VALUES (?, ?, ?, 'Failed', 'Invalid Webhook Signature', datetime('now'), datetime('now'))
                `, [eventId, eventType, typeof rawBody === 'string' ? rawBody : rawBody.toString('utf8')]);

                throw new Error('Invalid Razorpay Webhook Signature');
            }
        }

        // 2. Deduplication check
        const existingEvent = getOne("SELECT id, status FROM razorpay_webhook_events WHERE event_id = ?", [eventId]);
        if (existingEvent) {
            return { success: true, status: 'duplicate_ignored', event_id: eventId };
        }

        // Record incoming event
        const rawPayloadStr = typeof rawBody === 'string' ? rawBody : (rawBody ? rawBody.toString('utf8') : JSON.stringify(event));
        run(`
            INSERT INTO razorpay_webhook_events (
                event_id, event_type, payload, status, created_at
            ) VALUES (?, ?, ?, 'Processing', datetime('now'))
        `, [eventId, eventType, rawPayloadStr]);

        try {
            // Handle specific events
            if (eventType === 'payment.captured' || eventType === 'order.paid') {
                const paymentEntity = event.payload?.payment?.entity;
                const orderEntity = event.payload?.order?.entity;
                const orderId = paymentEntity?.order_id || orderEntity?.id;
                const paymentId = paymentEntity?.id;

                if (orderId && paymentId) {
                    // Check if already captured in our database
                    const existingSuccess = getOne(
                        "SELECT id FROM payments WHERE razorpay_payment_id = ? AND status = 'Success'",
                        [paymentId]
                    );

                    if (!existingSuccess) {
                        // Find booking by order id or notes
                        let booking = getOne("SELECT * FROM bookings WHERE razorpay_order_id = ?", [orderId]);
                        if (!booking && (paymentEntity?.notes?.booking_id || orderEntity?.notes?.booking_id)) {
                            const bId = paymentEntity?.notes?.booking_id || orderEntity?.notes?.booking_id;
                            booking = getOne("SELECT * FROM bookings WHERE id = ? OR booking_id = ?", [bId, bId]);
                        }

                        if (booking) {
                            // Synthesize verification and confirm booking
                            // Generate internal verification signature matching the event
                            const simulatedSig = RazorpayService.generateTestSignature(orderId, paymentId);
                            await this.verifyAndConfirmRazorpayPayment({
                                razorpay_order_id: orderId,
                                razorpay_payment_id: paymentId,
                                razorpay_signature: simulatedSig,
                                booking_id: booking.id
                            });
                        }
                    }
                }
            } else if (eventType === 'payment.failed') {
                const paymentEntity = event.payload?.payment?.entity;
                if (paymentEntity?.order_id) {
                    const paymentRecord = getOne("SELECT * FROM payments WHERE razorpay_order_id = ?", [paymentEntity.order_id]);
                    if (paymentRecord) {
                        run(`
                            UPDATE payments SET
                                razorpay_payment_id = ?,
                                status = 'Failed',
                                error_code = ?,
                                error_description = ?,
                                webhook_status = 'payment.failed',
                                updated_at = datetime('now')
                            WHERE id = ?
                        `, [
                            paymentEntity.id || null,
                            paymentEntity.error_code || 'PAYMENT_FAILED',
                            paymentEntity.error_description || 'Payment failed or declined by issuing bank',
                            paymentRecord.id
                        ]);
                    }
                }
            } else if (eventType === 'payment.authorized') {
                const paymentEntity = event.payload?.payment?.entity;
                if (paymentEntity?.order_id) {
                    const paymentRecord = getOne("SELECT * FROM payments WHERE razorpay_order_id = ?", [paymentEntity.order_id]);
                    if (paymentRecord && paymentRecord.status !== 'Success') {
                        run(`
                            UPDATE payments SET
                                razorpay_payment_id = ?,
                                status = 'Authorized',
                                authorized_at = datetime('now'),
                                webhook_status = 'payment.authorized',
                                updated_at = datetime('now')
                            WHERE id = ?
                        `, [paymentEntity.id || null, paymentRecord.id]);
                    }
                }
            } else if (eventType === 'refund.processed' || eventType === 'refund.created') {
                const refundEntity = event.payload?.refund?.entity;
                if (refundEntity?.payment_id) {
                    const paymentRecord = getOne("SELECT * FROM payments WHERE razorpay_payment_id = ?", [refundEntity.payment_id]);
                    if (paymentRecord) {
                        const refundAmountRupees = (refundEntity.amount || 0) / 100;
                        run(`
                            UPDATE payments SET
                                refund_id = ?,
                                refund_amount = ?,
                                refund_status = 'Processed',
                                status = CASE WHEN amount <= ? THEN 'Refunded' ELSE 'Partial Refund' END,
                                updated_at = datetime('now')
                            WHERE id = ?
                        `, [refundEntity.id, refundAmountRupees, refundAmountRupees, paymentRecord.id]);

                        const booking = getOne("SELECT * FROM bookings WHERE id = ?", [paymentRecord.booking_id]);
                        if (booking) {
                            const newPaid = Math.max(0, parseFloat(booking.paid_amount || 0) - refundAmountRupees);
                            const newBalance = Math.max(0, parseFloat(booking.total_amount || 0) - newPaid);
                            const paymentStatus = newPaid <= 0 ? 'Refunded' : 'Partial';

                            run(`
                                UPDATE bookings SET
                                    paid_amount = ?,
                                    balance_amount = ?,
                                    payment_status = ?,
                                    updated_at = datetime('now')
                                WHERE id = ?
                            `, [newPaid, newBalance, paymentStatus, booking.id]);

                            this.syncInvoice(booking.id);
                        }
                    }
                }
            }

            // Mark webhook event processed
            run(`
                UPDATE razorpay_webhook_events SET
                    status = 'Processed',
                    processed_at = datetime('now')
                WHERE event_id = ?
            `, [eventId]);

            return { success: true, status: 'processed', event_id: eventId };
        } catch (err) {
            run(`
                UPDATE razorpay_webhook_events SET
                    status = 'Failed',
                    error_message = ?,
                    processed_at = datetime('now')
                WHERE event_id = ?
            `, [err.message, eventId]);

            throw err;
        }
    }

    /**
     * Admin payment reconciliation tool: queries gateway for current state
     */
    static async reconcilePayment(paymentId) {
        const payment = getOne("SELECT * FROM payments WHERE id = ? OR payment_id = ?", [paymentId, paymentId]);
        if (!payment) throw new Error('Payment record not found');

        const booking = getOne("SELECT * FROM bookings WHERE id = ?", [payment.booking_id]);
        if (!booking) throw new Error('Associated booking not found');

        let rzpPayment = null;
        if (payment.razorpay_payment_id) {
            rzpPayment = await RazorpayService.fetchPayment(payment.razorpay_payment_id);
        } else if (payment.razorpay_order_id) {
            const rzpOrder = await RazorpayService.fetchOrder(payment.razorpay_order_id);
            if (rzpOrder.status === 'paid') {
                // If paid, fetch payments for this order
                // Reconcile status
                rzpPayment = {
                    status: 'captured',
                    amount: rzpOrder.amount_paid || rzpOrder.amount,
                    currency: rzpOrder.currency,
                    method: 'online'
                };
            }
        }

        if (!rzpPayment) {
            return {
                reconciled: false,
                message: 'No live Razorpay reference found to reconcile against',
                payment
            };
        }

        let updated = false;
        if ((rzpPayment.status === 'captured' || rzpPayment.captured) && payment.status !== 'Success') {
            const amountRupees = (rzpPayment.amount || 0) / 100;
            transaction(() => {
                run(`
                    UPDATE payments SET
                        status = 'Success',
                        reconciliation_status = 'Reconciled',
                        captured_at = datetime('now'),
                        paid_at = COALESCE(paid_at, datetime('now')),
                        updated_at = datetime('now')
                    WHERE id = ?
                `, [payment.id]);

                const newPaid = parseFloat(booking.paid_amount || 0) + amountRupees;
                const newBalance = Math.max(0, parseFloat(booking.total_amount || 0) - newPaid);
                const paymentStatus = newBalance <= 0 ? 'Paid' : 'Partial';

                let newBookingStatus = booking.booking_status;
                if (booking.booking_status === 'Pending') {
                    newBookingStatus = 'Confirmed';
                }

                run(`
                    UPDATE bookings SET
                        paid_amount = ?,
                        balance_amount = ?,
                        payment_status = ?,
                        booking_status = ?,
                        confirmed_at = COALESCE(confirmed_at, datetime('now')),
                        updated_at = datetime('now')
                    WHERE id = ?
                `, [newPaid, newBalance, paymentStatus, newBookingStatus, booking.id]);

                this.syncInvoice(booking.id);
            })();
            updated = true;
        } else {
            run(`
                UPDATE payments SET
                    reconciliation_status = 'Verified In-Sync',
                    updated_at = datetime('now')
                WHERE id = ?
            `, [payment.id]);
        }

        return {
            reconciled: true,
            updated,
            payment: getOne("SELECT * FROM payments WHERE id = ?", [payment.id]),
            booking: getOne("SELECT * FROM bookings WHERE id = ?", [booking.id]),
            gateway_data: rzpPayment
        };
    }

    /**
     * Synchronize or create GST Tax Invoice
     */
    static syncInvoice(bookingId) {
        return transaction(() => {
            const booking = getOne("SELECT * FROM bookings WHERE id = ?", [bookingId]);
            if (!booking) return null;

            const totalAmount = parseFloat(booking.total_amount || 0);
            const paidAmount = parseFloat(booking.paid_amount || 0);
            const balanceAmount = Math.max(0, totalAmount - paidAmount);

            let status = 'Unpaid';
            if (paidAmount >= totalAmount && totalAmount > 0) {
                status = 'Paid';
            } else if (paidAmount > 0) {
                status = 'Partially Paid';
            }

            const taxableAmount = parseFloat(booking.taxable_amount > 0 ? booking.taxable_amount : Math.round((totalAmount / 1.05) * 100) / 100);
            const gstAmount = parseFloat(booking.gst_amount > 0 ? booking.gst_amount : Math.round((totalAmount - taxableAmount) * 100) / 100);
            const cgstAmount = parseFloat(booking.cgst_amount > 0 ? booking.cgst_amount : Math.round((gstAmount / 2) * 100) / 100);
            const sgstAmount = parseFloat(booking.sgst_amount > 0 ? booking.sgst_amount : Math.round((gstAmount / 2) * 100) / 100);
            const igstAmount = parseFloat(booking.igst_amount || 0.0);

            const existingInvoice = getOne("SELECT * FROM invoices WHERE booking_id = ?", [booking.id]);

            if (existingInvoice) {
                run(`
                    UPDATE invoices SET
                        total_amount = ?, taxable_amount = ?, gst_rate = 5.0,
                        cgst_amount = ?, sgst_amount = ?, igst_amount = ?, gst_amount = ?,
                        customer_gstin = ?, booking_category = ?,
                        paid_amount = ?, balance_amount = ?, status = ?,
                        updated_at = datetime('now')
                    WHERE id = ?
                `, [
                    totalAmount, taxableAmount,
                    cgstAmount, sgstAmount, igstAmount, gstAmount,
                    booking.corporate_gstin || null, booking.booking_category || 'WEB',
                    paidAmount, balanceAmount, status,
                    existingInvoice.id
                ]);
                return getOne("SELECT * FROM invoices WHERE id = ?", [existingInvoice.id]);
            } else {
                const invoiceNumber = this.generateInvoiceNumber();
                const res = run(`
                    INSERT INTO invoices (
                        invoice_number, booking_id, customer_id,
                        total_amount, taxable_amount, gst_rate,
                        cgst_amount, sgst_amount, igst_amount, gst_amount,
                        customer_gstin, booking_category,
                        paid_amount, balance_amount, status,
                        version, issued_at, created_at, updated_at
                    ) VALUES (
                        ?, ?, ?,
                        ?, ?, 5.0,
                        ?, ?, ?, ?,
                        ?, ?,
                        ?, ?, ?,
                        1, datetime('now'), datetime('now'), datetime('now')
                    )
                `, [
                    invoiceNumber, booking.id, booking.customer_id,
                    totalAmount, taxableAmount,
                    cgstAmount, sgstAmount, igstAmount, gstAmount,
                    booking.corporate_gstin || null, booking.booking_category || 'WEB',
                    paidAmount, balanceAmount, status
                ]);
                return getOne("SELECT * FROM invoices WHERE id = ?", [res.lastInsertRowid]);
            }
        })();
    }

    /**
     * Record refund (Razorpay API or manual)
     */
    static async recordRefund(bookingId, amount, reason = '', recordedByUserId = null) {
        const booking = getOne("SELECT * FROM bookings WHERE id = ?", [bookingId]);
        if (!booking) throw new Error("Booking not found");

        const numAmount = parseFloat(amount);
        if (numAmount <= 0) throw new Error("Refund amount must be greater than zero");

        // Check if there is a successful Razorpay payment to refund
        const razorpayPayment = getOne(
            "SELECT * FROM payments WHERE booking_id = ? AND gateway = 'Razorpay' AND status = 'Success' AND razorpay_payment_id IS NOT NULL ORDER BY id DESC LIMIT 1",
            [booking.id]
        );

        let refundId = `REF-${Date.now()}`;
        let gatewayUsed = 'Manual Refund';

        if (razorpayPayment && razorpayPayment.razorpay_payment_id) {
            try {
                const rzpRefund = await RazorpayService.createRefund({
                    payment_id: razorpayPayment.razorpay_payment_id,
                    amount: Math.round(numAmount * 100),
                    notes: {
                        booking_id: String(booking.id),
                        booking_code: String(booking.booking_id),
                        reason
                    }
                });
                refundId = rzpRefund.id;
                gatewayUsed = 'Razorpay Refund';
            } catch (err) {
                console.warn('Razorpay Gateway refund error, recording internally:', err.message);
            }
        }

        return transaction(() => {
            const internalTxnId = `REF-${Date.now()}`;
            const res = run(`
                INSERT INTO payments (
                    payment_id, booking_id, customer_id, gateway, transaction_id,
                    refund_id, refund_amount, refund_status,
                    amount, expected_amount, currency, payment_method, status,
                    booking_category, booking_type,
                    paid_at, notes, created_at, updated_at
                ) VALUES (
                    ?, ?, ?, ?, ?,
                    ?, ?, 'Processed',
                    ?, ?, 'INR', 'Refund', 'Refunded',
                    ?, ?,
                    datetime('now'), ?, datetime('now'), datetime('now')
                )
            `, [
                this.generatePaymentId(), booking.id, booking.customer_id, gatewayUsed, internalTxnId,
                refundId, numAmount,
                -Math.abs(numAmount), numAmount,
                booking.booking_category || 'WEB', booking.trip_type || 'One-Way',
                `Refund issued: ${reason}`
            ]);

            const newPaid = Math.max(0, parseFloat(booking.paid_amount || 0) - numAmount);
            const newBalance = Math.max(0, parseFloat(booking.total_amount || 0) - newPaid);
            const paymentStatus = newPaid <= 0 ? 'Refunded' : 'Partial';

            run(`
                UPDATE bookings 
                SET paid_amount = ?, balance_amount = ?, payment_status = ?, updated_at = datetime('now')
                WHERE id = ?
            `, [newPaid, newBalance, paymentStatus, booking.id]);

            this.syncInvoice(booking.id);

            return getOne("SELECT * FROM payments WHERE id = ?", [res.lastInsertRowid]);
        })();
    }
}

export default PaymentService;
