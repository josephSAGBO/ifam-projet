<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        $messages = ContactMessage::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('contact.index', compact('messages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10|max:3000',
        ], [
            'subject.required' => 'Le sujet du message est obligatoire.',
            'message.required' => 'Le contenu du message est obligatoire.',
            'message.min' => 'Le message doit contenir au moins 10 caractères.',
        ]);

        $contactMessage = ContactMessage::create([
            'user_id' => auth()->id(),
            'subject' => $validated['subject'],
            'message' => $validated['message'],
        ]);

        // Notifier les administrateurs
        $admins = User::role('administrateur')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'sent_by' => auth()->id(),
                'title' => 'Nouveau message de contact',
                'message' => auth()->user()->full_name.' : '.$validated['subject'],
                'type' => 'info',
                'icon' => 'mail',
                'link' => route('admin.contact.show', $contactMessage),
            ]);
        }

        return redirect()->route('contact.index')
            ->with('success', 'Votre message a bien été envoyé à l\'administration de l\'IFAM.');
    }
}
