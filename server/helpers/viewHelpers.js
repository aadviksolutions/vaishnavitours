import fs from 'fs';
import path from 'path';
import { renderIcon } from './icons.js';
import { getVehicleIconUrl } from '../db.js';
import config from '../config.js';

// Extend Array prototype to support Laravel collection methods used in templates
if (!Array.prototype.isNotEmpty) {
    Array.prototype.isNotEmpty = function() { return this.length > 0; };
}
if (!Array.prototype.isEmpty) {
    Array.prototype.isEmpty = function() { return this.length === 0; };
}
if (!Array.prototype.pluck) {
    Array.prototype.pluck = function(prop) {
        const arr = this.map(item => item ? item[prop] : undefined).filter(Boolean);
        arr.implode = (delim) => arr.join(delim);
        return arr;
    };
}
if (!Array.prototype.implode) {
    Array.prototype.implode = function(delim) { return this.join(delim); };
}
if (!Array.prototype.first) {
    Array.prototype.first = function() { return this[0] || null; };
}
if (!Array.prototype.last) {
    Array.prototype.last = function() { return this[this.length - 1] || null; };
}
if (!Array.prototype.firstWhere) {
    Array.prototype.firstWhere = function(key, val) {
        return this.find(item => item && (item[key] === val || item[key] == val)) || null;
    };
}
if (!Array.prototype.where) {
    Array.prototype.where = function(key, opOrVal, val) {
        if (arguments.length === 2) {
            return this.filter(item => item && (item[key] === opOrVal || item[key] == opOrVal));
        }
        if (arguments.length >= 3) {
            if (opOrVal === '!=') return this.filter(item => item && item[key] !== val && item[key] != val);
            if (opOrVal === '=') return this.filter(item => item && (item[key] === val || item[key] == val));
            if (opOrVal === '>') return this.filter(item => item && item[key] > val);
            if (opOrVal === '>=') return this.filter(item => item && item[key] >= val);
            if (opOrVal === '<') return this.filter(item => item && item[key] < val);
            if (opOrVal === '<=') return this.filter(item => item && item[key] <= val);
        }
        return this.filter(item => item && (item[key] === opOrVal || item[key] == opOrVal));
    };
}
if (!Array.prototype.contains) {
    Array.prototype.contains = function(key, val) {
        if (arguments.length === 1) {
            return this.includes(key);
        }
        return this.some(item => (typeof item === 'object' && item !== null ? (item[key] === val || item[key] == val) : item === val));
    };
}
if (!Array.prototype.sum) {
    Array.prototype.sum = function(fn) {
        if (typeof fn === 'function') {
            return this.reduce((acc, item) => acc + Number(fn(item) || 0), 0);
        }
        return this.reduce((acc, item) => acc + Number(item || 0), 0);
    };
}
if (!Array.prototype.take) {
    Array.prototype.take = function(n) { return this.slice(0, n); };
}
if (!Array.prototype.count) {
    Array.prototype.count = function() { return this.length; };
}
if (!Array.prototype.hasPages) {
    Array.prototype.hasPages = function() { return false; };
}
if (!Array.prototype.links) {
    Array.prototype.links = function() { return ''; };
}
if (!Array.prototype.getCollection) {
    Array.prototype.getCollection = function() { return this; };
}
if (!Array.prototype.total) {
    Array.prototype.total = function() { return this.length; };
}

function timeDiffForHumans(val) {
    if (!val) return '';
    try {
        const d = new Date(val);
        const now = new Date();
        const diffMs = now - d;
        const diffSec = Math.floor(diffMs / 1000);
        if (isNaN(diffSec)) return String(val);
        if (diffSec < 60) return 'just now';
        const diffMin = Math.floor(diffSec / 60);
        if (diffMin < 60) return `${diffMin} minute${diffMin > 1 ? 's' : ''} ago`;
        const diffHours = Math.floor(diffMin / 60);
        if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
        const diffDays = Math.floor(diffHours / 24);
        if (diffDays < 30) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
        const diffMonths = Math.floor(diffDays / 30);
        if (diffMonths < 12) return `${diffMonths} month${diffMonths > 1 ? 's' : ''} ago`;
        const diffYears = Math.floor(diffMonths / 12);
        return `${diffYears} year${diffYears > 1 ? 's' : ''} ago`;
    } catch (e) {
        return String(val);
    }
}
if (!String.prototype.diffForHumans) {
    String.prototype.diffForHumans = function() { return timeDiffForHumans(this); };
}
if (!Date.prototype.diffForHumans) {
    Date.prototype.diffForHumans = function() { return timeDiffForHumans(this); };
}

