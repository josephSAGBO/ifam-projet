@extends('layouts.admin')
@section('title', 'Modifier la Catégorie')
@section('content')

<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.75rem;">
    <a href="{{ route('admin.expense-categories.index') }}" style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container); display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0;">Modifier : {{ $expenseCategory->name }}</h1>
</div>

<form action="{{ route('admin.expense-categories.update', $expenseCategory) }}" method="POST"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:2rem; max-width:500px; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
    @csrf @method('PUT')
    <div style="display:grid; gap:1.5rem;">
        <div>
            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Nom <span style="color:#dc2626;">*</span></label>
            <input type="text" name="name" value="{{ old('name',$expenseCategory->name) }}" required
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
            @error('name') <span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span> @enderror
        </div>
        <div>
            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Couleur</label>
            <input type="color" name="color" value="{{ old('color',$expenseCategory->color) }}"
                   style="width:60px; height:44px; padding:0.25rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); cursor:pointer; background:transparent;">
        </div>
        <div>
            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Description</label>
            <textarea name="description" rows="3" style="width:100%; box-sizing:border-box; padding:0.75rem 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem; resize:vertical;">{{ old('description',$expenseCategory->description) }}</textarea>
        </div>
    </div>
    <div style="margin-top:2rem; display:flex; justify-content:flex-end; gap:1rem;">
        <a href="{{ route('admin.expense-categories.index') }}" style="height:44px; display:inline-flex; align-items:center; padding:0 1.25rem; border-radius:0.75rem; color:var(--color-on-surface-variant); font-weight:600; text-decoration:none;">Annuler</a>
        <button type="submit" style="height:44px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1.5rem; border-radius:0.75rem; border:none; cursor:pointer; background:var(--color-primary-container); color:var(--color-on-primary); font-weight:700; box-shadow:0 3px 10px rgba(228,0,102,0.3);">
            <span class="material-symbols-outlined" style="font-size:20px;">save</span> Mettre à jour
        </button>
    </div>
</form>
@endsection
