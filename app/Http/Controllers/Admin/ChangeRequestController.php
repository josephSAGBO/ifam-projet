<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChangeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChangeRequestController extends Controller
{
    public function index()
    {
        // En attente d'abord, puis par date
        $requests = ChangeRequest::with('requester', 'processor')
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.change-requests.index', compact('requests'));
    }

    public function show(ChangeRequest $changeRequest)
    {
        $changeRequest->load('requester', 'processor');

        return view('admin.change-requests.show', compact('changeRequest'));
    }

    public function update(Request $request, ChangeRequest $changeRequest)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
        ]);

        if ($changeRequest->status !== 'pending') {
            return back()->with('error', 'Cette demande a déjà été traitée.');
        }

        $changeRequest->update([
            'status' => $validated['status'],
            'processed_by' => Auth::id(),
            'processed_at' => now(),
        ]);

        if ($validated['status'] === 'approved') {
            $changeRequest->applyChanges();
        }

        $message = $validated['status'] === 'approved'
            ? 'Demande approuvée avec succès et modifications appliquées.'
            : 'Demande rejetée avec succès.';

        return redirect()->route('admin.change-requests.show', $changeRequest)
            ->with('success', $message);
    }
}
