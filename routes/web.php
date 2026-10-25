<?php

use App\Http\Controllers\Admin\AdminBookingCategoryController;
use App\Http\Controllers\Admin\AdminBookingController;
use App\Http\Controllers\Admin\AdminCalendarController;
use App\Http\Controllers\Admin\AdminComplaintController;
use App\Http\Controllers\Admin\AdminCustomerController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminDriverController;
use App\Http\Controllers\Admin\AdminEnquiryController;
use App\Http\Controllers\Admin\AdminExpenseController;
use App\Http\Controllers\Admin\AdminFeedbackController;
use App\Http\Controllers\Admin\AdminInvoiceController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Admin\AdminPickupLocationController;
use App\Http\Controllers\Admin\AdminRateController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\Admin\AdminTourPackageController;
use App\Http\Controllers\Admin\AdminTripController;
use App\Http\Controllers\Admin\AdminVehicleCategoryController;
use App\Http\Controllers\Admin\AdminVehicleController;
use App\Http\Controllers\Admin\AdminWhatsappController;
use App\Http\Controllers\Api\GoogleMapsApiController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Customer\CustomerBookingController;
use App\Http\Controllers\Customer\CustomerDashboardController;
use App\Http\Controllers\Customer\CustomerInvoiceController;
use App\Http\Controllers\Customer\CustomerNotificationController;
use App\Http\Controllers\Customer\CustomerProfileController;
use App\Http\Controllers\PublicPaymentController;
use App\Http\Controllers\PublicWebsiteController;
use App\Models\Booking;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Website Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicWebsiteController::class, 'home'])->name('home');
Route::get('/booking', [PublicWebsiteController::class, 'booking'])->name('booking');
Route::post('/booking', [PublicWebsiteController::class, 'storeBooking'])->name('booking.store');
Route::get('/booking-success/{booking}', [PublicWebsiteController::class, 'bookingSuccess'])->name('booking.success');

// Razorpay Online Payments & Webhook
Route::get('/pay/{booking}', [PublicPaymentController::class, 'showPayPage'])->name('payment.pay');
Route::post('/pay/{booking}/pay-later', [PublicPaymentController::class, 'selectPayLater'])->name('payment.pay-later');
Route::post('/api/payments/razorpay/create-order', [PublicPaymentController::class, 'createOrder'])->name('api.payments.razorpay.create-order');
Route::post('/api/payments/razorpay/verify', [PublicPaymentController::class, 'verifyPayment'])->name('api.payments.razorpay.verify');
Route::post('/api/payments/razorpay/webhook', [PublicPaymentController::class, 'webhook'])->name('api.payments.razorpay.webhook');

