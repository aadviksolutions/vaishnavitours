<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::whereNull('user_id')
            ->latest()
            ->paginate(20);

        $unreadCount = Notification::whereNull('user_id')->where('is_read', false)->count();

        return view('admin.notifications.index', compact('notifications', 'unreadCount'));
    }

    public function markRead(Notification $notification)
    {
        $notification->update(['is_read' => true]);
        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead()
    {
        Notification::whereNull('user_id')->update(['is_read' => true]);
        return back()->with('success', 'All notifications marked as read.');
    }
}
