@extends('layouts.admin')
@section('title', 'Réductions')
@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">Réductions & Bourses</h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">Remises accordées aux étudiants sur leurs frais.</p>
    </div>
    <a href="{{ route('admin.discounts.create') }}"
       style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem;
              background:var(--color-primary-container); color:var(--color-on-primary); border-radius:0.75rem;
              text-decoration:none; font-size:0.875rem; font-weight:700; box-shadow:0 3px 10px rgba(228,0,102,0.3);">
        <span class="material-symbols-outlined" style="font-size:20px;">add_circle</span> Ajouter une réduction
    </a>
</div>

<div style="background:var(--color-surface-container-low); padding:1rem 1.5rem; border-radius:1rem; margin-bottom:1.5rem;">
    <form action="{{ route('admin.discounts.index') }}" method="GET" style="display:flex; gap:1rem; align-items:flex-end; flex-wrap:wrap;">
        @includeWhen($sites->isNotEmpty(), 'admin.partials.site-filter')
        <div>
            <label style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">Étudiant</label>
            <select name="student_id" onchange="this.form.submit()" style="height:38px; padding:0 1rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-lowest); font-size:0.875rem;">
                <option value="">Tous</option>
                @foreach($students as $s)
                    <option value="{{ $s->id }}" {{ request('student_id')==$s->id ? 'selected' : '' }}>
                        {{ $s->user->last_name ?? '' }} {{ $s->user->first_name ?? '' }}
                    </option>
                @endforeach
            </select>
        </div>
        @if(request()->filled('student_id'))
        <a href="{{ route('admin.discounts.index') }}" style="height:38px; display:inline-flex; align-items:center; color:var(--color-primary); text-decoration:none; font-size:0.875rem; font-weight:600;">Réinitialiser</a>
        @endif
    </form>
</div>

<div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden;">
    @if($discounts->isEmpty())
    <div style="padding:4rem 2rem; text-align:center; color:var(--color-on-surface-variant);">
        <span class="material-symbols-outlined" style="font-size:48px; opacity:0.25; display:block; margin-bottom:1rem;">redeem</span>
        <p style="margin:0;">Aucune réduction trouvée.</p>
    </div>
    @else
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
            <thead>
                <tr style="background:var(--color-surface-container-low); border-bottom:1px solid var(--color-outline-variant);">
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Libellé</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Étudiant</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Frais</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Valeur</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Validité</th>
                    <th style="padding:0.875rem 1rem;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($discounts as $discount)
                <tr style="border-bottom:1px solid var(--color-surface-container); transition:background 0.15s;"
                    onmouseover="this.style.background='var(--color-surface-container-low)'"
                    onmouseout="this.style.background=''">
                    <td style="padding:0.875rem 1rem; font-weight:600; color:var(--color-on-surface);">{{ $discount->label }}</td>
                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">{{ $discount->studentProfile->user->last_name ?? '' }} {{ $discount->studentProfile->user->first_name ?? '' }}</td>
                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">{{ $discount->fee->title ?? 'Global' }}</td>
                    <td style="padding:0.875rem 1rem; font-weight:700; color:#7c3aed;">
                        @if($discount->type === 'percentage')
                            {{ $discount->value }}%
                        @else
                            {{ number_format($discount->value, 2, ',', ' ') }} €
                        @endif
                    </td>
                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface-variant); font-size:0.8125rem;">
                        @if($discount->valid_from || $discount->valid_until)
                            {{ $discount->valid_from?->format('d/m/Y') ?? '∞' }} → {{ $discount->valid_until?->format('d/m/Y') ?? '∞' }}
                        @else
                            Illimitée
                        @endif
                    </td>
                    <td style="padding:0.875rem 1rem;">
                        <div style="display:flex; align-items:center; gap:0.5rem; justify-content:flex-end;">
                            <a href="{{ route('admin.discounts.edit', $discount) }}" style="width:34px; height:34px; border-radius:0.5rem; display:flex; align-items:center; justify-content:center; background:var(--color-surface-container); color:var(--color-on-surface-variant); text-decoration:none;">
                                <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                            </a>
                            <form method="POST" action="{{ route('admin.discounts.destroy', $discount) }}" onsubmit="return confirm('Supprimer cette réduction ?')">
                                @csrf @method('DELETE')
                                <button type="submit" style="width:34px; height:34px; border-radius:0.5rem; border:none; cursor:pointer; background:var(--color-surface-container); color:#dc2626;">
                                    <span class="material-symbols-outlined" style="font-size:18px;">delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($discounts->hasPages())
    <div style="padding:1rem 1.25rem; border-top:1px solid var(--color-surface-container);">
        {{ $discounts->links('vendor.pagination.simple-default') }}
    </div>
    @endif
    @endif
</div>
@endsection
