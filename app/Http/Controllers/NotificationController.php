<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::forUser(Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $unreadCount = Notification::forUser(Auth::id())->unread()->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    public function fetchUnread()
    {
        $notifications = Notification::forUser(Auth::id())
            ->unread()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $count = $notifications->count();

        return response()->json([
            'count' => $count,
            'notifications' => $notifications->map(function ($n) {
                return [
                    'id' => $n->id_notifikasi,
                    'type' => $n->type,
                    'title' => $n->title,
                    'message' => $n->message,
                    'reference_type' => $n->reference_type,
                    'reference_id' => $n->reference_id,
                    'created_at' => $n->created_at->diffForHumans(),
                    'url' => $n->reference_type === 'SPP' ? '/spp' : null,
                ];
            }),
        ]);
    }

    public function markAsRead($id)
    {
        $notif = Notification::forUser(Auth::id())->findOrFail($id);
        $notif->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        Notification::forUser(Auth::id())->unread()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }
}
