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

    /**
     * IDs des sites gérés par l'utilisateur courant.
     * null = accès global ; tableau = périmètre restreint.
     *
     * @return int[]|null
     */
    protected function managedSiteIds(): ?array
    {
        $user = auth()->user();

        if ($user && $user->hasAnyRole(['administrateur', 'direction', 'direction_daa'])) {
            return null; // accès global
        }

        return $user ? $user->sites()->pluck('sites.id')->all() : [];
    }

    protected function authorizePostAccess(AlumniPost $post): void
    {
        $managed = $this->managedSiteIds();
        if ($managed === null) {
            return;
        }

        if (empty($managed)) {
            abort(403, 'Accès refusé : vous n\'avez aucun site attribué.');
        }

        $post->loadMissing('alumni');
        if ($post->alumni?->site_id !== null && ! in_array($post->alumni->site_id, $managed, true)) {
            abort(403, 'Accès refusé : cette publication appartient à un alumni d\'un autre site.');
        }
    }

    public function index(Request $request)
    {
        $isReadOnly = auth()->user()->hasRole('direction');
        $status = $isReadOnly ? 'published' : $request->input('status', 'pending');

        $query = AlumniPost::with(['alumni.formation', 'media', 'validator']);
        if ($isReadOnly) {
            $query->whereHas('alumni', fn ($alumni) => $alumni->approved()->visibleInDirectory());
        }

        $managed = $this->managedSiteIds();
        if ($managed !== null) {
            $query->whereHas('alumni', fn ($aq) => $aq->whereIn('site_id', $managed));
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $posts = $query->latest()->paginate(12)->withQueryString();

        $countQuery = AlumniPost::query();
        if ($managed !== null) {
            $countQuery->whereHas('alumni', fn ($aq) => $aq->whereIn('site_id', $managed));
        }

        $counts = $isReadOnly
            ? ['published' => $posts->total()]
            : [
                'pending' => (clone $countQuery)->where('status', 'pending')->count(),
                'published' => (clone $countQuery)->where('status', 'published')->count(),
                'rejected' => (clone $countQuery)->where('status', 'rejected')->count(),
                'all' => $countQuery->count(),
            ];

        return view('admin.alumnis.posts.index', compact('posts', 'status', 'counts', 'isReadOnly'));
    }

    public function show(AlumniPost $post)
    {
        $this->authorizePostAccess($post);
        $post->load(['alumni.formation', 'alumni.studentProfile.user', 'media', 'validator']);

        return view('admin.alumnis.posts.show', compact('post'));
    }

    public function approve(AlumniPost $post)
    {
        $this->authorizePostAccess($post);
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
        $this->authorizePostAccess($post);
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
        $this->authorizePostAccess($post);
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
