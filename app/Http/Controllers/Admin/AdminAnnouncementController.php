<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\AuditLog;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminAnnouncementController extends Controller
{
    public function index(): Response
    {
        $announcement = [
            'enabled' => filter_var(Setting::get('announcement_enabled', false), FILTER_VALIDATE_BOOLEAN),
            'text' => Setting::get('announcement_text', 'Welcome to Movie®! Enjoy 4K HDR streaming with ultra-fast playback.'),
            'type' => Setting::get('announcement_type', 'info'),
            'link' => Setting::get('announcement_link', ''),
        ];

        $recentNotifications = AppNotification::with('user')
            ->orderByDesc('created_at')
            ->take(15)
            ->get();

        $users = User::select(['id', 'name', 'email'])->orderBy('name')->take(100)->get();

        return Inertia::render('Admin/Announcements/Index', [
            'announcement' => $announcement,
            'recentNotifications' => $recentNotifications,
            'totalUsers' => User::count(),
            'users' => $users,
        ]);
    }

    public function updateBanner(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => ['boolean'],
            'text' => ['nullable', 'string', 'max:500'],
            'type' => ['required', 'in:info,warning,danger,success'],
            'link' => ['nullable', 'string', 'max:255'],
        ]);

        Setting::set('announcement_enabled', $validated['enabled'] ? '1' : '0');
        Setting::set('announcement_text', $validated['text'] ?? '');
        Setting::set('announcement_type', $validated['type']);
        Setting::set('announcement_link', $validated['link'] ?? '');

        AuditLog::log('update_announcement', 'Site duyuru bandı ayarları güncellendi.');

        return back()->with('success', 'Duyuru bandı başarıyla güncellendi!');
    }

    public function sendBroadcast(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:500'],
            'type' => ['required', 'in:system,release,review,like'],
            'link' => ['nullable', 'string', 'max:255'],
            'target' => ['required', 'string'], // 'all' or user_id integer
        ]);

        $recipientCount = 0;

        if ($validated['target'] === 'all') {
            $userIds = User::pluck('id');
            foreach ($userIds as $uid) {
                AppNotification::create([
                    'user_id' => $uid,
                    'type' => $validated['type'],
                    'title' => $validated['title'],
                    'message' => $validated['body'],
                    'link' => $validated['link'] ?? null,
                ]);
            }
            $recipientCount = $userIds->count();
        } else {
            $user = User::findOrFail((int) $validated['target']);
            AppNotification::create([
                'user_id' => $user->id,
                'type' => $validated['type'],
                'title' => $validated['title'],
                'message' => $validated['body'],
                'link' => $validated['link'] ?? null,
            ]);
            $recipientCount = 1;
        }

        AuditLog::log(
            'broadcast_notification',
            "Dispatched notification '{$validated['title']}' to {$recipientCount} user(s)."
        );

        return back()->with('success', "Notification dispatched successfully to {$recipientCount} user(s)!");
    }
}
