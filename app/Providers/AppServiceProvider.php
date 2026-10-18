<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\Notification;
use App\Policies\BookingPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Booking::class, BookingPolicy::class);

        View::share('vaishnavi', config('vaishnavi'));

        View::composer('layouts.admin', function ($view) {
            try {
                $unread = Notification::whereNull('user_id')->where('is_read', false)->count();
                $view->with('unreadNotificationsCount', $unread);
            } catch (\Throwable $e) {
                $view->with('unreadNotificationsCount', 0);
            }
        });

        if (app()->environment('production') || env('VERCEL') || str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
    }
}
