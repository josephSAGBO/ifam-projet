@extends('layouts.admin')
@section('title', 'Catégories de Dépenses')
@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">Catégories de Dépenses</h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">Organiser les dépenses par catégories.</p>
    </div>
    <a href="{{ route('admin.expense-categories.create') }}"
       style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem;
              background:var(--color-primary-container); color:var(--color-on-primary); border-radius:0.75rem;
              text-decoration:none; font-size:0.875rem; font-weight:700; box-shadow:0 3px 10px rgba(228,0,102,0.3);">
        <span class="material-symbols-outlined" style="font-size:20px;">add_circle</span> Nouvelle catégorie
    </a>
</div>

<div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden;">
    @if($categories->isEmpty())
    <div style="padding:4rem 2rem; text-align:center; color:var(--color-on-surface-variant);">
        <span class="material-symbols-outlined" style="font-size:48px; opacity:0.25; display:block; margin-bottom:1rem;">category</span>
        <p style="margin:0;">Aucune catégorie créée.</p>
    </div>
    @else
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:1.25rem; padding:1.5rem;">
        @foreach($categories as $cat)
        <div style="border-radius:0.875rem; border:1px solid var(--color-outline-variant); padding:1.25rem; display:flex; align-items:flex-start; gap:1rem; transition:box-shadow 0.15s;"
             onmouseover="this.style.boxShadow='0 4px 15px rgba(11,28,48,0.08)'"
             onmouseout="this.style.boxShadow='none'">
            <div style="width:44px; height:44px; border-radius:0.75rem; background:{{ $cat->color }}22; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <span style="width:18px; height:18px; border-radius:50%; background:{{ $cat->color }}; display:block;"></span>
            </div>
            <div style="flex:1; min-width:0;">
                <div style="font-weight:700; color:var(--color-on-surface); margin-bottom:0.25rem;">{{ $cat->name }}</div>
                @if($cat->description)
                <div style="font-size:0.8125rem; color:var(--color-on-surface-variant); margin-bottom:0.5rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $cat->description }}</div>
                @endif
                <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">{{ $cat->expenses_count }} dépense(s)</div>
            </div>
            <div style="display:flex; flex-direction:column; gap:0.375rem;">
                <a href="{{ route('admin.expense-categories.edit', $cat) }}" style="width:32px; height:32px; border-radius:0.5rem; display:flex; align-items:center; justify-content:center; background:var(--color-surface-container); color:var(--color-on-surface-variant); text-decoration:none;">
                    <span class="material-symbols-outlined" style="font-size:16px;">edit</span>
                </a>
                <form method="POST" action="{{ route('admin.expense-categories.destroy', $cat) }}" onsubmit="return confirm('Supprimer cette catégorie ?')">
                    @csrf @method('DELETE')
                    <button type="submit" style="width:32px; height:32px; border-radius:0.5rem; border:none; cursor:pointer; background:var(--color-surface-container); color:#dc2626;">
                        <span class="material-symbols-outlined" style="font-size:16px;">delete</span>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
