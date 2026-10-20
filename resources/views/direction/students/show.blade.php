@extends('layouts.role')
@section('title', 'Dossier étudiant — Direction')
@section('role_label', 'Direction')
@section('home_route', route('direction.dashboard'))

@section('nav_links')
<a href="{{ route('direction.dashboard') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; color:var(--color-on-surface-variant); text-decoration:none; font-size:0.8125rem; font-weight:700;">
    <span class="material-symbols-outlined" style="font-size:18px;">dashboard</span> Dashboard
</a>
<a href="{{ route('direction.students.index') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; background:var(--color-primary-container); color:var(--color-on-primary); text-decoration:none; font-size:0.8125rem; font-weight:700;">
    <span class="material-symbols-outlined" style="font-size:18px;">school</span> Étudiants
</a>
<a href="{{ route('direction.finance.index') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; color:var(--color-on-surface-variant); text-decoration:none; font-size:0.8125rem; font-weight:700;">
    <span class="material-symbols-outlined" style="font-size:18px;">account_balance_wallet</span> Finances
</a>
<a href="{{ route('contact.index') }}" style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.5rem 0.875rem; border-radius:0.625rem; color:var(--color-on-surface-variant); text-decoration:none; font-size:0.8125rem; font-weight:700;">
    <span class="material-symbols-outlined" style="font-size:18px;">mail</span> Contact
</a>
@endsection

@section('content')

{{-- Breadcrumb --}}
<div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:2rem; font-size:0.8125rem; color:var(--color-on-surface-variant);">
    <a href="{{ route('direction.students.index') }}" style="color:var(--color-on-surface-variant); text-decoration:none; font-weight:600;">Étudiants</a>
    <span class="material-symbols-outlined" style="font-size:16px;">chevron_right</span>
    <span style="color:var(--color-on-surface); font-weight:700;">{{ $student->user->full_name ?? 'Étudiant' }}</span>
</div>

