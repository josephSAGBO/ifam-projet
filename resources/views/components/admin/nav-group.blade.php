{{--
    Composant : x-admin.nav-group
    Groupe de navigation avec sous-items (accordéon) dans la sidebar admin.

    Props:
      $label — libellé du groupe (ex: 'Administration')
      $icon  — icône Material Symbols (ex: 'admin_panel_settings')

    Slot par défaut : les x-admin.nav-sub-link enfants
--}}
@props(['label', 'icon'])

@php
    // Le groupe est ouvert si un de ses liens enfants est actif
    $isGroupActive = false;
    // On détecte l'activité via le contenu du slot (inspecté au runtime par Livewire/Blade)
    // Fallback : toujours ouvert si la requête est dans ce groupe
    $groupRoutePrefix = Str::slug($label);
@endphp

<div x-data="{ open: {{ request()->is('admin/' . Str::slug($label) . '/*') || request()->is('admin/' . Str::slug($label)) ? 'true' : 'false' }} }}"
     style="display:flex; flex-direction:column;">

    {{-- En-tête du groupe --}}
    <button @click="open = !open"
            style="display:flex; align-items:center; justify-content:space-between; width:100%;
                   padding:0.625rem 0.75rem; border-radius:0.75rem; border:none; cursor:pointer;
                   background:none; color:var(--color-on-surface); transition:background 0.15s;"
            onmouseover="this.style.background='var(--color-surface-container-low)'"
            onmouseout="this.style.background=''">
        <div style="display:flex; align-items:center; gap:0.75rem;">
            <span class="material-symbols-outlined" style="font-size:20px; color:var(--color-on-surface-variant);">{{ $icon }}</span>
            <span style="font-size:0.875rem; font-weight:500;">{{ $label }}</span>
        </div>
        <span class="material-symbols-outlined"
              style="font-size:18px; color:var(--color-on-surface-variant); transition:transform 0.2s;"
              :style="open ? 'transform:rotate(180deg)' : ''">expand_more</span>
    </button>

    {{-- Sous-liens (animés) --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="padding-left:2rem; padding-right:0.25rem; display:flex; flex-direction:column; gap:0.125rem;">
        {{ $slot }}
    </div>
</div>
