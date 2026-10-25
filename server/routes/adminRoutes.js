import express from 'express';
import db, { getOne, getAll, run, transaction, getVehicleIconUrl } from '../db.js';
import { ensureAuthenticated, ensureAdmin } from '../middleware/auth.js';
import BookingService from '../services/BookingService.js';
import PaymentService from '../services/PaymentService.js';
import WhatsappService from '../services/WhatsappService.js';
import InvoiceDocumentService from '../services/InvoiceDocumentService.js';
import GoogleMapsRoutingService from '../services/GoogleMapsRoutingService.js';
import config from '../config.js';
import { getBookingData } from './publicRoutes.js';
import bcryptjs from 'bcryptjs';
import multer from 'multer';

const upload = multer({
    storage: multer.memoryStorage(),
    limits: { fileSize: 10 * 1024 * 1024 }
});

const router = express.Router();

router.use(ensureAuthenticated);
router.use(ensureAdmin);

// Root redirect
router.get('/', (req, res) => res.redirect('/admin/dashboard'));

// 1. Dashboard
router.get('/dashboard', (req, res) => {
    const today = new Date().toISOString().split('T')[0];

    const todaysBookings = getOne("SELECT count(*) as c FROM bookings WHERE date(travel_date) = date(?)", [today])?.c || 0;
    const upcomingBookings = getOne("SELECT count(*) as c FROM bookings WHERE date(travel_date) > date(?) AND booking_status NOT IN ('Completed', 'Cancelled')", [today])?.c || 0;
    const pendingEnquiries = getOne("SELECT count(*) as c FROM enquiries WHERE status IN ('New', 'Contacted') OR status IS NULL")?.c || 0;
    const unassignedBookings = getOne("SELECT count(*) as c FROM bookings WHERE (driver_id IS NULL OR vehicle_id IS NULL) AND booking_status NOT IN ('Completed', 'Cancelled')")?.c || 0;
    const availableVehicles = getOne("SELECT count(*) as c FROM vehicles WHERE status = 'Available'")?.c || 0;
    const availableDrivers = getOne("SELECT count(*) as c FROM drivers WHERE status = 'Available'")?.c || 0;
    const ongoingTrips = getOne("SELECT count(*) as c FROM trips WHERE status = 'Trip Started'")?.c || 0;
    const incompleteTrips = getOne("SELECT count(*) as c FROM trips WHERE status != 'Completed'")?.c || 0;
    const pendingPayments = getOne("SELECT COALESCE(SUM(balance_amount), 0) as s FROM bookings WHERE booking_status != 'Cancelled'")?.s || 0;
    const outstandingExpenses = getOne("SELECT COALESCE(SUM(amount), 0) as s FROM expenses")?.s || 0;
    const gstInvoicesCount = getOne("SELECT count(*) as c FROM invoices")?.c || 0;

    const summaryCards = {
        todays_bookings: { title: "Today's Bookings", value: todaysBookings, icon: 'calendar', color: 'blue', route: `/admin/bookings?date=${today}` },
        upcoming_bookings: { title: 'Upcoming Bookings', value: upcomingBookings, icon: 'clock', color: 'indigo', route: '/admin/bookings?filter=upcoming' },
        pending_enquiries: { title: 'Pending Enquiries', value: pendingEnquiries, icon: 'mail', color: 'amber', route: '/admin/enquiries?status=New' },
        unassigned_bookings: { title: 'Unassigned Bookings', value: unassignedBookings, icon: 'alert-triangle', color: 'rose', route: '/admin/bookings?filter=unassigned' },
        available_vehicles: { title: 'Available Vehicles', value: availableVehicles, icon: 'car-front', color: 'emerald', route: '/admin/vehicles?status=Available' },
        available_drivers: { title: 'Available Drivers', value: availableDrivers, icon: 'user-check', color: 'teal', route: '/admin/drivers?filter=unassigned' },
        ongoing_trips: { title: 'Ongoing Trips', value: ongoingTrips, icon: 'navigation', color: 'purple', route: '/admin/trips?status=Trip Started' },
        incomplete_trips: { title: 'Incomplete Trips', value: incompleteTrips, icon: 'clock', color: 'orange', route: '/admin/trips' },
        pending_payments: { title: 'Pending Payments', value: `₹${parseFloat(pendingPayments).toFixed(2)}`, icon: 'credit-card', color: 'red', route: '/admin/bookings?payment_status=Pending' },
        outstanding_expenses: { title: 'Total Expenses', value: `₹${parseFloat(outstandingExpenses).toFixed(2)}`, icon: 'dollar-sign', color: 'slate', route: '/admin/expenses' },
        gst_invoices: { title: 'GST Invoices', value: gstInvoicesCount, icon: 'file-text', color: 'cyan', route: '/admin/invoices' }
    };

    const quickActions = [
        { title: 'Create Booking', route: '/admin/bookings/create', icon: 'plus-circle', bg: 'bg-emerald-600 hover:bg-emerald-700 text-white' },
        { title: 'Assign Driver', route: '/admin/bookings?filter=unassigned_driver', icon: 'user-plus', bg: 'bg-indigo-600 hover:bg-indigo-700 text-white' },
        { title: 'Assign Vehicle', route: '/admin/bookings?filter=unassigned_vehicle', icon: 'truck', bg: 'bg-blue-600 hover:bg-blue-700 text-white' },
        { title: 'Add Driver', route: '/admin/drivers/create', icon: 'user-check', bg: 'bg-slate-700 hover:bg-slate-800 text-white' },
        { title: 'Add Vehicle', route: '/admin/vehicles/create', icon: 'plus', bg: 'bg-slate-700 hover:bg-slate-800 text-white' },
        { title: 'Add Pickup Location', route: '/admin/locations/create', icon: 'map-pin', bg: 'bg-amber-600 hover:bg-amber-700 text-white' },
        { title: 'Add Tour Package', route: '/admin/tour-packages/create', icon: 'compass', bg: 'bg-teal-600 hover:bg-teal-700 text-white' },
        { title: 'Add Expense', route: '/admin/expenses/create', icon: 'dollar-sign', bg: 'bg-rose-600 hover:bg-rose-700 text-white' },
        { title: 'Create Invoice', route: '/admin/invoices', icon: 'file-text', bg: 'bg-cyan-600 hover:bg-cyan-700 text-white' },
        { title: 'View Enquiries', route: '/admin/enquiries', icon: 'inbox', bg: 'bg-purple-600 hover:bg-purple-700 text-white' },
    ];

    const recentBookings = getAll(`
        SELECT b.*, u.name as customer_name, v.name as vehicle_name, d.name as driver_name
        FROM bookings b
        LEFT JOIN users u ON b.customer_id = u.id
        LEFT JOIN vehicles v ON b.vehicle_id = v.id
        LEFT JOIN drivers d ON b.driver_id = d.id
        ORDER BY b.id DESC LIMIT 8
    `);

    const recentPayments = getAll(`
        SELECT p.*, b.booking_id as b_code, u.name as customer_name
        FROM payments p
        LEFT JOIN bookings b ON p.booking_id = b.id
        LEFT JOIN users u ON p.customer_id = u.id
        ORDER BY p.id DESC LIMIT 6
    `);

    const unreadNotifications = getAll("SELECT * FROM notifications WHERE is_read = 0 ORDER BY id DESC LIMIT 5");
    const unreadNotificationsCount = getOne("SELECT count(*) as c FROM notifications WHERE is_read = 0")?.c || 0;
    const recentQueries = getAll("SELECT * FROM enquiries WHERE status IN ('New', 'Contacted') OR status IS NULL ORDER BY id DESC LIMIT 6");

    // Monthly revenue data for the chart (past 6 months)
    const revenueMonths = [];
    const revenueData = [];
    const now = new Date();
    for (let i = 5; i >= 0; i--) {
        const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
        const mStr = d.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const pattern = `${y}-${m}%`;

        const rev = getOne(`
            SELECT COALESCE(SUM(amount), 0) as s 
            FROM payments 
            WHERE (LOWER(status) = 'success' OR LOWER(status) = 'completed') 
              AND (paid_at LIKE ? OR (paid_at IS NULL AND created_at LIKE ?))
        `, [pattern, pattern])?.s || 0;

        revenueMonths.push(mStr);
        revenueData.push(Math.round(parseFloat(rev) * 100) / 100);
    }

    res.render('admin/dashboard', {
        summaryCards,
        quickActions,
        todaysBookings,
        upcomingBookings,
        pendingEnquiries,
        unassignedBookings,
        availableVehicles,
        availableDrivers,
        ongoingTrips,
        incompleteTrips,
        pendingPayments,
        outstandingExpenses,
        gstInvoicesCount,
        recentBookings,
        recentPayments,
        unreadNotifications,
        unreadNotificationsCount,
        recentQueries,
        revenueMonths,
        revenueData,
        page_title: 'Admin Dashboard'
    });
});

