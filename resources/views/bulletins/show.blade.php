@extends('layouts.role')

@section('title', 'Bulletin officiel - ' . ($publication->studentProfile->user->full_name ?? 'Étudiant'))
@section('role_label', $viewerRole === 'parent' ? 'Parent' : 'Étudiant')
@section('home_route', $viewerRole === 'parent' ? route('parent.dashboard') : route('student.dashboard'))

@section('nav_links')
    @include($viewerRole === 'parent' ? 'parent.partials.nav' : 'student.partials.nav')
@endsection

@section('content')
@php
    $pdfRoute = $viewerRole === 'parent'
        ? route('parent.bulletins.pdf', ['child' => $publication->studentProfile->user_id, 'publication' => $publication->id])
        : route('student.bulletins.pdf', $publication);
    $backRoute = $viewerRole === 'parent'
        ? route('parent.dashboard')
        : route('student.bulletins.index');
@endphp

<div class="no-print" style="display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1.5rem;">
    <a href="{{ $backRoute }}"
       style="display:inline-flex; align-items:center; gap:0.375rem; color:var(--color-primary); text-decoration:none; font-size:0.875rem; font-weight:700;">
        <span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
        {{ $viewerRole === 'parent' ? 'Retour au tableau de bord' : 'Retour à mes bulletins' }}
    </a>
    <div style="display:flex; align-items:center; gap:0.625rem; flex-wrap:wrap;">
        <a href="{{ $pdfRoute }}"
           target="_blank"
           style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.625rem 1rem; border-radius:0.625rem; background:var(--color-primary-container); color:var(--color-on-primary); text-decoration:none; font-size:0.8125rem; font-weight:700; box-shadow:0 1px 4px rgba(11,28,48,0.08);">
            <span class="material-symbols-outlined" style="font-size:18px;">download</span>
            Télécharger le PDF officiel (A4)
        </a>
        <button type="button" onclick="window.print()"
                style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.625rem 0.875rem; border:1px solid var(--color-outline-variant); border-radius:0.625rem; background:var(--color-surface-container-high); color:var(--color-on-surface); font-size:0.8125rem; font-weight:700; cursor:pointer;">
            <span class="material-symbols-outlined" style="font-size:18px;">print</span>
            Imprimer
        </button>
    </div>
</div>

<div class="bulletin-sheet-container" style="width:100%; overflow-x:auto; -webkit-overflow-scrolling:touch; padding-bottom:1.5rem;">
    @include('bulletins.partials.official', [
        'student' => $publication->studentProfile,
        'formation' => $publication->formation,
        'academicSession' => $publication->academicSession,
        'bulletin' => $bulletin,
        'publishedAt' => $publication->published_at,
    ])
</div>
@endsection
