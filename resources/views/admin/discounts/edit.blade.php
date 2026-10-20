@extends('layouts.admin')
@section('title', 'Modifier la Réduction')
@section('content')

<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.75rem;">
    <a href="{{ route('admin.discounts.index') }}" style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container); display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0;">Modifier : {{ $discount->label }}</h1>
</div>

<form action="{{ route('admin.discounts.update', $discount) }}" method="POST"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:2rem; max-width:800px; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
    @csrf @method('PUT')
    <div style="display:grid; gap:1.5rem;">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Étudiant <span style="color:#dc2626;">*</span></label>
                <select name="student_profile_id" required data-searchable-select="Rechercher un étudiant…"
                        style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
                    @foreach($students as $s)
                        <option value="{{ $s->id }}" {{ old('student_profile_id',$discount->student_profile_id)==$s->id ? 'selected' : '' }}>{{ $s->user->last_name ?? '' }} {{ $s->user->first_name ?? '' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Frais concerné</label>
                <select name="fee_id" data-searchable-select="Rechercher un frais…"
                        style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
                    <option value="">Tous les frais</option>
                    @foreach($fees as $fee)
                        <option value="{{ $fee->id }}" {{ old('fee_id',$discount->fee_id)==$fee->id ? 'selected' : '' }}>{{ $fee->title }} — {{ $fee->studentProfile->user->last_name ?? '' }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Libellé <span style="color:#dc2626;">*</span></label>
            <input type="text" name="label" value="{{ old('label',$discount->label) }}" required
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
        </div>
        <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:1.5rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Type</label>
                <select name="type" required style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
                    <option value="fixed" {{ old('type',$discount->type)=='fixed'?'selected':'' }}>Montant fixe (€)</option>
                    <option value="percentage" {{ old('type',$discount->type)=='percentage'?'selected':'' }}>Pourcentage (%)</option>
                </select>
            </div>
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Valeur</label>
                <input type="number" name="value" value="{{ old('value',$discount->value) }}" required min="0" step="0.01"
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
                <div>
                    <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Début</label>
                    <input type="date" name="valid_from" value="{{ old('valid_from',$discount->valid_from?->format('Y-m-d')) }}" style="width:100%; box-sizing:border-box; height:44px; padding:0 0.5rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.8125rem;">
                </div>
                <div>
                    <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Fin</label>
                    <input type="date" name="valid_until" value="{{ old('valid_until',$discount->valid_until?->format('Y-m-d')) }}" style="width:100%; box-sizing:border-box; height:44px; padding:0 0.5rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.8125rem;">
                </div>
            </div>
        </div>
        <div>
            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Motif</label>
            <textarea name="reason" rows="2" style="width:100%; box-sizing:border-box; padding:0.75rem 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem; resize:vertical;">{{ old('reason',$discount->reason) }}</textarea>
        </div>
    </div>
    <div style="margin-top:2rem; display:flex; justify-content:flex-end; gap:1rem;">
        <a href="{{ route('admin.discounts.index') }}" style="height:44px; display:inline-flex; align-items:center; padding:0 1.25rem; border-radius:0.75rem; color:var(--color-on-surface-variant); font-weight:600; text-decoration:none;">Annuler</a>
        <button type="submit" style="height:44px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1.5rem; border-radius:0.75rem; border:none; cursor:pointer; background:var(--color-primary-container); color:var(--color-on-primary); font-weight:700; box-shadow:0 3px 10px rgba(228,0,102,0.3);">
            <span class="material-symbols-outlined" style="font-size:20px;">save</span> Mettre à jour
        </button>
    </div>
</form>
@endsection