<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(min(100%, 320px), 1fr)); gap:1.5rem; align-items:start;">

    {{-- Carte identité --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); text-align:center;">
        <div style="width:80px; height:80px; border-radius:50%; background:var(--color-primary-container); color:var(--color-on-primary); display:flex; align-items:center; justify-content:center; margin:0 auto 1rem; font-size:1.75rem; font-weight:800; font-family:var(--font-display);">
            {{ $student->user->initials ?? '??' }}
        </div>
        <h1 style="font-size:1.125rem; font-weight:800; color:var(--color-on-surface); margin:0 0 0.25rem;">
            {{ $student->user->full_name ?? '—' }}
        </h1>
        <div style="font-size:0.8125rem; color:var(--color-on-surface-variant); margin-bottom:1rem;">{{ $student->user->email ?? '' }}</div>

        <div style="font-family:monospace; font-size:0.875rem; font-weight:700; padding:0.375rem 0.875rem; border-radius:0.5rem; background:var(--color-surface-container-high); color:var(--color-on-surface); display:inline-block; margin-bottom:1.25rem;">
            {{ $student->user->matricule ?? 'N/A' }}
        </div>

        <div style="display:flex; flex-direction:column; gap:0.625rem; text-align:left;">
            @if($student->user->birth_date)
            <div style="display:flex; align-items:center; gap:0.5rem; font-size:0.8125rem; color:var(--color-on-surface-variant);">
                <span class="material-symbols-outlined" style="font-size:16px;">cake</span>
                {{ $student->user->birth_date->format('d/m/Y') }}
                ({{ $student->user->birth_date->age }} ans)
            </div>
            @endif
            @if($student->user->phone)
            <div style="display:flex; align-items:center; gap:0.5rem; font-size:0.8125rem; color:var(--color-on-surface-variant);">
                <span class="material-symbols-outlined" style="font-size:16px;">phone</span>
                {{ $student->user->phone }}
            </div>
            @endif
            @if($student->address)
            <div style="display:flex; align-items:center; gap:0.5rem; font-size:0.8125rem; color:var(--color-on-surface-variant);">
                <span class="material-symbols-outlined" style="font-size:16px;">location_on</span>
                {{ $student->address }}
            </div>
            @endif
        </div>
    </div>

    {{-- Détails --}}
    <div style="display:flex; flex-direction:column; gap:1.25rem;">

        {{-- Inscriptions --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
            <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem;">Inscriptions</h2>
            @if($student->enrollments->isEmpty())
                <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">Aucune inscription.</p>
            @else
            <div style="display:flex; flex-direction:column; gap:0.75rem;">
                @foreach($student->enrollments as $enroll)
                <div style="display:flex; align-items:center; justify-content:space-between; padding:0.75rem 1rem; border-radius:0.75rem; background:var(--color-surface-container-low);">
                    <div>
                        <div style="font-weight:700; color:var(--color-on-surface);">{{ $enroll->formation->name ?? '—' }}</div>
                        <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">
                            {{ $enroll->academicSession->name ?? '—' }} · Inscrit le {{ $enroll->enrollment_date ? \Carbon\Carbon::parse($enroll->enrollment_date)->format('d/m/Y') : '—' }}
                        </div>
                    </div>
                    @php
                        $sc = match($enroll->status) {
                            'active'    => ['bg' => '#ecfdf5', 'color' => '#065f46', 'dot' => '#059669', 'label' => 'Actif'],
                            'completed' => ['bg' => '#eff6ff', 'color' => '#1e40af', 'dot' => '#3b82f6', 'label' => 'Terminé'],
                            'withdrawn' => ['bg' => '#fff1f2', 'color' => '#9f1239', 'dot' => '#e11d48', 'label' => 'Abandonné'],
                            default     => ['bg' => '#f3f4f6', 'color' => '#374151', 'dot' => '#9ca3af', 'label' => ucfirst($enroll->status)],
                        };
                    @endphp
                    <span style="font-size:0.6875rem; font-weight:700; padding:0.25rem 0.625rem; border-radius:9999px; display:inline-flex; align-items:center; gap:0.25rem;
                                 background:{{ $sc['bg'] }}; color:{{ $sc['color'] }};">
                        <span style="width:6px; height:6px; border-radius:50%; background:{{ $sc['dot'] }};"></span>
                        {{ $sc['label'] }}
                    </span>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Situation financière --}}
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
            <h2 style="font-size:1rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem;">Situation financière</h2>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(115px, 1fr)); gap:1rem;">
                <div style="text-align:center; padding:0.75rem; border-radius:0.75rem; background:var(--color-surface-container-low);">
                    <div style="font-size:0.6875rem; font-weight:700; text-transform:uppercase; color:var(--color-on-surface-variant); margin-bottom:0.25rem;">Total dû</div>
                    <div style="font-size:1.125rem; font-weight:800; color:var(--color-on-surface);">{{ number_format($totalDue, 0, ',', ' ') }} FCFA</div>
                </div>
                <div style="text-align:center; padding:0.75rem; border-radius:0.75rem; background:#ecfdf5;">
                    <div style="font-size:0.6875rem; font-weight:700; text-transform:uppercase; color:#065f46; margin-bottom:0.25rem;">Payé</div>
                    <div style="font-size:1.125rem; font-weight:800; color:#059669;">{{ number_format($totalPaid, 0, ',', ' ') }} FCFA</div>
                </div>
                <div style="text-align:center; padding:0.75rem; border-radius:0.75rem; background:{{ $balance > 0 ? '#fff1f2' : '#ecfdf5' }};">
                    <div style="font-size:0.6875rem; font-weight:700; text-transform:uppercase; color:{{ $balance > 0 ? '#9f1239' : '#065f46' }}; margin-bottom:0.25rem;">Solde restant</div>
                    <div style="font-size:1.125rem; font-weight:800; color:{{ $balance > 0 ? '#e11d48' : '#059669' }};">{{ number_format($balance, 0, ',', ' ') }} FCFA</div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
