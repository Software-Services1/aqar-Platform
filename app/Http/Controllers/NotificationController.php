<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(AppNotification $notification)
    {
        abort_unless($notification->employee_id === auth()->id(), 403);
        $notification->markAsRead();

        return back();
    }

    public function markAllRead()
    {
        auth()->user()->unreadNotifications()->update(['is_read' => true]);

        return back()->with('success', 'تم تعليم كل الإشعارات كمقروءة.');
    }
}
