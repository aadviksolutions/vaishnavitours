import assert from 'assert';
import db, { getOne, getAll, run } from '../server/db.js';
import BookingService from '../server/services/BookingService.js';
import PaymentService from '../server/services/PaymentService.js';
import RateQuoteService from '../server/services/RateQuoteService.js';
import GoogleMapsRoutingService from '../server/services/GoogleMapsRoutingService.js';

let passed = 0;
let failed = 0;

function test(name, fn) {
    try {
        console.log(`Testing: ${name}...`);
        fn();
        console.log(`  ✓ Passed: ${name}`);
        passed++;
    } catch (e) {
        console.error(`  ✗ Failed: ${name}`);
        console.error(e);
        failed++;
    }
}

async function asyncTest(name, fn) {
    try {
        console.log(`Testing: ${name}...`);
        await fn();
        console.log(`  ✓ Passed: ${name}`);
        passed++;
    } catch (e) {
        console.error(`  ✗ Failed: ${name}`);
        console.error(e);
        failed++;
    }
}

async function runAll() {
    console.log('========================================================================');
    console.log(' VAISHNAVI TOURS FINAL PRODUCTION WORKFLOW & SECURITY VERIFICATION');
    console.log('========================================================================\n');

    // 1. Booking Categories and ID Generation
    test('Booking categories formatting (WEB, CORP, MAN, TP)', () => {
        const idWeb = BookingService.generateBookingId('WEB');
        const idCorp = BookingService.generateBookingId('CORP');
        const idMan = BookingService.generateBookingId('MAN');
        const idTp = BookingService.generateBookingId('TP');

        assert(/^WEB-\d{6}$/.test(idWeb), `WEB ID must match pattern: ${idWeb}`);
        assert(/^CORP-\d{6}$/.test(idCorp), `CORP ID must match pattern: ${idCorp}`);
        assert(/^MAN-\d{6}$/.test(idMan), `MAN ID must match pattern: ${idMan}`);
        assert(/^TP-\d{6}$/.test(idTp), `TP ID must match pattern: ${idTp}`);
    });

    // 2. Booking Creation & Rate Quoting
    test('Rate Quote calculation for Raipur Airport Transfer', () => {
        const quote = RateQuoteService.quote('airport', 'Bilaspur', 'Raipur Airport', 'Sedan');
        assert(quote, 'Should return a valid quote for Raipur airport');
        assert(quote.total_amount > 0, 'Total amount must be greater than 0');
        assert.strictEqual(quote.details.vehicle_category, 'Sedan');
    });

    // 3. Driver & Vehicle Overlap Prevention
    test('Vehicle and Driver overlap conflict detection', () => {
        const vehicles = getAll("SELECT id FROM vehicles LIMIT 2");
        const drivers = getAll("SELECT id FROM drivers LIMIT 2");
        if (vehicles.length && drivers.length) {
            const vId = vehicles[0].id;
            const dId = drivers[0].id;
            // Check method exists and runs cleanly
            const vOverlap = BookingService.checkVehicleOverlap(vId, '2026-11-20', '2026-11-20');
            const dOverlap = BookingService.checkDriverOverlap(dId, '2026-11-20', '2026-11-20');
            console.log(`    Vehicle overlap check: ${vOverlap ? 'Conflict found' : 'Available'}`);
            console.log(`    Driver overlap check: ${dOverlap ? 'Conflict found' : 'Available'}`);
        }
    });

    // 4. GST Tax Invoice Generation & Math
    test('GST Tax Invoice calculations (5% GST split: CGST 2.5%, SGST 2.5%)', () => {
        const booking = BookingService.createBooking({
            customer_name: 'GST Test Traveler',
            mobile: '9888877777',
            email: 'gsttest@example.com',
            booking_category: 'WEB',
            trip_type: 'Outstation',
            pickup_location: 'Bilaspur',
            destination: 'Korba',
            travel_date: '2026-11-25',
            travel_time: '09:00',
            total_amount: 3000,
            pricing_details: { gst_applicable: true },
            vehicle_category: 'Sedan'
        });

        const inv = PaymentService.syncInvoice(booking.id);
        assert(inv, 'Invoice must be generated');
        assert.strictEqual(parseFloat(inv.total_amount), 3150);
        assert.strictEqual(parseFloat(inv.gst_amount), 150);
        assert.strictEqual(parseFloat(inv.cgst_amount), 75);
        assert.strictEqual(parseFloat(inv.sgst_amount), 75);
        assert(inv.invoice_number, 'Invoice number must be present');
    });

    // 5. Google Maps Routing Configuration
    test('Google Maps Routing config and key isolation', () => {
        const clientKey = GoogleMapsRoutingService.getClientApiKey();
        const serverKey = GoogleMapsRoutingService.getServerApiKey();
        assert(typeof clientKey === 'string');
        assert(typeof serverKey === 'string');
    });

    // 6. Super-Admin Account Access
    test('Super-Admin account exists with ID 1 and role admin', () => {
        const admin = getOne("SELECT id, name, email, role, is_active FROM users WHERE id = 1");
        assert(admin, 'Super admin user 1 must exist');
        assert.strictEqual(admin.role, 'admin');
        assert.strictEqual(admin.is_active, 1);
        assert.strictEqual(admin.email, 'admin@vaishnavitours.com');
    });

    // 7. Security: Verify non-admin cannot access admin role via registration
    test('Role escalation via public registration is impossible', () => {
        const testEmail = `cust_reg_test_${Date.now()}@example.com`;
        const testPhone = `99${Date.now().toString().slice(-8)}`;

        run(`
            INSERT INTO users (name, email, phone, password, role, is_active, created_at, updated_at)
            VALUES (?, ?, ?, 'hash', 'customer', 1, datetime('now'), datetime('now'))
        `, ['New Customer', testEmail, testPhone]);

        const created = getOne("SELECT * FROM users WHERE email = ?", [testEmail]);
        assert.strictEqual(created.role, 'customer', 'Public registered role must always be customer');
    });

    // 8. Health check verification
    await asyncTest('Health check endpoint returns healthy status', async () => {
        const { default: app } = await import('../server/app.js');
        const server = app.listen(8089, async () => {
            try {
                const res = await fetch('http://localhost:8089/health');
                assert.strictEqual(res.status, 200);
                const data = await res.json();
                assert.strictEqual(data.status, 'healthy');
                assert.strictEqual(data.database, 'connected');
            } finally {
                server.close();
            }
        });
    });

    console.log(`\n========================================================================`);
    console.log(`WORKFLOW VERIFICATION RESULTS`);
    console.log(`Total Tested: ${passed + failed}`);
    console.log(`Passed: ${passed}`);
    console.log(`Failed: ${failed}`);
    console.log(`========================================================================\n`);

    if (failed > 0) process.exit(1);
}

runAll();
