import express from 'express';
import path from 'path';
import { fileURLToPath } from 'url';
import cookieParser from 'cookie-parser';
import session from 'express-session';
import methodOverride from 'method-override';
import cors from 'cors';
import config from './config.js';
import setupViewGlobals from './helpers/viewHelpers.js';
import { hydrateUser } from './middleware/auth.js';
import flashMiddleware from './middleware/flash.js';
import { runPaymentMigrations } from './migrations/payment_migrations.js';

// Run payment migrations on server boot
runPaymentMigrations();

import publicRoutes from './routes/publicRoutes.js';
import authRoutes from './routes/authRoutes.js';
import customerRoutes from './routes/customerRoutes.js';
import adminRoutes from './routes/adminRoutes.js';
import apiRoutes from './routes/apiRoutes.js';
import paymentRoutes from './routes/paymentRoutes.js';

import { getOne } from './db.js';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();

// Trust reverse proxies (Vercel, Caddy, Cloudflare, Nginx)
app.set('trust proxy', 1);

// View Engine Setup (EJS)
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

// Core Middlewares
app.use(cors());
app.use(express.urlencoded({ extended: true }));
app.use(express.json({
    verify: (req, res, buf) => {
        req.rawBody = buf;
    }
}));
app.use(cookieParser());
app.use(methodOverride('_method'));

// Static assets from public/ directory
app.use(express.static(path.join(__dirname, '..', 'public')));

// Session Setup
app.use(session({
    secret: config.app.sessionSecret,
    resave: false,
    saveUninitialized: false,
    cookie: {
        maxAge: 1000 * 60 * 60 * 24 * 7, // 7 days
        httpOnly: true,
        secure: config.app.env === 'production',
        sameSite: 'lax'
    }
}));

// Production Health Check Endpoints
app.get('/health', (req, res) => {
    try {
        const dbCheck = getOne("SELECT 1 as ok");
        res.status(200).json({
            status: 'healthy',
            uptime_seconds: Math.floor(process.uptime()),
            timestamp: new Date().toISOString(),
            database: dbCheck?.ok === 1 ? 'connected' : 'degraded',
            environment: config.app.env
        });
    } catch (err) {
        res.status(503).json({
            status: 'unhealthy',
            error: 'Database connectivity failed'
        });
    }
});
app.get('/api/health', (req, res) => res.redirect('/health'));

// Setup Auth, Flash, and Globals
app.use(hydrateUser);
app.use(flashMiddleware);
setupViewGlobals(app);

// Mount API routes
app.use('/api/payments', paymentRoutes);
app.use('/api', apiRoutes);
app.use(apiRoutes);

// Mount Public & Auth Routes
app.use('/', paymentRoutes);
app.use('/', authRoutes);
app.use('/', publicRoutes);

// Mount Customer Portal
app.use('/customer', customerRoutes);

// Friendly Customer root aliases (Section 12 requirement)
app.get('/dashboard', (req, res) => res.redirect('/customer/dashboard'));
app.get('/bookings', (req, res) => res.redirect('/customer/bookings'));
app.get('/payments', (req, res) => res.redirect('/customer/bookings?filter=payments'));
app.get('/invoices', (req, res) => res.redirect('/customer/bookings?filter=payments'));
app.get('/notifications', (req, res) => res.redirect('/customer/notifications'));
app.get('/profile', (req, res) => res.redirect('/customer/profile'));

// Mount Admin Dispatch Panel
app.use('/admin', adminRoutes);

// 404 Handler
app.use((req, res) => {
    res.status(404).render('errors/404', {
        pageTitle: '404 - Page Not Found | Vaishnavi Tours'
    }, (err, html) => {
        if (err) {
            return res.status(404).send('<h1>404 - Page Not Found</h1><p><a href="/">Return Home</a></p>');
        }
        res.status(404).send(html);
    });
});

// 500 Error Handler
app.use((err, req, res, next) => {
    console.error('Internal Server Error:', err);
    res.status(500).render('errors/500', {
        pageTitle: '500 - Server Error | Vaishnavi Tours',
        error: err
    }, (renderErr, html) => {
        if (renderErr) {
            return res.status(500).send('<h1>500 - Internal Server Error</h1><p>An unexpected condition occurred. Our team has been notified. Please return to <a href="/">Vaishnavi Tours Home</a>.</p>');
        }
        res.status(500).send(html);
    });
});

export default app;