Route::get('/services', [PublicWebsiteController::class, 'services'])->name('services');
Route::get('/ambulance-services', [PublicWebsiteController::class, 'ambulanceServices'])->name('ambulance-services');
Route::get('/services/local-taxi', [PublicWebsiteController::class, 'localTaxi'])->name('services.local-taxi');
Route::get('/services/outstation-taxi', [PublicWebsiteController::class, 'outstationTaxi'])->name('services.outstation-taxi');
Route::get('/services/airport-transfer', [PublicWebsiteController::class, 'airportTransfer'])->name('services.airport-transfer');
Route::get('/sitemap.xml', [PublicWebsiteController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [PublicWebsiteController::class, 'robots'])->name('robots');
Route::get('/vehicles', [PublicWebsiteController::class, 'vehicles'])->name('vehicles');
Route::get('/rates', [PublicWebsiteController::class, 'rates'])->name('rates');
Route::get('/network', [PublicWebsiteController::class, 'network'])->name('network');
Route::get('/service-network', [PublicWebsiteController::class, 'network'])->name('service-network');
Route::get('/terms-and-conditions', [PublicWebsiteController::class, 'termsAndConditions'])->name('terms-and-conditions');
Route::get('/cancellation-refund-policy', [PublicWebsiteController::class, 'cancellationPolicy'])->name('cancellation-refund-policy');
Route::get('/cancellation-policy', [PublicWebsiteController::class, 'cancellationPolicy'])->name('cancellation-policy');
Route::get('/cancellation', [PublicWebsiteController::class, 'cancellationPolicy'])->name('cancellation');
Route::get('/about', [PublicWebsiteController::class, 'about'])->name('about');
Route::get('/feedback', [PublicWebsiteController::class, 'feedback'])->name('feedback');
Route::post('/feedback', [PublicWebsiteController::class, 'storeFeedback'])->name('feedback.store');
Route::get('/contact', [PublicWebsiteController::class, 'contact'])->name('contact');
Route::get('/tour-packages', [PublicWebsiteController::class, 'tourPackages'])->name('tour-packages');
Route::post('/locations/quick-store', [PublicWebsiteController::class, 'storeQuickLocation'])->name('locations.quick-store');
Route::get('/enquiry', [PublicWebsiteController::class, 'enquiry'])->name('enquiry');
Route::post('/enquiry', [PublicWebsiteController::class, 'storeEnquiry'])->name('enquiry.store');

// Google Maps & Routing APIs
Route::prefix('api/maps')->name('api.maps.')->group(function () {
    Route::get('/config', [GoogleMapsApiController::class, 'config'])->name('config');
    Route::post('/calculate-route', [GoogleMapsApiController::class, 'calculateRoute'])->name('calculate-route');
    Route::post('/geocode', [GoogleMapsApiController::class, 'geocode'])->name('geocode');
    Route::post('/reverse-geocode', [GoogleMapsApiController::class, 'reverseGeocode'])->name('reverse-geocode');
});

// Section 19: Dedicated print-friendly invoice route protected by BookingPolicy
Route::get('/invoice/{booking}', [CustomerInvoiceController::class, 'bookingInvoice'])->name('invoice.show')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');

/*
|--------------------------------------------------------------------------
| Customer Portal Routes (Authenticated)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');

    Route::get('/bookings', [CustomerBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [CustomerBookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/cancel', [CustomerBookingController::class, 'cancel'])->name('bookings.cancel');

    Route::get('/profile', [CustomerProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [CustomerProfileController::class, 'update'])->name('profile.update');

    Route::get('/invoices/{invoice}', [CustomerInvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/notifications', [CustomerNotificationController::class, 'index'])->name('notifications');
});

// Friendly customer root route aliases (Section 12 requirement)
Route::middleware(['auth', 'customer'])->group(function () {
    Route::get('/dashboard', fn () => redirect()->route('customer.dashboard'))->name('dashboard');
    Route::get('/bookings', fn () => redirect()->route('customer.bookings.index'))->name('bookings');
    Route::get('/bookings/{booking}', fn (Booking $booking) => redirect()->route('customer.bookings.show', $booking))->name('bookings.view');
    Route::get('/payments', fn () => redirect()->route('customer.bookings.index'))->name('payments');
    Route::get('/invoices', fn () => redirect()->route('customer.bookings.index'))->name('invoices');
    Route::get('/notifications', fn () => redirect()->route('customer.notifications'))->name('notifications');
    Route::get('/profile', fn () => redirect()->route('customer.profile'))->name('profile');
});

/*
|--------------------------------------------------------------------------
| Admin Panel Routes (Authenticated + Admin Role)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn () => redirect()->route('admin.dashboard'))->name('home');
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Bookings & Controlled Workflow Actions
    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create', [AdminBookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [AdminBookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}', [AdminBookingController::class, 'show'])->name('bookings.show');
    Route::get('/bookings/{booking}/edit', [AdminBookingController::class, 'edit'])->name('bookings.edit');
    Route::put('/bookings/{booking}', [AdminBookingController::class, 'update'])->name('bookings.update');
    Route::delete('/bookings/{booking}', [AdminBookingController::class, 'destroy'])->name('bookings.destroy');

    Route::post('/bookings/{booking}/confirm', [AdminBookingController::class, 'confirm'])->name('bookings.confirm');
    Route::post('/bookings/{booking}/assign-vehicle', [AdminBookingController::class, 'assignVehicle'])->name('bookings.assign-vehicle');
    Route::post('/bookings/{booking}/assign-driver', [AdminBookingController::class, 'assignDriver'])->name('bookings.assign-driver');
    Route::post('/bookings/{booking}/assign', [AdminBookingController::class, 'assignVehicleDriver'])->name('bookings.assign');
    Route::post('/bookings/{booking}/amount', [AdminBookingController::class, 'updateAmount'])->name('bookings.amount');
    Route::post('/bookings/{booking}/payment', [AdminBookingController::class, 'updatePayment'])->name('bookings.payment');
    Route::post('/bookings/{booking}/status', [AdminBookingController::class, 'updateStatus'])->name('bookings.status');
    Route::post('/bookings/{booking}/cancel', [AdminBookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/bookings/{booking}/recalculate-route', [AdminBookingController::class, 'recalculateRoute'])->name('bookings.recalculate-route');

    // Trips
    Route::get('/trips', [AdminTripController::class, 'index'])->name('trips.index');
    Route::get('/trips/{trip}', [AdminTripController::class, 'show'])->name('trips.show');
    Route::put('/trips/{trip}', [AdminTripController::class, 'update'])->name('trips.update');
    Route::post('/trips/{trip}/close', [AdminTripController::class, 'closeTrip'])->name('trips.close');

    // Advance Booking Calendar
    Route::get('/calendar', [AdminCalendarController::class, 'index'])->name('calendar.index');

    // Vehicles CRUD & Vendor Payments
    Route::resource('vehicles', AdminVehicleController::class);
    Route::post('/vehicles/{vehicle}/payments', [AdminVehicleController::class, 'storeThirdPartyPayment'])->name('vehicles.payments.store');

    // Tour Packages
    Route::resource('tour-packages', AdminTourPackageController::class);

    // Pickup Locations
    Route::resource('locations', AdminPickupLocationController::class);

    // Expenses
    Route::resource('expenses', AdminExpenseController::class);

    // Complaints System (Drivers & Vehicles)
    Route::resource('complaints', AdminComplaintController::class);
    Route::post('/complaints/{complaint}/status', [AdminComplaintController::class, 'updateStatus'])->name('complaints.status');

    // Booking Categories (Configurable)
    Route::resource('booking-categories', AdminBookingCategoryController::class);

    // Vehicle Categories & Models (Configurable)
    Route::resource('vehicle-categories', AdminVehicleCategoryController::class);
    Route::post('/vehicle-categories/{vehicle_category}/toggle', [AdminVehicleCategoryController::class, 'toggleStatus'])->name('vehicle-categories.toggle');
    Route::post('/vehicle-categories/{vehicle_category}/models', [AdminVehicleCategoryController::class, 'storeModel'])->name('vehicle-categories.models.store');
    Route::put('/vehicle-categories/{vehicle_category}/models/{vehicle_model}', [AdminVehicleCategoryController::class, 'updateModel'])->name('vehicle-categories.models.update');
    Route::post('/vehicle-categories/{vehicle_category}/models/{vehicle_model}/toggle', [AdminVehicleCategoryController::class, 'toggleModelStatus'])->name('vehicle-categories.models.toggle');
    Route::delete('/vehicle-categories/{vehicle_category}/models/{vehicle_model}', [AdminVehicleCategoryController::class, 'destroyModel'])->name('vehicle-categories.models.destroy');

    // Rate configuration
    Route::resource('rates', AdminRateController::class)->except(['show']);

    // Drivers CRUD
    Route::resource('drivers', AdminDriverController::class);

    // Customers
    Route::get('/customers/sample-csv', [AdminCustomerController::class, 'sampleCsv'])->name('customers.sample-csv');
    Route::post('/customers/bulk-upload', [AdminCustomerController::class, 'bulkUpload'])->name('customers.bulk-upload');
    Route::get('/customers', [AdminCustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/create', [AdminCustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [AdminCustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{user}', [AdminCustomerController::class, 'show'])->name('customers.show');

    // Payments & Refunds
    Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/create', [AdminPaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments', [AdminPaymentController::class, 'store'])->name('payments.store');
    Route::post('/payments/{booking}/refund', [AdminPaymentController::class, 'refund'])->name('payments.refund');

    // Invoices
    Route::get('/invoices', [AdminInvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [AdminInvoiceController::class, 'show'])->name('invoices.show');

    // Enquiries Inbox
    Route::get('/enquiries/{enquiry}/convert', [AdminEnquiryController::class, 'convert'])->name('enquiries.convert');
    Route::post('/enquiries/manual', [AdminEnquiryController::class, 'storeManual'])->name('enquiries.store-manual');
    Route::resource('enquiries', AdminEnquiryController::class)->only(['index', 'show', 'update', 'destroy']);

    // Customer Feedback Moderation
    Route::get('/feedback', [AdminFeedbackController::class, 'index'])->name('feedback.index');
    Route::post('/feedback/{feedback}/toggle-status', [AdminFeedbackController::class, 'toggleStatus'])->name('feedback.toggle-status');
    Route::delete('/feedback/{feedback}', [AdminFeedbackController::class, 'destroy'])->name('feedback.destroy');

    // Notifications
    Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [AdminNotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/mark-read', [AdminNotificationController::class, 'markAllRead'])->name('notifications.mark-read');

    // Reports & Exports
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export-excel', [AdminReportController::class, 'exportExcel'])->name('reports.export.excel');
    Route::get('/reports/export-pdf', [AdminReportController::class, 'exportPdf'])->name('reports.export.pdf');
    Route::get('/reports/export-csv', [AdminReportController::class, 'exportBookingsCsv'])->name('reports.export.csv');
    Route::get('/reports/print', [AdminReportController::class, 'printReport'])->name('reports.print');

    // Settings
    Route::get('/settings', [AdminSettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [AdminSettingController::class, 'update'])->name('settings.update');

    // WhatsApp Notifications
    Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
        Route::get('/scan', [AdminWhatsappController::class, 'scan'])->name('scan');
        Route::get('/qr-status', [AdminWhatsappController::class, 'qrStatus'])->name('qr-status');
        Route::post('/save-phone', [AdminWhatsappController::class, 'savePhone'])->name('save-phone');
        Route::post('/logout', [AdminWhatsappController::class, 'logout'])->name('logout');
        Route::get('/templates', [AdminWhatsappController::class, 'templates'])->name('templates');
        Route::put('/templates/{template}', [AdminWhatsappController::class, 'updateTemplate'])->name('templates.update');
        Route::post('/templates/{template}/reset', [AdminWhatsappController::class, 'resetTemplate'])->name('templates.reset');
        Route::get('/logs', [AdminWhatsappController::class, 'logs'])->name('logs');
        Route::post('/test', [AdminWhatsappController::class, 'sendTest'])->name('send-test');
        Route::post('/bookings/{booking}/send', [AdminWhatsappController::class, 'sendToBooking'])->name('send-to-booking');
    });
});
