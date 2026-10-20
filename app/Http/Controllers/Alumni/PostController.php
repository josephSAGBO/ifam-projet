<?php

namespace App\Http\Controllers\Alumni;

use App\Http\Controllers\Controller;
use App\Models\AlumniPost;
use App\Models\AlumniPostMedia;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function index()
    {
        $alumni = auth()->user()->alumniProfile();
        if (! $alumni) {
            return redirect()->route('alumni.dashboard');
        }

        $posts = AlumniPost::where('alumni_id', $alumni->id)
            ->with(['media', 'validator'])
            ->latest()
            ->paginate(10);

        return view('alumni.posts.index', compact('alumni', 'posts'));
    }

    public function create()
    {
        $alumni = auth()->user()->alumniProfile();
        if (! $alumni) {
            return redirect()->route('alumni.dashboard');
        }

        return view('alumni.posts.create', compact('alumni'));
    }

    public function store(Request $request)
    {
        $alumni = auth()->user()->alumniProfile();
        if (! $alumni) {
            abort(403, 'Profil Alumni non trouvé.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'action' => 'nullable|in:draft,submit',
            'status' => 'nullable|in:draft,pending,submit',
            'photos' => 'nullable|array|max:8',
            'photos.*' => 'image|mimes:jpeg,jpg,png,webp|max:5120',
            'captions' => 'nullable|array',
            'captions.*' => 'nullable|string|max:255',
            'cover_index' => 'nullable|integer|min:0|max:7',
            'cover_photo' => 'nullable|integer|min:0|max:7',
        ], [
            'photos.max' => 'Vous ne pouvez téléverser que 8 photos au maximum par publication.',
            'photos.*.max' => 'Chaque photo ne doit pas dépasser 5 Mo.',
            'photos.*.mimes' => 'Les formats autorisés sont JPEG, JPG, PNG et WebP.',
        ]);

        $action = $validated['action'] ?? ($request->input('status') === 'pending' || $request->input('status') === 'submit' ? 'submit' : 'draft');
        $status = ($action === 'submit') ? 'pending' : 'draft';

        $post = DB::transaction(function () use ($alumni, $validated, $status, $request) {
            $post = AlumniPost::create([
                'alumni_id' => $alumni->id,
                'title' => $validated['title'],
                'content' => $validated['content'],
                'status' => $status,
            ]);

            if ($request->hasFile('photos')) {
                $coverIndex = (int) ($validated['cover_index'] ?? $validated['cover_photo'] ?? 0);
                $captions = $validated['captions'] ?? [];

                foreach ($request->file('photos') as $index => $file) {
                    $extension = $file->getClientOriginalExtension() ?: 'webp';
                    $filename = Str::uuid().'.'.$extension;
                    $path = $file->storeAs('alumni_posts/'.date('Y'), $filename, 'public');

                    AlumniPostMedia::create([
                        'alumni_post_id' => $post->id,
                        'file_path' => $path,
                        'disk' => 'public',
                        'mime_type' => $file->getMimeType() ?: 'image/jpeg',
                        'file_size' => $file->getSize(),
                        'original_name' => $file->getClientOriginalName(),
                        'caption' => $captions[$index] ?? null,
                        'display_order' => $index,
                        'is_cover' => ($index === $coverIndex),
                    ]);
                }
            }

            return $post;
        });

        if ($status === 'pending') {
            $this->notificationService->publicationAlumniSoumise($post);
            $message = 'Votre publication a été soumise pour modération. Elle sera mise en ligne après approbation de la direction.';
        } else {
            $message = 'Votre publication a été enregistrée en brouillon.';
        }

        return redirect()->route('alumni.posts.index')->with('success', $message);
    }

    public function show(AlumniPost $post)
    {
        $this->authorizePostAccess($post);
        $post->load(['media', 'alumni', 'validator']);

        return view('alumni.posts.show', compact('post'));
    }

    public function edit(AlumniPost $post)
    {
        $this->authorizePostAccess($post);

        if ($post->isPublished()) {
            return redirect()->route('alumni.posts.index')
                ->with('error', 'Une publication déjà en ligne ne peut pas être modifiée directement sans demande à l\'administration.');
        }

        $post->load('media');

        return view('alumni.posts.edit', compact('post'));
    }

    public function update(Request $request, AlumniPost $post)
    {
        $this->authorizePostAccess($post);

        if ($post->isPublished()) {
            return redirect()->route('alumni.posts.index')
                ->with('error', 'Une publication approuvée ne peut plus être modifiée par l\'auteur sans retrait préalable.');
        }

        $existingMediaCount = $post->media()->count();
        $deletedMediaIds = $request->input('delete_media', []);
        $remainingCount = $existingMediaCount - count($deletedMediaIds);
        $maxNewUploads = max(0, 8 - $remainingCount);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'action' => 'nullable|in:draft,submit',
            'status' => 'nullable|in:draft,pending,submit',
            'photos' => "nullable|array|max:{$maxNewUploads}",
            'photos.*' => 'image|mimes:jpeg,jpg,png,webp|max:5120',
            'cover_media_id' => 'nullable|integer',
            'delete_media' => 'nullable|array',
            'delete_media.*' => 'integer|exists:alumni_post_media,id',
        ], [
            'photos.max' => "Vous ne pouvez pas dépasser 8 photographies au total (actuellement {$remainingCount} conservées).",
            'photos.*.max' => 'Chaque photo ne doit pas dépasser 5 Mo.',
        ]);

        $action = $validated['action'] ?? ($request->input('status') === 'pending' || $request->input('status') === 'submit' ? 'submit' : 'draft');
        $status = ($action === 'submit') ? 'pending' : 'draft';

        DB::transaction(function () use ($post, $validated, $status, $request) {
            // Suppression des médias sélectionnés
            if (! empty($validated['delete_media'])) {
                foreach ($validated['delete_media'] as $mediaId) {
                    $media = AlumniPostMedia::where('alumni_post_id', $post->id)->find($mediaId);
                    $media?->delete();
                }
            }

            // Ajout des nouvelles photos
            if ($request->hasFile('photos')) {
                $currentMaxOrder = (int) $post->media()->max('display_order');
                foreach ($request->file('photos') as $index => $file) {
                    $extension = $file->getClientOriginalExtension() ?: 'webp';
                    $filename = Str::uuid().'.'.$extension;
                    $path = $file->storeAs('alumni_posts/'.date('Y'), $filename, 'public');

                    AlumniPostMedia::create([
                        'alumni_post_id' => $post->id,
                        'file_path' => $path,
                        'disk' => 'public',
                        'mime_type' => $file->getMimeType() ?: 'image/jpeg',
                        'file_size' => $file->getSize(),
                        'original_name' => $file->getClientOriginalName(),
                        'caption' => null,
                        'display_order' => $currentMaxOrder + 1 + $index,
                        'is_cover' => false,
                    ]);
                }
            }

            // Choix de la photo de couverture
            if ($request->filled('cover_media_id')) {
                AlumniPostMedia::where('alumni_post_id', $post->id)->update(['is_cover' => false]);
                AlumniPostMedia::where('alumni_post_id', $post->id)->where('id', $request->input('cover_media_id'))->update(['is_cover' => true]);
            } elseif ($post->media()->where('is_cover', true)->doesntExist()) {
                $first = $post->media()->first();
                $first?->update(['is_cover' => true]);
            }

            $post->update([
                'title' => $validated['title'],
                'content' => $validated['content'],
                'status' => $status,
                'rejection_reason' => ($status === 'pending') ? null : $post->rejection_reason,
            ]);
        });

        if ($status === 'pending') {
            $this->notificationService->publicationAlumniSoumise($post);
            $message = 'Votre publication a été mise à jour et soumise à la modération.';
        } else {
            $message = 'Brouillon mis à jour avec succès.';
        }

        return redirect()->route('alumni.posts.index')->with('success', $message);
    }

    public function submit(AlumniPost $post)
    {
        $this->authorizePostAccess($post);

        if (! in_array($post->status, ['draft', 'rejected'])) {
            return back()->with('error', 'Seul un brouillon ou une publication refusée peut être soumise.');
        }

        $post->update([
            'status' => 'pending',
            'rejection_reason' => null,
            'rejected_at' => null,
        ]);

        $this->notificationService->publicationAlumniSoumise($post);

        return back()->with('success', 'Publication transmise à la modération avec succès.');
    }

    public function cancelSubmission(AlumniPost $post)
    {
        $this->authorizePostAccess($post);

        if ($post->status !== 'pending') {
            return back()->with('error', 'Cette publication n\'est pas en attente de modération.');
        }

        $post->update(['status' => 'draft']);

        return back()->with('success', 'Soumission annulée. La publication est revenue au statut brouillon.');
    }

    public function destroy(AlumniPost $post)
    {
        $this->authorizePostAccess($post);

        if ($post->isPublished()) {
            return back()->with('error', 'Une publication déjà publiée ne peut pas être supprimée par l\'auteur. Veuillez contacter l\'administration.');
        }

        $post->delete();

        return redirect()->route('alumni.posts.index')->with('success', 'Publication supprimée.');
    }

    private function authorizePostAccess(AlumniPost $post): void
    {
        $alumni = auth()->user()->alumniProfile();
        if (! $alumni || $post->alumni_id !== $alumni->id) {
            abort(403, 'Accès non autorisé à cette publication.');
        }
    }
}
