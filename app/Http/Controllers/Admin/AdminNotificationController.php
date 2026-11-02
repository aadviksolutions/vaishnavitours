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

    public function poll(Request $request)
    {
        $lastId = (int) $request->query('last_id', 0);

        $query = Notification::whereNull('user_id');
        if ($lastId > 0) {
            $query->where('id', '>', $lastId);
        } else {
            $query->where('created_at', '>=', now()->subMinutes(5));
        }

        $newNotifications = $query->latest()->take(10)->get()->map(function ($n) {
            return [
                'id' => $n->id,
                'title' => $n->title,
                'message' => $n->message,
                'type' => $n->type ?? 'booking',
                'action_url' => $n->action_url ?: route('admin.notifications.index'),
                'created_at_human' => $n->created_at->diffForHumans(),
            ];
        });

        $unreadCount = Notification::whereNull('user_id')->where('is_read', false)->count();
        $latestId = (int) (Notification::whereNull('user_id')->max('id') ?? $lastId);

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
            'latest_id' => $latestId,
            'notifications' => $newNotifications,
        ]);
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