// 2. Bookings List & CRUD
router.get('/bookings', (req, res) => {
    const filter = req.query.filter || 'all';
    const sortBy = req.query.sort_by || 'created_desc';
    const date = req.query.date || '';
    const fromDate = req.query.from_date || '';
    const toDate = req.query.to_date || '';
    const status = req.query.status || '';
    const paymentStatus = req.query.payment_status || '';
    const search = req.query.search ? req.query.search.trim() : '';

    let sql = `
        SELECT b.*, 
               u.name as customer_name, u.phone as customer_phone, u.email as customer_email,
               v.name as vehicle_name, v.registration_number as vehicle_reg_number,
               d.name as driver_name, d.mobile as driver_phone
        FROM bookings b
        LEFT JOIN users u ON b.customer_id = u.id
        LEFT JOIN vehicles v ON b.vehicle_id = v.id
        LEFT JOIN drivers d ON b.driver_id = d.id
        WHERE 1=1
    `;
    const params = [];

    // Filter tabs
    if (filter === 'upcoming') {
        sql += ` AND date(b.travel_date) >= date('now') AND b.booking_status NOT IN ('Completed', 'Cancelled')`;
    } else if (filter === 'today') {
        sql += ` AND date(b.travel_date) = date('now') AND b.booking_status NOT IN ('Cancelled')`;
    } else if (filter === 'unassigned') {
        sql += ` AND (b.driver_id IS NULL OR b.vehicle_id IS NULL) AND b.booking_status NOT IN ('Completed', 'Cancelled')`;
    } else if (filter === 'unassigned_vehicle') {
        sql += ` AND b.vehicle_id IS NULL AND b.booking_status NOT IN ('Completed', 'Cancelled')`;
    } else if (filter === 'unassigned_driver') {
        sql += ` AND b.driver_id IS NULL AND b.booking_status NOT IN ('Completed', 'Cancelled')`;
    } else if (filter === 'assigned') {
        sql += ` AND b.driver_id IS NOT NULL AND b.vehicle_id IS NOT NULL AND b.booking_status NOT IN ('Completed', 'Cancelled')`;
    } else if (filter === 'completed') {
        sql += ` AND b.booking_status = 'Completed'`;
    } else if (filter === 'cancelled') {
        sql += ` AND b.booking_status = 'Cancelled'`;
    }

    // Date filters
    if (date) {
        sql += ` AND date(b.travel_date) = date(?)`;
        params.push(date);
    }
    if (fromDate) {
        sql += ` AND date(b.travel_date) >= date(?)`;
        params.push(fromDate);
    }
    if (toDate) {
        sql += ` AND date(b.travel_date) <= date(?)`;
        params.push(toDate);
    }

    // Status filters
    if (status) {
        sql += ` AND b.booking_status = ?`;
        params.push(status);
    }
    if (paymentStatus) {
        sql += ` AND b.payment_status = ?`;
        params.push(paymentStatus);
    }

    // Search query
    if (search) {
        sql += ` AND (
            b.booking_id LIKE ? OR 
            b.pickup_location LIKE ? OR 
            b.destination LIKE ? OR 
            u.name LIKE ? OR 
            u.phone LIKE ? OR
            b.corporate_company_name LIKE ? OR
            b.travel_partner_name LIKE ?
        )`;
        const s = `%${search}%`;
        params.push(s, s, s, s, s, s, s);
    }

    // Sorting logic
    if (sortBy === 'travel_date_asc' || sortBy === 'date_asc') {
        sql += ` ORDER BY date(b.travel_date) ASC, time(b.travel_time) ASC, b.id ASC`;
    } else if (sortBy === 'travel_date_desc' || sortBy === 'date_desc') {
        sql += ` ORDER BY date(b.travel_date) DESC, time(b.travel_time) DESC, b.id DESC`;
    } else if (sortBy === 'created_asc' || sortBy === 'old_to_new') {
        sql += ` ORDER BY b.id ASC`;
    } else if (sortBy === 'unassigned_first') {
        sql += ` ORDER BY (CASE WHEN b.vehicle_id IS NULL OR b.driver_id IS NULL THEN 0 ELSE 1 END) ASC, date(b.travel_date) ASC, b.id DESC`;
    } else if (sortBy === 'fare_desc') {
        sql += ` ORDER BY b.total_amount DESC, b.id DESC`;
    } else if (sortBy === 'fare_asc') {
        sql += ` ORDER BY b.total_amount ASC, b.id ASC`;
    } else {
        // Default: created_desc / new_to_old
        sql += ` ORDER BY b.id DESC`;
    }

    const rawBookings = getAll(sql, params);
    const bookings = rawBookings.map(b => ({
        ...b,
        customer: {
            name: b.customer_name || 'Guest User',
            phone: b.customer_phone || 'N/A',
            email: b.customer_email || ''
        },
        vehicle: b.vehicle_name ? {
            name: b.vehicle_name,
            registration_number: b.vehicle_reg_number,
            icon_url: getVehicleIconUrl({ name: b.vehicle_name, vehicle_type: b.vehicle_name })
        } : null,
        driver: b.driver_name ? { name: b.driver_name, phone: b.driver_phone } : null
    }));

    const dispatchCounts = {
        total: getOne("SELECT count(*) as c FROM bookings")?.c || 0,
        unassigned: getOne("SELECT count(*) as c FROM bookings WHERE (driver_id IS NULL OR vehicle_id IS NULL) AND booking_status NOT IN ('Completed', 'Cancelled')")?.c || 0,
        unassigned_vehicle: getOne("SELECT count(*) as c FROM bookings WHERE vehicle_id IS NULL AND booking_status NOT IN ('Completed', 'Cancelled')")?.c || 0,
        unassigned_driver: getOne("SELECT count(*) as c FROM bookings WHERE driver_id IS NULL AND booking_status NOT IN ('Completed', 'Cancelled')")?.c || 0,
        today: getOne("SELECT count(*) as c FROM bookings WHERE date(travel_date) = date('now') AND booking_status NOT IN ('Cancelled')")?.c || 0,
        upcoming: getOne("SELECT count(*) as c FROM bookings WHERE date(travel_date) >= date('now') AND booking_status NOT IN ('Completed', 'Cancelled')")?.c || 0,
        completed: getOne("SELECT count(*) as c FROM bookings WHERE booking_status = 'Completed'")?.c || 0,
        cancelled: getOne("SELECT count(*) as c FROM bookings WHERE booking_status = 'Cancelled'")?.c || 0
    };

    const availableVehicles = getAll("SELECT * FROM vehicles WHERE status = 'Available'");
    const availableDrivers = getAll("SELECT * FROM drivers WHERE status = 'Available'");
    const allVehicles = getAll("SELECT * FROM vehicles WHERE status != 'Inactive'");
    const allDrivers = getAll("SELECT * FROM drivers WHERE status != 'Inactive'");

    res.render('admin/bookings/index', {
        bookings,
        availableVehicles,
        availableDrivers,
        allVehicles,
        allDrivers,
        filter,
        sortBy,
        selectedDate: date,
        fromDate,
        toDate,
        status,
        paymentStatus,
        searchQuery: search,
        dispatchCounts,
        page_title: 'Booking Dispatch Console'
    });
});

