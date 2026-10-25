import express from 'express';
import db, { getOne, getAll, run } from '../db.js';
import config from '../config.js';
import BookingService from '../services/BookingService.js';
import RateQuoteService from '../services/RateQuoteService.js';
import WhatsappService from '../services/WhatsappService.js';

const router = express.Router();

export function getBookingData() {
    const vehicles = getAll("SELECT * FROM vehicles WHERE status != 'Inactive'");
    const fleetVehicles = getAll("SELECT * FROM vehicles WHERE status = 'Available'");
    const routes = getAll("SELECT * FROM rate_routes WHERE active = 1");

    for (const r of routes) {
        r.vehiclePrices = getAll("SELECT * FROM rate_vehicle_prices WHERE rate_route_id = ?", [r.id]);
    }

    const savedLocations = getAll("SELECT name FROM pickup_locations WHERE is_active = 1").map(l => l.name);
    const routeLocs = [];
    routes.forEach(r => {
        if (r.origin) routeLocs.push(r.origin);
        if (r.destination) routeLocs.push(r.destination);
    });

    const locations = Array.from(new Set([...routeLocs, ...savedLocations])).filter(Boolean).sort();
    const tourPackages = getAll("SELECT * FROM tour_packages WHERE is_active = 1");
    const activeVehicleCategories = getAll("SELECT * FROM vehicle_categories WHERE is_active = 1 ORDER BY sort_order ASC");

    for (const cat of activeVehicleCategories) {
        cat.activeModels = getAll("SELECT * FROM vehicle_models WHERE vehicle_category_id = ? AND is_active = 1 ORDER BY sort_order ASC", [cat.id]);
    }

    const dbCategoryNames = activeVehicleCategories.map(c => c.name);
    const rateCategoryNames = getAll("SELECT DISTINCT vehicle_category FROM rate_vehicle_prices").map(p => p.vehicle_category);
    const vehicleCategories = Array.from(new Set([...dbCategoryNames, ...rateCategoryNames]));

    // Precompute rate options
    const rateOptions = {};
    for (const route of routes) {
        const catNamesToQuote = Array.from(new Set([...dbCategoryNames, ...route.vehiclePrices.map(p => p.vehicle_category)]));
        const directions = [[route.origin, route.destination]];
        if (route.return_same_rate && route.origin && route.destination) {
            directions.push([route.destination, route.origin]);
        }
        for (const [orig, dest] of directions) {
            for (const catName of catNamesToQuote) {
                const q = RateQuoteService.quote(route.category, orig || '', dest || '', catName);
                if (q) {
                    rateOptions[`${route.category}|${orig || ''}|${dest || ''}|${catName}`] = q;
                }
            }
        }
    }

    return {
        vehicles,
        fleetVehicles,
        routes,
        locations,
        tourPackages,
        activeVehicleCategories,
        vehicleCategories,
        rateOptions
    };
}

// 1. Home
router.get('/', (req, res) => {
    const bookingData = getBookingData();
    const feedbacks = getAll("SELECT * FROM feedback WHERE is_public = 1 ORDER BY created_at DESC LIMIT 6");

    const tripCountRow = getOne("SELECT count(*) as count FROM bookings WHERE booking_status = 'Completed'");
    const stats = {
        trips_completed: tripCountRow?.count || 0,
        active_vehicles: bookingData.vehicles.length || 12,
        satisfaction_rate: '99.4%',
        support_hours: '24/7'
    };

    res.render('public/home', {
        ...bookingData,
        feedbacks,
        stats,
        pageTitle: config.seo.default_title
    });
});

// 2. Booking Intake Form
router.get('/booking', (req, res) => {
    const bookingData = getBookingData();
    res.render('public/booking', {
        ...bookingData,
        pageTitle: 'Book a Taxi Online | Vaishnavi Tours'
    });
});

