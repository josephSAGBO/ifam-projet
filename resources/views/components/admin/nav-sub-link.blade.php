{{--
    Composant : x-admin.nav-sub-link
    Sous-lien de navigation dans un groupe accordéon de la sidebar admin.

    Props:
      $route  — nom de la route Laravel (ex: 'admin.users.index')
      $label  — texte affiché
--}}
@props(['route', 'label'])

@php
    $isActive = request()->routeIs($route) || request()->routeIs($route . '.*');
@endphp

<a href="{{ route($route) }}"
   style="display:flex; align-items:center; gap:0.5rem; padding:0.5rem 0.75rem; border-radius:0.625rem;
          text-decoration:none; font-size:0.8125rem; transition:background 0.15s, color 0.15s;
          {{ $isActive
              ? 'background:var(--color-primary-container); color:var(--color-on-primary); font-weight:600;'
              : 'color:var(--color-on-surface-variant);'
          }}"
   @if(!$isActive)
   onmouseover="this.style.background='var(--color-surface-container-high)'; this.style.color='var(--color-on-surface)';"
   onmouseout="this.style.background=''; this.style.color='var(--color-on-surface-variant)';"
   @endif>
    <span style="width:6px; height:6px; border-radius:9999px; flex-shrink:0;
                 background: {{ $isActive ? 'var(--color-on-primary)' : 'var(--color-outline-variant)' }};"></span>
    {{ $label }}
</a>
