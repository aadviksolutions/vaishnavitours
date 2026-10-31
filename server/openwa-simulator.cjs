// OpenWA Localhost Simulator / Development Gateway
// Supports WhatsApp sendText, Session Management, and QR Code Simulation
// Runs with zero dependencies on pure Node.js

const http = require('http');

const PORT = process.env.PORT || 2785;
const messages = [];

// Realistic SVG QR Code Data URL for immediate visual rendering
const sampleQrSvg = encodeURIComponent(`
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="200" height="200">
  <rect width="200" height="200" fill="#ffffff"/>
  <!-- Top-left finder -->
  <rect x="20" y="20" width="50" height="50" fill="#000000"/>
  <rect x="27" y="27" width="36" height="36" fill="#ffffff"/>
  <rect x="35" y="35" width="20" height="20" fill="#000000"/>
  <!-- Top-right finder -->
  <rect x="130" y="20" width="50" height="50" fill="#000000"/>
  <rect x="137" y="27" width="36" height="36" fill="#ffffff"/>
  <rect x="145" y="35" width="20" height="20" fill="#000000"/>
  <!-- Bottom-left finder -->
  <rect x="20" y="130" width="50" height="50" fill="#000000"/>
  <rect x="27" y="137" width="36" height="36" fill="#ffffff"/>
  <rect x="35" y="145" width="20" height="20" fill="#000000"/>
  <!-- Random QR data pattern blocks -->
  <rect x="80" y="25" width="10" height="20" fill="#000"/>
  <rect x="100" y="35" width="15" height="10" fill="#000"/>
  <rect x="25" y="80" width="20" height="10" fill="#000"/>
  <rect x="85" y="70" width="30" height="30" fill="#25D366"/>
  <text x="100" y="90" font-family="sans-serif" font-size="16" fill="#fff" text-anchor="middle" font-weight="bold">WA</text>
  <rect x="130" y="85" width="15" height="15" fill="#000"/>
  <rect x="155" y="75" width="20" height="20" fill="#000"/>
  <rect x="80" y="120" width="20" height="10" fill="#000"/>
  <rect x="110" y="130" width="15" height="30" fill="#000"/>
  <rect x="140" y="125" width="25" height="15" fill="#000"/>
  <rect x="135" y="155" width="35" height="20" fill="#000"/>
  <rect x="85" y="150" width="15" height="25" fill="#000"/>
</svg>
`);

const qrDataUrl = `data:image/svg+xml;utf8,${sampleQrSvg}`;

let sessionState = {
    id: 'default',
    name: 'default',
    status: 'qr_ready', // 'qr_ready' | 'ready' | 'disconnected'
    phone: null,
    pushName: null,
    connectedAt: null
};

