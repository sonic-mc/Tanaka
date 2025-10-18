<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class DashboardNotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Mark a single database notification as read
    public function markAsRead(DatabaseNotification $notification)
    {
        $user = Auth::user();

        if ($notification->notifiable_type !== get_class($user) || $notification->notifiable_id !== $user->id) {
            if ($user->role !== 'admin') {
                abort(403);
            }
        }

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return back()->with('success', 'Notification marked as read.');
    }

    // Mark all database notifications as read
    public function markAllAsRead()
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }
}
