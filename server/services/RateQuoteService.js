import db, { getOne, getAll } from '../db.js';

export class RateQuoteService {
    /**
     * Generate a rate quote for a booking
     */
    static quote(category, origin, destination, vehicleCategory, distanceKm = null) {
        const route = this.findRoute(category, origin, destination);
        if (!route) {
            return null;
        }

        const vehiclePrices = getAll(
            'SELECT * FROM rate_vehicle_prices WHERE rate_route_id = ? ORDER BY id ASC',
            [route.id]
        );

        const price = vehiclePrices.find(vp => this.matchVehicleCategory(vp.vehicle_category, vehicleCategory));
        if (!price) {
            return null;
        }

        const baseFare = price.base_fare !== null && price.base_fare !== undefined 
            ? parseFloat(price.base_fare) 
            : parseFloat(price.vehicle_rent || 0);

        let totalAmount = baseFare;
        if (price.vehicle_rent !== null && price.per_km_rate !== null && distanceKm !== null && distanceKm !== undefined) {
            totalAmount += parseFloat(distanceKm) * parseFloat(price.per_km_rate);
        }

        const details = {
            category,
            origin: route.origin || origin,
            destination: route.destination || destination,
            vehicle_category: price.vehicle_category,
            base_fare: baseFare,
            included_km: route.km_limit,
            included_hours: route.included_hours !== null ? parseFloat(route.included_hours) : null,
            extra_km_rate: price.extra_km_rate !== null ? parseFloat(price.extra_km_rate) : null,
            extra_hour_rate: price.extra_hour_rate !== null ? parseFloat(price.extra_hour_rate) : null,
            extra_km_configured: price.extra_km_rate !== null,
            extra_hour_configured: price.extra_hour_rate !== null,
            vehicle_rent: price.vehicle_rent !== null ? parseFloat(price.vehicle_rent) : null,
            per_km_rate: price.per_km_rate !== null ? parseFloat(price.per_km_rate) : null,
            distance_km: distanceKm,
            gst_applicable: Boolean(price.gst_applicable),
            toll_type: price.toll_type,
            parking_type: price.parking_type,
            border_tax_type: price.border_tax_type,
            night_charge: price.night_charge !== null ? parseFloat(price.night_charge) : null,
            driver_food_type: price.driver_food_type,
            is_estimate: category === 'round_trip',
            estimate_notice: category === 'round_trip'
                ? 'Estimated base: vehicle rent + per-KM tariff. Toll, parking, border tax, driver food & night charges extra.'
                : null,
        };

        return {
            route,
            price,
            total_amount: Math.round(totalAmount * 100) / 100,
            details,
        };
    }

    static findRoute(category, origin, destination) {
        if (['round_trip', 'local_8h_80km', 'local_4h_40km'].includes(category)) {
            return getOne(
                'SELECT * FROM rate_routes WHERE category = ? AND origin IS NULL AND destination IS NULL AND active = 1 LIMIT 1',
                [category]
            );
        }

        const routes = getAll(
            'SELECT * FROM rate_routes WHERE category = ? AND active = 1',
            [category]
        );

        for (const route of routes) {
            if (this.sameLocation(route.origin, origin) && this.sameLocation(route.destination, destination)) {
                return route;
            }
        }

        for (const route of routes) {
            if (route.return_same_rate && this.sameLocation(route.origin, destination) && this.sameLocation(route.destination, origin)) {
                return route;
            }
        }

        return null;
    }

    static sameLocation(first, second) {
        if (!first || !second) {
            return false;
        }

        const f = String(first).toLowerCase().trim();
        const s = String(second).toLowerCase().trim();

        if (f === s) {
            return true;
        }

        const fAirport = f.includes('airport');
        const sAirport = s.includes('airport');

        if (fAirport !== sAirport) {
            return false;
        }

        if (fAirport && sAirport) {
            const isRaipurF = f.includes('raipur') || f.includes('swami vivekananda') || f.includes('mana') || f.includes('rpr');
            const isRaipurS = s.includes('raipur') || s.includes('swami vivekananda') || s.includes('mana') || s.includes('rpr');
            return isRaipurF && isRaipurS;
        }

        const knownCities = ['bilaspur', 'raipur', 'raigarh', 'korba', 'ambikapur', 'bhilai', 'sambalpur', 'jharsuguda'];
        for (const city of knownCities) {
            const fHas = f.includes(city);
            const sHas = s.includes(city);
            if (fHas || sHas) {
                return fHas && sHas;
            }
        }

        return s.includes(f) || f.includes(s);
    }

    static matchVehicleCategory(priceCategory, requestedCategory) {
        if (!priceCategory || !requestedCategory) {
            return false;
        }

        const priceCat = String(priceCategory).trim();
        const reqCat = String(requestedCategory).trim();

        if (priceCat.toLowerCase() === reqCat.toLowerCase()) {
            return true;
        }

        const pLower = priceCat.toLowerCase();
        const rLower = reqCat.toLowerCase();

        // SUV / MUV — 6+1 Seating <-> Ertiga <-> MUV
        const isSUVReq = rLower.includes('ertiga') || rLower.includes('suv') || rLower.includes('muv');
        const isSUVPriced = pLower.includes('ertiga') || pLower.includes('suv') || pLower.includes('muv');
        if (isSUVReq && isSUVPriced) {
            return true;
        }

        // Traveller <-> Tempo Traveller
        const isTravellerReq = rLower.includes('traveller') || rLower.includes('tempo');
        const isTravellerPriced = pLower.includes('traveller') || pLower.includes('tempo');
        if (isTravellerReq && isTravellerPriced) {
            return true;
        }

        // Hatchback fallback to Sedan if route doesn't have dedicated Hatchback tier
        if (rLower.includes('hatchback') && (pLower.includes('hatchback') || pLower.includes('sedan'))) {
            return true;
        }

        // Urbania fallback to Traveller if Urbania not explicitly listed
        if (rLower.includes('urbania') && (pLower.includes('urbania') || pLower.includes('traveller'))) {
            return true;
        }

        // Bus matching
        if (rLower.includes('bus') && pLower.includes('bus')) {
            return true;
        }

        return false;
    }
}

export default RateQuoteService;