const server = http.createServer((req, res) => {
    // CORS headers
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-API-Key');

    if (req.method === 'OPTIONS') {
        res.writeHead(204);
        res.end();
        return;
    }

    const parsedUrl = new URL(req.url, `http://${req.headers.host || 'localhost'}`);
    const pathname = parsedUrl.pathname;

    // 1. Send Text Message
    if (req.method === 'POST' && (pathname === '/sendText' || pathname.endsWith('/sendText') || pathname.endsWith('/send-text'))) {
        let body = '';
        req.on('data', chunk => { body += chunk; });
        req.on('end', () => {
            try {
                const data = JSON.parse(body || '{}');
                const logEntry = {
                    id: 'sim_' + Date.now(),
                    chatId: data.chatId || data.phone || 'unknown',
                    text: data.text || data.message || '',
                    timestamp: new Date().toISOString(),
                    receivedAt: new Date().toLocaleTimeString()
                };

                messages.unshift(logEntry);
                if (messages.length > 50) messages.pop();

                console.log(`\n========================================`);
                console.log(`[WHATSAPP MESSAGE RECEIVED]`);
                console.log(`To: ${logEntry.chatId}`);
                console.log(`Time: ${logEntry.receivedAt}`);
                console.log(`Message:\n${logEntry.text}`);
                console.log(`========================================\n`);

                res.writeHead(200, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({
                    success: true,
                    status: 'sent',
                    id: logEntry.id,
                    chatId: logEntry.chatId,
                    timestamp: logEntry.timestamp
                }));
            } catch (err) {
                res.writeHead(400, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ error: err.message }));
            }
        });
        return;
    }

    // 2. Sessions list
    if (req.method === 'GET' && pathname === '/api/sessions') {
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify([sessionState]));
        return;
    }

    // 3. Single session info
    if (req.method === 'GET' && (pathname === '/api/sessions/default' || pathname.match(/^\/api\/sessions\/[^\/]+$/))) {
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify(sessionState));
        return;
    }

    // 4. Session QR code
    if (req.method === 'GET' && pathname.endsWith('/qr')) {
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({
            status: sessionState.status,
            qrCode: sessionState.status === 'ready' ? null : qrDataUrl
        }));
        return;
    }

    // 5. Start session
    if (req.method === 'POST' && pathname.endsWith('/start')) {
        if (sessionState.status !== 'ready') {
            sessionState.status = 'qr_ready';
        }
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify(sessionState));
        return;
    }

    // 6. Logout / Disconnect session
    if (req.method === 'POST' && (pathname.endsWith('/logout') || pathname === '/simulate/disconnect')) {
        sessionState.status = 'qr_ready';
        sessionState.phone = null;
        sessionState.pushName = null;
        sessionState.connectedAt = null;

        if (pathname === '/simulate/disconnect') {
            res.writeHead(302, { 'Location': '/' });
            res.end();
            return;
        }

        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ success: true, status: 'disconnected' }));
        return;
    }

    // 7. Simulate phone scan
    if (req.method === 'POST' && (pathname === '/simulate/scan' || pathname.endsWith('/simulate-scan'))) {
        let body = '';
        req.on('data', chunk => { body += chunk; });
        req.on('end', () => {
            let phone = '919876543210';
            let pushName = 'Vaishnavi Tours';
            try {
                const params = new URLSearchParams(body);
                if (params.get('phone')) phone = params.get('phone');
                if (params.get('pushName')) pushName = params.get('pushName');
            } catch (_) {}

            sessionState.status = 'ready';
            sessionState.phone = phone;
            sessionState.pushName = pushName;
            sessionState.connectedAt = new Date().toISOString();

            console.log(`\n[OpenWA Simulator] Simulated QR Scan: Linked to +${phone} (${pushName})\n`);

            if (pathname === '/simulate/scan') {
                res.writeHead(302, { 'Location': '/' });
                res.end();
                return;
            }

            res.writeHead(200, { 'Content-Type': 'application/json' });
            res.end(JSON.stringify(sessionState));
        });
        return;
    }

    // 8. Health / Status
    if (pathname === '/health' || pathname === '/api/health' || pathname === '/status') {
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({
            status: sessionState.status,
            phone: sessionState.phone,
            pushName: sessionState.pushName,
            qrCode: sessionState.status === 'ready' ? null : qrDataUrl,
            engine: 'OpenWA Gateway (Localhost)',
            messagesCount: messages.length
        }));
        return;
    }

    // 9. Web UI Dashboard
    if (pathname === '/' || pathname === '/dashboard') {
        res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
        const rows = messages.map(m => `
            <div style="background:#fff;border-radius:8px;padding:15px;margin-bottom:12px;box-shadow:0 1px 3px rgba(0,0,0,0.1);border-left:4px solid #25D366;">
                <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:13px;color:#666;">
                    <span><strong>To:</strong> ${escapeHtml(m.chatId)}</span>
                    <span>${m.receivedAt}</span>
                </div>
                <div style="white-space:pre-wrap;font-family:inherit;font-size:14px;color:#222;line-height:1.5;">${escapeHtml(m.text)}</div>
            </div>
        `).join('') || '<p style="color:#888;text-align:center;padding:30px;">No messages received yet.</p>';

        const isReady = sessionState.status === 'ready';

        res.end(`<!DOCTYPE html>
<html>
<head>
    <title>OpenWA Localhost Gateway</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta http-equiv="refresh" content="6">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background:#f0f2f5; margin:0; padding:20px; color:#111; }
        .container { max-width: 860px; margin: 0 auto; }
        .header { background:#128C7E; color:#fff; padding:20px; border-radius:10px; margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; }
        .badge { background:#25D366; color:#fff; padding:4px 12px; border-radius:12px; font-size:12px; font-weight:600; }
        .badge-warning { background:#f59e0b; }
        .card { background:#fff; border-radius:10px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.1); margin-bottom:20px; }
        .btn { background:#25D366; color:#fff; border:none; padding:8px 16px; border-radius:6px; font-weight:600; cursor:pointer; text-decoration:none; display:inline-block; }
        .btn-danger { background:#ef4444; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h2 style="margin:0;">💬 OpenWA WhatsApp Gateway (Localhost)</h2>
                <p style="margin:5px 0 0 0;font-size:14px;opacity:0.9;">Port ${PORT} — Connected to Vaishnavi Tours</p>
            </div>
            <span class="badge ${isReady ? '' : 'badge-warning'}">● ${isReady ? 'Device Connected' : 'QR Scan Ready'}</span>
        </div>

        <div class="card">
            <h3 style="margin-top:0;">📲 WhatsApp Device Connection</h3>
            ${isReady ? `
                <div style="background:#f0fdf4; border:1px solid #86efac; border-radius:8px; padding:15px; margin-bottom:15px;">
                    <div style="font-size:16px; font-weight:700; color:#166534;">✓ WhatsApp Phone Linked!</div>
                    <div style="margin-top:6px; color:#15803d; font-size:14px;">
                        <strong>Phone Number:</strong> +${escapeHtml(sessionState.phone)}<br>
                        <strong>Push Name:</strong> ${escapeHtml(sessionState.pushName)}<br>
                        <strong>Connected At:</strong> ${sessionState.connectedAt}
                    </div>
                </div>
                <form method="POST" action="/simulate/disconnect">
                    <button type="submit" class="btn btn-danger">🔴 Disconnect Phone / Reset QR</button>
                </form>
            ` : `
                <div style="display:flex; gap:20px; align-items:center; flex-wrap:wrap;">
                    <img src="${qrDataUrl}" alt="QR" style="width:160px; height:160px; border:2px solid #ddd; border-radius:8px; padding:5px; background:#fff;">
                    <div>
                        <p style="margin:0 0 10px 0; font-size:14px; color:#555;">
                            Point phone camera or test the automatic scanner flow in Laravel Admin:
                        </p>
                        <form method="POST" action="/simulate/scan">
                            <input type="text" name="phone" value="919876543210" style="padding:6px 10px; border:1px solid #ccc; border-radius:4px; font-size:13px; width:150px;" placeholder="Phone">
                            <button type="submit" class="btn" style="margin-left:6px;">📱 Simulate Scan (Connect)</button>
                        </form>
                    </div>
                </div>
            `}
        </div>

        <h3 style="margin-bottom:15px;color:#333;">Live Messages Log (${messages.length})</h3>
        ${rows}
    </div>
</body>
</html>`);
        return;
    }

    res.writeHead(404, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ error: 'Not found' }));
});

function escapeHtml(text) {
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

server.listen(PORT, '0.0.0.0', () => {
    console.log(`[OpenWA Gateway] Running at http://localhost:${PORT}`);
    console.log(`[OpenWA Gateway] Dashboard available at http://localhost:${PORT}/`);
});

