@extends('layouts.admin')
@section('title', 'Modifier le Paiement')
@section('content')

<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.75rem;">
    <a href="{{ route('admin.payments.index') }}" style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container); display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">Modifier le Paiement</h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">{{ number_format($payment->amount_paid, 2, ',', ' ') }} FCFA — {{ $payment->payment_date->format('d/m/Y') }}</p>
    </div>
</div>

<form action="{{ route('admin.payments.update', $payment) }}" method="POST"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:2rem; max-width:800px; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
    @csrf @method('PUT')
    <div style="display:grid; gap:1.5rem;">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Étudiant <span style="color:#dc2626;">*</span></label>
                <select name="student_profile_id" required data-searchable-select="Rechercher un étudiant…"
                        style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
                    @foreach($students as $s)
                        <option value="{{ $s->id }}" {{ old('student_profile_id', $payment->student_profile_id) == $s->id ? 'selected' : '' }}>
                            {{ $s->user->last_name ?? '' }} {{ $s->user->first_name ?? '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Frais associé <span style="color:#dc2626;">*</span></label>
                <select name="fee_id" required data-searchable-select="Rechercher des frais…"
                        style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
                    @foreach($fees as $fee)
                        <option value="{{ $fee->id }}" {{ old('fee_id', $payment->fee_id) == $fee->id ? 'selected' : '' }}>
                            {{ $fee->title }} — {{ $fee->studentProfile->user->last_name ?? '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:1.5rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Montant (FCFA) <span style="color:#dc2626;">*</span></label>
                <div style="position:relative;">
                    <input type="number" name="amount_paid" value="{{ old('amount_paid', $payment->amount_paid) }}" required min="0.01" step="0.01"
                           style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem 0 2rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
                    <span style="position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); font-weight:600; color:var(--color-on-surface-variant);">FCFA</span>
                </div>
            </div>
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Date <span style="color:#dc2626;">*</span></label>
                <input type="date" name="payment_date" value="{{ old('payment_date', $payment->payment_date->format('Y-m-d')) }}" required
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
            </div>
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Méthode <span style="color:#dc2626;">*</span></label>
                <select name="method" required style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
                    <option value="cash" {{ old('method',$payment->method)=='cash' ? 'selected' : '' }}>Espèces</option>
                    <option value="bank_transfer" {{ old('method',$payment->method)=='bank_transfer' ? 'selected' : '' }}>Virement</option>
                    <option value="card" {{ old('method',$payment->method)=='card' ? 'selected' : '' }}>Carte</option>
                    <option value="check" {{ old('method',$payment->method)=='check' ? 'selected' : '' }}>Chèque</option>
                    <option value="other" {{ old('method',$payment->method)=='other' ? 'selected' : '' }}>Autre</option>
                </select>
            </div>
        </div>
        <div>
            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Référence</label>
            <input type="text" name="reference" value="{{ old('reference', $payment->reference) }}"
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
        </div>
        <div>
            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Notes</label>
            <textarea name="notes" rows="2" style="width:100%; box-sizing:border-box; padding:0.75rem 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem; resize:vertical;">{{ old('notes', $payment->notes) }}</textarea>
        </div>
    </div>
    <div style="margin-top:2rem; display:flex; justify-content:flex-end; gap:1rem;">
        <a href="{{ route('admin.payments.index') }}" style="height:44px; display:inline-flex; align-items:center; padding:0 1.25rem; border-radius:0.75rem; color:var(--color-on-surface-variant); font-weight:600; text-decoration:none;">Annuler</a>
        <button type="submit" style="height:44px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1.5rem; border-radius:0.75rem; border:none; cursor:pointer; background:var(--color-primary-container); color:var(--color-on-primary); font-weight:700; box-shadow:0 3px 10px rgba(228,0,102,0.3);">
            <span class="material-symbols-outlined" style="font-size:20px;">save</span> Mettre à jour
        </button>
    </div>
</form>
@endsection
