<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Automatically mark all notifications as read when visiting the page
        Notification::markAllReadForUser($user->id);

        $query = Notification::with(['car'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Filter by read status
        if ($request->filled('read')) {
            $query->where('is_read', $request->read === '1');
        }

        $notifications = $query->paginate(20)->withQueryString();

        // Get unread count (should be 0 after marking all as read)
        $unreadCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Display the specified notification.
     */
    public function show(Notification $notification)
    {
        // Check ownership
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        // Mark as read
        $notification->markAsRead();

        $notification->load('car');

        return view('notifications.show', compact('notification'));
    }

    /**
     * Mark notification as read.
     */
    public function markRead(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->markAsRead();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'შეტყობინება წაკითხულად მოინიშნა!'
            ]);
        }

        return redirect()->back()->with('success', 'შეტყობინება წაკითხულად მოინიშნა!');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead()
    {
        Notification::markAllReadForUser(auth()->id());

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'ყველა შეტყობინება წაკითხულად მოინიშნა!'
            ]);
        }

        return redirect()->back()->with('success', 'ყველა შეტყობინება წაკითხულად მოინიშნა!');
    }

    /**
     * Delete a notification.
     */
    public function destroy(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'შეტყობინება წაიშალა!'
            ]);
        }

        return redirect()->route('notifications.index')
            ->with('success', 'შეტყობინება წაიშალა!');
    }

    /**
     * Get unread notifications count (AJAX).
     */
    public function getUnreadCount()
    {
        $count = Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Get recent notifications (AJAX).
     */
    public function getRecent()
    {
        $notifications = Notification::with('car')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'message' => $notification->message,
                    'is_read' => $notification->is_read,
                    'time_ago' => $notification->time_ago,
                    'car_id' => $notification->car_id,
                    'url' => route('notifications.index'),
                ];
            }),
            'unread_count' => Notification::where('user_id', auth()->id())
                ->where('is_read', false)
                ->count(),
        ]);
    }

    /**
     * Send notification to user (admin only).
     */
    public function send(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
            'car_id' => 'nullable|exists:cars,id',
        ]);

        $notification = Notification::createForUser(
            $validated['user_id'],
            $validated['message'],
            $validated['car_id'] ?? null
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'შეტყობინება გაიგზავნა!'
            ]);
        }

        return redirect()->back()->with('success', 'შეტყობინება გაიგზავნა!');
    }

    /**
     * Send notification to multiple users (admin only).
     */
    public function sendBulk(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'message' => 'required|string|max:1000',
        ]);

        $count = 0;
        foreach ($validated['user_ids'] as $userId) {
            Notification::createForUser($userId, $validated['message']);
            $count++;
        }

        return redirect()->back()
            ->with('success', "შეტყობინება გაეგზავნა {$count} მომხმარებელს!");
    }
}
