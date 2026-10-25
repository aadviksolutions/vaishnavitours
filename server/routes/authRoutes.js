import express from 'express';
import bcrypt from 'bcryptjs';
import db, { getOne, run, transaction } from '../db.js';
import { guestOnly } from '../middleware/auth.js';

const router = express.Router();

// Rate limiting for login attempts (Brute-force protection)
const loginAttempts = new Map();

function isRateLimited(key) {
    const record = loginAttempts.get(key);
    if (!record) return false;
    const now = Date.now();
    if (now > record.resetTime) {
        loginAttempts.delete(key);
        return false;
    }
    return record.count >= 5;
}

function recordFailedLogin(key) {
    const now = Date.now();
    const record = loginAttempts.get(key) || { count: 0, resetTime: now + 15 * 60 * 1000 };
    if (now > record.resetTime) {
        record.count = 0;
        record.resetTime = now + 15 * 60 * 1000;
    }
    record.count++;
    loginAttempts.set(key, record);
}

function resetLoginAttempts(key) {
    loginAttempts.delete(key);
}

router.get('/login', guestOnly, (req, res) => {
    res.render('auth/login', {
        pageTitle: 'Login | Vaishnavi Tours',
        errors: res.locals.flash?.error ? { email: res.locals.flash.error } : {}
    });
});

router.post('/login', guestOnly, (req, res) => {
    const { email, password } = req.body;
    if (!email || !password) {
        res.setFlash('error', 'Please provide both email/phone and password.');
        res.setOldInput(req.body);
        return res.redirect('/login');
    }

    const cleanInput = String(email).trim();
    const ip = req.ip || req.headers['x-forwarded-for'] || 'unknown';
    const rateLimitKey = `${ip}_${cleanInput.toLowerCase()}`;

    if (isRateLimited(rateLimitKey) || isRateLimited(ip)) {
        res.setFlash('error', 'Too many failed login attempts. Please wait 15 minutes before trying again.');
        res.setOldInput(req.body);
        return res.redirect('/login');
    }

    const user = getOne(
        "SELECT * FROM users WHERE (email = ? OR phone = ?) AND is_active = 1",
        [cleanInput, cleanInput]
    );

    if (!user || !bcrypt.compareSync(password, user.password)) {
        recordFailedLogin(rateLimitKey);
        recordFailedLogin(ip);
        res.setFlash('error', 'The provided credentials do not match our records.');
        res.setOldInput(req.body);
        return res.redirect('/login');
    }

    // Reset rate limits on success
    resetLoginAttempts(rateLimitKey);
    resetLoginAttempts(ip);

    req.session.userId = user.id;

    const returnUrl = req.session.returnTo;
    delete req.session.returnTo;

    if (user.role === 'admin') {
        res.setFlash('success', 'Welcome back, Admin!');
        return res.redirect(returnUrl || '/admin/dashboard');
    }

    res.setFlash('success', 'Logged in successfully!');
    return res.redirect(returnUrl || '/customer/dashboard');
});

router.get('/register', guestOnly, (req, res) => {
    res.render('auth/register', {
        pageTitle: 'Register Account | Vaishnavi Tours',
        errors: {}
    });
});

router.post('/register', guestOnly, (req, res) => {
    const { name, email, phone, password, password_confirmation, city, address } = req.body;

    if (!name || !email || !phone || !password) {
        res.setFlash('error', 'Please fill in all required fields.');
        res.setOldInput(req.body);
        return res.redirect('/register');
    }

    if (password !== password_confirmation) {
        res.setFlash('error', 'Password confirmation does not match.');
        res.setOldInput(req.body);
        return res.redirect('/register');
    }

    let cleanPhone = String(phone).replace(/[^0-9]/g, '');
    if (cleanPhone.length === 12 && cleanPhone.startsWith('91')) {
        cleanPhone = cleanPhone.substring(2);
    } else if (cleanPhone.length === 11 && cleanPhone.startsWith('0')) {
        cleanPhone = cleanPhone.substring(1);
    }

    if (cleanPhone.length !== 10) {
        res.setFlash('error', 'Please enter a valid 10-digit mobile number.');
        res.setOldInput(req.body);
        return res.redirect('/register');
    }

    const cleanEmail = String(email).trim().toLowerCase();

    // Check duplicate email or phone
    const existing = getOne("SELECT id FROM users WHERE email = ? OR phone = ?", [cleanEmail, cleanPhone]);
    if (existing) {
        res.setFlash('error', 'An account with this email or phone number already exists.');
        res.setOldInput(req.body);
        return res.redirect('/register');
    }

    try {
        const hashedPassword = bcrypt.hashSync(password, 10);
        const newUser = transaction(() => {
            const userRes = run(`
                INSERT INTO users (
                    name, email, phone, password, role, is_active, created_at, updated_at
                ) VALUES (?, ?, ?, ?, 'customer', 1, datetime('now'), datetime('now'))
            `, [name.trim(), cleanEmail, cleanPhone, hashedPassword]);

            run(`
                INSERT INTO customers (
                    user_id, address, city, state, pincode, created_at, updated_at
                ) VALUES (?, ?, ?, 'Chhattisgarh', '495001', datetime('now'), datetime('now'))
            `, [userRes.lastInsertRowid, address || null, city || 'Bilaspur']);

            return userRes.lastInsertRowid;
        })();

        req.session.userId = newUser;
        res.setFlash('success', 'Your account has been created successfully! Welcome to Vaishnavi Tours.');
        res.redirect('/customer/dashboard');
    } catch (err) {
        console.error('Registration error:', err);
        res.setFlash('error', 'Registration failed. Please try again.');
        res.setOldInput(req.body);
        res.redirect('/register');
    }
});

router.post('/logout', (req, res) => {
    req.session.destroy(() => {
        res.redirect('/login');
    });
});

router.get('/forgot-password', (req, res) => {
    res.render('auth/forgot-password', {
        pageTitle: 'Forgot Password | Vaishnavi Tours'
    });
});

router.post('/forgot-password', (req, res) => {
    res.setFlash('info', 'If an account exists with this email or phone, a password reset link has been dispatched.');
    res.redirect('/login');
});

export default router;
