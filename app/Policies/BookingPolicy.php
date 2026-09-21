<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /**
     * Determine whether the user can view the booking.
     */
    public function view(User $user, Booking $booking): bool
    {
        return $user->isAdmin() || (int)$booking->customer_id === (int)$user->id;
    }

    /**
     * Determine whether the user can update the booking.
     */
    public function update(User $user, Booking $booking): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can request cancellation or cancel the booking.
     */
    public function cancel(User $user, Booking $booking): bool
    {
        return $user->isAdmin() || (int)$booking->customer_id === (int)$user->id;
    }

    /**
     * Determine whether the user can view the invoice.
     */
    public function viewInvoice(User $user, Booking $booking): bool
    {
        return $user->isAdmin() || (int)$booking->customer_id === (int)$user->id;
    }

    /**
     * Determine whether the user can assign vehicles or drivers.
     */
    public function assign(User $user, Booking $booking): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the booking.
     */
    public function delete(User $user, Booking $booking): bool
    {
        return $user->isAdmin();
    }
}
