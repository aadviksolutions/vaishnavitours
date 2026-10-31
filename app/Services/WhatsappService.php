<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Enquiry;
use App\Models\Setting;
use App\Models\WhatsappLog;
use App\Models\WhatsappTemplate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WhatsappService — integrates with OpenWA self-hosted API Gateway.
 *
 * OpenWA endpoint pattern: POST {base_url}/sendText
 * Payload: { "chatId": "919XXXXXXXXX@c.us", "text": "message" }
 *
 * Admin must configure OPENWA_BASE_URL and OPENWA_API_KEY in .env
 * or via Admin → Settings → WhatsApp Integration.
 */
class WhatsappService
{
    private string $baseUrl;

    private ?string $apiKey;

    private ?string $adminNumber;

    private bool $enabled;

    public function __construct()
    {
        $this->baseUrl = rtrim(
            Setting::get('openwa_base_url') ?: config('services.openwa.base_url', ''),
            '/'
        );
        $this->apiKey = Setting::get('openwa_api_key') ?: config('services.openwa.api_key');
        $this->adminNumber = Setting::get('openwa_admin_number') ?: Setting::get('whatsapp_number');
        $this->enabled = (bool) Setting::get('openwa_enabled', '1');
    }

    /**
     * Send a WhatsApp notification for a specific event.
     * Sends to customer and/or admin based on template settings.
     */
    public function sendEvent(
        string $eventKey,
        array $variables,
        ?string $customerPhone = null,
        mixed $related = null
    ): void {
        try {
            if (! $this->enabled || empty($this->baseUrl)) {
                return;
            }

            $template = WhatsappTemplate::getTemplate($eventKey);
            if (! $template) {
                return;
            }

            $message = $this->renderTemplate($template->template_body, $variables);

            // Send to customer
            if ($template->send_to_customer && $customerPhone) {
                $this->dispatch($eventKey, 'customer', $customerPhone, $message, $related);
            }

            // Send to admin (same message for confirmation)
            if ($template->send_to_admin && $this->adminNumber) {
                $adminMessage = "📢 *[ADMIN COPY]* — {$template->event_label}\n\n{$message}";
                $this->dispatch($eventKey, 'admin', $this->adminNumber, $adminMessage, $related);
            }
        } catch (\Throwable $e) {
            Log::warning("WhatsApp sendEvent failed for event '{$eventKey}': ".$e->getMessage());
        }
    }

    /**
     * Send directly to a phone number without a template.
     */
    public function sendRaw(string $phone, string $message, string $eventKey = 'manual'): bool
    {
        return $this->dispatch($eventKey, 'manual', $phone, $message);
    }

    /**
     * Replace {{variable}} placeholders in template body.
     */
    public function renderTemplate(string $body, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $body = str_replace('{{'.$key.'}}', (string) ($value ?? ''), $body);
            $body = str_replace('{{ '.$key.' }}', (string) ($value ?? ''), $body);
        }

