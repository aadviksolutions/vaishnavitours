<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_key',
        'event_label',
        'template_body',
        'is_active',
        'send_to_customer',
        'send_to_admin',
        'available_variables',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'send_to_customer' => 'boolean',
        'send_to_admin' => 'boolean',
        'available_variables' => 'array',
    ];

    /**
     * All supported notification events with their default templates.
     */
    public const EVENTS = [
        'new_booking' => [
            'label' => '🆕 New Booking Received',
            'default' => "Hello *{{customer_name}}*! 🙏\n\nYour booking has been received successfully.\n\n📋 *Booking ID:* {{booking_id}}\n📍 *From:* {{pickup}}\n📍 *To:* {{destination}}\n📅 *Date:* {{travel_date}}\n🚗 *Vehicle:* {{vehicle_category}}\n💰 *Fare:* ₹{{total_amount}}\n\nWe'll confirm your booking shortly. For any queries, call us at {{company_phone}}.\n\n*Vaishnavi Tours* 🚖",
            'variables' => ['customer_name', 'booking_id', 'pickup', 'destination', 'travel_date', 'vehicle_category', 'total_amount', 'company_phone'],
        ],
        'booking_confirmed' => [
            'label' => '✅ Booking Confirmed',
            'default' => "Hello *{{customer_name}}*! ✅\n\nGreat news! Your booking is *CONFIRMED*.\n\n📋 *Booking ID:* {{booking_id}}\n📍 *From:* {{pickup}}\n📍 *To:* {{destination}}\n📅 *Date:* {{travel_date}} at {{travel_time}}\n🚗 *Vehicle:* {{vehicle_category}}\n💰 *Total Fare:* ₹{{total_amount}}\n\nPlease be ready at the pickup point. Call us at {{company_phone}} for any changes.\n\n*Vaishnavi Tours* ✈️",
            'variables' => ['customer_name', 'booking_id', 'pickup', 'destination', 'travel_date', 'travel_time', 'vehicle_category', 'total_amount', 'company_phone'],
        ],
        'booking_cancelled' => [
            'label' => '❌ Booking Cancelled',
            'default' => "Hello *{{customer_name}}*,\n\nYour booking has been *CANCELLED*.\n\n📋 *Booking ID:* {{booking_id}}\n📍 *Route:* {{pickup}} → {{destination}}\n📅 *Was Scheduled:* {{travel_date}}\n\nIf this was a mistake or you need to rebook, please call us at {{company_phone}}.\n\n*Vaishnavi Tours* 🚖",
            'variables' => ['customer_name', 'booking_id', 'pickup', 'destination', 'travel_date', 'company_phone'],
        ],
        'driver_assigned' => [
            'label' => '🧑‍✈️ Driver Assigned',
            'default' => "Hello *{{customer_name}}*! 🧑‍✈️\n\nYour chauffeur has been assigned for Booking *{{booking_id}}*.\n\n👤 *Driver:* {{driver_name}}\n📞 *Driver Contact:* {{driver_phone}}\n🚗 *Vehicle:* {{vehicle_name}} ({{vehicle_number}})\n📅 *Pickup:* {{travel_date}} at {{travel_time}}\n📍 *From:* {{pickup}}\n\nYour driver will contact you before pickup. For help, call {{company_phone}}.\n\n*Vaishnavi Tours* 🚖",
            'variables' => ['customer_name', 'booking_id', 'driver_name', 'driver_phone', 'vehicle_name', 'vehicle_number', 'travel_date', 'travel_time', 'pickup', 'company_phone'],
        ],
        'payment_update' => [
            'label' => '💳 Payment Update',
            'default' => "Hello *{{customer_name}}*! 💳\n\nPayment update for Booking *{{booking_id}}*:\n\n✅ *Amount Paid:* ₹{{paid_amount}}\n💰 *Total Fare:* ₹{{total_amount}}\n⏳ *Balance:* ₹{{balance_amount}}\n📊 *Payment Status:* {{payment_status}}\n\nThank you for your payment! Call {{company_phone}} for any queries.\n\n*Vaishnavi Tours* 🚖",
            'variables' => ['customer_name', 'booking_id', 'paid_amount', 'total_amount', 'balance_amount', 'payment_status', 'company_phone'],
        ],
        'travel_reminder' => [
            'label' => '⏰ Travel Reminder',
            'default' => "Hello *{{customer_name}}*! ⏰\n\nFriendly reminder — your trip is *TOMORROW*!\n\n📋 *Booking ID:* {{booking_id}}\n📍 *From:* {{pickup}}\n📍 *To:* {{destination}}\n📅 *Date:* {{travel_date}} at {{travel_time}}\n👤 *Driver:* {{driver_name}}\n📞 *Driver Contact:* {{driver_phone}}\n🚗 *Vehicle:* {{vehicle_name}} ({{vehicle_number}})\n\nPlease be ready 10 mins before pickup. Safe journey! 🙏\n\n*Vaishnavi Tours* 🚖",
            'variables' => ['customer_name', 'booking_id', 'pickup', 'destination', 'travel_date', 'travel_time', 'driver_name', 'driver_phone', 'vehicle_name', 'vehicle_number'],
        ],
        'new_enquiry' => [
            'label' => '📩 New Enquiry Received',
            'default' => "Hello *{{name}}*! 📩\n\nThank you for enquiring with Vaishnavi Tours!\n\n📋 *Enquiry ID:* {{enquiry_id}}\n📍 *From:* {{pickup}}\n📍 *To:* {{destination}}\n📅 *Travel Date:* {{travel_date}}\n\nOur team will get back to you shortly with a quote. For urgent queries, call {{company_phone}}.\n\n*Vaishnavi Tours* 🚖",
            'variables' => ['name', 'enquiry_id', 'pickup', 'destination', 'travel_date', 'company_phone'],
        ],
        'trip_started' => [
            'label' => '🚀 Trip Started',
            'default' => "Hello *{{customer_name}}*! 🚀\n\nYour trip has *STARTED*!\n\n📋 *Booking ID:* {{booking_id}}\n👤 *Driver:* {{driver_name}}\n📞 *Driver Contact:* {{driver_phone}}\n🚗 *Vehicle:* {{vehicle_name}} ({{vehicle_number}})\n📍 *Destination:* {{destination}}\n\nHave a safe and comfortable journey! 🙏\n\n*Vaishnavi Tours* 🚖",
            'variables' => ['customer_name', 'booking_id', 'driver_name', 'driver_phone', 'vehicle_name', 'vehicle_number', 'destination'],
        ],
        'trip_completed' => [
            'label' => '🏁 Trip Completed',
            'default' => "Hello *{{customer_name}}*! 🏁\n\nYour trip is *COMPLETED*. We hope you had a great experience!\n\n📋 *Booking ID:* {{booking_id}}\n📍 *Route:* {{pickup}} → {{destination}}\n💰 *Total Fare:* ₹{{total_amount}}\n\nWe'd love your feedback! Visit: {{feedback_url}}\n\nThank you for choosing Vaishnavi Tours! 🙏\n\n*Vaishnavi Tours* 🚖",
            'variables' => ['customer_name', 'booking_id', 'pickup', 'destination', 'total_amount', 'feedback_url'],
        ],
        'vehicle_assigned' => [
            'label' => '🚗 Vehicle Assigned',
            'default' => "Hello *{{customer_name}}*! 🚗\n\nA vehicle has been assigned for your Booking *{{booking_id}}*.\n\n🚗 *Vehicle:* {{vehicle_name}}\n🔢 *Registration:* {{vehicle_number}}\n📅 *Pickup:* {{travel_date}} at {{travel_time}}\n📍 *From:* {{pickup}}\n\nYour driver will be assigned shortly. For queries, call {{company_phone}}.\n\n*Vaishnavi Tours* 🚖",
            'variables' => ['customer_name', 'booking_id', 'vehicle_name', 'vehicle_number', 'travel_date', 'travel_time', 'pickup', 'company_phone'],
        ],
    ];

    /**
     * Get a template by event key, falling back to default.
     */
    public static function getTemplate(string $eventKey): ?self
    {
        return static::where('event_key', $eventKey)->where('is_active', true)->first();
    }

    /**
     * Seed all default templates if not present.
     */
    public static function seedDefaults(): void
    {
        foreach (self::EVENTS as $key => $data) {
            static::firstOrCreate(
                ['event_key' => $key],
                [
                    'event_label' => $data['label'],
                    'template_body' => $data['default'],
                    'is_active' => true,
                    'send_to_customer' => true,
                    'send_to_admin' => true,
                    'available_variables' => $data['variables'],
                ]
            );
        }
    }
}
