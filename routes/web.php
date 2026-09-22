<?php

use App\Http\Controllers\Admin\AdminBookingController;
use App\Http\Controllers\Admin\AdminCustomerController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminDriverController;
use App\Http\Controllers\Admin\AdminInvoiceController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\Admin\AdminTripController;
use App\Http\Controllers\Admin\AdminVehicleController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Customer\CustomerBookingController;
use App\Http\Controllers\Customer\CustomerDashboardController;
use App\Http\Controllers\Customer\CustomerInvoiceController;
use App\Http\Controllers\Customer\CustomerNotificationController;
use App\Http\Controllers\Customer\CustomerProfileController;
use App\Http\Controllers\PublicWebsiteController;
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

Route::get('/vehicles', [PublicWebsiteController::class, 'vehicles'])->name('vehicles');
Route::get('/rates', [PublicWebsiteController::class, 'rates'])->name('rates');
Route::get('/network', [PublicWebsiteController::class, 'network'])->name('network');
Route::get('/service-network', [PublicWebsiteController::class, 'network'])->name('service-network');
Route::get('/about', [PublicWebsiteController::class, 'about'])->name('about');
Route::get('/feedback', [PublicWebsiteController::class, 'feedback'])->name('feedback');
Route::post('/feedback', [PublicWebsiteController::class, 'storeFeedback'])->name('feedback.store');
Route::get('/contact', [PublicWebsiteController::class, 'contact'])->name('contact');
Route::get('/enquiry', [PublicWebsiteController::class, 'enquiry'])->name('enquiry');
Route::post('/enquiry', [PublicWebsiteController::class, 'storeEnquiry'])->name('enquiry.store');

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
    Route::get('/bookings/{booking}', fn (\App\Models\Booking $booking) => redirect()->route('customer.bookings.show', $booking))->name('bookings.view');
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

    // Trips
    Route::get('/trips', [AdminTripController::class, 'index'])->name('trips.index');
    Route::get('/trips/{trip}', [AdminTripController::class, 'show'])->name('trips.show');
    Route::put('/trips/{trip}', [AdminTripController::class, 'update'])->name('trips.update');

    // Vehicles CRUD
    Route::resource('vehicles', AdminVehicleController::class);

    // Drivers CRUD
    Route::resource('drivers', AdminDriverController::class);

    // Customers
    Route::get('/customers', [AdminCustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{user}', [AdminCustomerController::class, 'show'])->name('customers.show');

    // Payments
    Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/create', [AdminPaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments', [AdminPaymentController::class, 'store'])->name('payments.store');

    // Invoices
    Route::get('/invoices', [AdminInvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [AdminInvoiceController::class, 'show'])->name('invoices.show');

    // Notifications
    Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [AdminNotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/mark-read', [AdminNotificationController::class, 'markAllRead'])->name('notifications.mark-read');

    // Reports
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');

    // Settings
    Route::get('/settings', [AdminSettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [AdminSettingController::class, 'update'])->name('settings.update');
});