// 3. Store Booking
router.post('/booking', (req, res) => {
    try {
        const body = { ...req.body };

        // Normalize aliases
        if (body.name && !body.customer_name) body.customer_name = body.name;
        if (body.phone && !body.mobile) body.mobile = body.phone;
        if (body.pickup_date && !body.travel_date) body.travel_date = body.pickup_date;
        if (body.pickup_time && !body.travel_time) body.travel_time = body.pickup_time;

        if (body.mobile) {
            body.mobile = String(body.mobile).replace(/[^0-9]/g, '');
            if (body.mobile.length === 12 && body.mobile.startsWith('91')) body.mobile = body.mobile.substring(2);
            if (body.mobile.length === 11 && body.mobile.startsWith('0')) body.mobile = body.mobile.substring(1);
        }

        if (body.pickup_location === 'Other' && body.pickup_other) {
            body.pickup_location = body.pickup_other.trim();
        }
        if (body.destination === 'Other' && body.destination_other) {
            body.destination = body.destination_other.trim();
        }

        if (['local_8h_80km', 'local_4h_40km'].includes(body.rate_category) && !body.destination) {
            body.destination = `${body.pickup_location || 'Bilaspur'} (Local)`;
        }

        const newBooking = BookingService.createBooking(body, req.user);

        // WhatsApp notification trigger
        WhatsappService.sendEvent('booking_confirmed', {
            booking_id: newBooking.booking_id,
            customer_name: body.customer_name || 'Customer',
            pickup_location: newBooking.pickup_location,
            destination: newBooking.destination,
            travel_date: newBooking.travel_date,
            travel_time: newBooking.travel_time,
            total_amount: newBooking.total_amount
        }, body.mobile, newBooking.id).catch(e => console.warn('WhatsApp error:', e));

        res.setFlash('success', `Booking request received! Your Booking ID is #${newBooking.booking_id}.`);
        res.redirect(`/booking-success/${newBooking.id}`);
    } catch (err) {
        console.error('Booking intake error:', err);
        res.setFlash('error', err.message || 'Unable to process booking request. Please check inputs.');
        res.setOldInput(req.body);
        res.redirect('/booking');
    }
});

// 4. Booking Success Screen
router.get('/booking-success/:bookingId', (req, res) => {
    const bId = req.params.bookingId;
    const booking = getOne("SELECT * FROM bookings WHERE id = ? OR booking_id = ?", [bId, bId]);
    if (!booking) {
        res.setFlash('error', 'Booking record not found.');
        return res.redirect('/');
    }

    const vehicle = booking.vehicle_id ? getOne("SELECT * FROM vehicles WHERE id = ?", [booking.vehicle_id]) : null;
    const driver = booking.driver_id ? getOne("SELECT * FROM drivers WHERE id = ?", [booking.driver_id]) : null;
    const customer = booking.customer_id ? getOne("SELECT * FROM users WHERE id = ?", [booking.customer_id]) : null;

    booking.vehicle = vehicle;
    booking.driver = driver;
    booking.customer = customer;

    res.render('public/booking-success', {
        booking,
        pageTitle: `Booking Confirmed #${booking.booking_id} | Vaishnavi Tours`
    });
});

// 5. Services Hub & Sub-services
router.get('/services', (req, res) => {
    res.render('public/services/index', {
        pageTitle: 'Our Taxi & Cab Services | Vaishnavi Tours'
    });
});

router.get('/ambulance-services', (req, res) => {
    res.render('public/ambulance-services', {
        pageTitle: '24/7 Ambulance & Emergency Transfer | Vaishnavi Tours'
    });
});

router.get('/services/local-taxi', (req, res) => {
    const vehicles = getAll("SELECT * FROM vehicles WHERE status != 'Inactive'");
    res.render('public/services/local-taxi', {
        vehicles,
        pageTitle: 'Local Taxi Service Bilaspur | Vaishnavi Tours'
    });
});

router.get('/services/outstation-taxi', (req, res) => {
    const vehicles = getAll("SELECT * FROM vehicles WHERE status != 'Inactive'");
    res.render('public/services/outstation-taxi', {
        vehicles,
        pageTitle: 'Outstation Cabs from Bilaspur | Vaishnavi Tours'
    });
});

router.get('/services/airport-transfer', (req, res) => {
    const vehicles = getAll("SELECT * FROM vehicles WHERE status != 'Inactive'");
    res.render('public/services/airport-transfer', {
        vehicles,
        pageTitle: 'Raipur & Bilaspur Airport Transfer | Vaishnavi Tours'
    });
});

