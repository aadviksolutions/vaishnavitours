import db, { getAll } from './db.js';
import dotenv from 'dotenv';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

dotenv.config({ path: path.join(__dirname, '..', '.env') });

// Load dynamic settings from the SQLite settings table
export function loadDbSettings() {
    try {
        const rows = getAll('SELECT `key`, `value` FROM settings');
        const settings = {};
        for (const row of rows) {
            settings[row.key] = row.value;
        }
        return settings;
    } catch (e) {
        console.warn('Could not read settings from database:', e.message);
        return {};
    }
}

const dbSettings = loadDbSettings();

export const config = {
    app: {
        name: process.env.APP_NAME || dbSettings.company_name || 'Vaishnavi Tours',
        env: process.env.NODE_ENV || process.env.APP_ENV || 'development',
        port: parseInt(process.env.PORT || '8080', 10),
        url: process.env.APP_URL || 'http://localhost:8080',
        sessionSecret: process.env.SESSION_SECRET || process.env.APP_KEY || 'vaishnavi-tours-secure-secret-key-2026'
    },
    vaishnavi: {
        business_name: dbSettings.company_name || 'Vaishnavi Tours',
        tagline: dbSettings.company_tagline || '24/7 Car Rentals, Cabs & Travel Service',
        phone_primary: dbSettings.phone_primary || '9244784443',
        phone_secondary: dbSettings.phone_secondary || '9179484443',
        phone_primary_tel: '+91' + (dbSettings.phone_primary || '9244784443').replace(/\D/g, ''),
        phone_secondary_tel: '+91' + (dbSettings.phone_secondary || '9179484443').replace(/\D/g, ''),
        emergency_phone: dbSettings.emergency_phone || '9244784443',
        whatsapp: dbSettings.whatsapp_number || '9244784443',
        whatsapp_link: `https://wa.me/91${(dbSettings.whatsapp_number || '9244784443').replace(/\D/g, '')}`,
        contact_email: dbSettings.contact_email || 'info@vaishnavitours.com',
        support_email: dbSettings.support_email || 'support@vaishnavitours.com',
        address: `${dbSettings.address_line_1 || 'Mangal Chowk'}, ${dbSettings.address_line_2 || 'Bilaspur'}`,
        city: dbSettings.city || 'Bilaspur',
        state: dbSettings.state || 'Chhattisgarh',
        pincode: dbSettings.pincode || '495001',
        gstin: dbSettings.gstin || '22DXEPS5353C1ZN',
        booking_terms: dbSettings.booking_terms || 'Toll plaza taxes, state border permits & parking fees are extra if applicable. Night driving allowance applies between 10:00 PM and 06:00 AM.',
        google_maps_url: dbSettings.google_maps_url || 'https://www.google.com/search?kgmid=/g/11tjf7l5kx&hl=en-IN&q=Vaishnavi+Tours+Taxi+Cab+Service+Car+Rental',
        google_rating: '4.9',
        google_review_count: '160+',
        map_center_lat: 22.0797,
        map_center_lng: 82.1409,
        map_default_zoom: 12,
        google_maps_client_api_key: process.env.GOOGLE_MAPS_CLIENT_API_KEY || process.env.GOOGLE_MAPS_API_KEY || '',
        google_maps_server_api_key: process.env.GOOGLE_MAPS_SERVER_API_KEY || process.env.GOOGLE_MAPS_API_KEY || '',
        ambulance_services: [
            {
                title: 'Ambulance with Body Freezer',
                icon: 'snowflake',
                description: 'Dedicated ambulance transportation with body freezer facility for safe and respectful transportation of deceased persons.',
                features: ['Body Freezer Facility', 'Safe & Respectful Transportation', 'Local & Outstation Transfer', '24/7 Assistance']
            },
            {
                title: 'Ambulance with Ventilator',
                icon: 'wind',
                description: 'Ambulance transportation equipped to support patients who require ventilator-assisted transfer, subject to vehicle and medical-support availability.',
                features: ['Ventilator Support', 'Patient Transfer Assistance', 'Emergency & Non-Emergency Transfer', 'Trained Support Availability']
            },
            {
                title: 'ICU Ambulance',
                icon: 'activity',
                description: 'Advanced ambulance transportation for patients requiring higher-level medical support during transfer, subject to ambulance and medical equipment availability.',
                features: ['ICU Ambulance Setup', 'Critical Patient Transfer', 'Medical Equipment Support', 'Local & Outstation Service']
            },
            {
                title: 'Dead Body Transfer',
                icon: 'heart',
                description: 'Safe, respectful and dignified transportation support for deceased persons, including local and long-distance transfers.',
                features: ['Dignified Transportation', 'Safe Handling', 'Local & Outstation Transfer', 'Assistance During Transfer']
            }
        ]
    },
    seo: {
        site_name: 'Vaishnavi Tours',
        canonical_domain: process.env.APP_URL || 'https://vaishnavitours.vercel.app',
        default_title: 'Bilaspur Taxi & Cab Service | 24/7 Car Rental | Vaishnavi Tours',
        title_separator: ' | ',
        default_description: 'Book reliable 24/7 taxi and cab service in Bilaspur, Chhattisgarh with Vaishnavi Tours. Local cabs, outstation travel to Raipur, Korba, Ambikapur, airport transfers to Raipur Airport. Transparent rates and verified chauffeurs.',
        default_keywords: 'taxi service bilaspur, cab service bilaspur, taxi booking bilaspur, outstation cab bilaspur, airport taxi bilaspur, car rental bilaspur, cab booking bilaspur chhattisgarh, one way taxi bilaspur',
        default_image: 'assets/images/hero-taxi.jpg',
        locale: 'en_IN',
        geo: {
            latitude: '22.0797',
            longitude: '82.1409',
            region: 'IN-CT',
            placename: 'Bilaspur, Chhattisgarh'
        },
        service_areas: [
            'Bilaspur', 'Raipur', 'Korba', 'Ambikapur', 'Raigarh',
            'Durg', 'Bhilai', 'Champa', 'Janjgir', 'Ratanpur',
            'Amarkantak', 'Jagdalpur'
        ],
        airports: [
            {
                name: 'Swami Vivekananda Airport, Raipur (RPR)',
                distance_from_bilaspur: '135 km',
                typical_duration: '2.5 to 3 hours via NH 130'
            },
            {
                name: 'Bilasa Devi Kevat Airport, Bilaspur (PAB)',
                distance_from_bilaspur: '12 km',
                typical_duration: '25 to 30 minutes'
            }
        ]
    }
};

export default config;
