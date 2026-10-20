@extends('layouts.admin')
@section('title', 'Dépenses')
@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">Dépenses</h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">Suivi des sorties financières de l'établissement.</p>
    </div>
    <a href="{{ route('admin.expenses.create') }}"
       style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem;
              background:var(--color-primary-container); color:var(--color-on-primary); border-radius:0.75rem;
              text-decoration:none; font-size:0.875rem; font-weight:700; box-shadow:0 3px 10px rgba(228,0,102,0.3);">
        <span class="material-symbols-outlined" style="font-size:20px;">add_circle</span> Nouvelle dépense
    </a>
</div>

{{-- Filters --}}
<div style="background:var(--color-surface-container-low); padding:1rem 1.5rem; border-radius:1rem; margin-bottom:1.5rem;">
    <form action="{{ route('admin.expenses.index') }}" method="GET" style="display:flex; gap:1rem; align-items:flex-end; flex-wrap:wrap;">
        <div>
            <label style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">Catégorie</label>
            <select name="category_id" onchange="this.form.submit()" style="height:38px; padding:0 1rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-lowest); font-size:0.875rem;">
                <option value="">Toutes</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id')==$cat->id?'selected':'' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">Du</label>
            <input type="date" name="from" value="{{ request('from') }}" onchange="this.form.submit()" style="height:38px; padding:0 0.75rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-lowest); font-size:0.875rem;">
        </div>
        <div>
            <label style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">Au</label>
            <input type="date" name="to" value="{{ request('to') }}" onchange="this.form.submit()" style="height:38px; padding:0 0.75rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-lowest); font-size:0.875rem;">
        </div>
        @if(request()->hasAny(['category_id','from','to']))
        <a href="{{ route('admin.expenses.index') }}" style="height:38px; display:inline-flex; align-items:center; color:var(--color-primary); text-decoration:none; font-size:0.875rem; font-weight:600;">Réinitialiser</a>
        @endif
    </form>
</div>

{{-- Total card --}}
<div style="background:linear-gradient(135deg, #dc2626, #b91c1c); border-radius:1rem; padding:1.25rem 1.5rem; margin-bottom:1.5rem; display:flex; align-items:center; gap:1rem; color:#fff; max-width:300px;">
    <span class="material-symbols-outlined" style="font-size:32px; opacity:0.8;">trending_down</span>
    <div>
        <div style="font-size:0.75rem; font-weight:600; opacity:0.8; text-transform:uppercase; letter-spacing:0.06em;">Total dépenses</div>
        <div style="font-size:1.5rem; font-weight:800;">{{ number_format($totalAmount, 2, ',', ' ') }} €</div>
    </div>
</div>

<div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden;">
    @if($expenses->isEmpty())
    <div style="padding:4rem 2rem; text-align:center; color:var(--color-on-surface-variant);">
        <span class="material-symbols-outlined" style="font-size:48px; opacity:0.25; display:block; margin-bottom:1rem;">receipt_long</span>
        <p style="margin:0;">Aucune dépense trouvée.</p>
    </div>
    @else
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
            <thead>
                <tr style="background:var(--color-surface-container-low); border-bottom:1px solid var(--color-outline-variant);">
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Date</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Titre</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Catégorie</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Fournisseur</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Montant</th>
                    <th style="padding:0.875rem 1rem;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($expenses as $expense)
                <tr style="border-bottom:1px solid var(--color-surface-container); transition:background 0.15s;"
                    onmouseover="this.style.background='var(--color-surface-container-low)'"
                    onmouseout="this.style.background=''">
                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">{{ $expense->expense_date->format('d/m/Y') }}</td>
                    <td style="padding:0.875rem 1rem; font-weight:600; color:var(--color-on-surface);">
                        {{ $expense->title }}
                        @if($expense->reference)
                        <div style="font-size:0.75rem; color:var(--color-on-surface-variant); font-weight:400;">Réf: {{ $expense->reference }}</div>
                        @endif
                    </td>
                    <td style="padding:0.875rem 1rem;">
                        @if($expense->category)
                        <span style="display:inline-flex; align-items:center; gap:0.375rem; padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700;
                                     background:{{ $expense->category->color }}22; color:{{ $expense->category->color }};">
                            <span style="width:6px; height:6px; border-radius:50%; background:{{ $expense->category->color }}; display:inline-block;"></span>
                            {{ $expense->category->name }}
                        </span>
                        @else
                            -
                        @endif
                    </td>
                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">{{ $expense->vendor ?? '-' }}</td>
                    <td style="padding:0.875rem 1rem; font-weight:700; color:#dc2626;">{{ number_format($expense->amount, 2, ',', ' ') }} €</td>
                    <td style="padding:0.875rem 1rem;">
                        <div style="display:flex; align-items:center; gap:0.5rem; justify-content:flex-end;">
                            <a href="{{ route('admin.expenses.edit', $expense) }}" style="width:34px; height:34px; border-radius:0.5rem; display:flex; align-items:center; justify-content:center; background:var(--color-surface-container); color:var(--color-on-surface-variant); text-decoration:none;">
                                <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                            </a>
                            <form method="POST" action="{{ route('admin.expenses.destroy', $expense) }}" onsubmit="return confirm('Supprimer cette dépense ?')">
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
    @if($expenses->hasPages())
    <div style="padding:1rem 1.25rem; border-top:1px solid var(--color-surface-container);">
        {{ $expenses->links('vendor.pagination.simple-default') }}
    </div>
    @endif
    @endif
</div>
@endsection
