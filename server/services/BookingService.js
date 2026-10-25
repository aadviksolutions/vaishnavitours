import db, { getOne, getAll, run, transaction } from '../db.js';
import RateQuoteService from './RateQuoteService.js';
import bcrypt from 'bcryptjs';

export class BookingService {
    static generateBookingId(category = null) {
        if (!category) {
            const rows = getAll("SELECT booking_id FROM bookings WHERE booking_id LIKE 'VT-%' ORDER BY id DESC LIMIT 100");
            let maxNumber = 1000;
            for (const r of rows) {
                const match = String(r.booking_id).match(/^VT-(\d+)$/);
                if (match) {
                    const num = parseInt(match[1], 10);
                    if (num > maxNumber) {
                        maxNumber = num;
                    }
                }
            }
            if (maxNumber === 1000) {
                const countRow = getOne("SELECT count(*) as count FROM bookings");
                maxNumber = 1000 + (countRow ? countRow.count : 0);
            }
            return `VT-${maxNumber + 1}`;
        }

        let prefix = String(category).trim().toUpperCase();
        if (!['CORP', 'WEB', 'MAN', 'TP'].includes(prefix)) {
            prefix = 'WEB';
        }

        const latestRow = getOne("SELECT booking_id FROM bookings WHERE booking_id LIKE ? ORDER BY id DESC LIMIT 1", [`${prefix}-%`]);
        let maxSeq = 0;
        if (latestRow && latestRow.booking_id) {
            const m = String(latestRow.booking_id).match(new RegExp(`^${prefix}-(\\d+)$`));
            if (m) {
                maxSeq = parseInt(m[1], 10);
            }
        }

        let candidate;
        do {
            maxSeq++;
            candidate = `${prefix}-${String(maxSeq).padStart(6, '0')}`;
        } while (getOne("SELECT id FROM bookings WHERE booking_id = ?", [candidate]));

        return candidate;
    }

    static createBooking(data, currentUser = null) {
        return transaction(() => {
            let user = currentUser;

            // Guest customer handling / deduplication
            if (!user) {
                const phone = String(data.mobile || data.phone || '').trim();
                const email = String(data.email || '').trim();

                let existingUser = null;
                if (phone) {
                    existingUser = getOne("SELECT * FROM users WHERE phone = ?", [phone]);
                }
                if (!existingUser && email) {
                    existingUser = getOne("SELECT * FROM users WHERE email = ?", [email]);
                }

                if (existingUser) {
                    user = existingUser;
                } else {
                    const defaultPasswordHash = bcrypt.hashSync('Password@123', 10);
                    const userEmail = email || (phone ? `${phone}@vaishnavitours.in` : `guest_${Date.now()}@vaishnavitours.in`);
                    const res = run(
                        "INSERT INTO users (name, phone, email, password, role, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, 'customer', 1, datetime('now'), datetime('now'))",
                        [data.customer_name || 'Traveler', phone || null, userEmail, defaultPasswordHash]
                    );
                    user = {
                        id: res.lastInsertRowid,
                        name: data.customer_name || 'Traveler',
                        phone,
                        email: userEmail,
                        role: 'customer'
                    };

                    run(
                        "INSERT INTO customers (user_id, city, state, pincode, created_at, updated_at) VALUES (?, ?, 'Chhattisgarh', '495001', datetime('now'), datetime('now'))",
                        [user.id, data.pickup_location || 'Bilaspur']
                    );
                }
            }

            // Customer record verification
            let customer = getOne("SELECT * FROM customers WHERE user_id = ?", [user.id]);
            if (!customer) {
                const res = run(
                    "INSERT INTO customers (user_id, city, state, pincode, created_at, updated_at) VALUES (?, ?, 'Chhattisgarh', '495001', datetime('now'), datetime('now'))",
                    [user.id, data.pickup_location || 'Bilaspur']
                );
                customer = { id: res.lastInsertRowid, user_id: user.id };
            }

            // Vehicle Category lookup
            const selectedCategory = data.vehicle_category || null;
            let vehicleCategoryId = data.vehicle_category_id || null;
            if (!vehicleCategoryId && selectedCategory) {
                const catRow = getOne("SELECT id FROM vehicle_categories WHERE name = ? OR code = ?", [selectedCategory, selectedCategory.toLowerCase().replace(/[\s\/\—\-]+/g, '_')]);
                if (catRow) vehicleCategoryId = catRow.id;
            }

            // Rate calculation
            let totalAmount = parseFloat(data.total_amount || 0);
            let pricingDetails = data.pricing_details ? (typeof data.pricing_details === 'string' ? JSON.parse(data.pricing_details) : data.pricing_details) : null;
            let rateCategory = data.rate_category || null;

            if (!totalAmount && data.rate_category && data.pickup_location && data.destination && selectedCategory) {
                const quote = RateQuoteService.quote(
                    data.rate_category,
                    data.pickup_location,
                    data.destination,
                    selectedCategory,
                    data.route_distance_km ? parseFloat(data.route_distance_km) : null
                );
                if (quote) {
                    totalAmount = quote.total_amount;
                    pricingDetails = quote.details;
                    rateCategory = quote.details.category;
                }
            }

            // Tax calculation (5% GST if applicable)
            let taxableAmount = totalAmount;
            let gstRate = 5.0;
            let gstAmount = 0.0;
            let cgstAmount = 0.0;
            let sgstAmount = 0.0;

            if (pricingDetails && pricingDetails.gst_applicable) {
                gstAmount = Math.round(taxableAmount * 0.05 * 100) / 100;
                cgstAmount = Math.round((gstAmount / 2) * 100) / 100;
                sgstAmount = Math.round((gstAmount / 2) * 100) / 100;
                totalAmount = Math.round((taxableAmount + gstAmount) * 100) / 100;
            }

            const bookingCategory = data.booking_category || 'WEB';
            const bookingId = this.generateBookingId(bookingCategory);

            const res = run(`
                INSERT INTO bookings (
                    booking_id, booking_category, customer_id, trip_type,
                    pickup_location, destination, via_location,
                    travel_date, travel_time, return_date, return_time,
                    duration_days, route_distance_km, route_duration_minutes, route_duration_text,
                    vehicle_category, vehicle_model, vehicle_category_id,
                    rate_category, pricing_details,
                    taxable_amount, gst_rate, cgst_amount, sgst_amount, gst_amount,
                    total_amount, paid_amount, balance_amount,
                    payment_status, booking_status,
                    notes, terms_accepted, terms_accepted_at,
                    created_at, updated_at
                ) VALUES (
                    ?, ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?,
                    ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, 0.0, ?,
                    'Pending', 'Pending',
                    ?, 1, datetime('now'),
                    datetime('now'), datetime('now')
                )
            `, [
                bookingId, bookingCategory, customer.id, data.trip_type || 'One-Way',
                data.pickup_location, data.destination, data.via_location || null,
                data.travel_date, data.travel_time, data.return_date || null, data.return_time || null,
                data.duration_days || 1, data.route_distance_km || null, data.route_duration_minutes || null, data.route_duration_text || null,
                selectedCategory, data.vehicle_model || null, vehicleCategoryId,
                rateCategory, pricingDetails ? JSON.stringify(pricingDetails) : null,
                taxableAmount, gstRate, cgstAmount, sgstAmount, gstAmount,
                totalAmount, totalAmount,
                data.notes || null
            ]);

            const newBookingRowId = res.lastInsertRowid;

            // Status history entry
            run(`
                INSERT INTO booking_status_histories (
                    booking_id, old_status, new_status, comment, changed_by, created_at
                ) VALUES (?, NULL, 'Pending', 'Booking created by customer online', ?, datetime('now'))
            `, [newBookingRowId, user.id]);

            // Notification for Admin
            run(`
                INSERT INTO notifications (
                    user_id, title, message, type, is_read, action_url, created_at, updated_at
                ) VALUES (
                    1, 'New Booking Request', ?, 'booking_new', 0, ?, datetime('now'), datetime('now')
                )
            `, [
                `New booking ${bookingId} received for ${data.pickup_location} → ${data.destination} on ${data.travel_date}.`,
                `/admin/bookings/${newBookingRowId}`
            ]);

            return getOne("SELECT * FROM bookings WHERE id = ?", [newBookingRowId]);
        })();
    }

