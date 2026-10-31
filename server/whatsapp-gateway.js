// Live WhatsApp Multi-Device Gateway for Vaishnavi Tours
// Powered by @whiskeysockets/baileys (Official WhatsApp Multi-Device WebSocket Protocol)
// Provides OpenWA-compatible REST API for Laravel integration

import http from 'http';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import QRCode from 'qrcode';
import pino from 'pino';
import makeWASocket, {
    useMultiFileAuthState,
    DisconnectReason,
    Browsers,
    fetchLatestBaileysVersion
} from '@whiskeysockets/baileys';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const PORT = parseInt(process.env.PORT || '2785', 10);
const AUTH_DIR = path.resolve(__dirname, '..', '.whatsapp_auth');

// In-memory gateway state
const state = {
    status: 'initializing', // 'initializing' | 'qr_ready' | 'ready' | 'disconnected'
    phone: null,
    pushName: null,
    qrCodeDataUrl: null,
    rawQr: null,
    connectedAt: null,
    messagesCount: 0
};

const messageLogs = [];
let sock = null;
let isStarting = false;

// Ensure auth directory exists
if (!fs.existsSync(AUTH_DIR)) {
    fs.mkdirSync(AUTH_DIR, { recursive: true });
}

const logger = pino({ level: 'silent' });

async function initWhatsApp() {
    if (isStarting) return;
    isStarting = true;

    try {
        const { state: authState, saveCreds } = await useMultiFileAuthState(AUTH_DIR);
        const { version } = await fetchLatestBaileysVersion().catch(() => ({ version: [2, 3000, 1015901307] }));

        sock = makeWASocket({
            version,
            auth: authState,
            logger,
            printQRInTerminal: true,
            browser: Browsers.windows('Desktop'),
            syncFullHistory: false,
            generateHighQualityLinkPreview: false
        });

        sock.ev.on('creds.update', saveCreds);

        sock.ev.on('connection.update', async (update) => {
            const { connection, lastDisconnect, qr } = update;

            if (qr) {
                state.rawQr = qr;
                try {
                    state.qrCodeDataUrl = await QRCode.toDataURL(qr, {
                        margin: 2,
                        scale: 8,
                        errorCorrectionLevel: 'M'
                    });
                    state.status = 'qr_ready';
                    console.log('\n======================================================');
                    console.log('📲 Real WhatsApp QR Code Ready!');
                    console.log('Scan it on: http://localhost:8000/admin/whatsapp/scan');
                    console.log('======================================================\n');
                } catch (qrErr) {
                    console.error('Failed to generate QR data URL:', qrErr);
                }
            }

            if (connection === 'open') {
                const rawId = sock?.user?.id || '';
                const cleanPhone = rawId.split(':')[0].replace(/[^0-9]/g, '');
                state.status = 'ready';
                state.phone = cleanPhone;
                state.pushName = sock?.user?.name || 'Vaishnavi Tours';
                state.qrCodeDataUrl = null;
                state.rawQr = null;
                state.connectedAt = new Date().toISOString();

                console.log('\n======================================================');
                console.log(`✅ WhatsApp Linked Successfully to +${cleanPhone} (${state.pushName})`);
                console.log('======================================================\n');
            }

            if (connection === 'close') {
                const statusCode = lastDisconnect?.error?.output?.statusCode;
                const shouldReconnect = statusCode !== DisconnectReason.loggedOut;

                console.log(`[WhatsApp Gateway] Connection closed (code: ${statusCode}). Reconnect: ${shouldReconnect}`);

                state.status = 'disconnected';

                if (statusCode === DisconnectReason.loggedOut) {
                    console.log('[WhatsApp Gateway] Device logged out. Clearing auth cache...');
                    try {
                        fs.rmSync(AUTH_DIR, { recursive: true, force: true });
                        fs.mkdirSync(AUTH_DIR, { recursive: true });
                    } catch (_) {}
                    state.phone = null;
                    state.pushName = null;
                    state.qrCodeDataUrl = null;
                }

                // Reconnect automatically
                isStarting = false;
                setTimeout(() => {
                    initWhatsApp();
                }, 3000);
            }
        });

    } catch (err) {
        console.error('[WhatsApp Gateway] Init Error:', err);
        state.status = 'disconnected';
    } finally {
        isStarting = false;
    }
}

// Normalize phone to WhatsApp JID format (e.g. 919244784443@s.whatsapp.net)
function formatJid(chatId) {
    let clean = (chatId || '').replace(/[^0-9]/g, '');
    if (clean.length === 10) clean = '91' + clean;
    return `${clean}@s.whatsapp.net`;
}

