<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlumniPost;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class AlumniPostModerationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');

        $query = AlumniPost::with(['alumni.formation', 'media', 'validator']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $posts = $query->latest()->paginate(12)->withQueryString();

        $counts = [
            'pending' => AlumniPost::where('status', 'pending')->count(),
            'published' => AlumniPost::where('status', 'published')->count(),
            'rejected' => AlumniPost::where('status', 'rejected')->count(),
            'all' => AlumniPost::count(),
        ];

        return view('admin.alumnis.posts.index', compact('posts', 'status', 'counts'));
    }

    public function show(AlumniPost $post)
    {
        $post->load(['alumni.formation', 'alumni.studentProfile.user', 'media', 'validator']);

        return view('admin.alumnis.posts.show', compact('post'));
    }

    public function approve(AlumniPost $post)
    {
        $post->update([
            'status' => 'published',
            'validated_by' => auth()->id(),
            'published_at' => now(),
            'rejection_reason' => null,
            'rejected_at' => null,
        ]);

        $this->notificationService->publicationAlumniValidee($post);

        $redirectRoute = auth()->user()->hasRole('direction') ? 'direction.alumni-posts.index' : 'admin.alumnis.posts.index';

        return redirect()->route($redirectRoute, ['status' => 'published'])
            ->with('success', "La publication « {$post->title} » a été approuvée et mise en ligne avec succès.");
    }

    public function reject(Request $request, AlumniPost $post)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:10|max:1000',
        ], [
            'rejection_reason.required' => 'Le motif du refus est obligatoire.',
            'rejection_reason.min' => 'Veuillez saisir un motif explicite d\'au moins 10 caractères.',
        ]);

        $post->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'rejected_at' => now(),
            'validated_by' => auth()->id(),
        ]);

        $this->notificationService->publicationAlumniRefusee($post, $validated['rejection_reason']);

        $redirectRoute = auth()->user()->hasRole('direction') ? 'direction.alumni-posts.index' : 'admin.alumnis.posts.index';

        return redirect()->route($redirectRoute)
            ->with('success', "La publication a été refusée. Le motif a été notifié à l'alumni.");
    }

    public function unpublish(Request $request, AlumniPost $post)
    {
        $motif = $request->input('reason', 'Retrait par l\'administration');

        $post->update([
            'status' => 'rejected',
            'rejection_reason' => $motif,
            'rejected_at' => now(),
            'validated_by' => auth()->id(),
        ]);

        $this->notificationService->publicationAlumniRetiree($post, $motif);

        return back()->with('success', 'La publication a été retirée du site public.');
    }
}