router.get('/bookings/create', (req, res) => {
    const bookingData = getBookingData();
    const customers = getAll("SELECT * FROM users WHERE role = 'customer' ORDER BY name ASC");
    const vehicles = getAll("SELECT * FROM vehicles WHERE status = 'Available' ORDER BY name ASC");
    const drivers = getAll("SELECT * FROM drivers WHERE status = 'Available' ORDER BY name ASC");
    const pickupLocations = getAll("SELECT * FROM pickup_locations WHERE is_active = 1 ORDER BY name ASC");
    const tourPackages = getAll("SELECT * FROM tour_packages WHERE is_active = 1 ORDER BY name ASC");
    const bookingCategories = getAll("SELECT * FROM booking_categories WHERE is_active = 1 ORDER BY id ASC");

    let prefill = null;
    if (req.query.from_enquiry) {
        prefill = getOne("SELECT * FROM enquiries WHERE id = ?", [req.query.from_enquiry]) || null;
    }

    res.render('admin/bookings/create', {
        ...bookingData,
        customers,
        vehicles,
        drivers,
        pickupLocations,
        tourPackages,
        bookingCategories,
        prefill,
        page_title: 'Create Manual Booking'
    });
});

router.post('/bookings', (req, res) => {
    try {
        const newBooking = BookingService.createBooking(req.body, req.user);
        res.setFlash('success', `Booking #${newBooking.booking_id} created successfully.`);
        res.redirect(`/admin/bookings/${newBooking.id}`);
    } catch (err) {
        console.error('Error creating booking:', err);
        res.setFlash('error', err.message);
        res.redirect('/admin/bookings/create');
    }
});

router.get('/bookings/:id', (req, res) => {
    const booking = getOne("SELECT * FROM bookings WHERE id = ?", [req.params.id]);
    if (!booking) {
        res.setFlash('error', 'Booking not found.');
        return res.redirect('/admin/bookings');
    }

    booking.customer = getOne("SELECT * FROM users WHERE id = ?", [booking.customer_id]);
    booking.vehicle = booking.vehicle_id ? getOne("SELECT * FROM vehicles WHERE id = ?", [booking.vehicle_id]) : null;
    booking.driver = booking.driver_id ? getOne("SELECT * FROM drivers WHERE id = ?", [booking.driver_id]) : null;
    booking.payments = getAll("SELECT * FROM payments WHERE booking_id = ? ORDER BY paid_at DESC", [booking.id]);
    booking.invoice = getOne("SELECT * FROM invoices WHERE booking_id = ?", [booking.id]);
    booking.statusHistories = getAll(`
        SELECT h.*, u.name as changer_name 
        FROM booking_status_histories h
        LEFT JOIN users u ON h.changed_by = u.id
        WHERE h.booking_id = ?
        ORDER BY h.id DESC
    `, [booking.id]);

    const availableVehicles = getAll("SELECT * FROM vehicles WHERE status = 'Available' ORDER BY name ASC");
    const availableDrivers = getAll("SELECT * FROM drivers WHERE status = 'Available' ORDER BY name ASC");

    if (booking.pricing_details && typeof booking.pricing_details === 'string') {
        try { booking.pricing_details = JSON.parse(booking.pricing_details); } catch (e) {}
    }

    res.render('admin/bookings/show', {
        booking,
        availableVehicles,
        availableDrivers,
        page_title: `Booking #${booking.booking_id}`
    });
});

// Controlled workflow actions
router.post('/bookings/:id/confirm', (req, res) => {
    BookingService.updateStatus(req.params.id, 'Confirmed', 'Booking confirmed by admin dispatch', req.user.id);
    res.setFlash('success', 'Booking confirmed.');
    res.redirect(`/admin/bookings/${req.params.id}`);
});

router.post('/bookings/:id/assign', (req, res) => {
    const { vehicle_id, driver_id } = req.body;
    const booking = getOne("SELECT * FROM bookings WHERE id = ?", [req.params.id]);
    if (!booking) {
        res.setFlash('error', 'Booking not found.');
        return res.redirect('/admin/bookings');
    }

    if (vehicle_id) {
        const conflict = BookingService.checkVehicleOverlap(vehicle_id, booking.travel_date, booking.return_date, booking.id);
        if (conflict) {
            res.setFlash('error', `Warning: Vehicle is already assigned to #${conflict.booking_id} around ${conflict.travel_date}.`);
        }
        run("UPDATE bookings SET vehicle_id = ?, updated_at = datetime('now') WHERE id = ?", [vehicle_id, booking.id]);
        run("UPDATE vehicles SET status = 'On Trip', updated_at = datetime('now') WHERE id = ?", [vehicle_id]);
    }

    if (driver_id) {
        const conflict = BookingService.checkDriverOverlap(driver_id, booking.travel_date, booking.return_date, booking.id);
        if (conflict) {
            res.setFlash('error', `Warning: Driver is already assigned to #${conflict.booking_id} around ${conflict.travel_date}.`);
        }
        run("UPDATE bookings SET driver_id = ?, updated_at = datetime('now') WHERE id = ?", [driver_id, booking.id]);
        run("UPDATE drivers SET status = 'On Trip', updated_at = datetime('now') WHERE id = ?", [driver_id]);
    }

    let nextStatus = booking.booking_status;
    if (vehicle_id && driver_id) {
        nextStatus = 'Driver Assigned';
    } else if (vehicle_id) {
        nextStatus = 'Vehicle Assigned';
    }

    BookingService.updateStatus(booking.id, nextStatus, 'Fleet vehicle and driver assigned by admin dispatch', req.user.id);
    res.setFlash('success', 'Vehicle & driver successfully assigned.');
    res.redirect(`/admin/bookings/${req.params.id}`);
});

router.post('/bookings/:id/status', (req, res) => {
    const { status, remarks } = req.body;
    BookingService.updateStatus(req.params.id, status, remarks || `Status set to ${status}`, req.user.id);
    res.setFlash('success', `Status updated to ${status}.`);
    res.redirect(`/admin/bookings/${req.params.id}`);
});

router.post('/bookings/:id/amount', (req, res) => {
    const { total_amount } = req.body;
    const booking = getOne("SELECT * FROM bookings WHERE id = ?", [req.params.id]);
    if (!booking) return res.redirect('/admin/bookings');

    const total = parseFloat(total_amount);
    const balance = Math.max(0, total - parseFloat(booking.paid_amount || 0));
    run("UPDATE bookings SET total_amount = ?, balance_amount = ?, updated_at = datetime('now') WHERE id = ?", [total, balance, booking.id]);
    PaymentService.syncInvoice(booking.id);

    res.setFlash('success', 'Tariff updated and invoice recalculated.');
    res.redirect(`/admin/bookings/${booking.id}`);
});

router.post('/bookings/:id/payment', (req, res) => {
    try {
        PaymentService.recordPayment(req.params.id, req.body, req.user.id);
        res.setFlash('success', 'Payment recorded successfully.');
    } catch (e) {
        res.setFlash('error', e.message);
    }
    res.redirect(`/admin/bookings/${req.params.id}`);
});

// 3. Trips
router.get('/trips', (req, res) => {
    const trips = getAll(`
        SELECT t.*, b.booking_id as b_code, b.pickup_location, b.destination, v.name as vehicle_name, d.name as driver_name
        FROM trips t
        LEFT JOIN bookings b ON t.booking_id = b.id
        LEFT JOIN vehicles v ON t.vehicle_id = v.id
        LEFT JOIN drivers d ON t.driver_id = d.id
        ORDER BY t.id DESC
    `);
    res.render('admin/trips/index', { trips, page_title: 'Trips & Dispatch Tracking' });
});

router.get('/trips/:id', (req, res) => {
    const trip = getOne("SELECT * FROM trips WHERE id = ?", [req.params.id]);
    if (!trip) return res.redirect('/admin/trips');
    trip.booking = getOne("SELECT * FROM bookings WHERE id = ?", [trip.booking_id]);
    trip.vehicle = getOne("SELECT * FROM vehicles WHERE id = ?", [trip.vehicle_id]);
    trip.driver = getOne("SELECT * FROM drivers WHERE id = ?", [trip.driver_id]);

    res.render('admin/trips/show', { trip, page_title: `Trip #${trip.id}` });
});

