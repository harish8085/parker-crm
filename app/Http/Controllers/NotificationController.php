<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get unread notification count
     */
    public function unreadCount()
    {
        $count = Auth::user()->unreadNotifications()->count();
        return response()->json(['count' => $count]);
    }

    /**
     * Get latest notifications (5 for dropdown)
     */
    public function latest()
    {
        $notifications = Auth::user()->notifications()
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($notification) {
                $data = $notification->data;
                if($data) {
                    return [
                        'id' => $notification->id,
                        'message' => $data['message'] ?? 'New notification',
                        'url' => $data['url'] ?? '#',
                        'read_at' => $notification->read_at,
                        'created_at' => $notification->created_at->diffForHumans(),
                    ];
                }
                return null;
            })
            ->filter(function ($item) {
                return $item !== null;
            })
            ->values();

        return response()->json(['notifications' => $notifications]);
    }

    /**
     * Get all notifications (for view more page)
     */
    public function index()
    {
        $notifications = Auth::user()->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $formattedNotifications = $notifications->map(function ($notification) {
            $data = $notification->data;
            return [
                'id' => $notification->id,
                'message' => $data['message'] ?? 'New notification',
                'url' => $data['url'] ?? '#',
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                'created_at_human' => $notification->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'notifications' => $formattedNotifications,
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ]
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->find($id);
        
        if ($notification) {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }
}
