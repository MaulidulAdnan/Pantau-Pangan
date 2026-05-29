<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $user = auth('api')->user();
        $notifications = $user->notifications()->paginate(20);
        return response()->json($notifications);
    }

    public function markAsRead($id)
    {
        $user = auth('api')->user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();
        return response()->json(['message' => 'Notifikasi ditandai sudah dibaca']);
    }

    public function markAllAsRead()
    {
        auth('api')->user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'Semua notifikasi ditandai sudah dibaca']);
    }

    public function unreadCount()
    {
        $count = auth('api')->user()->unreadNotifications()->count();
        return response()->json(['unread_count' => $count]);
    }

    public function clearRead()
    {
        auth('api')->user()->readNotifications()->delete();
        return response()->json(['message' => 'Notifikasi yang sudah dibaca berhasil dihapus']);
    }
}
