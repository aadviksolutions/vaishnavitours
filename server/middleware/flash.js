export function flashMiddleware(req, res, next) {
    // Read and clear flash from session
    if (req.session && req.session.flash) {
        res.locals.flash = req.session.flash;
        delete req.session.flash;
    } else {
        res.locals.flash = {};
    }

    // Helper to set flash on response redirect
    res.setFlash = (type, message) => {
        if (!req.session) return;
        req.session.flash = req.session.flash || {};
        req.session.flash[type] = message;
    };

    // Helper for old form input retention
    if (req.session && req.session.oldInput) {
        res.locals.old = (key, fallback = '') => {
            const val = req.session.oldInput?.[key];
            return val !== undefined ? val : fallback;
        };
        delete req.session.oldInput;
    } else {
        res.locals.old = (key, fallback = '') => fallback;
    }

    res.setOldInput = (input) => {
        if (!req.session) return;
        req.session.oldInput = { ...input };
    };

    next();
}

export default flashMiddleware;
