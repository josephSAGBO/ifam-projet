@extends('layouts.admin')

@section('title', 'Modération des Publications Alumni')

@section('content')

{{-- Header --}}
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.25rem;">
            <a href="{{ route('admin.alumnis.index') }}" style="color:var(--color-primary); font-size:0.875rem; text-decoration:none; font-weight:600;">
                ← Espace Alumni
            </a>
        </div>
        <h1 style="font-family:var(--font-display); font-size:1.75rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Modération des Publications Alumni
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Validez, refusez ou suspendez les actualités, créations et réalisations soumises par les diplômés de l'IFAM.
        </p>
    </div>
</div>

@if(session('success'))
    <div style="background:#dcfce7; color:#15803d; padding:1rem 1.25rem; border-radius:0.75rem; font-size:0.9375rem; font-weight:600; margin-bottom:1.5rem; display:flex; align-items:center; gap:0.5rem;">
        <span class="material-symbols-outlined">check_circle</span>
        {{ session('success') }}
    </div>
@endif

{{-- Filtres par Statut --}}
<div style="display:flex; gap:0.5rem; margin-bottom:1.5rem; border-bottom:1px solid var(--color-outline-variant); padding-bottom:0.5rem; flex-wrap:wrap;">
    <a href="{{ route('admin.alumnis.posts.index', ['status' => 'pending']) }}"
       style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:600; text-decoration:none;
              {{ $status === 'pending' ? 'background:#fef3c7; color:#b45309; font-weight:700;' : 'color:var(--color-on-surface-variant);' }}">
        <span class="material-symbols-outlined" style="font-size:18px;">hourglass_empty</span>
        En attente de validation ({{ $counts['pending'] }})
    </a>
    <a href="{{ route('admin.alumnis.posts.index', ['status' => 'published']) }}"
       style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:600; text-decoration:none;
              {{ $status === 'published' ? 'background:#dcfce7; color:#15803d; font-weight:700;' : 'color:var(--color-on-surface-variant);' }}">
        <span class="material-symbols-outlined" style="font-size:18px;">check_circle</span>
        En ligne / Publiées ({{ $counts['published'] }})
    </a>
    <a href="{{ route('admin.alumnis.posts.index', ['status' => 'rejected']) }}"
       style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:600; text-decoration:none;
              {{ $status === 'rejected' ? 'background:#fee2e2; color:#b91c1c; font-weight:700;' : 'color:var(--color-on-surface-variant);' }}">
        <span class="material-symbols-outlined" style="font-size:18px;">cancel</span>
        Refusées ({{ $counts['rejected'] }})
    </a>
    <a href="{{ route('admin.alumnis.posts.index', ['status' => 'all']) }}"
       style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem; border-radius:0.5rem; font-size:0.875rem; font-weight:600; text-decoration:none;
              {{ $status === 'all' ? 'background:var(--color-surface-container-high); color:var(--color-on-surface); font-weight:700;' : 'color:var(--color-on-surface-variant);' }}">
        <span class="material-symbols-outlined" style="font-size:18px;">list</span>
        Toutes ({{ $counts['all'] }})
    </a>
</div>

{{-- Liste des publications --}}
@if($posts->isEmpty())
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:3.5rem 1rem; text-align:center; color:var(--color-on-surface-variant);">
        <span class="material-symbols-outlined" style="font-size:56px; color:var(--color-outline); margin-bottom:0.75rem;">inbox</span>
        <p style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">Aucune publication dans cette catégorie</p>
        <p style="font-size:0.875rem; margin:0;">Toutes les publications ont été traitées ou aucune n'a encore été soumise.</p>
    </div>
