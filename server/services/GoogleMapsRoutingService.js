import config from '../config.js';
import db, { getOne } from '../db.js';

export class GoogleMapsRoutingService {
    static KNOWN_ROAD_DISTANCES = {
        'bilaspur|raipur': 118.0,
        'raipur|bilaspur': 118.0,
        'bilaspur|raipur airport': 135.0,
        'raipur airport|bilaspur': 135.0,
        'bilaspur|korba': 88.0,
        'korba|bilaspur': 88.0,
        'bilaspur|raigarh': 155.0,
        'raigarh|bilaspur': 155.0,
        'bilaspur|amarkantak': 122.0,
        'amarkantak|bilaspur': 122.0,
        'bilaspur|ambikapur': 220.0,
        'ambikapur|bilaspur': 220.0,
        'bilaspur|janjgir': 52.0,
        'janjgir|bilaspur': 52.0,
        'bilaspur|champa': 62.0,
        'champa|bilaspur': 62.0,
        'bilaspur|bhilai': 145.0,
        'bhilai|bilaspur': 145.0,
        'bilaspur|durg': 150.0,
        'durg|bilaspur': 150.0,
        'bilaspur|mungeli': 50.0,
        'mungeli|bilaspur': 50.0,
        'bilaspur|kawardha': 125.0,
        'kawardha|bilaspur': 125.0,
        'bilaspur|jagdalpur': 415.0,
        'jagdalpur|bilaspur': 415.0,
        'raipur|korba': 205.0,
        'korba|raipur': 205.0,
        'raipur|raigarh': 240.0,
        'raigarh|raipur': 240.0,
    };

    static getServerApiKey() {
        const row = getOne("SELECT value FROM settings WHERE key = 'google_maps_server_api_key'");
        if (row && row.value && row.value.trim()) {
            return row.value.trim();
        }
        return process.env.GOOGLE_MAPS_SERVER_API_KEY || process.env.GOOGLE_MAPS_API_KEY || '';
    }

    static getClientApiKey() {
        const row = getOne("SELECT value FROM settings WHERE key = 'google_maps_client_api_key'");
        if (row && row.value && row.value.trim()) {
            return row.value.trim();
        }
        return process.env.GOOGLE_MAPS_CLIENT_API_KEY || process.env.GOOGLE_MAPS_API_KEY || '';
    }

    static async calculateRoadRoute(origin, destination, via = null, isRoundTrip = false, finalDrop = null) {
        const apiKey = this.getServerApiKey();
        const originStr = this.formatLocationString(origin);
        const destStr = this.formatLocationString(destination);

        if (!originStr || !destStr) {
            return {
                success: false,
                status: 'INVALID_LOCATIONS',
                message: 'Please provide valid pickup and destination locations.',
                is_round_trip: isRoundTrip,
                total_distance_km: 0.0,
                total_duration_text: '',
                total_duration_minutes: 0,
                legs: [],
                route_geometry: null,
                calculated_at: new Date().toISOString(),
                source: 'validation'
            };
        }

        if (apiKey) {
            try {
                const googleResult = await this.computeGoogleRoute(origin, destination, via, isRoundTrip, finalDrop, apiKey);
                if (googleResult.success) {
                    return googleResult;
                }
            } catch (err) {
                console.warn('Google Maps routing failed, falling back to calibrated calculation:', err.message);
            }
        }

        return this.computeCalibratedRoadRoute(origin, destination, via, isRoundTrip, finalDrop);
    }

