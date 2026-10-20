<?php

namespace App\Http\Controllers\Alumni;

use App\Http\Controllers\Controller;
use App\Models\Alumni;
use App\Models\AlumniPost;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $alumni = $user->alumniProfile();

        if (! $alumni) {
            // Création automatique du lien si l'étudiant a été diplômé
            $alumni = Alumni::where('email', $user->email)
                ->orWhere('student_profile_id', $user->studentProfile?->id)
                ->first();
        }

        $postsCount = 0;
        $pendingCount = 0;
        $publishedCount = 0;
        $rejectedPosts = collect();
        $recentPosts = collect();

        if ($alumni) {
            $postsQuery = AlumniPost::where('alumni_id', $alumni->id);
            $postsCount = $postsQuery->count();
            $pendingCount = (clone $postsQuery)->where('status', 'pending')->count();
            $publishedCount = (clone $postsQuery)->where('status', 'published')->count();
            $rejectedPosts = (clone $postsQuery)->where('status', 'rejected')->latest()->get();
            $recentPosts = (clone $postsQuery)->with('media')->latest()->take(5)->get();
        }

        return view('alumni.dashboard', compact(
            'user',
            'alumni',
            'postsCount',
            'pendingCount',
            'publishedCount',
            'rejectedPosts',
            'recentPosts'
        ));
    }
}
