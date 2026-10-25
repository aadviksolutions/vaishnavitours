import express from 'express';
import GoogleMapsRoutingService from '../services/GoogleMapsRoutingService.js';
import config from '../config.js';
import db, { getOne, getAll, run } from '../db.js';

const router = express.Router();

router.get('/maps/config', (req, res) => {
    res.json({
        success: true,
        client_api_key: GoogleMapsRoutingService.getClientApiKey(),
        has_server_api_key: Boolean(GoogleMapsRoutingService.getServerApiKey()),
        map_center_lat: config.vaishnavi.map_center_lat,
        map_center_lng: config.vaishnavi.map_center_lng,
        default_zoom: config.vaishnavi.map_default_zoom,
        service_areas: config.seo.service_areas
    });
});

router.post('/maps/calculate-route', async (req, res) => {
    try {
        const { origin, destination, via, is_round_trip, final_drop } = req.body;
        const result = await GoogleMapsRoutingService.calculateRoadRoute(
            origin,
            destination,
            via,
            Boolean(is_round_trip),
            final_drop
        );
        res.json(result);
    } catch (err) {
        console.error('Error in calculate-route API:', err);
        res.status(500).json({
            success: false,
            status: 'SERVER_ERROR',
            message: err.message
        });
    }
});

router.post('/maps/geocode', async (req, res) => {
    try {
        const { address } = req.body;
        const apiKey = GoogleMapsRoutingService.getServerApiKey();
        if (!apiKey) {
            return res.json({
                success: true,
                status: 'CALIBRATED',
                formatted_address: address,
                location: { lat: config.vaishnavi.map_center_lat, lng: config.vaishnavi.map_center_lng }
            });
        }
        const resp = await fetch(`https://maps.googleapis.com/maps/api/geocode/json?address=${encodeURIComponent(address)}&key=${apiKey}`);
        const data = await resp.json();
        if (data.status === 'OK' && data.results && data.results.length) {
            const item = data.results[0];
            return res.json({
                success: true,
                status: 'OK',
                formatted_address: item.formatted_address,
                location: item.geometry.location,
                place_id: item.place_id
            });
        }
        res.json({ success: false, status: data.status, message: data.error_message || 'Geocoding failed' });
    } catch (err) {
        res.status(500).json({ success: false, message: err.message });
    }
});

router.post('/maps/reverse-geocode', async (req, res) => {
    try {
        const { lat, lng } = req.body;
        const apiKey = GoogleMapsRoutingService.getServerApiKey();
        if (!apiKey) {
            return res.json({
                success: true,
                status: 'CALIBRATED',
                formatted_address: `${lat}, ${lng}`,
                location: { lat: parseFloat(lat), lng: parseFloat(lng) }
            });
        }
        const resp = await fetch(`https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&key=${apiKey}`);
        const data = await resp.json();
        if (data.status === 'OK' && data.results && data.results.length) {
            const item = data.results[0];
            return res.json({
                success: true,
                status: 'OK',
                formatted_address: item.formatted_address,
                location: item.geometry.location,
                place_id: item.place_id
            });
        }
        res.json({ success: false, status: data.status, message: data.error_message || 'Reverse geocoding failed' });
    } catch (err) {
        res.status(500).json({ success: false, message: err.message });
    }
});

router.post('/locations/quick-store', (req, res) => {
    try {
        const { name, city, latitude, longitude, place_id } = req.body;
        if (!name) {
            return res.status(422).json({ success: false, message: 'Name is required' });
        }
        const result = run(`
            INSERT INTO pickup_locations (
                name, city, latitude, longitude, place_id, is_active, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, 1, datetime('now'), datetime('now'))
        `, [name, city || 'Bilaspur', latitude || null, longitude || null, place_id || null]);

        res.json({
            success: true,
            location: {
                id: result.lastInsertRowid,
                name,
                city: city || 'Bilaspur'
            }
        });
    } catch (err) {
        res.status(500).json({ success: false, message: err.message });
    }
});

export default router;
