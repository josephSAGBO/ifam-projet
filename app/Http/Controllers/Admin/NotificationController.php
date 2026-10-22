<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = Notification::with('user', 'sender')->where('user_id', auth()->id());

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->input('unread') === '1') {
            $query->whereNull('read_at');
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(20);
        $unreadCount = Notification::where('user_id', auth()->id())->whereNull('read_at')->count();

        return view('admin.notifications.index', compact('notifications', 'unreadCount'));
    }

    public function create()
    {
        $users = User::orderBy('last_name')->get();

        return view('admin.notifications.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipients' => 'required|in:all,students,teachers,individual',
            'user_ids' => 'required_if:recipients,individual|array',
            'user_ids.*' => 'exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,success,warning,danger',
            'icon' => 'nullable|string|max:100',
            'link' => 'nullable|string|max:500',
        ]);

        // Résoudre les destinataires
        $recipients = collect();
        if ($validated['recipients'] === 'all') {
            $recipients = User::where('is_active', true)->pluck('id');
        } elseif ($validated['recipients'] === 'students') {
            $recipients = User::where('is_active', true)->whereHas('roles', fn ($q) => $q->where('name', 'etudiant'))->pluck('id');
        } elseif ($validated['recipients'] === 'teachers') {
            $recipients = User::where('is_active', true)->whereHas('roles', fn ($q) => $q->where('name', 'enseignant'))->pluck('id');
        } elseif ($validated['recipients'] === 'individual') {
            $recipients = collect($validated['user_ids']);
        }

        foreach ($recipients as $userId) {
            Notification::create([
                'user_id' => $userId,
                'sent_by' => auth()->id(),
                'title' => $validated['title'],
                'message' => $validated['message'],
                'type' => $validated['type'],
                'icon' => $validated['icon'] ?? 'notifications',
                'link' => $validated['link'] ?? null,
            ]);
        }

        return redirect()->route('admin.notifications.index')
            ->with('success', count($recipients).' notification(s) envoyée(s) avec succès.');
    }

    public function markRead(Notification $notification)
    {
        abort_unless($notification->user_id === auth()->id(), 403);
        $notification->markAsRead();

        return back();
    }

    public function markReadAndRedirect(Notification $notification)
    {
        abort_unless($notification->user_id === auth()->id(), 403);
        $notification->markAsRead();

        if ($notification->link) {
            return redirect()->away($notification->link);
        }

        return redirect()->route('admin.notifications.index');
    }

    public function markAllRead()
    {
        Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }

    public function destroy(Notification $notification)
    {
        abort_unless($notification->user_id === auth()->id(), 403);
        $notification->delete();

        return back()->with('success', 'Notification supprimée.');
    }
}
