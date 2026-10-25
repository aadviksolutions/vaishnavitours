<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Setting;
use App\Models\WhatsappLog;
use App\Models\WhatsappTemplate;
use App\Services\WhatsappService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminWhatsappController extends Controller
{
    public function __construct(private readonly WhatsappService $whatsapp) {}

    /**
     * Manage all WhatsApp message templates.
     */
    public function templates()
    {
        // Auto-seed defaults if no templates exist yet
        if (WhatsappTemplate::count() === 0) {
            WhatsappTemplate::seedDefaults();
        }

        $templates = WhatsappTemplate::orderBy('event_key')->get();

        return view('admin.whatsapp.templates', compact('templates'));
    }

    /**
     * Update a single template.
     */
    public function updateTemplate(Request $request, WhatsappTemplate $template)
    {
        $data = $request->validate([
            'template_body' => 'required|string|max:4096',
            'is_active' => 'nullable|boolean',
            'send_to_customer' => 'nullable|boolean',
            'send_to_admin' => 'nullable|boolean',
        ]);

        $template->update([
            'template_body' => $data['template_body'],
            'is_active' => $request->boolean('is_active'),
            'send_to_customer' => $request->boolean('send_to_customer'),
            'send_to_admin' => $request->boolean('send_to_admin'),
        ]);

        return back()->with('success', "Template *{$template->event_label}* updated successfully.");
    }

    /**
     * Reset a template to its default.
     */
    public function resetTemplate(WhatsappTemplate $template)
    {
        $defaults = WhatsappTemplate::EVENTS[$template->event_key] ?? null;

        if ($defaults) {
            $template->update(['template_body' => $defaults['default']]);
        }

        return back()->with('success', "Template reset to default for *{$template->event_label}*.");
    }

    /**
     * WhatsApp message logs.
     */
    public function logs(Request $request)
    {
        $query = WhatsappLog::latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('event_key')) {
            $query->where('event_key', $request->event_key);
        }

        if ($request->filled('recipient_type')) {
            $query->where('recipient_type', $request->recipient_type);
        }

        $logs = $query->paginate(25)->withQueryString();
        $eventKeys = WhatsappLog::distinct()->pluck('event_key')->sort()->values();

        return view('admin.whatsapp.logs', compact('logs', 'eventKeys'));
    }

    /**
     * Send a test/manual WhatsApp message.
     */
    public function sendTest(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
            'message' => 'required|string|max:2000',
        ]);

        $sent = $this->whatsapp->sendRaw($data['phone'], $data['message'], 'test');

        return back()->with(
            $sent ? 'success' : 'error',
            $sent ? '✅ Test message sent successfully!' : '❌ Failed to send message. Check your OpenWA configuration.'
        );
    }

    /**
     * Send a WhatsApp message directly to a booking customer.
     */
    public function sendToBooking(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'message' => 'required|string|max:4096',
        ]);

        $phone = $this->whatsapp->getCustomerPhone($booking);

        if (! $phone) {
            return back()->with('error', 'Customer phone number not available.');
        }

        $sent = $this->whatsapp->sendRaw($phone, $data['message'], 'manual_to_booking');

        return back()->with(
            $sent ? 'success' : 'error',
            $sent ? '✅ Message sent to customer via WhatsApp!' : '❌ Failed to send message.'
        );
    }

    /**
     * Show the QR code scanning and device linking page.
     */
    public function scan()
    {
        $session = $this->whatsapp->getSessionInfo();
        $adminPhone = Setting::get('openwa_admin_number') ?: Setting::get('whatsapp_number');

        return view('admin.whatsapp.scan', compact('session', 'adminPhone'));
    }

    /**
     * AJAX endpoint to poll live QR code & connection status.
     */
    public function qrStatus(): JsonResponse
    {
        $session = $this->whatsapp->getSessionInfo();

        // Auto-save connected phone number to settings if connected and not yet saved
        if ($session['online'] && $session['status'] === 'ready' && ! empty($session['phone'])) {
            $currentAdmin = Setting::get('openwa_admin_number');
            if (empty($currentAdmin)) {
                $clean = preg_replace('/[^0-9]/', '', $session['phone']);
                if (strlen($clean) === 12 && str_starts_with($clean, '91')) {
                    $clean = substr($clean, 2);
                }
                Setting::set('openwa_admin_number', $clean, 'string', 'whatsapp');
                Setting::set('whatsapp_number', $clean, 'string', 'general');
            }
        }

        return response()->json($session);
    }

    /**
     * Manually save the fetched WhatsApp number as the admin number.
     */
    public function savePhone(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'string'],
        ]);

        $clean = preg_replace('/[^0-9]/', '', $data['phone']);
        if (strlen($clean) === 12 && str_starts_with($clean, '91')) {
            $clean = substr($clean, 2);
        }

        Setting::set('openwa_admin_number', $clean, 'string', 'whatsapp');
        Setting::set('whatsapp_number', $clean, 'string', 'general');

        return back()->with('success', "WhatsApp number +91 {$clean} saved as admin notification number!");
    }

    /**
     * Disconnect / logout session to scan a different number.
     */
    public function logout()
    {
        $this->whatsapp->logoutSession();

        return redirect()->route('admin.whatsapp.scan')->with('success', 'WhatsApp session disconnected. You can now scan a new QR code.');
    }
}
