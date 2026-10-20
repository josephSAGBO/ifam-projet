{{--
    Composant : x-admin.nav-link
    Lien de navigation simple dans la sidebar admin.

    Props:
      $route  — nom de la route Laravel (ex: 'admin.dashboard')
      $icon   — nom de l'icône Material Symbols (ex: 'grid_view')
      $label  — texte affiché
--}}
@props(['route', 'icon', 'label'])

@php
    $isActive = request()->routeIs($route) || request()->routeIs($route . '.*');
@endphp

<a href="{{ route($route) }}"
   style="display:flex; align-items:center; gap:0.75rem; padding:0.625rem 0.75rem; border-radius:0.75rem;
          text-decoration:none; transition:background 0.15s, color 0.15s;
          {{ $isActive
              ? 'background:var(--color-primary-container); color:var(--color-on-primary); font-weight:600; box-shadow:0 2px 6px rgba(228,0,102,0.25);'
              : 'color:var(--color-on-surface-variant);'
          }}"
   @if(!$isActive)
   onmouseover="this.style.background='var(--color-surface-container-high)'; this.style.color='var(--color-on-surface)';"
   onmouseout="this.style.background=''; this.style.color='var(--color-on-surface-variant)';"
   @endif>
    <span class="material-symbols-outlined" style="font-size:20px;">{{ $icon }}</span>
    <span style="font-size:0.875rem;">{{ $label }}</span>
</a>
