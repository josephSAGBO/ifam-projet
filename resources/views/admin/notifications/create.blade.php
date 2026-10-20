@extends('layouts.admin')
@section('title', 'Envoyer une Notification')
@section('content')

<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.75rem;">
    <a href="{{ route('admin.notifications.index') }}" style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container); display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">Envoyer une Notification</h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">Diffuser un message à un ou plusieurs utilisateurs.</p>
    </div>
</div>

<form action="{{ route('admin.notifications.store') }}" method="POST"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:2rem; max-width:800px; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
    @csrf
    <div style="display:grid; gap:1.5rem;">

        {{-- Recipients --}}
        <div>
            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.75rem;">Destinataires <span style="color:#dc2626;">*</span></label>
            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:0.75rem;" id="recipientOptions">
                @foreach(['all'=>'Tous les utilisateurs','students'=>'Étudiants','teachers'=>'Enseignants','individual'=>'Individuel'] as $val => $label)
                <label style="cursor:pointer;">
                    <input type="radio" name="recipients" value="{{ $val }}" {{ old('recipients','all')===$val?'checked':'' }}
                           style="display:none;" onchange="toggleIndividual()">
                    <div class="recipient-card" data-val="{{ $val }}"
                         style="padding:0.875rem; border-radius:0.75rem; border:2px solid {{ old('recipients','all')===$val ? 'var(--color-primary-container)' : 'var(--color-outline-variant)' }};
                                background:{{ old('recipients','all')===$val ? 'rgba(228,0,102,0.05)' : 'transparent' }};
                                text-align:center; transition:all 0.15s;">
                        <span class="material-symbols-outlined" style="font-size:24px; display:block; margin-bottom:0.25rem; color:var(--color-primary);">
                            {{ ['all'=>'groups','students'=>'school','teachers'=>'person_search','individual'=>'person_add'][$val] }}
                        </span>
                        <span style="font-size:0.8125rem; font-weight:600; color:var(--color-on-surface);">{{ $label }}</span>
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        {{-- Individual users select --}}
        <div id="individualSelect" style="{{ old('recipients')==='individual' ? '' : 'display:none;' }}">
            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Utilisateurs concernés <span style="color:#dc2626;">*</span></label>
            <select name="user_ids[]" multiple style="width:100%; box-sizing:border-box; height:160px; padding:0.5rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.875rem;">
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ in_array($user->id, old('user_ids', [])) ? 'selected' : '' }}>
                        {{ $user->last_name }} {{ $user->first_name }} ({{ $user->email }})
                    </option>
                @endforeach
            </select>
            <p style="font-size:0.75rem; color:var(--color-on-surface-variant); margin:0.375rem 0 0;">Ctrl+clic pour sélectionner plusieurs.</p>
        </div>

        {{-- Title --}}
        <div>
            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Titre <span style="color:#dc2626;">*</span></label>
            <input type="text" name="title" value="{{ old('title') }}" required placeholder="ex: Rappel de réunion, Nouvelle évaluation…"
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
            @error('title') <span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span> @enderror
        </div>

        {{-- Message --}}
        <div>
            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Message <span style="color:#dc2626;">*</span></label>
            <textarea name="message" rows="4" required style="width:100%; box-sizing:border-box; padding:0.75rem 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem; resize:vertical;">{{ old('message') }}</textarea>
            @error('message') <span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span> @enderror
        </div>

        <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:1.5rem;">
            {{-- Type --}}
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Type <span style="color:#dc2626;">*</span></label>
                <select name="type" required style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
                    <option value="info" {{ old('type')=='info'?'selected':'' }}>ℹ️ Information</option>
                    <option value="success" {{ old('type')=='success'?'selected':'' }}>✅ Succès</option>
                    <option value="warning" {{ old('type')=='warning'?'selected':'' }}>⚠️ Avertissement</option>
                    <option value="danger" {{ old('type')=='danger'?'selected':'' }}>🚨 Urgent</option>
                </select>
            </div>
            {{-- Icon --}}
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Icône</label>
                <input type="text" name="icon" value="{{ old('icon', 'notifications') }}" placeholder="ex: notifications, warning…"
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
                <p style="font-size:0.7rem; margin:0.25rem 0 0; color:var(--color-on-surface-variant);">Nom d'icône Material Symbols</p>
            </div>
            {{-- Link --}}
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Lien (optionnel)</label>
                <input type="text" name="link" value="{{ old('link') }}" placeholder="ex: /admin/fees"
                       style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
            </div>
        </div>

    </div>

    <div style="margin-top:2rem; display:flex; justify-content:flex-end; gap:1rem;">
        <a href="{{ route('admin.notifications.index') }}" style="height:44px; display:inline-flex; align-items:center; padding:0 1.25rem; border-radius:0.75rem; color:var(--color-on-surface-variant); font-weight:600; text-decoration:none;">Annuler</a>
        <button type="submit" style="height:44px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1.5rem; border-radius:0.75rem; border:none; cursor:pointer; background:var(--color-primary-container); color:var(--color-on-primary); font-weight:700; box-shadow:0 3px 10px rgba(228,0,102,0.3);">
            <span class="material-symbols-outlined" style="font-size:20px;">send</span> Envoyer
        </button>
    </div>
</form>

<script>
function toggleIndividual() {
    const val = document.querySelector('input[name="recipients"]:checked').value;
    document.getElementById('individualSelect').style.display = val === 'individual' ? '' : 'none';

    // Update card styles
    document.querySelectorAll('.recipient-card').forEach(card => {
        const isActive = card.dataset.val === val;
        card.style.borderColor = isActive ? 'var(--color-primary-container)' : 'var(--color-outline-variant)';
        card.style.background  = isActive ? 'rgba(228,0,102,0.05)' : 'transparent';
    });
}

// Sync card clicks with radio inputs
document.querySelectorAll('.recipient-card').forEach(card => {
    card.addEventListener('click', () => {
        const radio = document.querySelector(`input[value="${card.dataset.val}"]`);
        if (radio) { radio.checked = true; toggleIndividual(); }
    });
});
</script>
@endsection