router.post('/trips/:id/close', (req, res) => {
    const { end_odometer } = req.body;
    const trip = getOne("SELECT * FROM trips WHERE id = ?", [req.params.id]);
    if (!trip) return res.redirect('/admin/trips');

    run(`
        UPDATE trips SET
            end_odometer = ?, status = 'Completed', trip_status = 'Closed',
            completed_at = datetime('now'), updated_at = datetime('now')
        WHERE id = ?
    `, [end_odometer ? parseFloat(end_odometer) : null, trip.id]);

    BookingService.updateStatus(trip.booking_id, 'Completed', 'Trip marked completed by dispatch', req.user.id);
    res.setFlash('success', 'Trip successfully closed and vehicles released to available status.');
    res.redirect(`/admin/trips/${trip.id}`);
});

// 4. Vehicles
router.get('/vehicles', (req, res) => {
    const vehicles = getAll("SELECT * FROM vehicles ORDER BY id DESC");
    for (const veh of vehicles) {
        veh.bookings = () => {
            const bList = getAll("SELECT * FROM bookings WHERE vehicle_id = ?", [veh.id]);
            bList.whereIn = (col, vals) => {
                const filtered = bList.filter(b => vals.includes(b[col]));
                filtered.latest = () => filtered.sort((a, b) => b.id - a.id);
                filtered.first = () => filtered[0] || null;
                return filtered;
            };
            bList.latest = () => bList.sort((a, b) => b.id - a.id);
            bList.first = () => bList[0] || null;
            return bList;
        };
        veh.getExpiringDocuments = () => ({});
    }
    const categories = getAll("SELECT * FROM vehicle_categories WHERE is_active = 1 ORDER BY sort_order ASC");
    res.render('admin/vehicles/index', { vehicles, categories, page_title: 'Fleet Vehicles' });
});

router.get('/vehicles/create', (req, res) => {
    const categories = getAll("SELECT * FROM vehicle_categories WHERE is_active = 1 ORDER BY sort_order ASC");
    res.render('admin/vehicles/create', { categories, page_title: 'Add Fleet Vehicle' });
});

router.post('/vehicles', (req, res) => {
    const { name, registration_number, vehicle_type, seating_capacity, fuel_type, per_km_rate, status } = req.body;
    run(`
        INSERT INTO vehicles (name, registration_number, vehicle_type, seating_capacity, fuel_type, per_km_rate, status, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, datetime('now'), datetime('now'))
    `, [name, registration_number, vehicle_type || 'Sedan', seating_capacity || 4, fuel_type || 'Diesel', per_km_rate || null, status || 'Available']);

    res.setFlash('success', 'Vehicle added to fleet.');
    res.redirect('/admin/vehicles');
});

// 5. Drivers
router.get('/drivers', (req, res) => {
    const drivers = getAll("SELECT * FROM drivers ORDER BY id DESC");
    const stats = {
        total: getOne("SELECT count(*) as c FROM drivers")?.c || 0,
        active: getOne("SELECT count(*) as c FROM drivers WHERE status != 'Inactive'")?.c || 0,
        available_unassigned: getOne("SELECT count(*) as c FROM drivers WHERE status = 'Available'")?.c || 0,
        assigned: getOne("SELECT count(*) as c FROM drivers WHERE status IN ('Assigned', 'On Trip')")?.c || 0
    };
    res.render('admin/drivers/index', { drivers, stats, page_title: 'Drivers & Chauffeurs' });
});

router.get('/drivers/create', (req, res) => {
    const vehicles = getAll("SELECT * FROM vehicles WHERE status != 'Inactive'");
    res.render('admin/drivers/create', { vehicles, page_title: 'Register Driver' });
});

router.post('/drivers', (req, res) => {
    const { name, mobile, license_number, license_expiry, status } = req.body;
    run(`
        INSERT INTO drivers (name, mobile, license_number, license_expiry, status, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, datetime('now'), datetime('now'))
    `, [name, mobile, license_number, license_expiry || null, status || 'Available']);

    res.setFlash('success', 'Driver registered successfully.');
    res.redirect('/admin/drivers');
});

// 6. Rates Matrix
router.get('/rates', (req, res) => {
    const routes = getAll("SELECT * FROM rate_routes ORDER BY category ASC, origin ASC");
    for (const r of routes) {
        r.vehiclePrices = getAll("SELECT * FROM rate_vehicle_prices WHERE rate_route_id = ?", [r.id]);
    }
    const categories = {
        'airport': 'Quick Airport Booking',
        'outstation': 'Outstation Route',
        'round_trip': 'Round Trip Per KM',
        'local_8h_80km': 'Local Rental 8 Hours / 80 KM',
        'local_4h_40km': 'Local Rental 4 Hours / 40 KM'
    };
    const vehicleCategories = getAll("SELECT * FROM vehicle_categories WHERE is_active = 1 ORDER BY sort_order ASC");
    res.render('admin/rates/index', { routes, categories, vehicleCategories, page_title: 'Rate Configuration' });
});

// 7. Customers & Partners
router.get('/customers', (req, res) => {
    const search = req.query.search ? req.query.search.trim() : '';
    const typeFilter = req.query.type || 'all';

    let sql = `
        SELECT u.*, 
               c.customer_type, c.company_name, c.contact_person, c.gstin, c.billing_address, 
               c.partner_name, c.partner_commission, c.alternate_phone, c.address, c.city, c.state, c.pincode, c.notes,
               (SELECT count(*) FROM bookings WHERE customer_id = u.id) as booking_count
        FROM users u
        LEFT JOIN customers c ON u.id = c.user_id
        WHERE u.role = 'customer'
    `;
    const params = [];

    if (search) {
        sql += ` AND (
            u.name LIKE ? OR 
            u.phone LIKE ? OR 
            u.email LIKE ? OR 
            c.company_name LIKE ? OR 
            c.partner_name LIKE ? OR 
            c.gstin LIKE ? OR 
            c.city LIKE ?
        )`;
        const s = `%${search}%`;
        params.push(s, s, s, s, s, s, s);
    }

    if (typeFilter !== 'all') {
        if (typeFilter === 'corporate') {
            sql += ` AND c.customer_type = 'corporate'`;
        } else if (typeFilter === 'travel_partner') {
            sql += ` AND c.customer_type = 'travel_partner'`;
        } else if (typeFilter === 'individual') {
            sql += ` AND (c.customer_type = 'individual' OR c.customer_type IS NULL)`;
        }
    }

    sql += ` ORDER BY u.id DESC`;

    const rawCustomers = getAll(sql, params);
    
    const customers = rawCustomers.map(c => ({
        ...c,
        customer: {
            city: c.city,
            state: c.state,
            pincode: c.pincode,
            address: c.address,
            alternate_phone: c.alternate_phone,
            customer_type: c.customer_type || 'individual',
            company_name: c.company_name,
            contact_person: c.contact_person,
            gstin: c.gstin,
            billing_address: c.billing_address,
            partner_name: c.partner_name,
            partner_commission: c.partner_commission,
            notes: c.notes
        },
        bookings: {
            count: () => c.booking_count || 0
        }
    }));

    const stats = {
        total: getOne("SELECT count(*) as c FROM users WHERE role = 'customer'")?.c || 0,
        individual: getOne("SELECT count(*) as c FROM customers WHERE customer_type = 'individual' OR customer_type IS NULL")?.c || 0,
        corporate: getOne("SELECT count(*) as c FROM customers WHERE customer_type = 'corporate'")?.c || 0,
        travel_partner: getOne("SELECT count(*) as c FROM customers WHERE customer_type = 'travel_partner'")?.c || 0
    };

    res.render('admin/customers/index', {
        customers,
        stats,
        currentType: typeFilter,
        searchQuery: search,
        page_title: 'Customers & Partners Directory'
    });
});

