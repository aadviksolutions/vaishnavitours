import db, { getOne } from '../db.js';

export function hydrateUser(req, res, next) {
    if (req.session && req.session.userId) {
        const user = getOne(
            "SELECT id, name, email, phone, role, is_active FROM users WHERE id = ? AND is_active = 1",
            [req.session.userId]
        );
        if (user) {
            req.user = user;
            res.locals.currentUser = user;
            res.locals.isAuthenticated = true;
            res.locals.isAdmin = user.role === 'admin';
            res.locals.isCustomer = user.role === 'customer';
        } else {
            req.session.userId = null;
            req.user = null;
            res.locals.currentUser = null;
            res.locals.isAuthenticated = false;
            res.locals.isAdmin = false;
            res.locals.isCustomer = false;
        }
    } else {
        req.user = null;
        res.locals.currentUser = null;
        res.locals.isAuthenticated = false;
        res.locals.isAdmin = false;
        res.locals.isCustomer = false;
    }
    next();
}

export function ensureAuthenticated(req, res, next) {
    if (req.user) {
        return next();
    }
    req.session.returnTo = req.originalUrl;
    req.session.flash = { error: 'Please log in to continue.' };
    return res.redirect('/login');
}

export function ensureAdmin(req, res, next) {
    if (!req.user) {
        req.session.returnTo = req.originalUrl;
        req.session.flash = { error: 'Admin access required. Please log in.' };
        return res.redirect('/login');
    }
    if (req.user.role !== 'admin') {
        return res.status(403).send(`
            <!DOCTYPE html>
            <html>
            <head><title>403 Forbidden</title><link rel="stylesheet" href="/css/style.css"></head>
            <body style="display: flex; align-items: center; justify-content: center; height: 100vh; font-family: sans-serif; background: #0F172A; color: #F8FAFC;">
                <div style="text-align: center; max-width: 500px; padding: 2rem;">
                    <h1 style="font-size: 3rem; color: #F59E0B; margin-bottom: 1rem;">403</h1>
                    <h2>Access Denied</h2>
                    <p style="color: #94A3B8; margin: 1rem 0 2rem;">You do not have administrative privileges to access this console.</p>
                    <a href="/" style="background: #D97706; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: 600;">Return Home</a>
                </div>
            </body>
            </html>
        `);
    }
    next();
}

export function ensureCustomer(req, res, next) {
    if (!req.user) {
        req.session.returnTo = req.originalUrl;
        req.session.flash = { error: 'Please log in to access your customer portal.' };
        return res.redirect('/login');
    }
    next();
}

export function guestOnly(req, res, next) {
    if (req.user) {
        if (req.user.role === 'admin') {
            return res.redirect('/admin/dashboard');
        }
        return res.redirect('/customer/dashboard');
    }
    next();
}