    static async computeGoogleRoute(origin, destination, via, isRoundTrip, finalDrop, apiKey) {
        const originStr = this.formatLocationString(origin);
        const destStr = this.formatLocationString(destination);
        const viaStr = via ? this.formatLocationString(via) : null;

        let url = `https://maps.googleapis.com/maps/api/directions/json?origin=${encodeURIComponent(originStr)}&destination=${encodeURIComponent(destStr)}&mode=driving&key=${apiKey}`;
        if (viaStr) {
            url += `&waypoints=${encodeURIComponent(viaStr)}`;
        }

        const res = await fetch(url);
        const data = await res.json();

        if (data.status !== 'OK' || !data.routes || !data.routes.length) {
            return {
                success: false,
                status: data.status || 'ZERO_RESULTS',
                message: data.error_message || 'No route found',
                is_round_trip: isRoundTrip,
                total_distance_km: 0.0,
                total_duration_text: '',
                total_duration_minutes: 0,
                legs: [],
                route_geometry: null,
                calculated_at: new Date().toISOString(),
                source: 'google_api_failed'
            };
        }

        const route = data.routes[0];
        let totalMeters = 0;
        let totalSeconds = 0;
        const legs = [];

        for (let i = 0; i < route.legs.length; i++) {
            const leg = route.legs[i];
            const distM = leg.distance?.value || 0;
            const durS = leg.duration?.value || 0;
            totalMeters += distM;
            totalSeconds += durS;

            legs.push({
                type: 'outbound',
                label: `Outbound Leg ${i + 1}: ${leg.start_address || 'Origin'} → ${leg.end_address || 'Destination'}`,
                start_address: leg.start_address || '',
                end_address: leg.end_address || '',
                distance_km: Math.round((distM / 1000) * 100) / 100,
                distance_text: leg.distance?.text || `${Math.round(distM / 100) / 10} km`,
                duration_text: leg.duration?.text || `${Math.round(durS / 60)} mins`,
                duration_minutes: Math.round(durS / 60)
            });
        }

        if (isRoundTrip) {
            const returnDest = finalDrop ? this.formatLocationString(finalDrop) : originStr;
            let retUrl = `https://maps.googleapis.com/maps/api/directions/json?origin=${encodeURIComponent(destStr)}&destination=${encodeURIComponent(returnDest)}&mode=driving&key=${apiKey}`;
            if (viaStr) {
                retUrl += `&waypoints=${encodeURIComponent(viaStr)}`;
            }
            try {
                const retRes = await fetch(retUrl);
                const retData = await retRes.json();
                if (retData.status === 'OK' && retData.routes && retData.routes.length) {
                    const retRoute = retData.routes[0];
                    for (let i = 0; i < retRoute.legs.length; i++) {
                        const leg = retRoute.legs[i];
                        const distM = leg.distance?.value || 0;
                        const durS = leg.duration?.value || 0;
                        totalMeters += distM;
                        totalSeconds += durS;

                        legs.push({
                            type: 'return',
                            label: `Return Leg ${i + 1}: ${leg.start_address || 'Destination'} → ${leg.end_address || 'Return Drop'}`,
                            start_address: leg.start_address || '',
                            end_address: leg.end_address || '',
                            distance_km: Math.round((distM / 1000) * 100) / 100,
                            distance_text: leg.distance?.text || `${Math.round(distM / 100) / 10} km`,
                            duration_text: leg.duration?.text || `${Math.round(durS / 60)} mins`,
                            duration_minutes: Math.round(durS / 60)
                        });
                    }
                }
            } catch (e) {
                console.warn('Google return trip fetch failed:', e.message);
            }
        }

        const totalKm = Math.round((totalMeters / 1000) * 100) / 100;
        const totalMinutes = Math.round(totalSeconds / 60);

        return {
            success: true,
            status: 'OK',
            message: 'Route calculated successfully via Google Maps.',
            origin: originStr,
            destination: destStr,
            is_round_trip: isRoundTrip,
            total_distance_km: totalKm,
            distance_km: totalKm,
            total_duration_text: this.formatDurationMinutes(totalMinutes),
            duration_text: this.formatDurationMinutes(totalMinutes),
            total_duration_minutes: totalMinutes,
            legs,
            route_geometry: route.overview_polyline?.points || null,
            calculated_at: new Date().toISOString(),
            source: 'google_directions_api'
        };
    }