    static checkVehicleOverlap(vehicleId, travelDate, returnDate = null, excludeBookingId = null) {
        if (!vehicleId || !travelDate) return false;
        const endDate = returnDate || travelDate;

        let sql = `
            SELECT id, booking_id, travel_date, return_date 
            FROM bookings 
            WHERE vehicle_id = ? 
            AND booking_status NOT IN ('Completed', 'Cancelled')
            AND (
                (travel_date BETWEEN ? AND ?) 
                OR (COALESCE(return_date, travel_date) BETWEEN ? AND ?)
                OR (? BETWEEN travel_date AND COALESCE(return_date, travel_date))
            )
        `;
        const params = [vehicleId, travelDate, endDate, travelDate, endDate, travelDate];

        if (excludeBookingId) {
            sql += ` AND id != ?`;
            params.push(excludeBookingId);
        }

        const conflict = getOne(sql, params);
        return conflict;
    }

    static checkDriverOverlap(driverId, travelDate, returnDate = null, excludeBookingId = null) {
        if (!driverId || !travelDate) return false;
        const endDate = returnDate || travelDate;

        let sql = `
            SELECT id, booking_id, travel_date, return_date 
            FROM bookings 
            WHERE driver_id = ? 
            AND booking_status NOT IN ('Completed', 'Cancelled')
            AND (
                (travel_date BETWEEN ? AND ?) 
                OR (COALESCE(return_date, travel_date) BETWEEN ? AND ?)
                OR (? BETWEEN travel_date AND COALESCE(return_date, travel_date))
            )
        `;
        const params = [driverId, travelDate, endDate, travelDate, endDate, travelDate];

        if (excludeBookingId) {
            sql += ` AND id != ?`;
            params.push(excludeBookingId);
        }

        const conflict = getOne(sql, params);
        return conflict;
    }

    static updateStatus(bookingId, newStatus, comments = '', changedByUserId = null) {
        return transaction(() => {
            const booking = getOne("SELECT * FROM bookings WHERE id = ?", [bookingId]);
            if (!booking) throw new Error("Booking not found");

            run(
                "UPDATE bookings SET booking_status = ?, updated_at = datetime('now') WHERE id = ?",
                [newStatus, bookingId]
            );

            run(`
                INSERT INTO booking_status_histories (
                    booking_id, old_status, new_status, comment, changed_by, created_at
                ) VALUES (?, ?, ?, ?, ?, datetime('now'))
            `, [bookingId, booking.booking_status, newStatus, comments || `Status changed to ${newStatus}`, changedByUserId]);

            // Release vehicle/driver when completed or cancelled
            if (['Completed', 'Cancelled'].includes(newStatus)) {
                if (booking.vehicle_id) {
                    run("UPDATE vehicles SET status = 'Available', updated_at = datetime('now') WHERE id = ?", [booking.vehicle_id]);
                }
                if (booking.driver_id) {
                    run("UPDATE drivers SET status = 'Available', updated_at = datetime('now') WHERE id = ?", [booking.driver_id]);
                }
            }

            return getOne("SELECT * FROM bookings WHERE id = ?", [bookingId]);
        })();
    }
}

export default BookingService;
