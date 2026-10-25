import app from '../server/app.js';
import assert from 'assert';

async function runSmokeTest() {
    console.log('Testing server endpoints on test port 8085...');
    const server = app.listen(8085, async () => {
        try {
            // 1. Home
            const resHome = await fetch('http://localhost:8085/');
            console.log('GET / -> Status:', resHome.status);
            assert.strictEqual(resHome.status, 200, 'Home page must return 200');

            // 2. Find booking with balance
            const { getOne } = await import('../server/db.js');
            let testBooking = getOne("SELECT * FROM bookings WHERE balance_amount > 0 AND booking_status != 'Cancelled' LIMIT 1");
            if (!testBooking) {
                const { default: BookingService } = await import('../server/services/BookingService.js');
                testBooking = BookingService.createBooking({
                    customer_name: 'Smoke Test User',
                    mobile: '9123456780',
                    pickup_location: 'Bilaspur',
                    destination: 'Raipur',
                    travel_date: '2026-11-20',
                    travel_time: '10:00',
                    total_amount: 1500,
                    vehicle_category: 'Sedan'
                });
            }

            const resPay = await fetch(`http://localhost:8085/pay/${testBooking.id}`);
            console.log(`GET /pay/${testBooking.id} -> Status:`, resPay.status);
            assert.strictEqual(resPay.status, 200, 'Pay page must return 200');

            // 3. Razorpay create-order API
            const resOrder = await fetch('http://localhost:8085/api/payments/razorpay/create-order', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ booking_id: testBooking.id })
            });
            console.log('POST /api/payments/razorpay/create-order -> Status:', resOrder.status);
            const orderData = await resOrder.json();
            console.log('Order created:', orderData.order_id, 'Amount:', orderData.amount, 'Currency:', orderData.currency);
            assert.strictEqual(orderData.success, true);
            assert(orderData.order_id);

            // 4. Webhook endpoint with test event
            const webhookRes = await fetch('http://localhost:8085/api/payments/razorpay/webhook', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id: `evt_smoke_${Date.now()}`,
                    event: 'payment.authorized',
                    payload: { payment: { entity: { id: `pay_smoke_${Date.now()}`, order_id: orderData.order_id } } }
                })
            });
            console.log('POST /api/payments/razorpay/webhook -> Status:', webhookRes.status);
            assert.strictEqual(webhookRes.status, 200);

            console.log('\nAll endpoint smoke tests passed successfully!');
        } catch (err) {
            console.error('Smoke test error:', err);
            process.exit(1);
        } finally {
            server.close(() => {
                console.log('Smoke test server closed.');
                process.exit(0);
            });
        }
    });
}

runSmokeTest();