        return $body;
    }

    /**
     * Normalise phone to WhatsApp chat ID format.
     * Indian numbers: 10-digit → 91XXXXXXXXXX@c.us
     */
    public function normalizeChatId(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);

        // Strip leading 0
        if (str_starts_with($clean, '0')) {
            $clean = substr($clean, 1);
        }

        // Add country code if missing (assume India)
        if (strlen($clean) === 10) {
            $clean = '91'.$clean;
        }

        return $clean.'@c.us';
    }

    /**
     * Build variables array from a Booking model.
     */
    public function bookingVariables(Booking $booking): array
    {
        $booking->loadMissing(['customer', 'driver', 'vehicle']);
        $customer = $booking->customer;
        $driver = $booking->driver;
        $vehicle = $booking->vehicle;

        $formatDate = function ($dt) {
            if (! $dt) {
                return '';
            }
            if ($dt instanceof \DateTimeInterface) {
                return $dt->format('d M Y');
            }
            try {
                return Carbon::parse($dt)->format('d M Y');
            } catch (\Throwable) {
                return (string) $dt;
            }
        };

        $formatTime = function ($tm) {
            if (! $tm) {
                return '';
            }
            try {
                return Carbon::parse($tm)->format('h:i A');
            } catch (\Throwable) {
                return (string) $tm;
            }
        };

        return [
            'customer_name' => $customer?->name ?? 'Valued Customer',
            'booking_id' => $booking->booking_id,
            'pickup' => $booking->pickup_location,
            'destination' => $booking->destination,
            'travel_date' => $formatDate($booking->travel_date),
            'travel_time' => $formatTime($booking->travel_time),
            'vehicle_category' => $booking->vehicle_category ?? 'TBD',
            'total_amount' => number_format((float) $booking->total_amount, 0),
            'paid_amount' => number_format((float) $booking->paid_amount, 0),
            'balance_amount' => number_format((float) $booking->balance_amount, 0),
            'payment_status' => $booking->payment_status ?? 'Pending',
            'booking_status' => $booking->booking_status,
            'driver_name' => $driver?->name ?? 'To be assigned',
            'driver_phone' => $driver?->mobile ?? $driver?->phone ?? '',
            'vehicle_name' => $vehicle?->name ?? ($booking->vehicle_category ?? 'TBD'),
            'vehicle_number' => $vehicle?->registration_number ?? '',
            'company_phone' => Setting::get('phone_primary') ?? '',
            'feedback_url' => config('app.url').'/feedback',
        ];
    }

    /**
     * Build variables array from an Enquiry model.
     */
    public function enquiryVariables(Enquiry $enquiry): array
    {
        return [
            'name' => $enquiry->name,
            'enquiry_id' => $enquiry->enquiry_id,
            'pickup' => $enquiry->pickup_location ?? '',
            'destination' => $enquiry->destination ?? '',
            'travel_date' => $enquiry->travel_date ? $enquiry->travel_date->format('d M Y') : 'TBD',
            'vehicle_preference' => $enquiry->vehicle_preference ?? 'Any',
            'company_phone' => Setting::get('phone_primary') ?? '',
        ];
    }

    /**
     * Get the customer phone from a Booking.
     */
    public function getCustomerPhone(Booking $booking): ?string
    {
        $booking->loadMissing('customer');

        return $booking->customer?->phone ?? null;
    }

    /**
     * Check if the WhatsApp service is configured and enabled.
     */
    public function isConfigured(): bool
    {
        return $this->enabled && ! empty($this->baseUrl);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Private
    // ─────────────────────────────────────────────────────────────────────────

    private function dispatch(
        string $eventKey,
        string $recipientType,
        string $phone,
        string $message,
        mixed $related = null
    ): bool {
        $chatId = $this->normalizeChatId($phone);

        $log = WhatsappLog::create([
            'event_key' => $eventKey,
            'recipient_type' => $recipientType,
            'to_number' => $chatId,
            'message' => $message,
            'status' => 'pending',
            'related_id' => $related?->id,
            'related_type' => $related ? get_class($related) : null,
        ]);

        try {
            $payload = [
                'chatId' => $chatId,
                'text' => $message,
            ];

            $request = Http::timeout(15);

            if ($this->apiKey) {
                $request = $request->withToken($this->apiKey);
            }

            $response = $request->post($this->baseUrl.'/sendText', $payload);

            $success = $response->successful();
            $log->update([
                'status' => $success ? 'sent' : 'failed',
                'response' => $response->body(),
            ]);

            return $success;
        } catch (\Throwable $e) {
            Log::error('WhatsApp send failed', [
                'event' => $eventKey,
                'to' => $chatId,
                'error' => $e->getMessage(),
            ]);

            $log->update([
                'status' => 'failed',
                'response' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Fetch session information & QR code from OpenWA.
     */
    public function getSessionInfo(string $sessionId = 'default'): array
    {
        if (empty($this->baseUrl)) {
            return [
                'online' => false,
                'status' => 'offline',
                'message' => 'OpenWA Gateway URL not set in Company Settings.',
                'phone' => null,
                'pushName' => null,
                'qr' => null,
                'sessionId' => $sessionId,
            ];
        }

        try {
            $client = Http::timeout(6);
            if ($this->apiKey) {
                $client = $client->withToken($this->apiKey);
            }

            // 1. Try fetching specific session details
            $res = $client->get("{$this->baseUrl}/api/sessions/{$sessionId}");
            if ($res->successful()) {
                $data = $res->json();
                $status = $data['status'] ?? 'unknown';
                $phone = $data['phone'] ?? null;
                $pushName = $data['pushName'] ?? null;
                $qr = null;

                if (in_array($status, ['qr_ready', 'initializing', 'created'], true)) {
                    $qrRes = $client->get("{$this->baseUrl}/api/sessions/{$sessionId}/qr");
                    if ($qrRes->successful()) {
                        $qr = $qrRes->json('qrCode');
                    }
                }

                return [
                    'online' => true,
                    'status' => $status,
                    'phone' => $phone,
                    'pushName' => $pushName,
                    'qr' => $qr,
                    'sessionId' => $sessionId,
                ];
            }

            // 2. Try fetching all sessions
            $allRes = $client->get("{$this->baseUrl}/api/sessions");
            if ($allRes->successful()) {
                $sessions = $allRes->json();
                if (is_array($sessions) && count($sessions) > 0) {
                    $first = $sessions[0];
                    $status = $first['status'] ?? 'ready';
                    $qr = null;
                    if (in_array($status, ['qr_ready', 'initializing', 'created'], true)) {
                        $qrRes = $client->get("{$this->baseUrl}/api/sessions/{$sessionId}/qr");
                        if ($qrRes->successful()) {
                            $qr = $qrRes->json('qrCode');
                        }
                    }

                    return [
                        'online' => true,
                        'status' => $status,
                        'phone' => $first['phone'] ?? null,
                        'pushName' => $first['pushName'] ?? null,
                        'qr' => $qr,
                        'sessionId' => $first['name'] ?? $first['id'] ?? $sessionId,
                    ];
                }
            }

            // 3. Fallback to health / status
            $health = $client->get("{$this->baseUrl}/health");
            if ($health->successful()) {
                $data = $health->json();

                return [
                    'online' => true,
                    'status' => $data['status'] ?? 'ready',
                    'phone' => $data['phone'] ?? Setting::get('openwa_admin_number') ?? Setting::get('whatsapp_number'),
                    'pushName' => $data['pushName'] ?? 'Vaishnavi Tours',
                    'qr' => $data['qrCode'] ?? null,
                    'sessionId' => $sessionId,
                ];
            }

            return [
                'online' => false,
                'status' => 'offline',
                'message' => 'Gateway responded with HTTP '.$res->status(),
                'phone' => null,
                'pushName' => null,
                'qr' => null,
                'sessionId' => $sessionId,
            ];
        } catch (\Throwable $e) {
            return [
                'online' => false,
                'status' => 'offline',
                'message' => 'Unable to connect to OpenWA gateway: '.$e->getMessage(),
                'phone' => null,
                'pushName' => null,
                'qr' => null,
                'sessionId' => $sessionId,
            ];
        }
    }

    /**
     * Start a session or refresh QR code.
     */
    public function startSession(string $sessionId = 'default'): array
    {
        if (empty($this->baseUrl)) {
            return ['success' => false, 'message' => 'OpenWA URL is not configured.'];
        }

        try {
            $client = Http::timeout(10);
            if ($this->apiKey) {
                $client = $client->withToken($this->apiKey);
            }

            $client->post("{$this->baseUrl}/api/sessions", ['name' => $sessionId]);
            $startRes = $client->post("{$this->baseUrl}/api/sessions/{$sessionId}/start");
            $qrRes = $client->get("{$this->baseUrl}/api/sessions/{$sessionId}/qr");

            return [
                'success' => true,
                'status' => $startRes->json('status') ?? 'qr_ready',
                'qr' => $qrRes->json('qrCode'),
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Disconnect / logout session.
     */
    public function logoutSession(string $sessionId = 'default'): bool
    {
        if (empty($this->baseUrl)) {
            return false;
        }

        try {
            $client = Http::timeout(8);
            if ($this->apiKey) {
                $client = $client->withToken($this->apiKey);
            }

            $res = $client->post("{$this->baseUrl}/api/sessions/{$sessionId}/logout");

            return $res->successful();
        } catch (\Throwable) {
            return false;
        }
    }
}