function parseCustomerCsv(bufferOrString) {
    const text = (typeof bufferOrString === 'string' ? bufferOrString : bufferOrString.toString('utf-8')).replace(/^\uFEFF/, '').trim();
    if (!text) return [];

    const lines = [];
    let currentLine = [];
    let currentField = '';
    let inQuotes = false;

    for (let i = 0; i < text.length; i++) {
        const char = text[i];
        const nextChar = text[i + 1];

        if (inQuotes) {
            if (char === '"') {
                if (nextChar === '"') {
                    currentField += '"';
                    i++;
                } else {
                    inQuotes = false;
                }
            } else {
                currentField += char;
            }
        } else {
            if (char === '"') {
                inQuotes = true;
            } else if (char === ',') {
                currentLine.push(currentField.trim());
                currentField = '';
            } else if (char === '\r') {
                // Ignore CR
            } else if (char === '\n') {
                currentLine.push(currentField.trim());
                if (currentLine.some(f => f.length > 0)) {
                    lines.push(currentLine);
                }
                currentLine = [];
                currentField = '';
            } else {
                currentField += char;
            }
        }
    }

    if (currentField.length > 0 || currentLine.length > 0) {
        currentLine.push(currentField.trim());
        if (currentLine.some(f => f.length > 0)) {
            lines.push(currentLine);
        }
    }

    if (lines.length < 2) return [];

    const rawHeaders = lines[0].map(h => h.toLowerCase().replace(/[^a-z0-9]/g, '_').replace(/_+/g, '_').replace(/^_|_$/g, ''));
    
    const headerMap = {
        'customer_name': 'name',
        'full_name': 'name',
        'client_name': 'name',
        'name': 'name',
        'mobile_number': 'phone',
        'mobile': 'phone',
        'phone_number': 'phone',
        'contact': 'phone',
        'contact_number': 'phone',
        'phone': 'phone',
        'email_id': 'email',
        'email_address': 'email',
        'email': 'email',
        'type': 'customer_type',
        'customer_type': 'customer_type',
        'category': 'customer_type',
        'company': 'company_name',
        'company_name': 'company_name',
        'firm_name': 'company_name',
        'contact_person': 'contact_person',
        'gstin': 'gstin',
        'gst_number': 'gstin',
        'gst': 'gstin',
        'city': 'city',
        'address': 'address',
        'billing_address': 'billing_address',
        'state': 'state',
        'pincode': 'pincode',
        'pin_code': 'pincode',
        'pin': 'pincode',
        'partner_name': 'partner_name',
        'agency_name': 'partner_name',
        'travel_partner': 'partner_name',
        'commission': 'partner_commission',
        'partner_commission': 'partner_commission',
        'notes': 'notes',
        'remarks': 'notes'
    };

    const headers = rawHeaders.map(h => headerMap[h] || h);

    const rows = [];
    for (let r = 1; r < lines.length; r++) {
        const line = lines[r];
        const obj = {};
        for (let c = 0; c < headers.length; c++) {
            obj[headers[c]] = (line[c] !== undefined ? line[c] : '').trim();
        }
        rows.push({ rowNumber: r + 1, data: obj });
    }

    return rows;
}

// Download Sample CSV for Bulk Upload
router.get('/customers/sample-csv', (req, res) => {
    const csvContent = [
        'Name,Mobile,Email,Customer_Type,Company_Name,Contact_Person,GSTIN,City,Address,State,Pincode,Partner_Name,Partner_Commission,Notes',
        'Rajesh Sharma,9876543210,rajesh.sharma@example.com,individual,,,Bilaspur,"Civil Lines, Bilaspur",Chhattisgarh,495001,,,Regular individual passenger',
        'Tata Projects Ltd,9823456789,transport@tataprojects.com,corporate,Tata Projects Ltd,Mr. Anil Verma,22AAAAA0000A1Z5,Raipur,"Plot 45, Industrial Area",Chhattisgarh,492001,,,Monthly corporate billing client',
        'Sharma Travel Agency,9898765432,sharmatours@gmail.com,travel_partner,,,,Bilaspur,"Station Road",Chhattisgarh,495001,Sharma Travel Agency,10,10% commission travel partner'
    ].join('\r\n');

    res.setHeader('Content-Type', 'text/csv; charset=utf-8');
    res.setHeader('Content-Disposition', 'attachment; filename="sample_customers_vaishnavi_tours.csv"');
    res.send(csvContent);
});

// Bulk Upload Customers Handler
router.post('/customers/bulk-upload', upload.single('csv_file'), (req, res) => {
    try {
        if (!req.file || !req.file.buffer) {
            res.setFlash('error', 'Please select a valid CSV file to upload.');
            return res.redirect('/admin/customers');
        }

        const duplicateAction = req.body.duplicate_action || 'update'; // 'update' or 'skip'
        const defaultType = req.body.default_type || 'auto';
        const parsedRows = parseCustomerCsv(req.file.buffer);

        if (!parsedRows || parsedRows.length === 0) {
            res.setFlash('error', 'The uploaded CSV file is empty or missing data rows.');
            return res.redirect('/admin/customers');
        }

        let insertedCount = 0;
        let updatedCount = 0;
        let skippedCount = 0;
        const errors = [];
        const defaultPasswordHash = bcryptjs.hashSync('Password@123', 10);

        for (const { rowNumber, data } of parsedRows) {
            try {
                const rawName = (data.name || data.company_name || data.partner_name || '').trim();
                const rawPhone = (data.phone || '').trim().replace(/\D/g, '');

                if (!rawName) {
                    errors.push(`Row ${rowNumber}: Customer/Company name is required.`);
                    continue;
                }

                if (!rawPhone || rawPhone.length < 10) {
                    errors.push(`Row ${rowNumber} (${rawName}): Valid 10-digit mobile number is required.`);
                    continue;
                }

                const cleanPhone = rawPhone.slice(-10);
                let cleanEmail = (data.email || '').trim().toLowerCase();
                if (!cleanEmail || !cleanEmail.includes('@')) {
                    cleanEmail = `${cleanPhone}@vaishnavitours.in`;
                }

                let custType = (data.customer_type || '').trim().toLowerCase();
                if (custType.includes('corp') || custType === 'b2b' || data.company_name || data.gstin) {
                    custType = 'corporate';
                } else if (custType.includes('partner') || custType.includes('travel') || custType.includes('agent') || data.partner_name) {
                    custType = 'travel_partner';
                } else if (defaultType !== 'auto') {
                    custType = defaultType;
                } else {
                    custType = 'individual';
                }

                const companyName = data.company_name || (custType === 'corporate' ? rawName : null);
                const partnerName = data.partner_name || (custType === 'travel_partner' ? rawName : null);
                const contactPerson = data.contact_person || rawName;
                const gstin = data.gstin ? data.gstin.trim().toUpperCase() : null;
                const city = data.city || 'Bilaspur';
                const state = data.state || 'Chhattisgarh';
                const pincode = data.pincode || '495001';
                const address = data.address || data.billing_address || null;
                const billingAddress = data.billing_address || data.address || null;
                const commission = data.partner_commission ? parseFloat(data.partner_commission) || 0 : null;
                const notes = data.notes || null;

                let user = getOne("SELECT * FROM users WHERE phone = ? OR email = ?", [cleanPhone, cleanEmail]);

                if (user) {
                    if (duplicateAction === 'skip') {
                        skippedCount++;
                        continue;
                    }

                    run("UPDATE users SET name = ?, updated_at = datetime('now') WHERE id = ?", [rawName, user.id]);

                    const existingCust = getOne("SELECT id FROM customers WHERE user_id = ?", [user.id]);
                    if (existingCust) {
                        run(`
                            UPDATE customers SET
                                customer_type = ?,
                                company_name = COALESCE(?, company_name),
                                contact_person = COALESCE(?, contact_person),
                                gstin = COALESCE(?, gstin),
                                billing_address = COALESCE(?, billing_address),
                                partner_name = COALESCE(?, partner_name),
                                partner_commission = COALESCE(?, partner_commission),
                                address = COALESCE(?, address),
                                city = COALESCE(?, city),
                                state = COALESCE(?, state),
                                pincode = COALESCE(?, pincode),
                                notes = COALESCE(?, notes),
                                updated_at = datetime('now')
                            WHERE id = ?
                        `, [
                            custType, companyName, contactPerson, gstin, billingAddress,
                            partnerName, commission, address, city, state, pincode, notes,
                            existingCust.id
                        ]);
                    } else {
                        run(`
                            INSERT INTO customers (
                                user_id, customer_type, company_name, contact_person, gstin,
                                billing_address, partner_name, partner_commission,
                                address, city, state, pincode, notes, created_at, updated_at
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'), datetime('now'))
                        `, [
                            user.id, custType, companyName, contactPerson, gstin,
                            billingAddress, partnerName, commission,
                            address, city, state, pincode, notes
                        ]);
                    }
                    updatedCount++;
                } else {
                    const userRes = run(`
                        INSERT INTO users (
                            name, email, phone, role, is_active, password, created_at, updated_at
                        ) VALUES (?, ?, ?, 'customer', 1, ?, datetime('now'), datetime('now'))
                    `, [rawName, cleanEmail, cleanPhone, defaultPasswordHash]);

                    run(`
                        INSERT INTO customers (
                            user_id, customer_type, company_name, contact_person, gstin,
                            billing_address, partner_name, partner_commission,
                            address, city, state, pincode, notes, created_at, updated_at
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'), datetime('now'))
                    `, [
                        userRes.lastInsertRowid, custType, companyName, contactPerson, gstin,
                        billingAddress, partnerName, commission,
                        address, city, state, pincode, notes
                    ]);
                    insertedCount++;
                }
            } catch (rowErr) {
                errors.push(`Row ${rowNumber}: ${rowErr.message}`);
            }
        }

        let flashMsg = `Bulk upload finished: ${insertedCount} new customer(s) added, ${updatedCount} updated.`;
        if (skippedCount > 0) flashMsg += ` ${skippedCount} duplicate(s) skipped.`;
        if (errors.length > 0) {
            flashMsg += ` (${errors.length} rows skipped with errors: ${errors.slice(0, 3).join('; ')}${errors.length > 3 ? '...' : ''})`;
            res.setFlash(insertedCount > 0 || updatedCount > 0 ? 'success' : 'error', flashMsg);
        } else {
            res.setFlash('success', flashMsg);
        }

        res.redirect('/admin/customers');
    } catch (err) {
        res.setFlash('error', 'Bulk upload error: ' + err.message);
        res.redirect('/admin/customers');
    }
});

