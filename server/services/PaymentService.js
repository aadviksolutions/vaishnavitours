import db, { getOne, getAll, run, transaction } from '../db.js';

export class PaymentService {
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

    static recordPayment(bookingId, data, recordedByUserId = null) {
        return transaction(() => {
            const booking = getOne("SELECT * FROM bookings WHERE id = ?", [bookingId]);
            if (!booking) throw new Error("Booking not found");

            const amount = parseFloat(data.amount);
            const txnId = data.transaction_id || `TXN-${Date.now()}`;
            const gateway = data.gateway || 'Manual / Direct';
            const method = data.payment_method || 'Cash';
            const status = data.status || 'Success';

            const res = run(`
                INSERT INTO payments (
                    booking_id, customer_id, gateway, transaction_id,
                    amount, currency, payment_method, status,
                    paid_at, notes, created_at, updated_at
                ) VALUES (
                    ?, ?, ?, ?,
                    ?, 'INR', ?, ?,
                    datetime('now'), ?, datetime('now'), datetime('now')
                )
            `, [
                booking.id, booking.customer_id, gateway, txnId,
                amount, method, status,
                data.notes || null
            ]);

            if (status === 'Success') {
                const newPaid = parseFloat(booking.paid_amount || 0) + amount;
                const newBalance = Math.max(0, parseFloat(booking.total_amount || 0) - newPaid);
                const paymentStatus = newBalance <= 0 ? 'Paid' : 'Partial';

                run(`
                    UPDATE bookings 
                    SET paid_amount = ?, balance_amount = ?, payment_status = ?, updated_at = datetime('now')
                    WHERE id = ?
                `, [newPaid, newBalance, paymentStatus, booking.id]);

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

    static recordRefund(bookingId, amount, reason = '', recordedByUserId = null) {
        return transaction(() => {
            const booking = getOne("SELECT * FROM bookings WHERE id = ?", [bookingId]);
            if (!booking) throw new Error("Booking not found");

            const numAmount = parseFloat(amount);
            const txnId = `REF-${Date.now()}`;

            const res = run(`
                INSERT INTO payments (
                    booking_id, customer_id, gateway, transaction_id,
                    amount, currency, payment_method, status,
                    paid_at, notes, created_at, updated_at
                ) VALUES (
                    ?, ?, 'Manual Refund', ?,
                    ?, 'INR', 'Refund', 'Refunded',
                    datetime('now'), ?, datetime('now'), datetime('now')
                )
            `, [
                booking.id, booking.customer_id, txnId,
                -Math.abs(numAmount), `Refund issued: ${reason}`
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
