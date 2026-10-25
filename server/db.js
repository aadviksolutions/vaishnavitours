import Database from 'better-sqlite3';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const dbPath = process.env.DB_DATABASE 
    ? (path.isAbsolute(process.env.DB_DATABASE) ? process.env.DB_DATABASE : path.join(__dirname, '..', process.env.DB_DATABASE))
    : path.join(__dirname, '..', 'database', 'database.sqlite');

const db = new Database(dbPath, {
    // verbose: process.env.NODE_ENV === 'development' ? console.log : null
});

// Enable WAL mode for better concurrency and foreign keys
try {
    db.pragma('journal_mode = WAL');
    db.pragma('foreign_keys = ON');
} catch (e) {
    console.error('Error setting SQLite pragmas:', e);
}

/**
 * Real vehicle photo resolution helper
 */
export function getVehicleIconUrl(row) {
    if (!row) return '/assets/vehicles/maruti-suzuki-dzire.jpg';
    if (row.image) {
        let clean = String(row.image).replace(/\\/g, '/').replace(/^\/+/, '');
        if (clean.startsWith('public/')) clean = clean.slice(7);
        return '/' + clean;
    }
    const name = String(row.name || '').toLowerCase();
    const type = String(row.vehicle_type || row.category || '').toLowerCase();

    if (name.includes('dzire') || type.includes('sedan')) {
        return '/assets/vehicles/maruti-suzuki-dzire.jpg';
    }
    if (name.includes('ertiga') || type.includes('muv') || type.includes('suv')) {
        return '/assets/vehicles/maruti-suzuki-ertiga.jpg';
    }
    if (name.includes('innova') || name.includes('crysta') || type.includes('innova')) {
        return '/assets/vehicles/toyota-innova-crysta.jpg';
    }
    if (name.includes('urbania') || type.includes('urbania')) {
        return '/assets/vehicles/force-urbania.jpg';
    }
    if (name.includes('traveller') || name.includes('tempo') || type.includes('tempo') || type.includes('traveller')) {
        return '/assets/vehicles/force-tempo-traveller.jpg';
    }
    if (name.includes('tiago') || name.includes('wagon') || type.includes('hatchback')) {
        return '/assets/vehicles/tata-tiago-wagonr.jpg';
    }
    if (name.includes('bus') || type.includes('bus')) {
        return '/assets/vehicles/luxury-bus-coach.jpg';
    }
    return '/assets/vehicles/maruti-suzuki-dzire.jpg';
}

function decorateRow(row) {
    if (row && typeof row === 'object') {
        if ('vehicle_type' in row || 'registration_number' in row || ('seating_capacity' in row && 'per_km_rate' in row)) {
            if (!row.icon_url) {
                row.icon_url = getVehicleIconUrl(row);
            }
        }
    }
    return row;
}

/**
 * Execute a query returning a single row or null
 */
export function getOne(sql, params = []) {
    const stmt = db.prepare(sql);
    const row = stmt.get(params) || null;
    return decorateRow(row);
}

/**
 * Execute a query returning an array of rows
 */
export function getAll(sql, params = []) {
    const stmt = db.prepare(sql);
    const rows = stmt.all(params);
    return rows.map(decorateRow);
}

/**
 * Execute an INSERT / UPDATE / DELETE query
 * Returns { changes, lastInsertRowid }
 */
export function run(sql, params = []) {
    const stmt = db.prepare(sql);
    return stmt.run(params);
}

/**
 * Execute in a database transaction
 */
export function transaction(fn) {
    const tx = db.transaction(fn);
    return tx;
}

/**
 * Export raw db instance for advanced operations
 */
export default db;
