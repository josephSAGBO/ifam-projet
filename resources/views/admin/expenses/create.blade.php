@extends('layouts.admin')
@section('title', 'Nouvelle Dépense')
@section('content')

<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.75rem;">
    <a href="{{ route('admin.expenses.index') }}" style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container); display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0;">Nouvelle Dépense</h1>
</div>

<form action="{{ route('admin.expenses.store') }}" method="POST"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:2rem; max-width:800px; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
    @csrf
    <div style="display:grid; gap:1.5rem;">
        <div>
            <label for="expense-site" style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Site <span style="color:#dc2626;">*</span></label>
            <select id="expense-site" name="site_id" required style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
                @foreach($sites as $site)
                    <option value="{{ $site->id }}" {{ (string) old('site_id', $defaultSiteId) === (string) $site->id ? 'selected' : '' }}>{{ $site->nom }}</option>
                @endforeach
            </select>
            @error('site_id') <span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span> @enderror
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Titre <span style="color:#dc2626;">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required placeholder="ex: Achat de fournitures"
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
                @error('title') <span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span> @enderror
            </div>
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Catégorie <span style="color:#dc2626;">*</span></label>
                <select name="expense_category_id" required style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
                    <option value="">Sélectionner</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('expense_category_id')==$cat->id?'selected':'' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('expense_category_id') <span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span> @enderror
            </div>
        </div>
        <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:1.5rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Montant (€) <span style="color:#dc2626;">*</span></label>
                <div style="position:relative;">
                    <input type="number" name="amount" value="{{ old('amount') }}" required min="0.01" step="0.01"
                           style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem 0 2rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
                    <span style="position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); font-weight:600; color:var(--color-on-surface-variant);">€</span>
                </div>
            </div>
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Date <span style="color:#dc2626;">*</span></label>
                <input type="date" name="expense_date" value="{{ old('expense_date', date('Y-m-d')) }}" required
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
            </div>
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Mode de paiement <span style="color:#dc2626;">*</span></label>
                <select name="payment_method" required style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
                    <option value="cash" {{ old('payment_method')=='cash'?'selected':'' }}>Espèces</option>
                    <option value="bank_transfer" {{ old('payment_method')=='bank_transfer'?'selected':'' }}>Virement</option>
                    <option value="card" {{ old('payment_method')=='card'?'selected':'' }}>Carte</option>
                    <option value="check" {{ old('payment_method')=='check'?'selected':'' }}>Chèque</option>
                    <option value="other" {{ old('payment_method')=='other'?'selected':'' }}>Autre</option>
                </select>
            </div>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Fournisseur / Bénéficiaire</label>
                <input type="text" name="vendor" value="{{ old('vendor') }}" placeholder="ex: Bureau Vallée, EDF…"
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
            </div>
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Référence / N° de facture</label>
                <input type="text" name="reference" value="{{ old('reference') }}"
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
            </div>
        </div>
        <div>
            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Notes</label>
            <textarea name="notes" rows="2" style="width:100%; box-sizing:border-box; padding:0.75rem 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem; resize:vertical;">{{ old('notes') }}</textarea>
        </div>
    </div>
    <div style="margin-top:2rem; display:flex; justify-content:flex-end; gap:1rem;">
        <a href="{{ route('admin.expenses.index') }}" style="height:44px; display:inline-flex; align-items:center; padding:0 1.25rem; border-radius:0.75rem; color:var(--color-on-surface-variant); font-weight:600; text-decoration:none;">Annuler</a>
        <button type="submit" style="height:44px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1.5rem; border-radius:0.75rem; border:none; cursor:pointer; background:var(--color-primary-container); color:var(--color-on-primary); font-weight:700; box-shadow:0 3px 10px rgba(228,0,102,0.3);">
            <span class="material-symbols-outlined" style="font-size:20px;">add_circle</span> Enregistrer
        </button>
    </div>
</form>
@endsection