// 6. Vehicles Fleet
router.get('/vehicles', (req, res) => {
    const vehicles = getAll("SELECT * FROM vehicles WHERE status != 'Inactive'");
    res.render('public/vehicles', {
        vehicles,
        pageTitle: 'Fleet & Vehicle Tariffs | Vaishnavi Tours'
    });
});

// 7. Rates Matrix
router.get('/rates', (req, res) => {
    const routes = getAll("SELECT * FROM rate_routes WHERE active = 1 ORDER BY category ASC, origin ASC");
    for (const r of routes) {
        r.vehiclePrices = getAll("SELECT * FROM rate_vehicle_prices WHERE rate_route_id = ?", [r.id]);
    }

    const routeLocs = [];
    routes.forEach(r => {
        if (r.origin) routeLocs.push(r.origin);
        if (r.destination) routeLocs.push(r.destination);
    });
    const locations = Array.from(new Set(routeLocs)).filter(Boolean).sort();

    const activeCategories = getAll("SELECT name FROM vehicle_categories WHERE is_active = 1 ORDER BY sort_order ASC").map(c => c.name);
    const ratePriceCategories = getAll("SELECT DISTINCT vehicle_category FROM rate_vehicle_prices").map(p => p.vehicle_category);
    const vehicleCategories = Array.from(new Set([...activeCategories, ...ratePriceCategories]));

    const selectedQuotes = [];
    if (req.query.from && req.query.to && req.query.rate_category) {
        for (const cat of vehicleCategories) {
            if (req.query.vehicle_category && req.query.vehicle_category !== cat) continue;
            const quote = RateQuoteService.quote(
                req.query.rate_category,
                req.query.from,
                req.query.to,
                cat,
                req.query.estimated_km ? parseFloat(req.query.estimated_km) : null
            );
            if (quote) selectedQuotes.push(quote);
        }
    }

    res.render('public/rates', {
        routes,
        locations,
        vehicleCategories,
        selectedQuotes,
        pageTitle: 'Transparent Rate Card & Fare Estimates | Vaishnavi Tours'
    });
});

// 8. Service Network
router.get(['/network', '/service-network'], (req, res) => {
    res.render('public/network', {
        pageTitle: 'Chhattisgarh Route Network | Vaishnavi Tours'
    });
});

// 9. Tour Packages
router.get('/tour-packages', (req, res) => {
    const packages = getAll("SELECT * FROM tour_packages WHERE is_active = 1");
    res.render('public/tour-packages', {
        packages,
        pageTitle: 'Holiday & Tour Packages | Vaishnavi Tours'
    });
});

// 10. About & Contact
router.get('/about', (req, res) => {
    res.render('public/about', {
        pageTitle: 'About Us | Vaishnavi Tours Bilaspur'
    });
});

router.get('/contact', (req, res) => {
    res.render('public/contact', {
        pageTitle: 'Contact Us & 24/7 Helpline | Vaishnavi Tours'
    });
});

// 11. Policies
router.get('/terms-and-conditions', (req, res) => {
    res.render('public/terms', {
        pageTitle: 'Terms & Conditions | Vaishnavi Tours'
    });
});

router.get(['/cancellation-refund-policy', '/cancellation-policy', '/cancellation'], (req, res) => {
    res.render('public/cancellation', {
        pageTitle: 'Cancellation & Refund Policy | Vaishnavi Tours'
    });
});

// 12. Feedback
router.get('/feedback', (req, res) => {
    const feedbacks = getAll("SELECT * FROM feedback WHERE is_public = 1 ORDER BY created_at DESC LIMIT 20");
    res.render('public/feedback', {
        feedbacks,
        pageTitle: 'Customer Testimonials & Reviews | Vaishnavi Tours'
    });
});

router.post('/feedback', (req, res) => {
    const { name, email, rating, comment } = req.body;
    if (!name || !rating || !comment) {
        res.setFlash('error', 'Name, rating, and review comments are required.');
        return res.redirect('/feedback');
    }
    run(`
        INSERT INTO feedback (user_id, name, email, rating, comment, is_public, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, 1, datetime('now'), datetime('now'))
    `, [req.user?.id || null, name.trim(), email ? email.trim() : null, parseInt(rating, 10), comment.trim()]);

    res.setFlash('success', 'Thank you for your valuable feedback! It has been posted.');
    res.redirect('/feedback');
});