function dateCarbonFormat(val, fmt = 'Y-m-d') {
    if (!val) return '';
    try {
        const d = new Date(val);
        if (isNaN(d.getTime())) return String(val);
        const year = d.getFullYear();
        const monthShort = d.toLocaleString('en-US', { month: 'short' });
        const monthLong = d.toLocaleString('en-US', { month: 'long' });
        const monthNum = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        const hours24 = String(d.getHours()).padStart(2, '0');
        const hours12 = String(d.getHours() % 12 || 12).padStart(2, '0');
        const mins = String(d.getMinutes()).padStart(2, '0');
        const ampm = d.getHours() >= 12 ? 'PM' : 'AM';

        let res = fmt;
        res = res.replace(/Y/g, year);
        res = res.replace(/m/g, monthNum);
        res = res.replace(/d/g, day);
        res = res.replace(/M/g, monthShort);
        res = res.replace(/F/g, monthLong);
        res = res.replace(/H/g, hours24);
        res = res.replace(/h/g, hours12);
        res = res.replace(/i/g, mins);
        res = res.replace(/A/g, ampm);
        res = res.replace(/a/g, ampm.toLowerCase());
        return res;
    } catch (e) {
        return String(val);
    }
}
if (!String.prototype.format) {
    String.prototype.format = function(fmt) { return dateCarbonFormat(this, fmt); };
}
if (!Date.prototype.format) {
    Date.prototype.format = function(fmt) { return dateCarbonFormat(this, fmt); };
}

const ROUTE_MAP = {
    'home': '/',
    'booking': '/booking',
    'booking.submit': '/booking',
    'services': '/services',
    'services.local-taxi': '/services/local-taxi',
    'services.outstation-taxi': '/services/outstation-taxi',
    'services.airport-transfer': '/services/airport-transfer',
    'ambulance-services': '/ambulance-services',
    'vehicles': '/vehicles',
    'rates': '/rates',
    'service-network': '/service-network',
    'network': '/service-network',
    'about': '/about',
    'contact': '/contact',
    'terms-and-conditions': '/terms-and-conditions',
    'cancellation-refund-policy': '/cancellation-refund-policy',
    'feedback': '/feedback',
    'enquiry': '/enquiry',
    'tour-packages': '/tour-packages',
    'login': '/login',
    'login.submit': '/login',
    'register': '/register',
    'register.submit': '/register',
    'logout': '/logout',
    'password.request': '/forgot-password',
    'password.email': '/forgot-password',
    'admin.dashboard': '/admin/dashboard',
    'admin.bookings.index': '/admin/bookings',
    'admin.bookings.create': '/admin/bookings/create',
    'admin.calendar.index': '/admin/calendar',
    'admin.trips.index': '/admin/trips',
    'admin.enquiries.index': '/admin/enquiries',
    'admin.vehicles.index': '/admin/vehicles',
    'admin.vehicle-categories.index': '/admin/vehicle-categories',
    'admin.drivers.index': '/admin/drivers',
    'admin.tour-packages.index': '/admin/tour-packages',
    'admin.locations.index': '/admin/locations',
    'admin.booking-categories.index': '/admin/booking-categories',
    'admin.customers.index': '/admin/customers',
    'admin.customers.create': '/admin/customers/create',
    'admin.customers.store': '/admin/customers',
    'admin.payments.index': '/admin/payments',
    'admin.expenses.index': '/admin/expenses',
    'admin.invoices.index': '/admin/invoices',
    'admin.complaints.index': '/admin/complaints',
    'admin.rates.index': '/admin/rates',
    'admin.notifications.index': '/admin/notifications',
    'admin.reports.index': '/admin/reports',
    'admin.feedback.index': '/admin/feedback',
    'admin.whatsapp.scan': '/admin/whatsapp/scan',
    'admin.settings.index': '/admin/settings',
    'customer.dashboard': '/customer/dashboard',
    'customer.bookings.index': '/customer/bookings',
    'customer.notifications': '/customer/notifications',
    'customer.profile': '/customer/profile'
};