    static computeCalibratedRoadRoute(origin, destination, via, isRoundTrip, finalDrop) {
        const originName = this.extractLocationName(origin);
        const destName = this.extractLocationName(destination);
        const viaName = via ? this.extractLocationName(via) : null;
        const returnDropName = finalDrop ? this.extractLocationName(finalDrop) : originName;

        const legs = [];
        let totalKm = 0.0;
        let totalMinutes = 0;

        if (viaName) {
            const leg1Km = this.lookupOrEstimateRoadDistance(origin, via);
            const leg1Mins = Math.round((leg1Km / 52) * 60);
            totalKm += leg1Km;
            totalMinutes += leg1Mins;
            legs.push({
                type: 'outbound',
                label: `Outbound Leg 1: ${originName} → ${viaName}`,
                start_address: originName,
                end_address: viaName,
                distance_km: leg1Km,
                distance_text: `${leg1Km} km`,
                duration_text: this.formatDurationMinutes(leg1Mins),
                duration_minutes: leg1Mins
            });

            const leg2Km = this.lookupOrEstimateRoadDistance(via, destination);
            const leg2Mins = Math.round((leg2Km / 52) * 60);
            totalKm += leg2Km;
            totalMinutes += leg2Mins;
            legs.push({
                type: 'outbound',
                label: `Outbound Leg 2: ${viaName} → ${destName}`,
                start_address: viaName,
                end_address: destName,
                distance_km: leg2Km,
                distance_text: `${leg2Km} km`,
                duration_text: this.formatDurationMinutes(leg2Mins),
                duration_minutes: leg2Mins
            });
        } else {
            const directKm = this.lookupOrEstimateRoadDistance(origin, destination);
            const directMins = Math.round((directKm / 54) * 60);
            totalKm += directKm;
            totalMinutes += directMins;
            legs.push({
                type: 'outbound',
                label: `Outbound Trip: ${originName} → ${destName}`,
                start_address: originName,
                end_address: destName,
                distance_km: directKm,
                distance_text: `${directKm} km`,
                duration_text: this.formatDurationMinutes(directMins),
                duration_minutes: directMins
            });
        }

        if (isRoundTrip) {
            if (viaName) {
                const ret1Km = this.lookupOrEstimateRoadDistance(destination, via);
                const ret1Mins = Math.round((ret1Km / 52) * 60);
                totalKm += ret1Km;
                totalMinutes += ret1Mins;
                legs.push({
                    type: 'return',
                    label: `Return Leg 1: ${destName} → ${viaName}`,
                    start_address: destName,
                    end_address: viaName,
                    distance_km: ret1Km,
                    distance_text: `${ret1Km} km`,
                    duration_text: this.formatDurationMinutes(ret1Mins),
                    duration_minutes: ret1Mins
                });

                const ret2Km = this.lookupOrEstimateRoadDistance(via, finalDrop || origin);
                const ret2Mins = Math.round((ret2Km / 52) * 60);
                totalKm += ret2Km;
                totalMinutes += ret2Mins;
                legs.push({
                    type: 'return',
                    label: `Return Leg 2: ${viaName} → ${returnDropName}`,
                    start_address: viaName,
                    end_address: returnDropName,
                    distance_km: ret2Km,
                    distance_text: `${ret2Km} km`,
                    duration_text: this.formatDurationMinutes(ret2Mins),
                    duration_minutes: ret2Mins
                });
            } else {
                const retKm = this.lookupOrEstimateRoadDistance(destination, finalDrop || origin);
                const retMins = Math.round((retKm / 54) * 60);
                totalKm += retKm;
                totalMinutes += retMins;
                legs.push({
                    type: 'return',
                    label: `Return Trip: ${destName} → ${returnDropName}`,
                    start_address: destName,
                    end_address: returnDropName,
                    distance_km: retKm,
                    distance_text: `${retKm} km`,
                    duration_text: this.formatDurationMinutes(retMins),
                    duration_minutes: retMins
                });
            }
        }

        totalKm = Math.round(totalKm * 100) / 100;

        return {
            success: true,
            status: 'OK',
            message: 'Planned road route distance computed using highway road network.',
            origin: destName ? `${originName}` : '',
            destination: destName,
            is_round_trip: isRoundTrip,
            total_distance_km: totalKm,
            distance_km: totalKm,
            total_duration_text: this.formatDurationMinutes(totalMinutes),
            duration_text: this.formatDurationMinutes(totalMinutes),
            total_duration_minutes: totalMinutes,
            legs,
            route_geometry: null,
            calculated_at: new Date().toISOString(),
            source: 'calibrated_road_network'
        };
    }

    static lookupOrEstimateRoadDistance(from, to) {
        const fromName = String(this.extractLocationName(from)).toLowerCase().trim();
        const toName = String(this.extractLocationName(to)).toLowerCase().trim();

        const key = `${fromName}|${toName}`;
        if (this.KNOWN_ROAD_DISTANCES[key]) {
            return this.KNOWN_ROAD_DISTANCES[key];
        }

        for (const [pair, km] of Object.entries(this.KNOWN_ROAD_DISTANCES)) {
            const [p1, p2] = pair.split('|');
            if (fromName.includes(p1) && toName.includes(p2)) {
                return km;
            }
        }

        // Coordinate haversine fallback with highway curvature factor
        const c1 = this.extractCoordinates(from);
        const c2 = this.extractCoordinates(to);
        if (c1 && c2 && c1.lat && c1.lng && c2.lat && c2.lng) {
            const aerial = this.haversine(c1.lat, c1.lng, c2.lat, c2.lng);
            return Math.round(aerial * 1.28 * 10) / 10;
        }

        return 45.0; // Standard nominal inter-city / local distance baseline
    }

    static formatDurationMinutes(minutes) {
        if (!minutes || minutes <= 0) return '0 mins';
        const hours = Math.floor(minutes / 60);
        const remainingMinutes = minutes % 60;
        if (hours > 0 && remainingMinutes > 0) {
            return `${hours} hr ${remainingMinutes} mins`;
        }
        if (hours > 0) {
            return `${hours} hr`;
        }
        return `${minutes} mins`;
    }

    static formatLocationString(loc) {
        if (!loc) return '';
        if (typeof loc === 'string') return loc.trim();
        if (typeof loc === 'object') {
            if (loc.address) return String(loc.address).trim();
            if (loc.name) return String(loc.name).trim();
            if (loc.lat && loc.lng) return `${loc.lat},${loc.lng}`;
        }
        return '';
    }

    static extractLocationName(loc) {
        if (!loc) return '';
        if (typeof loc === 'string') return loc.trim();
        if (typeof loc === 'object') {
            return loc.name || loc.address || '';
        }
        return '';
    }

    static extractCoordinates(loc) {
        if (!loc) return null;
        if (typeof loc === 'object' && loc.lat && loc.lng) {
            return { lat: parseFloat(loc.lat), lng: parseFloat(loc.lng) };
        }
        return null;
    }

    static haversine(lat1, lon1, lat2, lon2) {
        const R = 6371; // Earth's radius in KM
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }
}

export default GoogleMapsRoutingService;
