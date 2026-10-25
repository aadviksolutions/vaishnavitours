import db, { getAll, run } from '../db.js';

export function runPaymentMigrations() {
    console.log('Checking and running payment gateway migrations...');

    // 1. Check existing columns in payments table
    const paymentCols = getAll("PRAGMA table_info(payments)").map(c => c.name);

    const colsToAddPayments = [
        { name: 'razorpay_order_id', def: 'VARCHAR' },
        { name: 'razorpay_payment_id', def: 'VARCHAR' },
        { name: 'razorpay_signature', def: 'VARCHAR' },
        { name: 'signature_verified', def: 'INTEGER DEFAULT 0' },
        { name: 'booking_category', def: 'VARCHAR' },
        { name: 'booking_type', def: 'VARCHAR' },
        { name: 'expected_amount', def: 'NUMERIC' },
        { name: 'fee', def: 'NUMERIC DEFAULT 0' },
        { name: 'tax', def: 'NUMERIC DEFAULT 0' },
        { name: 'error_code', def: 'VARCHAR' },
        { name: 'error_description', def: 'TEXT' },
        { name: 'refund_id', def: 'VARCHAR' },
        { name: 'refund_amount', def: 'NUMERIC DEFAULT 0' },
        { name: 'refund_status', def: 'VARCHAR' },
        { name: 'authorized_at', def: 'DATETIME' },
        { name: 'captured_at', def: 'DATETIME' },
        { name: 'webhook_status', def: 'VARCHAR' },
        { name: 'reconciliation_status', def: 'VARCHAR' },
        { name: 'method_details', def: 'TEXT' }
    ];

    for (const col of colsToAddPayments) {
        if (!paymentCols.includes(col.name)) {
            console.log(`Adding column payments.${col.name}...`);
            run(`ALTER TABLE payments ADD COLUMN ${col.name} ${col.def}`);
        }
    }

    // 2. Check existing columns in bookings table
    const bookingCols = getAll("PRAGMA table_info(bookings)").map(c => c.name);

    const colsToAddBookings = [
        { name: 'razorpay_order_id', def: 'VARCHAR' },
        { name: 'confirmed_at', def: 'DATETIME' }
    ];

    for (const col of colsToAddBookings) {
        if (!bookingCols.includes(col.name)) {
            console.log(`Adding column bookings.${col.name}...`);
            run(`ALTER TABLE bookings ADD COLUMN ${col.name} ${col.def}`);
        }
    }

    // 3. Webhook events table for deduplication & audit trail
    run(`
        CREATE TABLE IF NOT EXISTS razorpay_webhook_events (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            event_id VARCHAR UNIQUE NOT NULL,
            event_type VARCHAR NOT NULL,
            payload TEXT NOT NULL,
            status VARCHAR NOT NULL DEFAULT 'Processed',
            error_message TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            processed_at DATETIME
        )
    `);

    // 4. Create Indexes
    try {
        run(`CREATE INDEX IF NOT EXISTS idx_payments_razorpay_order ON payments(razorpay_order_id)`);
        run(`CREATE INDEX IF NOT EXISTS idx_payments_razorpay_payment ON payments(razorpay_payment_id)`);
        run(`CREATE INDEX IF NOT EXISTS idx_payments_booking_id ON payments(booking_id)`);
        run(`CREATE INDEX IF NOT EXISTS idx_bookings_razorpay_order ON bookings(razorpay_order_id)`);
        run(`CREATE INDEX IF NOT EXISTS idx_webhook_event_id ON razorpay_webhook_events(event_id)`);
    } catch (e) {
        console.warn('Index creation warning:', e.message);
    }

    console.log('Payment gateway migrations completed successfully.');
}

if (process.argv[1]?.endsWith('payment_migrations.js')) {
    runPaymentMigrations();
}
