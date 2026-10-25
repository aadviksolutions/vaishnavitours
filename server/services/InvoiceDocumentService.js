import db, { getOne, getAll } from '../db.js';
import config from '../config.js';

export class InvoiceDocumentService {
    static forBooking(booking) {
        const invoice = getOne("SELECT * FROM invoices WHERE booking_id = ?", [booking.id]);
        const customer = getOne("SELECT * FROM users WHERE id = ?", [booking.customer_id]);
        const customerProfile = customer ? getOne("SELECT * FROM customers WHERE user_id = ?", [customer.id]) : null;
        const vehicle = booking.vehicle_id ? getOne("SELECT * FROM vehicles WHERE id = ?", [booking.vehicle_id]) : null;
        const driver = booking.driver_id ? getOne("SELECT * FROM drivers WHERE id = ?", [booking.driver_id]) : null;
        const payments = getAll("SELECT * FROM payments WHERE booking_id = ? ORDER BY paid_at DESC", [booking.id]);

        const totalAmount = parseFloat(invoice?.total_amount || booking.total_amount || 0);
        const paidAmount = parseFloat(invoice?.paid_amount || booking.paid_amount || 0);
        const balanceAmount = Math.max(0, totalAmount - paidAmount);

        const taxableAmount = parseFloat(invoice?.taxable_amount || booking.taxable_amount || Math.round((totalAmount / 1.05) * 100) / 100);
        const gstAmount = parseFloat(invoice?.gst_amount || booking.gst_amount || Math.round((totalAmount - taxableAmount) * 100) / 100);
        const cgstAmount = parseFloat(invoice?.cgst_amount || booking.cgst_amount || Math.round((gstAmount / 2) * 100) / 100);
        const sgstAmount = parseFloat(invoice?.sgst_amount || booking.sgst_amount || Math.round((gstAmount / 2) * 100) / 100);
        const igstAmount = parseFloat(invoice?.igst_amount || booking.igst_amount || 0.0);

        const customerAddressParts = [
            booking.corporate_billing_address || customerProfile?.address,
            customerProfile?.city,
            customerProfile?.state,
            customerProfile?.pincode
        ].filter(Boolean);

        const serviceDescription = [
            booking.pickup_location,
            booking.via_location ? `via ${booking.via_location}` : null,
            'to',
            booking.destination,
            booking.trip_type,
            'Taxi Service',
            vehicle ? `with ${vehicle.name}` : null
        ].filter(Boolean).join(' ');

        return {
            company: {
                name: config.vaishnavi.business_name,
                address: config.vaishnavi.address,
                phone: config.vaishnavi.phone_primary,
                email: config.vaishnavi.contact_email,
                gstin: config.vaishnavi.gstin,
            },
            invoice: {
                number: invoice?.invoice_number || `INV-${new Date().getFullYear()}-00000`,
                date: invoice?.issued_at ? new Date(invoice.issued_at).toLocaleDateString('en-IN') : new Date().toLocaleDateString('en-IN'),
                status: invoice?.status || 'Unpaid',
                category: booking.booking_category || 'WEB',
                terms: config.vaishnavi.booking_terms,
            },
            booking: {
                id: booking.booking_id,
                trip_type: booking.trip_type,
                travel_date: booking.travel_date,
                travel_time: booking.travel_time,
                return_date: booking.return_date,
                return_time: booking.return_time,
                pickup_location: booking.pickup_location,
                destination: booking.destination,
                via_location: booking.via_location,
                service_description: serviceDescription,
                route_distance_km: booking.route_distance_km,
                route_duration_text: booking.route_duration_text,
            },
            customer: {
                name: booking.corporate_contact_person || customer?.name || 'Customer',
                company_name: booking.corporate_company_name || null,
                phone: booking.corporate_phone || customer?.phone || '',
                email: booking.corporate_email || customer?.email || '',
                address: customerAddressParts.join(', '),
                gstin: booking.corporate_gstin || null,
            },
            vehicle: vehicle ? {
                name: vehicle.name,
                registration_number: vehicle.registration_number,
                vehicle_type: vehicle.vehicle_type,
            } : null,
            driver: driver ? {
                name: driver.name,
                mobile: driver.mobile,
                license_number: driver.license_number,
            } : null,
            financials: {
                taxable_amount: taxableAmount,
                gst_rate: 5.0,
                cgst_amount: cgstAmount,
                sgst_amount: sgstAmount,
                igst_amount: igstAmount,
                gst_amount: gstAmount,
                total_amount: totalAmount,
                paid_amount: paidAmount,
                balance_amount: balanceAmount,
            },
            payments,
            bank: {
                account_name: config.vaishnavi.bank_account_name || 'Vaishnavi Tours',
                bank_name: config.vaishnavi.bank_name || 'State Bank of India',
                account_number: config.vaishnavi.bank_account_number || '',
                ifsc_code: config.vaishnavi.bank_ifsc_code || '',
            }
        };
    }
}

export default InvoiceDocumentService;