router.get('/customers/create', (req, res) => {
    res.render('admin/customers/create', {
        initialType: req.query.type || 'individual',
        page_title: 'Add New Customer / Partner'
    });
});

router.post('/customers', (req, res) => {
    try {
        const {
            customer_type = 'individual',
            name,
            phone,
            email,
            alternate_phone,
            address,
            city = 'Bilaspur',
            state = 'Chhattisgarh',
            pincode = '495001',
            notes,
            // Corporate
            company_name,
            corporate_contact_person,
            corporate_phone,
            corporate_email,
            corporate_gstin,
            corporate_billing_address,
            corporate_payment_terms,
            // Travel Partner
            partner_name,
            partner_contact_person,
            partner_phone,
            partner_email,
            partner_gstin,
            partner_address,
            partner_commission
        } = req.body;

        let finalName = '';
        let finalPhone = '';
        let finalEmail = '';

        if (customer_type === 'corporate') {
            finalName = (company_name || corporate_contact_person || 'Corporate Client').trim();
            finalPhone = (corporate_phone || phone || '').trim().replace(/\D/g, '').slice(-10);
            finalEmail = (corporate_email || email || '').trim();
        } else if (customer_type === 'travel_partner') {
            finalName = (partner_name || partner_contact_person || 'Travel Partner').trim();
            finalPhone = (partner_phone || phone || '').trim().replace(/\D/g, '').slice(-10);
            finalEmail = (partner_email || email || '').trim();
        } else {
            finalName = (name || '').trim();
            finalPhone = (phone || '').trim().replace(/\D/g, '').slice(-10);
            finalEmail = (email || '').trim();
        }

        if (!finalName) {
            throw new Error(customer_type === 'corporate' ? 'Company name is required.' : (customer_type === 'travel_partner' ? 'Agency / Partner name is required.' : 'Customer name is required.'));
        }
        if (!finalPhone || finalPhone.length < 10) {
            throw new Error('A valid 10-digit mobile number is required.');
        }

        if (!finalEmail) {
            finalEmail = `${finalPhone}@vaishnavitours.in`;
        }

        const effectiveGstin = customer_type === 'corporate' ? (corporate_gstin || '').trim() : (partner_gstin || '').trim();
        const effectiveAddress = customer_type === 'corporate' ? (corporate_billing_address || address || '').trim() : (customer_type === 'travel_partner' ? (partner_address || address || '').trim() : (address || '').trim());
        const effectiveContactPerson = customer_type === 'corporate' ? (corporate_contact_person || name || '').trim() : (customer_type === 'travel_partner' ? (partner_contact_person || name || '').trim() : (name || '').trim());

        let user = getOne("SELECT * FROM users WHERE phone = ? OR email = ?", [finalPhone, finalEmail]);
        const defaultPasswordHash = bcryptjs.hashSync('Password@123', 10);

        if (!user) {
            const userRes = run(`
                INSERT INTO users (
                    name, email, phone, role, is_active, password, created_at, updated_at
                ) VALUES (?, ?, ?, 'customer', 1, ?, datetime('now'), datetime('now'))
            `, [finalName, finalEmail, finalPhone, defaultPasswordHash]);
            user = getOne("SELECT * FROM users WHERE id = ?", [userRes.lastInsertRowid]);
        } else {
            run("UPDATE users SET name = ?, updated_at = datetime('now') WHERE id = ?", [finalName, user.id]);
        }

        const existingCust = getOne("SELECT * FROM customers WHERE user_id = ?", [user.id]);
        if (existingCust) {
            run(`
                UPDATE customers SET 
                    customer_type = ?,
                    company_name = ?,
                    contact_person = ?,
                    gstin = ?,
                    billing_address = ?,
                    partner_name = ?,
                    partner_commission = ?,
                    alternate_phone = ?,
                    address = ?,
                    city = ?,
                    state = ?,
                    pincode = ?,
                    notes = ?,
                    updated_at = datetime('now')
                WHERE id = ?
            `, [
                customer_type,
                customer_type === 'corporate' ? (company_name || finalName) : null,
                effectiveContactPerson,
                effectiveGstin || null,
                effectiveAddress || null,
                customer_type === 'travel_partner' ? (partner_name || finalName) : null,
                customer_type === 'travel_partner' ? partner_commission : null,
                alternate_phone || null,
                effectiveAddress || null,
                city || 'Bilaspur',
                state || 'Chhattisgarh',
                pincode || '495001',
                notes || (customer_type === 'corporate' ? corporate_payment_terms : null),
                existingCust.id
            ]);
        } else {
            run(`
                INSERT INTO customers (
                    user_id, customer_type, company_name, contact_person, gstin,
                    billing_address, partner_name, partner_commission, alternate_phone,
                    address, city, state, pincode, notes, created_at, updated_at
                ) VALUES (
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, datetime('now'), datetime('now')
                )
            `, [
                user.id,
                customer_type,
                customer_type === 'corporate' ? (company_name || finalName) : null,
                effectiveContactPerson,
                effectiveGstin || null,
                effectiveAddress || null,
                customer_type === 'travel_partner' ? (partner_name || finalName) : null,
                customer_type === 'travel_partner' ? partner_commission : null,
                alternate_phone || null,
                effectiveAddress || null,
                city || 'Bilaspur',
                state || 'Chhattisgarh',
                pincode || '495001',
                notes || (customer_type === 'corporate' ? corporate_payment_terms : null)
            ]);
        }

        const typeLabel = customer_type === 'corporate' ? 'Corporate client' : (customer_type === 'travel_partner' ? 'Travel partner' : 'Customer');
        res.setFlash('success', `${typeLabel} "${finalName}" saved successfully.`);
        res.redirect(`/admin/customers/${user.id}`);
    } catch (err) {
        console.error('Error adding customer:', err);
        res.setFlash('error', err.message);
        res.redirect('/admin/customers');
    }
});

router.get('/customers/:id', (req, res) => {
    const user = getOne("SELECT * FROM users WHERE id = ?", [req.params.id]);
    if (!user) {
        res.setFlash('error', 'Customer not found.');
        return res.redirect('/admin/customers');
    }

    const customer = getOne("SELECT * FROM customers WHERE user_id = ?", [user.id]) || {};
    const rawBookings = getAll("SELECT * FROM bookings WHERE customer_id = ? ORDER BY id DESC", [user.id]);
    const rawPayments = getAll("SELECT * FROM payments WHERE customer_id = ? ORDER BY id DESC", [user.id]);

    const totalBookings = rawBookings.length;
    const completedTrips = rawBookings.filter(b => b.booking_status === 'Completed').length;
    const totalSpent = rawPayments.filter(p => p.status === 'Success').reduce((acc, p) => acc + (parseFloat(p.amount) || 0), 0);

    user.customer = customer;
    user.bookings = rawBookings;
    user.payments = rawPayments;

    res.render('admin/customers/show', {
        user,
        customer,
        totalBookings,
        completedTrips,
        totalSpent,
        page_title: `Customer: ${user.name}`
    });
});

