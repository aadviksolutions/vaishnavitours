import db, { getOne, getAll, run } from '../db.js';

export class WhatsappService {
    static getSettings() {
        const baseUrl = getOne("SELECT value FROM settings WHERE key = 'openwa_base_url'")?.value || process.env.OPENWA_BASE_URL || 'http://localhost:2785';
        const apiKey = getOne("SELECT value FROM settings WHERE key = 'openwa_api_key'")?.value || process.env.OPENWA_API_KEY || '';
        const adminNumber = getOne("SELECT value FROM settings WHERE key = 'openwa_admin_number'")?.value || getOne("SELECT value FROM settings WHERE key = 'whatsapp_number'")?.value || '9244784443';
        const enabled = getOne("SELECT value FROM settings WHERE key = 'openwa_enabled'")?.value !== '0';

        return {
            baseUrl: baseUrl.replace(/\/+$/, ''),
            apiKey,
            adminNumber,
            enabled
        };
    }

    static renderTemplate(body, variables) {
        if (!body) return '';
        let res = body;
        for (const [key, value] of Object.entries(variables)) {
            const val = String(value !== null && value !== undefined ? value : '');
            res = res.replace(new RegExp(`\\{\\{\\s*${key}\\s*\\}\\}`, 'g'), val);
        }
        return res;
    }

    static normalizePhone(phone) {
        if (!phone) return null;
        let clean = String(phone).replace(/\D/g, '');
        if (clean.length === 10) {
            clean = '91' + clean;
        } else if (clean.startsWith('0') && clean.length === 11) {
            clean = '91' + clean.substring(1);
        }
        return clean.endsWith('@c.us') ? clean : `${clean}@c.us`;
    }

    static async dispatch(eventKey, recipientType, phone, message, bookingId = null) {
        const { baseUrl, apiKey, enabled } = this.getSettings();
        const chatId = this.normalizePhone(phone);
        if (!chatId) return false;

        let status = 'sent';
        let error = null;

        if (enabled && baseUrl) {
            try {
                const headers = { 'Content-Type': 'application/json' };
                if (apiKey) headers['api_key'] = apiKey;

                const res = await fetch(`${baseUrl}/sendText`, {
                    method: 'POST',
                    headers,
                    body: JSON.stringify({ chatId, text: message })
                });

                if (!res.ok) {
                    status = 'failed';
                    error = `HTTP ${res.status}: ${await res.text()}`;
                }
            } catch (err) {
                status = 'failed';
                error = err.message;
            }
        } else {
            status = 'simulated';
        }

        try {
            run(`
                INSERT INTO whatsapp_logs (
                    event_key, recipient_type, to_number, message,
                    status, response, related_type, related_id, created_at, updated_at
                ) VALUES (
                    ?, ?, ?, ?,
                    ?, ?, 'booking', ?, datetime('now'), datetime('now')
                )
            `, [eventKey, recipientType, phone, message, status, error, bookingId]);
        } catch (e) {
            console.error('Error logging WhatsApp notification:', e);
        }

        return status === 'sent' || status === 'simulated';
    }

    static async sendEvent(eventKey, variables, customerPhone = null, bookingId = null) {
        const template = getOne("SELECT * FROM whatsapp_templates WHERE event_key = ? AND is_active = 1", [eventKey]);
        if (!template) return false;

        const message = this.renderTemplate(template.template_body, variables);
        const { adminNumber } = this.getSettings();

        if (template.send_to_customer && customerPhone) {
            await this.dispatch(eventKey, 'customer', customerPhone, message, bookingId);
        }

        if (template.send_to_admin && adminNumber) {
            const adminMessage = `📢 *[ADMIN COPY]* — ${template.event_label}\n\n${message}`;
            await this.dispatch(eventKey, 'admin', adminNumber, adminMessage, bookingId);
        }

        return true;
    }
}

export default WhatsappService;
