@extends('layouts.admin')

@section('title', 'Bulletin - ' . ($student->user->full_name ?? 'Étudiant'))

@section('content')
<div class="bulletin-actions" style="display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:1.25rem; flex-wrap:wrap;">
    <a href="{{ route('admin.bulletins.index', ['session_id' => $selectedSessionId, 'formation_id' => $selectedFormationId]) }}" style="color:var(--color-primary); text-decoration:none; font-weight:700;">
        ← Retour aux bulletins
    </a>
    <div style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
        <a href="{{ route('admin.bulletins.pdf', ['student' => $student->id, 'session_id' => $selectedSessionId, 'formation_id' => $selectedFormationId]) }}" target="_blank" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.625rem 1rem; border-radius:0.75rem; background:var(--color-primary-container); color:var(--color-on-primary); text-decoration:none; font-weight:700;">
            <span class="material-symbols-outlined" style="font-size:18px;">download</span>
            Télécharger PDF (A4)
        </a>
        <button type="button" onclick="window.print()" style="padding:0.625rem 1rem; border:0; border-radius:0.75rem; background:var(--color-surface-container-high); color:var(--color-on-surface); font-weight:700; cursor:pointer; border:1px solid var(--color-outline-variant);">
            Imprimer
        </button>
        <form method="POST" action="{{ route('admin.bulletins.publish', ['student' => $student->id, 'session_id' => $selectedSessionId, 'formation_id' => $selectedFormationId]) }}">
            @csrf
            <button type="submit" style="padding:0.625rem 1rem; border:0; border-radius:0.75rem; background:var(--color-secondary-container); color:var(--color-on-secondary); font-weight:700; cursor:pointer;">
                {{ $publication && $publication->is_published ? 'Dépublier' : 'Publier le bulletin' }}
            </button>
        </form>
    </div>
</div>

@include('bulletins.partials.official', [
    'student' => $student,
    'formation' => $formation,
    'academicSession' => $academicSession,
    'bulletin' => $bulletin,
    'publishedAt' => $publication?->published_at,
])
@endsection
