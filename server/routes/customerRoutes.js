import express from 'express';
import db, { getOne, getAll, run, transaction } from '../db.js';
import { ensureAuthenticated, ensureCustomer } from '../middleware/auth.js';
import InvoiceDocumentService from '../services/InvoiceDocumentService.js';
import bcrypt from 'bcryptjs';

const router = express.Router();

router.use(ensureAuthenticated);
router.use(ensureCustomer);

// Dashboard
router.get('/dashboard', (req, res) => {
    const user = req.user;
    const bookings = getAll(`
        SELECT b.*, v.name as vehicle_name, d.name as driver_name, d.mobile as driver_mobile
        FROM bookings b
        LEFT JOIN vehicles v ON b.vehicle_id = v.id
        LEFT JOIN drivers d ON b.driver_id = d.id
        WHERE b.customer_id = ?
        ORDER BY b.id DESC
    `, [user.id]);

    const stats = {
        total_bookings: bookings.length,
        upcoming_trips: bookings.filter(b => ['Pending', 'Confirmed', 'Vehicle Assigned', 'Driver Assigned'].includes(b.booking_status)).length,
        completed_trips: bookings.filter(b => b.booking_status === 'Completed').length,
        pending_balance: bookings.reduce((acc, b) => acc + (parseFloat(b.balance_amount) > 0 ? parseFloat(b.balance_amount) : 0), 0)
    };

    const upcomingTrip = bookings.find(b => ['Confirmed', 'Vehicle Assigned', 'Driver Assigned', 'Trip Started', 'On The Way'].includes(b.booking_status)) || null;
    const recentBookings = bookings.slice(0, 5);
    const notifications = getAll("SELECT * FROM notifications WHERE user_id = ? ORDER BY id DESC LIMIT 5", [user.id]);

    res.render('customer/dashboard', {
        user,
        stats,
        upcomingTrip,
        recentBookings,
        notifications,
        pageTitle: 'Customer Dashboard | Vaishnavi Tours'
    });
});

// Bookings Index
router.get('/bookings', (req, res) => {
    const user = req.user;
    const filter = req.query.filter || 'all';
    let sql = `
        SELECT b.*, v.name as vehicle_name, d.name as driver_name
        FROM bookings b
        LEFT JOIN vehicles v ON b.vehicle_id = v.id
        LEFT JOIN drivers d ON b.driver_id = d.id
        WHERE b.customer_id = ?
    `;
    const params = [user.id];

    if (filter === 'upcoming') {
        sql += ` AND b.booking_status IN ('Pending', 'Confirmed', 'Vehicle Assigned', 'Driver Assigned', 'Trip Started', 'On The Way')`;
    } else if (filter === 'completed') {
        sql += ` AND b.booking_status = 'Completed'`;
    } else if (filter === 'cancelled') {
        sql += ` AND b.booking_status = 'Cancelled'`;
    } else if (req.query.status) {
        sql += ` AND b.booking_status = ?`;
        params.push(req.query.status);
    }

    if (req.query.search) {
        sql += ` AND (b.booking_id LIKE ? OR b.pickup_location LIKE ? OR b.destination LIKE ?)`;
        const s = `%${req.query.search}%`;
        params.push(s, s, s);
    }

    sql += ` ORDER BY b.id DESC`;
    const bookings = getAll(sql, params);

    res.render('customer/bookings/index', {
        bookings,
        filter,
        pageTitle: 'My Bookings | Vaishnavi Tours'
    });
});

// Booking Details
router.get('/bookings/:id', (req, res) => {
    const booking = getOne("SELECT * FROM bookings WHERE id = ? AND customer_id = ?", [req.params.id, req.user.id]);
    if (!booking) {
        res.setFlash('error', 'Booking not found or unauthorized.');
        return res.redirect('/customer/bookings');
    }

    booking.vehicle = booking.vehicle_id ? getOne("SELECT * FROM vehicles WHERE id = ?", [booking.vehicle_id]) : null;
    booking.driver = booking.driver_id ? getOne("SELECT * FROM drivers WHERE id = ?", [booking.driver_id]) : null;
    booking.trip = getOne("SELECT * FROM trips WHERE booking_id = ?", [booking.id]);
    booking.payments = getAll("SELECT * FROM payments WHERE booking_id = ? ORDER BY paid_at DESC", [booking.id]);
    booking.invoice = getOne("SELECT * FROM invoices WHERE booking_id = ?", [booking.id]);
    booking.statusHistories = getAll(`
        SELECT h.*, u.name as changer_name 
        FROM booking_status_histories h
        LEFT JOIN users u ON h.changed_by = u.id
        WHERE h.booking_id = ?
        ORDER BY h.id DESC
    `, [booking.id]);

    if (booking.pricing_details && typeof booking.pricing_details === 'string') {
        try { booking.pricing_details = JSON.parse(booking.pricing_details); } catch (e) {}
    }

    res.render('customer/bookings/show', {
        booking,
        pageTitle: `Booking #${booking.booking_id} | Vaishnavi Tours`
    });
});