// 8. Invoices
router.get('/invoices', (req, res) => {
    const invoices = getAll(`
        SELECT i.*, b.booking_id as b_code, u.name as customer_name
        FROM invoices i
        LEFT JOIN bookings b ON i.booking_id = b.id
        LEFT JOIN users u ON i.customer_id = u.id
        ORDER BY i.id DESC
    `);
    const stats = {
        total_invoices: getOne("SELECT count(*) as c FROM invoices")?.c || 0,
        total_taxable: parseFloat(getOne("SELECT COALESCE(sum(taxable_amount), 0) as s FROM invoices")?.s || 0),
        total_gst: parseFloat(getOne("SELECT COALESCE(sum(gst_amount), 0) as s FROM invoices")?.s || 0),
        total_billed: parseFloat(getOne("SELECT COALESCE(sum(total_amount), 0) as s FROM invoices")?.s || 0)
    };
    res.render('admin/invoices/index', { invoices, stats, page_title: 'Tax Invoices' });
});

router.get('/invoices/:id', (req, res) => {
    const invoice = getOne("SELECT * FROM invoices WHERE id = ?", [req.params.id]);
    if (!invoice) return res.redirect('/admin/invoices');

    const booking = getOne("SELECT * FROM bookings WHERE id = ?", [invoice.booking_id]);
    const document = InvoiceDocumentService.forBooking(booking);
    const backUrl = '/admin/invoices';

    res.render('invoices/printable', {
        invoice,
        booking,
        document,
        backUrl,
        page_title: `Tax Invoice #${invoice.invoice_number}`
    });
});

// 9. Payments Management
router.get('/payments', (req, res) => {
    const {
        search,
        booking_id,
        customer_name,
        mobile,
        date_from,
        date_to,
        booking_category,
        method,
        status,
        booking_status,
        razorpay_payment_id,
        razorpay_order_id
    } = req.query;

    let sql = `
        SELECT p.*,
               b.booking_id as b_code,
               b.booking_status,
               b.booking_category as b_cat,
               b.pickup_location,
               b.destination,
               b.total_amount as booking_total,
               b.paid_amount as booking_paid,
               b.balance_amount as booking_balance,
               u.name as customer_name,
               u.phone as customer_mobile,
               u.email as customer_email,
               inv.invoice_number
        FROM payments p
        LEFT JOIN bookings b ON p.booking_id = b.id
        LEFT JOIN users u ON p.customer_id = u.id
        LEFT JOIN invoices inv ON inv.booking_id = b.id
        WHERE 1=1
    `;
    const params = [];

    if (search) {
        const s = `%${search.trim()}%`;
        sql += ` AND (
            p.payment_id LIKE ? OR 
            p.transaction_id LIKE ? OR 
            p.razorpay_payment_id LIKE ? OR 
            p.razorpay_order_id LIKE ? OR 
            b.booking_id LIKE ? OR 
            u.name LIKE ? OR 
            u.phone LIKE ? OR
            inv.invoice_number LIKE ?
        )`;
        params.push(s, s, s, s, s, s, s, s);
    }

    if (booking_id) {
        sql += ` AND (b.booking_id LIKE ? OR p.booking_id = ?)`;
        params.push(`%${booking_id.trim()}%`, booking_id.trim());
    }

    if (customer_name) {
        sql += ` AND u.name LIKE ?`;
        params.push(`%${customer_name.trim()}%`);
    }

    if (mobile) {
        sql += ` AND u.phone LIKE ?`;
        params.push(`%${mobile.trim()}%`);
    }

    if (date_from) {
        sql += ` AND date(p.created_at) >= date(?)`;
        params.push(date_from);
    }

    if (date_to) {
        sql += ` AND date(p.created_at) <= date(?)`;
        params.push(date_to);
    }

    if (booking_category) {
        sql += ` AND (b.booking_category = ? OR p.booking_category = ?)`;
        params.push(booking_category, booking_category);
    }

    if (method) {
        sql += ` AND p.payment_method = ?`;
        params.push(method);
    }

    if (status) {
        sql += ` AND p.status = ?`;
        params.push(status);
    }

    if (booking_status) {
        sql += ` AND b.booking_status = ?`;
        params.push(booking_status);
    }

    if (razorpay_payment_id) {
        sql += ` AND p.razorpay_payment_id LIKE ?`;
        params.push(`%${razorpay_payment_id.trim()}%`);
    }

    if (razorpay_order_id) {
        sql += ` AND p.razorpay_order_id LIKE ?`;
        params.push(`%${razorpay_order_id.trim()}%`);
    }

    sql += ` ORDER BY p.id DESC`;

    const payments = getAll(sql, params);

    // Parse method_details JSON
    for (const p of payments) {
        if (p.method_details && typeof p.method_details === 'string') {
            try { p.parsed_method_details = JSON.parse(p.method_details); } catch (e) {}
        }
    }

    // Key Performance Metrics
    const totalCollected = parseFloat(getOne("SELECT COALESCE(sum(amount), 0) as s FROM payments WHERE status = 'Success'")?.s || 0);
    const razorpayCollected = parseFloat(getOne("SELECT COALESCE(sum(amount), 0) as s FROM payments WHERE gateway = 'Razorpay' AND status = 'Success'")?.s || 0);
    const totalRefunded = parseFloat(getOne("SELECT COALESCE(sum(ABS(amount)), 0) as s FROM payments WHERE status = 'Refunded' OR payment_method = 'Refund'")?.s || 0);
    const pendingTransactionsCount = getOne("SELECT count(*) as c FROM payments WHERE status IN ('Pending', 'Created', 'Authorized')")?.c || 0;

    const metrics = {
        total_collected: totalCollected,
        razorpay_collected: razorpayCollected,
        total_refunded: totalRefunded,
        pending_count: pendingTransactionsCount
    };

    res.render('admin/payments/index', {
        payments,
        metrics,
        totalCollected,
        filters: req.query,
        page_title: 'Payment Gateway & Transactions Ledger'
    });
});

// Admin Payment Details JSON / Drawer
router.get('/payments/:id/details', (req, res) => {
    const payment = getOne(`
        SELECT p.*,
               b.booking_id as b_code,
               b.booking_status,
               b.booking_category as b_cat,
               b.trip_type,
               b.pickup_location,
               b.destination,
               b.travel_date,
               b.travel_time,
               b.total_amount as booking_total,
               b.paid_amount as booking_paid,
               b.balance_amount as booking_balance,
               u.name as customer_name,
               u.phone as customer_mobile,
               u.email as customer_email,
               inv.invoice_number
        FROM payments p
        LEFT JOIN bookings b ON p.booking_id = b.id
        LEFT JOIN users u ON p.customer_id = u.id
        LEFT JOIN invoices inv ON inv.booking_id = b.id
        WHERE p.id = ? OR p.payment_id = ?
    `, [req.params.id, req.params.id]);

    if (!payment) {
        return res.status(404).json({ success: false, message: 'Payment record not found' });
    }

    if (payment.method_details && typeof payment.method_details === 'string') {
        try { payment.method_details = JSON.parse(payment.method_details); } catch (e) {}
    }

    if (payment.gateway_response && typeof payment.gateway_response === 'string') {
        try { payment.gateway_response = JSON.parse(payment.gateway_response); } catch (e) {}
    }

    res.json({ success: true, payment });
});

