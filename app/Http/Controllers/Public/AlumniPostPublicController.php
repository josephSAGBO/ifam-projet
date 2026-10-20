<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AlumniPost;
use Illuminate\Http\Request;

class AlumniPostPublicController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = AlumniPost::query()
            ->published()
            ->whereHas('alumni', function ($q) {
                $q->approved()->visibleInDirectory();
            })
            ->with(['alumni.formation', 'media']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                    ->orWhereHas('alumni', function ($aq) use ($search) {
                        $aq->where('nom', 'like', "%{$search}%")
                            ->orWhere('prenom', 'like', "%{$search}%");
                    });
            });
        }

        $posts = $query->latest('published_at')->paginate(9)->withQueryString();

        return view('public.alumnis.posts.index', compact('posts', 'search'));
    }

    public function show(string $slug)
    {
        $post = AlumniPost::query()
            ->published()
            ->where(function ($query) use ($slug) {
                $query->where('slug', $slug);

                if (ctype_digit($slug)) {
                    $query->orWhere('id', $slug);
                }
            })
            ->with(['alumni.formation', 'media', 'validator'])
            ->firstOrFail();

        // Vérification hermétique de l'auteur
        if (! $post->alumni->is_approved || ! $post->alumni->is_visible_directory) {
            abort(404, 'Cette actualité n\'est pas accessible.');
        }

        $otherPosts = AlumniPost::published()
            ->where('id', '!=', $post->id)
            ->whereHas('alumni', fn ($q) => $q->approved()->visibleInDirectory())
            ->with(['alumni', 'media'])
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('public.alumnis.posts.show', compact('post', 'otherPosts'));
    }
}