// Cancel Booking Request
router.post('/bookings/:id/cancel', (req, res) => {
    const booking = getOne("SELECT * FROM bookings WHERE id = ? AND customer_id = ?", [req.params.id, req.user.id]);
    if (!booking) {
        res.setFlash('error', 'Booking not found.');
        return res.redirect('/customer/bookings');
    }

    if (['Trip Started', 'On The Way', 'Completed', 'Cancelled'].includes(booking.booking_status)) {
        res.setFlash('error', `This booking cannot be cancelled because it is already ${booking.booking_status.toLowerCase()}.`);
        return res.redirect(`/customer/bookings/${booking.id}`);
    }

    const reason = req.body.cancellation_reason || 'Customer cancellation request';

    run(`
        UPDATE bookings 
        SET cancellation_status = 'Requested', cancellation_reason = ?, updated_at = datetime('now')
        WHERE id = ?
    `, [reason, booking.id]);

    run(`
        INSERT INTO booking_status_histories (
            booking_id, status, comments, changed_by, created_at, updated_at
        ) VALUES (
            ?, ?, ?, ?, datetime('now'), datetime('now')
        )
    `, [booking.id, booking.booking_status, `Cancellation requested by customer. Reason: ${reason}`, req.user.id]);

    run(`
        INSERT INTO notifications (
            user_id, title, message, type, is_read, action_url, created_at, updated_at
        ) VALUES (
            1, 'Cancellation Requested: #${booking.booking_id}', ?, 'booking_cancel_request', 0, ?, datetime('now'), datetime('now')
        )
    `, [`Customer ${req.user.name} requested cancellation for #${booking.booking_id}. Reason: ${reason}`, `/admin/bookings/${booking.id}`]);

    res.setFlash('success', 'Your cancellation request has been submitted to dispatch for review.');
    res.redirect(`/customer/bookings/${booking.id}`);
});

// Profile Edit
router.get('/profile', (req, res) => {
    const user = req.user;
    const customer = getOne("SELECT * FROM customers WHERE user_id = ?", [user.id]) || {};
    user.customer = customer;

    res.render('customer/profile', {
        user,
        pageTitle: 'My Profile | Vaishnavi Tours'
    });
});

router.put('/profile', (req, res) => {
    const user = req.user;
    const { name, phone, alternate_phone, address, city, state, pincode, current_password, new_password, new_password_confirmation } = req.body;

    const cleanPhone = String(phone || '').replace(/[^0-9]/g, '');
    if (cleanPhone.length !== 10) {
        res.setFlash('error', 'Please enter a valid 10-digit mobile number.');
        return res.redirect('/customer/profile');
    }

    // Password change verification
    if (new_password) {
        if (!current_password) {
            res.setFlash('error', 'Current password is required to set a new password.');
            return res.redirect('/customer/profile');
        }
        const fullUser = getOne("SELECT password FROM users WHERE id = ?", [user.id]);
        if (!bcrypt.compareSync(current_password, fullUser.password)) {
            res.setFlash('error', 'Current password does not match.');
            return res.redirect('/customer/profile');
        }
        if (new_password !== new_password_confirmation) {
            res.setFlash('error', 'New password confirmation does not match.');
            return res.redirect('/customer/profile');
        }
        const newHash = bcrypt.hashSync(new_password, 10);
        run("UPDATE users SET password = ? WHERE id = ?", [newHash, user.id]);
    }

    run("UPDATE users SET name = ?, phone = ?, updated_at = datetime('now') WHERE id = ?", [name.trim(), cleanPhone, user.id]);

    const existingCust = getOne("SELECT id FROM customers WHERE user_id = ?", [user.id]);
    if (existingCust) {
        run(`
            UPDATE customers 
            SET address = ?, city = ?, state = ?, pincode = ?, updated_at = datetime('now')
            WHERE id = ?
        `, [address || null, city || 'Bilaspur', state || 'Chhattisgarh', pincode || '495001', existingCust.id]);
    } else {
        run(`
            INSERT INTO customers (user_id, address, city, state, pincode, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, datetime('now'), datetime('now'))
        `, [user.id, address || null, city || 'Bilaspur', state || 'Chhattisgarh', pincode || '495001']);
    }

    res.setFlash('success', 'Profile updated successfully.');
    res.redirect('/customer/profile');
});

// Notifications
router.get('/notifications', (req, res) => {
    const notifications = getAll("SELECT * FROM notifications WHERE user_id = ? ORDER BY id DESC", [req.user.id]);
    run("UPDATE notifications SET is_read = 1 WHERE user_id = ?", [req.user.id]);

    res.render('customer/notifications', {
        notifications,
        pageTitle: 'My Notifications | Vaishnavi Tours'
    });
});

// Invoice View
router.get('/invoices/:id', (req, res) => {
    const invoice = getOne("SELECT * FROM invoices WHERE id = ?", [req.params.id]);
    if (!invoice || invoice.customer_id !== req.user.id) {
        res.setFlash('error', 'Invoice not found or unauthorized.');
        return res.redirect('/customer/bookings');
    }

    const booking = getOne("SELECT * FROM bookings WHERE id = ?", [invoice.booking_id]);
    const document = InvoiceDocumentService.forBooking(booking);
    const backUrl = `/customer/bookings/${booking.id}`;

    res.render('invoices/printable', {
        invoice,
        booking,
        document,
        backUrl,
        pageTitle: `Tax Invoice #${invoice.invoice_number}`
    });
});

export default router;