// 10. Expenses
router.get('/expenses', (req, res) => {
    const expenses = getAll(`
        SELECT e.*, b.booking_id as b_code, u.name as entered_by_name
        FROM expenses e
        LEFT JOIN bookings b ON e.booking_id = b.id
        LEFT JOIN users u ON e.entered_by = u.id
        ORDER BY e.id DESC
    `);
    const totalFuel = parseFloat(getOne("SELECT COALESCE(sum(amount), 0) as s FROM expenses WHERE category = 'Fuel'")?.s || 0);
    const totalToll = parseFloat(getOne("SELECT COALESCE(sum(amount), 0) as s FROM expenses WHERE category = 'Toll'")?.s || 0);
    const totalOther = parseFloat(getOne("SELECT COALESCE(sum(amount), 0) as s FROM expenses WHERE category NOT IN ('Fuel', 'Toll')")?.s || 0);
    const grandTotal = totalFuel + totalToll + totalOther;
    const totals = {
        total: grandTotal,
        fuel: totalFuel,
        toll: totalToll,
        other: totalOther
    };
    res.render('admin/expenses/index', { expenses, totals, totalFuel, totalToll, totalOther, grandTotal, page_title: 'Expenses Tracker' });
});

// 11. Enquiries
router.get('/enquiries', (req, res) => {
    const currentSource = req.query.source || 'website';
    const sourceCounts = {
        website: getOne("SELECT count(*) as c FROM enquiries WHERE source = 'website' OR source IS NULL")?.c || 0,
        manual: getOne("SELECT count(*) as c FROM enquiries WHERE source = 'manual'")?.c || 0,
        corporate: getOne("SELECT count(*) as c FROM enquiries WHERE source = 'corporate'")?.c || 0,
        third_party: getOne("SELECT count(*) as c FROM enquiries WHERE source = 'third_party'")?.c || 0,
        all: getOne("SELECT count(*) as c FROM enquiries")?.c || 0
    };
    const enquiries = getAll("SELECT * FROM enquiries ORDER BY id DESC");
    res.render('admin/enquiries/index', { enquiries, currentSource, sourceCounts, page_title: 'Customer Enquiries Inbox' });
});

// 12. Reports
router.get('/reports', (req, res) => {
    const categories = {
        'bookings': { title: 'Booking Reports', icon: '🚖', badge: 'Core Ops', description: 'Comprehensive booking logs, passenger trips, dispatch and fare status.', orientation: 'landscape' },
        'customers': { title: 'Customer Reports', icon: '👥', badge: 'CRM', description: 'Customer profiles, lifetime booking counts, billing history and outstanding balances.', orientation: 'landscape' },
        'vehicles': { title: 'Vehicle / Fleet Reports', icon: '🚗', badge: 'Fleet', description: 'Vehicle utilization, trip assignments, operational status and document expiries.', orientation: 'landscape' },
        'drivers': { title: 'Driver Reports', icon: '👨‍✈️', badge: 'Staff', description: 'Chauffeur rosters, trip history, cancellation records and driving licence expiries.', orientation: 'landscape' },
        'revenue': { title: 'Revenue & Collection Reports', icon: '💰', badge: 'Finance', description: 'Daily collections, payment method breakdown and revenue trends.', orientation: 'landscape' },
        'expenses': { title: 'Expenses & Maintenance Reports', icon: '🧾', badge: 'Finance', description: 'Fuel, toll and operational overhead costs across trips.', orientation: 'landscape' }
    };
    const currentCategory = req.query.category || 'bookings';
    const categoryMeta = categories[currentCategory] || categories['bookings'];
    const vehicles = getAll("SELECT * FROM vehicles ORDER BY name ASC");
    const drivers = getAll("SELECT * FROM drivers ORDER BY name ASC");
    const data = getAll(`
        SELECT b.*, u.name as customer_name, v.name as vehicle_name, d.name as driver_name
        FROM bookings b
        LEFT JOIN users u ON b.customer_id = u.id
        LEFT JOIN vehicles v ON b.vehicle_id = v.id
        LEFT JOIN drivers d ON b.driver_id = d.id
        ORDER BY b.id DESC LIMIT 50
    `);
    res.render('admin/reports/index', {
        categories,
        currentCategory,
        categoryMeta,
        data,
        vehicles,
        drivers,
        page_title: 'Reports & Business Analytics'
    });
});

// 11. Settings
router.get('/settings', (req, res) => {
    const settings = {};
    getAll("SELECT `key`, `value` FROM settings").forEach(s => settings[s.key] = s.value);
    res.render('admin/settings/index', { settings, page_title: 'System Settings' });
});

router.post('/settings', (req, res) => {
    for (const [key, value] of Object.entries(req.body)) {
        if (key.startsWith('_')) continue;
        const exists = getOne("SELECT id FROM settings WHERE key = ?", [key]);
        if (exists) {
            run("UPDATE settings SET value = ?, updated_at = datetime('now') WHERE key = ?", [value, key]);
        } else {
            run("INSERT INTO settings (key, value, created_at, updated_at) VALUES (?, ?, datetime('now'), datetime('now'))", [key, value]);
        }
    }
    res.setFlash('success', 'Settings updated successfully.');
    res.redirect('/admin/settings');
});

// 12. WhatsApp Scan & QR Status
router.get('/whatsapp/scan', (req, res) => {
    res.render('admin/whatsapp/scan', { page_title: 'WhatsApp Gateway & QR Link' });
});

router.get('/whatsapp/qr-status', (req, res) => {
    res.json({
        status: 'ready',
        phone: '9244784443',
        pushName: 'Vaishnavi Tours Dispatch'
    });
});

router.get('/whatsapp/logs', (req, res) => {
    const logs = getAll("SELECT * FROM whatsapp_logs ORDER BY id DESC LIMIT 50");
    const eventKeys = ['booking_confirmed', 'driver_assigned', 'payment_reminder', 'booking_cancelled'];
    res.render('admin/whatsapp/logs', { logs, eventKeys, page_title: 'WhatsApp Dispatch Logs' });
});

router.get('/whatsapp/templates', (req, res) => {
    const rawTemplates = getAll("SELECT * FROM whatsapp_templates ORDER BY id ASC");
    const templates = rawTemplates.map(t => {
        let vars = [];
        try {
            vars = typeof t.available_variables === 'string' ? JSON.parse(t.available_variables || '[]') : (t.available_variables || []);
        } catch (e) {
            vars = [];
        }
        return { ...t, available_variables: Array.isArray(vars) ? vars : [] };
    });
    res.render('admin/whatsapp/templates', { templates, page_title: 'WhatsApp Notification Templates' });
});

// Vehicle Categories Management
router.get('/vehicle-categories', (req, res) => {
    const categories = getAll("SELECT * FROM vehicle_categories ORDER BY sort_order ASC").map(cat => ({
        ...cat,
        models: getAll("SELECT * FROM vehicle_models WHERE vehicle_category_id = ? ORDER BY sort_order ASC", [cat.id]),
        vehicles: getAll("SELECT * FROM vehicles WHERE vehicle_category_id = ? OR vehicle_type = ?", [cat.id, cat.name])
    }));
    const stats = {
        total_categories: categories.length,
        active_categories: categories.filter(c => c.is_active).length,
        total_models: getAll("SELECT count(*) as c FROM vehicle_models")?.c || 0,
        active_models: getAll("SELECT count(*) as c FROM vehicle_models WHERE is_active = 1")?.c || 0
    };

    res.render('admin/vehicle-categories/index', {
        categories,
        stats,
        page_title: 'Vehicle Categories & Models'
    });
});

// 13. Notifications
router.get('/notifications', (req, res) => {
    const notifications = getAll("SELECT * FROM notifications ORDER BY id DESC LIMIT 100");
    const unreadCount = getOne("SELECT count(*) as c FROM notifications WHERE is_read = 0")?.c || 0;
    res.render('admin/notifications/index', {
        notifications,
        unreadCount,
        page_title: 'Operations Alerts & Notifications'
    });
});

router.post('/notifications/mark-read', (req, res) => {
    run("UPDATE notifications SET is_read = 1 WHERE is_read = 0");
    res.setFlash('success', 'All notifications marked as read.');
    res.redirect('/admin/notifications');
});

router.post('/notifications/:id/read', (req, res) => {
    const notif = getOne("SELECT * FROM notifications WHERE id = ?", [req.params.id]);
    if (notif) {
        run("UPDATE notifications SET is_read = 1, updated_at = datetime('now') WHERE id = ?", [notif.id]);
        if (notif.action_url) {
            return res.redirect(notif.action_url);
        }
    }
    res.setFlash('success', 'Notification marked as read.');
    res.redirect('/admin/notifications');
});

export default router;