// 13. Enquiry
router.get('/enquiry', (req, res) => {
    res.render('public/enquiry', {
        pageTitle: 'Corporate & Group Travel Enquiry | Vaishnavi Tours'
    });
});

router.post('/enquiry', (req, res) => {
    const { name, phone, email, service_type, vehicle_preference, subject, message } = req.body;
    if (!name || !phone || !message) {
        res.setFlash('error', 'Name, mobile phone, and message are required.');
        return res.redirect('/enquiry');
    }

    const cleanPhone = String(phone).replace(/[^0-9]/g, '');
    run(`
        INSERT INTO enquiries (
            source, name, phone, email, service_type, vehicle_preference, subject, message, status, created_at, updated_at
        ) VALUES (
            'Website', ?, ?, ?, ?, ?, ?, ?, 'New', datetime('now'), datetime('now')
        )
    `, [name.trim(), cleanPhone, email ? email.trim() : null, service_type || null, vehicle_preference || null, subject || null, message.trim()]);

    res.setFlash('success', 'Thank you! Your enquiry has been received. Our team will call you back shortly.');
    res.redirect('/enquiry');
});

// 14. Sitemap XML & Robots.txt
router.get('/sitemap.xml', (req, res) => {
    const domain = config.app.url.replace(/\/+$/, '');
    const today = new Date().toISOString().split('T')[0];

    const pages = [
        { loc: `${domain}/`, changefreq: 'daily', priority: '1.0' },
        { loc: `${domain}/services`, changefreq: 'weekly', priority: '0.9' },
        { loc: `${domain}/ambulance-services`, changefreq: 'weekly', priority: '0.8' },
        { loc: `${domain}/services/local-taxi`, changefreq: 'weekly', priority: '0.9' },
        { loc: `${domain}/services/outstation-taxi`, changefreq: 'weekly', priority: '0.9' },
        { loc: `${domain}/services/airport-transfer`, changefreq: 'weekly', priority: '0.9' },
        { loc: `${domain}/vehicles`, changefreq: 'weekly', priority: '0.8' },
        { loc: `${domain}/rates`, changefreq: 'weekly', priority: '0.8' },
        { loc: `${domain}/service-network`, changefreq: 'weekly', priority: '0.8' },
        { loc: `${domain}/booking`, changefreq: 'weekly', priority: '0.8' },
        { loc: `${domain}/about`, changefreq: 'monthly', priority: '0.7' },
        { loc: `${domain}/contact`, changefreq: 'monthly', priority: '0.7' },
        { loc: `${domain}/feedback`, changefreq: 'weekly', priority: '0.6' },
        { loc: `${domain}/terms-and-conditions`, changefreq: 'monthly', priority: '0.5' },
        { loc: `${domain}/cancellation-refund-policy`, changefreq: 'monthly', priority: '0.5' },
    ];

    let xml = '<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n';
    for (const p of pages) {
        xml += `  <url>\n    <loc>${p.loc}</loc>\n    <lastmod>${today}</lastmod>\n    <changefreq>${p.changefreq}</changefreq>\n    <priority>${p.priority}</priority>\n  </url>\n`;
    }
    xml += '</urlset>';

    res.header('Content-Type', 'application/xml');
    res.send(xml);
});

router.get('/robots.txt', (req, res) => {
    const domain = config.app.url.replace(/\/+$/, '');
    const content = `User-agent: *
Allow: /
Allow: /assets/
Allow: /build/
Allow: /css/
Allow: /js/

Disallow: /admin
Disallow: /admin/
Disallow: /customer
Disallow: /customer/
Disallow: /login
Disallow: /register
Disallow: /forgot-password
Disallow: /dashboard
Disallow: /invoices
Disallow: /bookings
Disallow: /payments
Disallow: /notifications
Disallow: /profile
Disallow: /booking-success/
Disallow: /invoice/

Sitemap: ${domain}/sitemap.xml
`;
    res.header('Content-Type', 'text/plain');
    res.send(content);
});

export default router;