@else
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:1.25rem;">
        @foreach($posts as $post)
            <div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden; display:flex; flex-direction:column; border:1px solid var(--color-outline-variant);">
                {{-- Visuel de couverture --}}
                <div style="height:180px; background:var(--color-surface-container-high); position:relative; overflow:hidden;">
                    @if($post->cover_url)
                        <img src="{{ $post->cover_url }}" alt="" style="width:100%; height:100%; object-fit:cover;">
                    @else
                        <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:var(--color-on-surface-variant);">
                            <span class="material-symbols-outlined" style="font-size:48px;">image</span>
                        </div>
                    @endif
                    <div style="position:absolute; top:0.75rem; right:0.75rem;">
                        @if($post->isPending())
                            <span style="background:#fef3c7; color:#b45309; padding:0.25rem 0.625rem; border-radius:999px; font-size:0.75rem; font-weight:700; display:inline-flex; align-items:center; gap:0.25rem;">
                                <span class="material-symbols-outlined" style="font-size:14px;">hourglass_empty</span>
                                En attente
                            </span>
                        @elseif($post->isPublished())
                            <span style="background:#dcfce7; color:#15803d; padding:0.25rem 0.625rem; border-radius:999px; font-size:0.75rem; font-weight:700; display:inline-flex; align-items:center; gap:0.25rem;">
                                <span class="material-symbols-outlined" style="font-size:14px;">check_circle</span>
                                En ligne
                            </span>
                        @elseif($post->isRejected())
                            <span style="background:#fee2e2; color:#b91c1c; padding:0.25rem 0.625rem; border-radius:999px; font-size:0.75rem; font-weight:700; display:inline-flex; align-items:center; gap:0.25rem;">
                                <span class="material-symbols-outlined" style="font-size:14px;">cancel</span>
                                Refusé
                            </span>
                        @else
                            <span style="background:#f3f4f6; color:#4b5563; padding:0.25rem 0.625rem; border-radius:999px; font-size:0.75rem; font-weight:700;">
                                Brouillon
                            </span>
                        @endif
                    </div>
                    <div style="position:absolute; bottom:0.5rem; left:0.75rem; background:rgba(0,0,0,0.6); color:#fff; padding:0.125rem 0.5rem; border-radius:0.375rem; font-size:0.6875rem; font-weight:600; display:flex; align-items:center; gap:0.25rem;">
                        <span class="material-symbols-outlined" style="font-size:12px;">photo_library</span>
                        {{ $post->media->count() }} photo(s)
                    </div>
                </div>

                {{-- Corps de la carte --}}
                <div style="padding:1.25rem; flex:1; display:flex; flex-direction:column;">
                    <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.75rem;">
                        @if($post->alumni->photo_url)
                            <img src="{{ $post->alumni->photo_url }}" alt="" style="width:28px; height:28px; border-radius:50%; object-fit:cover;">
                        @else
                            <div style="width:28px; height:28px; border-radius:50%; background:var(--color-primary-container); color:var(--color-on-primary); display:flex; align-items:center; justify-content:center; font-size:0.6875rem; font-weight:700;">
                                {{ $post->alumni->initials }}
                            </div>
                        @endif
                        <div style="font-size:0.8125rem; font-weight:600; color:var(--color-on-surface);">
                            {{ $post->alumni->full_name }}
                        </div>
                    </div>

                    <h3 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.5rem; line-height:1.35;">
                        {{ $post->title }}
                    </h3>

                    <p style="font-size:0.8125rem; color:var(--color-on-surface-variant); margin:0 0 1rem; flex:1; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden;">
                        {{ Str::limit(strip_tags($post->content), 120) }}
                    </p>

                    @if($post->isRejected() && $post->rejection_reason)
                        <div style="background:#fef2f2; border-left:3px solid #ef4444; padding:0.5rem 0.75rem; border-radius:0.25rem; margin-bottom:1rem; font-size:0.75rem; color:#991b1b;">
                            <strong>Motif :</strong> {{ $post->rejection_reason }}
                        </div>
                    @endif

                    <div style="display:flex; align-items:center; justify-content:space-between; border-top:1px solid var(--color-outline-variant); padding-top:0.75rem; margin-top:auto;">
                        <span style="font-size:0.75rem; color:var(--color-on-surface-variant);">
                            {{ $post->created_at->format('d/m/Y à H:i') }}
                        </span>
                        <a href="{{ route('admin.alumnis.posts.show', $post->id) }}"
                           style="height:32px; padding:0 0.875rem; border-radius:0.5rem; background:var(--color-surface-container-high); color:var(--color-on-surface); font-size:0.8125rem; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:0.25rem;">
                            Examiner
                            <span class="material-symbols-outlined" style="font-size:16px;">arrow_forward</span>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($posts->hasPages())
        <div style="margin-top:1.5rem;">
            {{ $posts->links() }}
        </div>
    @endif
@endif

@endsection
