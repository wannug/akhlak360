<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function navbar(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->limit(5)
            ->get();

        $unreadCount = $request->user()
            ->notifications()
            ->unread()
            ->count();

        return response()->json([
            'label' => $unreadCount,
            'label_color' => $unreadCount > 0 ? 'danger' : 'secondary',
            'icon_color' => $unreadCount > 0 ? 'warning' : 'muted',
            'dropdown' => view('notifications.partials.navbar-dropdown', compact('notifications'))->render(),
        ]);
    }

    public function markAsRead(Request $request, AppNotification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        $notification->update([
            'read_at' => now(),
        ]);

        $this->audit($request, 'mark_read', "Marked notification #{$notification->id} as read.");

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $count = $request->user()
            ->notifications()
            ->unread()
            ->update(['read_at' => now()]);

        $this->audit($request, 'mark_all_read', "Marked {$count} notifications as read.");

        return back()->with('success', 'All notifications marked as read.');
    }

    private function audit(Request $request, string $action, string $description): void
    {
        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => $action,
            'module' => 'notifications',
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