export function setupViewGlobals(app) {
    app.use((req, res, next) => {
        // Config function & object (supports config('key') and config.key)
        const configFn = (key, fallback = '') => {
            if (!key) return config;
            const parts = key.split('.');
            let val = config;
            for (const p of parts) {
                if (val && typeof val === 'object' && p in val) {
                    val = val[p];
                } else {
                    return fallback;
                }
            }
            return val !== undefined ? val : fallback;
        };
        Object.assign(configFn, config);
        res.locals.config = configFn;
        res.locals.icon = renderIcon;
        res.locals.asset = (assetPath) => {
            if (!assetPath) return '';
            const clean = assetPath.startsWith('/') ? assetPath : `/${assetPath}`;
            return clean;
        };
        res.locals.url = (path) => path || '/';
        res.locals.currentPath = req.path;
        res.locals.currentUrl = `${config.app.url}${req.originalUrl}`;
        res.locals.formatCurrency = (amount) => {
            const num = parseFloat(amount || 0);
            return '₹' + num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        };
        res.locals.formatDate = (dateVal) => {
            if (!dateVal) return '';
            try {
                const d = new Date(dateVal);
                return d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
            } catch (e) {
                return String(dateVal);
            }
        };
        res.locals.csrfToken = () => 'static-csrf-token';
        res.locals.csrfField = () => '<input type="hidden" name="_token" value="static-csrf-token">';

        // Route helper
        const routeHelperFn = (name, param) => {
            if (ROUTE_MAP[name]) return ROUTE_MAP[name];
            if (name === 'admin.bookings.show') return `/admin/bookings/${param?.id || param}`;
            if (name === 'admin.bookings.edit') return `/admin/bookings/${param?.id || param}/edit`;
            if (name === 'admin.customers.show') return `/admin/customers/${param?.id || param}`;
            if (name === 'admin.customers.edit') return `/admin/customers/${param?.id || param}/edit`;
            if (name === 'admin.trips.show') return `/admin/trips/${param?.id || param}`;
            if (name === 'admin.invoices.show') return `/admin/invoices/${param?.id || param}`;
            if (name === 'admin.rates.edit') return `/admin/rates/${param?.id || param}/edit`;
            if (name === 'admin.rates.destroy') return `/admin/rates/${param?.id || param}`;
            if (name === 'admin.notifications.mark-read') return '/admin/notifications/mark-read';
            if (name === 'admin.notifications.read') return `/admin/notifications/${param?.id || param}/read`;
            if (name === 'admin.enquiries.index') return `/admin/enquiries`;
            if (name === 'admin.reports.export.excel') return `/admin/reports/export-excel`;
            if (name === 'admin.reports.export.pdf') return `/admin/reports/export-pdf`;
            if (name === 'customer.bookings.show') return `/customer/bookings/${param?.id || param}`;
            if (name === 'booking.success') return `/booking-success/${param?.id || param}`;
            return '#';
        };
        res.locals.route = routeHelperFn;
        globalThis.routeHelper = routeHelperFn;

        // Session & flash helper
        res.locals.session = (key) => {
            if (!key) return req.session;
            return res.locals.flash?.[key] || req.session?.[key] || null;
        };

        // Old input helper fallback
        if (!res.locals.old) {
            res.locals.old = (key, fallback = '') => fallback;
        }

        // Errors helper (MessageBag simulation)
        const errorBag = (req.session && req.session.errors) || {};
        res.locals.errors = {
            ...errorBag,
            any: () => Object.keys(errorBag).length > 0,
            all: () => Object.values(errorBag).flat(),
            has: (key) => Boolean(errorBag[key]),
            first: (key) => (Array.isArray(errorBag[key]) ? errorBag[key][0] : errorBag[key] || '')
        };

        // Date / Request helpers
        res.locals.now = () => new Date();
        res.locals.strtotime = (str) => {
            if (!str) return 0;
            if (typeof str === 'number') return str;
            const t = new Date(str).getTime();
            if (!isNaN(t)) return Math.floor(t / 1000);
            if (/^\d{1,2}:\d{2}(?::\d{2})?$/.test(str.trim())) {
                const [h, m] = str.trim().split(':').map(Number);
                const d = new Date();
                d.setHours(h, m, 0, 0);
                return Math.floor(d.getTime() / 1000);
            }
            return 0;
        };
        res.locals.date = (format = 'Y-m-d', timestamp) => {
            let d = new Date();
            if (timestamp) {
                if (typeof timestamp === 'number') {
                    d = new Date(timestamp * 1000);
                } else if (typeof timestamp === 'string' && /^\d{1,2}:\d{2}(?::\d{2})?$/.test(timestamp.trim())) {
                    const parts = timestamp.trim().split(':');
                    const h = parseInt(parts[0], 10);
                    const m = parseInt(parts[1], 10);
                    const h12 = h % 12 || 12;
                    const ampm = h >= 12 ? 'PM' : 'AM';
                    if (format === 'h:i A') {
                        return `${String(h12).padStart(2, '0')}:${String(m).padStart(2, '0')} ${ampm}`;
                    }
                    return `${h12}:${String(m).padStart(2, '0')} ${ampm}`;
                } else {
                    d = new Date(timestamp);
                }
            }
            if (isNaN(d.getTime())) return String(timestamp || '');
            if (format === 'h:i A') {
                const h12 = d.getHours() % 12 || 12;
                const ampm = d.getHours() >= 12 ? 'PM' : 'AM';
                return `${String(h12).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')} ${ampm}`;
            }
            return dateCarbonFormat(d, format);
        };

        const reqProxy = {
            filled: (key) => Boolean(req.query?.[key] || req.body?.[key]),
            has: (key) => Boolean((req.query && key in req.query) || (req.body && key in req.body)),
            hasAny: (keys = []) => keys.some(k => Boolean(req.query?.[k] || req.body?.[k])),
            all: () => ({ ...(req.query || {}), ...(req.body || {}) }),
            get: (key, fallback = '') => (req.query?.[key] ?? req.body?.[key] ?? fallback),
            query: req.query || {},
            body: req.body || {},
            path: req.path,
            url: req.url,
            except: (keys = []) => {
                const out = { ...(req.query || {}) };
                for (const k of keys) delete out[k];
                return out;
            }
        };
        const requestFn = (key, fallback = '') => {
            if (!key) return reqProxy;
            const val = req.query?.[key] ?? req.body?.[key];
            return val !== undefined ? val : fallback;
        };
        Object.assign(requestFn, reqProxy);
        req.filled = reqProxy.filled;
        req.has = reqProxy.has;
        req.hasAny = reqProxy.hasAny;
        req.all = reqProxy.all;
        res.locals.request = requestFn;

        // Auth helpers
        res.locals.user = res.locals.currentUser || null;
        res.locals.auth = () => ({
            user: () => res.locals.currentUser,
            check: () => Boolean(res.locals.isAuthenticated)
        });
        res.locals.Auth = {
            user: () => res.locals.currentUser,
            check: () => Boolean(res.locals.isAuthenticated)
        };

        // PHP built-in helpers
        res.locals.loop = { index: 0, iteration: 1, first: false, last: false, count: 0 };
        res.locals.strlen = (str) => (str ? String(str).length : 0);
        res.locals.substr = (str, start, len) => String(str || '').substr(start, len);
        res.locals.count = (arr) => (arr ? (Array.isArray(arr) ? arr.length : Object.keys(arr).length) : 0);
        res.locals.empty = (val) => !val || (Array.isArray(val) && val.length === 0);
        res.locals.isset = (val) => typeof val !== 'undefined' && val !== null;
        res.locals.is_string = (val) => typeof val === 'string';
        res.locals.is_array = (val) => Array.isArray(val);
        res.locals.is_numeric = (val) => !isNaN(parseFloat(val)) && isFinite(val);
        res.locals.is_object = (val) => typeof val === 'object' && val !== null;
        res.locals.is_int = (val) => Number.isInteger(val);
        res.locals.adminPhone = config.vaishnavi?.primary_phone || config.vaishnavi?.emergency_phone || '9244784443';
        res.locals.supportPhone = config.vaishnavi?.primary_phone || '9244784443';
        res.locals.supportTel = (config.vaishnavi?.primary_phone || '9244784443').replace(/\D/g, '');
        res.locals.emergencyPhone = config.vaishnavi?.emergency_phone || '9244784443';
        res.locals.bookingPhone = config.vaishnavi?.booking_phone || config.vaishnavi?.primary_phone || '9244784443';
        res.locals.prefill = null;
        res.locals.tourPackages = [];
        res.locals.activeVehicleCategories = [];
        res.locals.unreadCount = 0;
        res.locals.unreadNotificationsCount = 0;
        res.locals.is_null = (val) => val === null || typeof val === 'undefined';
        res.locals.in_array = (needle, haystack) => (haystack || []).includes(needle);
        res.locals.trim = (str) => String(str || '').trim();
        res.locals.ltrim = (str) => String(str || '').replace(/^\s+/, '');
        res.locals.rtrim = (str) => String(str || '').replace(/\s+$/, '');
        res.locals.str_replace = (search, replace, subject) => String(subject || '').replaceAll(search, replace);
        res.locals.array_keys = (obj) => Object.keys(obj || {});
        res.locals.array_values = (obj) => Object.values(obj || {});
        res.locals.array_search = (needle, haystack) => {
            if (Array.isArray(haystack)) {
                const idx = haystack.indexOf(needle);
                return idx !== -1 ? idx : false;
            }
            for (const k in (haystack || {})) {
                if (haystack[k] === needle) return k;
            }
            return false;
        };
        res.locals.intval = (val) => parseInt(val, 10) || 0;
        res.locals.floatval = (val) => parseFloat(val) || 0;
        res.locals.round = (val, dec = 0) => Number(Number(val || 0).toFixed(dec));
        res.locals.floor = (val) => Math.floor(Number(val || 0));
        res.locals.ceil = (val) => Math.ceil(Number(val || 0));
        res.locals.min = (...args) => Math.min(...args);
        res.locals.max = (...args) => Math.max(...args);
        res.locals.json_encode = (val) => JSON.stringify(val);
        res.locals.collect = (coll) => {
            const arr = Array.isArray(coll) ? [...coll] : Object.values(coll || {});
            return arr;
        };
        res.locals.explode = (delim, str) => String(str || '').split(delim);
        res.locals.mb_substr = (str, start, len) => String(str || '').substr(start, len);
        res.locals.json_decode = (val) => {
            try { return JSON.parse(val); } catch (e) { return {}; }
        };
        res.locals.number_format = (val, dec = 2) => Number(val || 0).toFixed(dec);
        res.locals.str_starts_with = (str, prefix) => String(str || '').startsWith(prefix);
        res.locals.str_ends_with = (str, suffix) => String(str || '').endsWith(suffix);
        res.locals.str_contains = (str, search) => String(str || '').includes(search);
        res.locals.strtolower = (str) => String(str || '').toLowerCase();
        res.locals.strtoupper = (str) => String(str || '').toUpperCase();
        res.locals.ucwords = (str) => String(str || '').replace(/\b\w/g, l => l.toUpperCase());
        res.locals.ucfirst = (str) => String(str || '').charAt(0).toUpperCase() + String(str || '').slice(1);
        res.locals.file_exists = (filePath) => {
            try {
                return filePath ? fs.existsSync(filePath) : false;
            } catch (e) {
                return false;
            }
        };
        res.locals.public_path = (p = '') => {
            const clean = String(p || '').replace(/^[\\\/]+/, '');
            return path.join(process.cwd(), 'public', clean);
        };
        res.locals.portraitPlaceholder = '/assets/images/user-placeholder.jpg';
        res.locals.addslashes = (str) => String(str || '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
        res.locals.vehicleIconUrl = getVehicleIconUrl;
        res.locals.preg_replace = (pattern, replacement, subject) => {
            try {
                if (typeof pattern === 'string' && pattern.startsWith('/') && pattern.lastIndexOf('/') > 0) {
                    const lastSlash = pattern.lastIndexOf('/');
                    const regexBody = pattern.slice(1, lastSlash);
                    const regexFlags = pattern.slice(lastSlash + 1);
                    return String(subject || '').replace(new RegExp(regexBody, regexFlags), replacement);
                }
                return String(subject || '').replace(new RegExp(pattern, 'g'), replacement);
            } catch (e) {
                return String(subject || '');
            }
        };
        res.locals.rtrim = (str, charlist = ' ') => {
            let s = String(str || '');
            if (charlist === '.') return s.replace(/\.+$/, '');
            if (charlist === '0') return s.replace(/0+$/, '');
            const escaped = charlist.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&');
            return s.replace(new RegExp(`[${escaped}]+$`), '');
        };
        res.locals.ltrim = (str, charlist = ' ') => {
            let s = String(str || '');
            const escaped = charlist.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&');
            return s.replace(new RegExp(`^[${escaped}]+`), '');
        };
        res.locals.trim = (str, charlist = ' ') => {
            return res.locals.rtrim(res.locals.ltrim(str, charlist), charlist);
        };

        // Array & filter helpers
        res.locals.filled = (val) => Boolean(val !== null && val !== undefined && val !== '');
        res.locals.array_filter = (arr, fn) => {
            if (Array.isArray(arr)) {
                return fn ? arr.filter(fn) : arr.filter(Boolean);
            }
            if (typeof arr === 'object' && arr !== null) {
                const out = {};
                for (const [k, v] of Object.entries(arr)) {
                    if (fn ? fn(v, k) : Boolean(v)) out[k] = v;
                }
                return out;
            }
            return [];
        };
        res.locals.array_map = (fn, arr) => (Array.isArray(arr) ? arr.map(fn) : []);
        res.locals.array_merge = (...arrs) => Object.assign({}, ...arrs);

        // String helper simulation (Laravel Str)
        res.locals.Str = {
            plural: (singular, count) => (Number(count) === 1 ? singular : `${singular}s`),
            limit: (str, limit = 100, end = '...') => {
                const s = String(str || '');
                return s.length > limit ? s.slice(0, limit) + end : s;
            },
            slug: (str) => String(str || '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, ''),
            title: (str) => String(str || '').replace(/\b\w/g, l => l.toUpperCase()),
            upper: (str) => String(str || '').toUpperCase(),
            lower: (str) => String(str || '').toLowerCase()
        };

        // Booking workflow constants simulation
        res.locals.Booking = {
            WORKFLOW_TRANSITIONS: {
                'Pending': ['Confirmed', 'Cancelled'],
                'Confirmed': ['Vehicle Assigned', 'Cancelled'],
                'Vehicle Assigned': ['Driver Assigned', 'Cancelled'],
                'Driver Assigned': ['Trip Started', 'Cancelled'],
                'Trip Started': ['On The Way', 'Cancelled'],
                'On The Way': ['Completed', 'Cancelled'],
                'Completed': [],
                'Cancelled': []
            }
        };

        // Carbon date simulation
        res.locals.Carbon = {
            parse: (dateVal) => {
                const d = dateVal ? new Date(dateVal) : new Date();
                return {
                    format: (fmt) => res.locals.formatDate(d),
                    diffForHumans: () => 'Recently',
                    toISOString: () => d.toISOString()
                };
            },
            now: () => new Date()
        };

        // Setting model simulation
        res.locals.Setting = {
            get: (key, fallback = '') => config.vaishnavi[key] || fallback
        };
        res.locals.App = {
            Models: {
                Setting: res.locals.Setting
            }
        };

        // Common hero support banner variables
        const supPhone = config.vaishnavi.phone_primary || '9244784443';
        const cleanP = supPhone.replace(/[^0-9]/g, '');
        res.locals.supportPhone = supPhone;
        res.locals.cleanPhone = cleanP;
        res.locals.supportTel = cleanP.length === 10 ? `+91${cleanP}` : `+${cleanP}`;

        next();
    });
}

export default setupViewGlobals;
