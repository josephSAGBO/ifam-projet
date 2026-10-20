@extends('layouts.admin')

@section('title', 'Gestion des Partenariats')

@section('content')

{{-- En-tête de la page --}}
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:2rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem; display:flex; align-items:center; gap:0.625rem;">
            <span class="material-symbols-outlined" style="color:var(--color-primary); font-size:28px;">handshake</span>
            Partenariats & Conventions
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Suivi des partenaires, offres mutuelles, durée et interlocuteurs internes
        </p>
    </div>
    <a href="{{ route('admin.partnerships.create') }}"
       style="height:44px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1.25rem; border-radius:0.75rem;
              background:var(--color-primary-container); color:var(--color-on-primary); text-decoration:none;
              font-size:0.875rem; font-weight:700; box-shadow:0 3px 10px rgba(182,0,80,0.25); transition:all 0.15s;"
       onmouseover="this.style.opacity='0.9'"
       onmouseout="this.style.opacity='1'">
        <span class="material-symbols-outlined" style="font-size:20px;">add_handshake</span>
        Nouveau Partenariat
    </a>
</div>

{{-- Barre de filtres et recherche --}}
<form method="GET" action="{{ route('admin.partnerships.index') }}"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:1.25rem 1.5rem; margin-bottom:1.5rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)) auto; gap:1rem; align-items:end;">
    <div>
        <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); margin-bottom:0.375rem; text-transform:uppercase; letter-spacing:0.06em;">Recherche</label>
        <div style="position:relative;">
            <span class="material-symbols-outlined" style="position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); font-size:18px; color:var(--color-on-surface-variant);">search</span>
            <input type="text" name="search" value="{{ $search }}" placeholder="Raison sociale, contact, secteur..."
                   style="width:100%; height:42px; padding:0 0.875rem 0 2.5rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; color:var(--color-on-surface); box-sizing:border-box;">
        </div>
    </div>

    <div>
        <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); margin-bottom:0.375rem; text-transform:uppercase; letter-spacing:0.06em;">Type de partenariat</label>
        <select name="type" style="width:100%; height:42px; padding:0 0.875rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; color:var(--color-on-surface); box-sizing:border-box;">
            <option value="">Tous les types</option>
            @foreach($types as $key => $label)
                <option value="{{ $key }}" {{ $type === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label style="display:block; font-size:0.75rem; font-weight:700; color:var(--color-on-surface-variant); margin-bottom:0.375rem; text-transform:uppercase; letter-spacing:0.06em;">Statut</label>
        <select name="statut" style="width:100%; height:42px; padding:0 0.875rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; color:var(--color-on-surface); box-sizing:border-box;">
            <option value="">Tous les statuts</option>
            <option value="actif" {{ $statut === 'actif' ? 'selected' : '' }}>Actif</option>
            <option value="en_discussion" {{ $statut === 'en_discussion' ? 'selected' : '' }}>En discussion</option>
            <option value="inactif" {{ $statut === 'inactif' ? 'selected' : '' }}>Inactif</option>
        </select>
    </div>

    <div style="display:flex; gap:0.5rem;">
        <button type="submit" style="height:42px; padding:0 1.25rem; border-radius:0.5rem; border:none; background:var(--color-primary-container); color:var(--color-on-primary); font-weight:700; font-size:0.875rem; cursor:pointer;">
            Filtrer
        </button>
        @if($search || $type || $statut)
            <a href="{{ route('admin.partnerships.index') }}" style="height:42px; display:inline-flex; align-items:center; padding:0 1rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); color:var(--color-on-surface-variant); font-size:0.8125rem; font-weight:600; text-decoration:none;">
                Effacer
            </a>
        @endif
    </div>
</form>

{{-- Table / Grille des Partenariats --}}
<div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden; border:1px solid var(--color-surface-container);">
    @if($partnerships->isEmpty())
        <div style="padding:4rem 1.5rem; text-align:center;">
            <span class="material-symbols-outlined" style="font-size:56px; color:var(--color-on-surface-variant); opacity:0.3; display:block; margin-bottom:1rem;">handshake</span>
            <h3 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.5rem;">Aucun partenariat trouvé</h3>
            <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0 0 1.5rem;">
                Commencez par ajouter une nouvelle convention ou adaptez vos filtres de recherche.
            </p>
            <a href="{{ route('admin.partnerships.create') }}" style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem; border-radius:0.5rem; background:var(--color-primary-container); color:var(--color-on-primary); text-decoration:none; font-weight:700; font-size:0.875rem;">
                <span class="material-symbols-outlined" style="font-size:18px;">add</span> Créer un partenariat
            </a>
        </div>
    @else
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
                <thead>
                    <tr style="background:var(--color-surface-container-low); border-bottom:2px solid var(--color-outline-variant);">
                        <th style="text-align:left; padding:0.875rem 1.25rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Partenaire & Contact</th>
                        <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Type & Secteur</th>
                        <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Durée</th>
                        <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Personne ressource</th>
                        <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Dernier Contact</th>
                        <th style="text-align:center; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Statut</th>
                        <th style="text-align:right; padding:0.875rem 1.25rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($partnerships as $p)
                        <tr style="border-bottom:1px solid var(--color-surface-container); transition:background 0.15s;" onmouseover="this.style.background='var(--color-surface-container-low)'" onmouseout="this.style.background='transparent'">
                            <td style="padding:1rem 1.25rem;">
                                <div style="font-weight:700; color:var(--color-on-surface); font-size:0.9375rem;">{{ $p->nom }}</div>
                                @if($p->full_contact_name)
                                    <div style="font-size:0.8125rem; color:var(--color-on-surface-variant); display:flex; align-items:center; gap:0.375rem; margin-top:0.125rem;">
                                        <span class="material-symbols-outlined" style="font-size:14px;">person</span>
                                        {{ $p->full_contact_name }}
                                    </div>
                                @endif
                                @if($p->telephone || $p->email)
                                    <div style="font-size:0.75rem; color:var(--color-on-surface-variant); font-family:monospace; margin-top:0.25rem;">
                                        {{ $p->telephone }} {{ $p->telephone && $p->email ? '•' : '' }} {{ $p->email }}
                                    </div>
                                @endif
                            </td>
                            <td style="padding:1rem;">
                                <span style="display:inline-block; padding:0.25rem 0.625rem; border-radius:0.375rem; font-size:0.75rem; font-weight:700; background:var(--color-primary-container); color:var(--color-on-primary);">
                                    {{ $types[$p->type_partenariat] ?? $p->type_partenariat }}
                                </span>
                                @if($p->secteur_intervention)
                                    <div style="font-size:0.75rem; color:var(--color-on-surface-variant); margin-top:0.375rem;">
                                        Secteur : {{ $p->secteur_intervention }}
                                    </div>
                                @endif
                            </td>
                            <td style="padding:1rem; font-weight:600; color:var(--color-on-surface);">
                                {{ $p->duree ?? 'Non spécifiée' }}
                            </td>
                            <td style="padding:1rem;">
                                @if($p->personne_ressource_interne)
                                    <div style="font-weight:600; color:var(--color-on-surface); font-size:0.8125rem; display:flex; align-items:center; gap:0.375rem;">
                                        <span class="material-symbols-outlined" style="font-size:16px; color:var(--color-primary);">badge</span>
                                        {{ $p->personne_ressource_interne }}
                                    </div>
                                @else
                                    <span style="color:var(--color-on-surface-variant); font-style:italic; font-size:0.75rem;">Non désignée</span>
                                @endif
                            </td>
                            <td style="padding:1rem; font-size:0.8125rem; color:var(--color-on-surface);">
                                @if($p->date_dernier_contact)
                                    <span style="font-weight:600;">{{ $p->date_dernier_contact->format('d/m/Y') }}</span>
                                    <div style="font-size:0.75rem; color:var(--color-on-surface-variant);">il y a {{ $p->date_dernier_contact->diffForHumans() }}</div>
                                @else
                                    <span style="color:var(--color-on-surface-variant); font-style:italic;">Aucun appel enregistré</span>
                                @endif
                            </td>
                            <td style="padding:1rem; text-align:center;">
                                @if($p->statut === 'actif')
                                    <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.75rem; font-weight:700; background:#ecfdf5; color:#065f46;">
                                        ● Actif
                                    </span>
                                @elseif($p->statut === 'en_discussion')
                                    <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.75rem; font-weight:700; background:#fffbe6; color:#92400e;">
                                        ● En discussion
                                    </span>
                                @else
                                    <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.75rem; font-weight:700; background:#fef2f2; color:#991b1b;">
                                        ● Inactif
                                    </span>
                                @endif
                            </td>
                            <td style="padding:1rem 1.25rem; text-align:right;">
                                <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.5rem;">
                                    <a href="{{ route('admin.partnerships.show', $p) }}" title="Voir les détails"
                                       style="width:34px; height:34px; border-radius:0.5rem; display:flex; align-items:center; justify-content:center; background:var(--color-surface-container); color:var(--color-on-surface); text-decoration:none;">
                                        <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                                    </a>
                                    <a href="{{ route('admin.partnerships.edit', $p) }}" title="Modifier"
                                       style="width:34px; height:34px; border-radius:0.5rem; display:flex; align-items:center; justify-content:center; background:var(--color-surface-container); color:var(--color-on-surface); text-decoration:none;">
                                        <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                                    </a>
                                    <form action="{{ route('admin.partnerships.destroy', $p) }}" method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer ce partenariat ?');" style="margin:0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Supprimer" style="width:34px; height:34px; border-radius:0.5rem; border:none; cursor:pointer; background:#fef2f2; color:#dc2626; display:flex; align-items:center; justify-content:center;">
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

        @if($partnerships->hasPages())
            <div style="padding:1rem 1.25rem; border-top:1px solid var(--color-surface-container);">
                {{ $partnerships->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
