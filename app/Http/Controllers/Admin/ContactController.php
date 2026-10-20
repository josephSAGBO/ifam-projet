<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;

class ContactController extends Controller
{
    public function index()
    {
        $messages = ContactMessage::with('user')
            ->orderBy('is_read', 'asc') // Non lus en premier
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.contact.index', compact('messages'));
    }

    public function show(ContactMessage $contact)
    {
        if (! $contact->is_read) {
            $contact->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        return view('admin.contact.show', compact('contact'));
    }
}