// HTTP Server
const server = http.createServer(async (req, res) => {
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
        req.on('end', async () => {
            try {
                const data = JSON.parse(body || '{}');
                const target = data.chatId || data.phone;
                const text = data.text || data.message;

                if (!target || !text) {
                    res.writeHead(400, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({ error: 'chatId and text are required' }));
                    return;
                }

                if (state.status !== 'ready' || !sock) {
                    res.writeHead(503, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({
                        success: false,
                        error: 'WhatsApp device is not linked or not online. Please scan QR first.'
                    }));
                    return;
                }

                const jid = formatJid(target);
                const sendResult = await sock.sendMessage(jid, { text });

                const logEntry = {
                    id: sendResult?.key?.id || ('wa_' + Date.now()),
                    chatId: target,
                    jid,
                    text,
                    timestamp: new Date().toISOString(),
                    receivedAt: new Date().toLocaleTimeString()
                };

                messageLogs.unshift(logEntry);
                if (messageLogs.length > 50) messageLogs.pop();
                state.messagesCount++;

                console.log(`[WhatsApp Outgoing] Sent to ${jid}: ${text.slice(0, 50)}...`);

                res.writeHead(200, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({
                    success: true,
                    status: 'sent',
                    id: logEntry.id,
                    chatId: target
                }));
            } catch (err) {
                console.error('[WhatsApp Send Error]', err);
                res.writeHead(500, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ success: false, error: err.message }));
            }
        });
        return;
    }

    // 2. Health & Status Check
    if (pathname === '/health' || pathname === '/api/health' || pathname === '/status') {
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({
            status: state.status,
            phone: state.phone,
            pushName: state.pushName,
            qrCode: state.qrCodeDataUrl,
            engine: 'Baileys Multi-Device (Real WhatsApp)',
            messagesCount: state.messagesCount
        }));
        return;
    }

    // 3. Sessions List
    if (req.method === 'GET' && pathname === '/api/sessions') {
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify([{
            id: 'default',
            name: 'default',
            status: state.status,
            phone: state.phone,
            pushName: state.pushName,
            qr: state.qrCodeDataUrl
        }]));
        return;
    }

    // 4. Session Info
    if (req.method === 'GET' && (pathname === '/api/sessions/default' || pathname.match(/^\/api\/sessions\/[^\/]+$/))) {
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({
            id: 'default',
            name: 'default',
            status: state.status,
            phone: state.phone,
            pushName: state.pushName,
            qr: state.qrCodeDataUrl
        }));
        return;
    }

    // 5. Session QR code
    if (req.method === 'GET' && pathname.endsWith('/qr')) {
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({
            status: state.status,
            qrCode: state.qrCodeDataUrl
        }));
        return;
    }

    // 6. Session Start / Refresh
    if (req.method === 'POST' && pathname.endsWith('/start')) {
        if (state.status !== 'ready' && !isStarting) {
            initWhatsApp();
        }
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({
            status: state.status,
            qrCode: state.qrCodeDataUrl
        }));
        return;
    }

    // 7. Logout / Disconnect
    if (req.method === 'POST' && (pathname.endsWith('/logout') || pathname === '/disconnect')) {
        try {
            if (sock) {
                await sock.logout().catch(() => {});
            }
            fs.rmSync(AUTH_DIR, { recursive: true, force: true });
            fs.mkdirSync(AUTH_DIR, { recursive: true });
        } catch (_) {}

        state.status = 'disconnected';
        state.phone = null;
        state.pushName = null;
        state.qrCodeDataUrl = null;

        setTimeout(() => { initWhatsApp(); }, 1500);

        if (pathname === '/disconnect') {
            res.writeHead(302, { 'Location': '/' });
            res.end();
            return;
        }

        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ success: true, status: 'disconnected' }));
        return;
    }

    // 8. Gateway Web Dashboard
    if (pathname === '/' || pathname === '/dashboard') {
        res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
        const isReady = state.status === 'ready';

        const logsHtml = messageLogs.map(m => `
            <div style="background:#fff;border-radius:8px;padding:12px;margin-bottom:10px;box-shadow:0 1px 3px rgba(0,0,0,0.06);border-left:4px solid #25D366;">
                <div style="display:flex;justify-content:space-between;font-size:12px;color:#64748b;margin-bottom:4px;">
                    <strong>To: ${escapeHtml(m.chatId)}</strong>
                    <span>${m.receivedAt}</span>
                </div>
                <div style="white-space:pre-wrap;font-size:13px;color:#1e293b;">${escapeHtml(m.text)}</div>
            </div>
        `).join('') || '<p style="color:#94a3b8;text-align:center;padding:24px;">No messages sent through gateway yet.</p>';

        res.end(`<!DOCTYPE html>
<html>
<head>
    <title>WhatsApp Real Gateway — Vaishnavi Tours</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta http-equiv="refresh" content="5">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background:#f1f5f9; margin:0; padding:24px; color:#0f172a; }
        .container { max-width: 820px; margin: 0 auto; }
        .header { background:#075e54; color:#fff; padding:20px 24px; border-radius:12px; margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; }
        .badge { background:#25D366; color:#fff; padding:6px 14px; border-radius:20px; font-size:13px; font-weight:700; }
        .badge-warning { background:#f59e0b; }
        .card { background:#fff; border-radius:12px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.08); margin-bottom:20px; }
        .btn { background:#25D366; color:#fff; border:none; padding:10px 18px; border-radius:6px; font-weight:600; cursor:pointer; text-decoration:none; display:inline-block; }
        .btn-danger { background:#ef4444; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h2 style="margin:0; font-size:1.35rem;">📱 WhatsApp Live Gateway</h2>
                <p style="margin:4px 0 0 0;font-size:13px;opacity:0.9;">Port ${PORT} — Multi-Device Engine for Vaishnavi Tours</p>
            </div>
            <span class="badge ${isReady ? '' : 'badge-warning'}">● ${isReady ? 'Device Connected' : (state.status === 'qr_ready' ? 'Scan QR Code' : state.status)}</span>
        </div>

        <div class="card">
            <h3 style="margin-top:0; font-size:1.15rem;">Device Linking Status</h3>
            ${isReady ? `
                <div style="background:#f0fdf4; border:1px solid #86efac; border-radius:8px; padding:16px; margin-bottom:16px;">
                    <div style="font-size:1.1rem; font-weight:700; color:#166534;">✓ WhatsApp Number Connected & Live!</div>
                    <div style="margin-top:6px; color:#15803d; font-size:14px; line-height:1.6;">
                        <strong>Phone Number:</strong> +${escapeHtml(state.phone)}<br>
                        <strong>Profile Name:</strong> ${escapeHtml(state.pushName)}<br>
                        <strong>Connected At:</strong> ${state.connectedAt}
                    </div>
                </div>
                <form method="POST" action="/disconnect" onsubmit="return confirm('Disconnect this WhatsApp device?');">
                    <button type="submit" class="btn btn-danger">🔴 Disconnect WhatsApp Account</button>
                </form>
            ` : `
                <div style="display:flex; gap:24px; align-items:center; flex-wrap:wrap;">
                    ${state.qrCodeDataUrl ? `
                        <img src="${state.qrCodeDataUrl}" alt="Scan QR Code" style="width:220px; height:220px; border:2px solid #cbd5e1; border-radius:8px; padding:6px; background:#fff;">
                    ` : `
                        <div style="width:220px; height:220px; border:2px dashed #cbd5e1; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#64748b;">
                            Generating QR...
                        </div>
                    `}
                    <div style="max-width:440px;">
                        <h4 style="margin:0 0 8px 0; color:#1e293b;">How to link your WhatsApp:</h4>
                        <ol style="margin:0; padding-left:20px; font-size:13.5px; line-height:1.7; color:#475569;">
                            <li>Open <strong>WhatsApp</strong> on your mobile phone (9244784443).</li>
                            <li>Tap <strong>3 dots (⋮)</strong> on Android or <strong>Settings</strong> on iPhone.</li>
                            <li>Tap <strong>Linked Devices</strong>.</li>
                            <li>Tap <strong>Link a Device</strong> and point your camera at the QR code.</li>
                        </ol>
                        <p style="margin:12px 0 0 0; font-size:12.5px; color:#16a34a; font-weight:600;">
                            ✨ You can also scan on the Laravel Admin: <a href="http://localhost:8000/admin/whatsapp/scan" target="_blank" style="color:#0284c7;">/admin/whatsapp/scan</a>
                        </p>
                    </div>
                </div>
            `}
        </div>

        <h3 style="margin-bottom:12px; font-size:1.1rem; color:#334155;">Recent Messages Log (${state.messagesCount})</h3>
        ${logsHtml}
    </div>
</body>
</html>`);
        return;
    }

    res.writeHead(404, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify({ error: 'Endpoint not found' }));
});

function escapeHtml(text) {
    return String(text || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

server.listen(PORT, '0.0.0.0', () => {
    console.log(`=======================================================`);
    console.log(`[WhatsApp Gateway] Listening on http://localhost:${PORT}`);
    console.log(`[WhatsApp Gateway] Initializing Baileys engine...`);
    console.log(`=======================================================`);
    initWhatsApp();
});
