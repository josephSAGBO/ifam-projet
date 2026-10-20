@extends('layouts.admin')

@section('title', 'Mise à jour du dossier parent')

@section('content')

<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.75rem;">
    <a href="{{ route('admin.parents.show', $parent) }}"
       style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container);
              display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);
              transition:all 0.15s;"
       onmouseover="this.style.background='var(--color-surface-container-high)'"
       onmouseout="this.style.background='var(--color-surface-container)'">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Dossier Parent : {{ $parent->full_name }}
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Complétez ou mettez à jour les informations et la fratrie rattachée.
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.parents.update', $parent) }}"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:2rem; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
    @csrf
    @method('PUT')

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:2rem;">

        <div style="grid-column:1 / -1; padding-bottom:1rem; border-bottom:1px solid var(--color-surface-container); margin-bottom:0.5rem;">
            <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">Informations professionnelles</h2>
        </div>

        <div>
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Profession
            </label>
            <input type="text" name="profession" value="{{ old('profession', $profile->profession) }}"
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                          border:1.5px solid {{ $errors->has('profession') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                          background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);">
            @error('profession') <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p> @enderror
        </div>

        <div>
            <label style="display:block; font-size:0.8125rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                Employeur
            </label>
            <input type="text" name="employer" value="{{ old('employer', $profile->employer) }}"
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.75rem;
                          border:1.5px solid {{ $errors->has('employer') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                          background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface);">
            @error('employer') <p style="margin:0.375rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p> @enderror
        </div>

        <div style="grid-column:1 / -1; padding-bottom:1rem; border-bottom:1px solid var(--color-surface-container); margin-top:1rem; margin-bottom:0.5rem;">
            <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">Enfants rattachés</h2>
            <p style="font-size:0.8125rem; color:var(--color-on-surface-variant); margin:0;">
                Sélectionnez les étudiants et précisez le lien de parenté (Père, Mère, Tuteur...).
            </p>
        </div>

        <div style="grid-column:1 / -1;" x-data="childrenManager()">
            
            <div id="children-container" style="display:flex; flex-direction:column; gap:1rem; margin-bottom:1rem;">
                {{-- Affichage dynamique via Alpine --}}
                <template x-for="(child, index) in childrenList" :key="index">
                    <div style="display:grid; grid-template-columns:1fr 1fr auto; gap:1rem; align-items:center; background:var(--color-surface-container-low); padding:1rem; border-radius:0.75rem; border:1px solid var(--color-outline-variant);">
                        
                        <div>
                            <label style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.25rem;">Étudiant</label>
                            <select x-model="child.id" :name="'children[' + index + ']'" required
                                    data-searchable-select="Rechercher un étudiant par nom, matricule ou e-mail…"
                                    style="width:100%; height:40px; padding:0 0.75rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-lowest); font-size:0.875rem;">
                                <option value="">Sélectionner un étudiant...</option>
                                @foreach($allStudents as $student)
                                    <option value="{{ $student->id }}">{{ $student->full_name }} ({{ $student->matricule ?? 'Sans matricule' }} · {{ $student->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.25rem;">Lien (ex: Père, Mère)</label>
                            <input type="text" x-model="child.relationship" :name="'relationships[' + child.id + ']'" placeholder="Ex: Père"
                                   style="width:100%; height:40px; padding:0 0.75rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-lowest); font-size:0.875rem;">
                        </div>

                        <div style="padding-top:1.25rem;">
                            <button type="button" @click="removeChild(index)"
                                    style="width:36px; height:36px; display:flex; align-items:center; justify-content:center; border:none; background:none; color:var(--color-error); cursor:pointer; border-radius:0.5rem;"
                                    onmouseover="this.style.background='rgba(179,38,30,0.1)'" onmouseout="this.style.background='none'">
                                <span class="material-symbols-outlined" style="font-size:20px;">delete</span>
                            </button>
                        </div>

                    </div>
                </template>
            </div>

            <button type="button" @click="addChild()"
                    style="display:inline-flex; align-items:center; gap:0.5rem; height:40px; padding:0 1rem; border-radius:0.75rem; border:1.5px dashed var(--color-outline-variant); background:none; color:var(--color-primary); font-weight:600; font-size:0.875rem; cursor:pointer;"
                    onmouseover="this.style.background='var(--color-surface-container-low)'" onmouseout="this.style.background='none'">
                <span class="material-symbols-outlined" style="font-size:20px;">add</span>
                Ajouter un enfant
            </button>
            
            @error('children') <p style="margin:0.5rem 0 0; font-size:0.75rem; color:var(--color-error);">{{ $message }}</p> @enderror

        </div>

        <div style="grid-column:1 / -1; padding-bottom:1rem; border-bottom:1px solid var(--color-surface-container); margin-top:1rem; margin-bottom:0.5rem;">
            <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">Notes Administratives</h2>
        </div>

        <div style="grid-column:1 / -1;">
            <textarea name="admin_notes" rows="3"
                      style="width:100%; box-sizing:border-box; padding:1rem; border-radius:0.75rem; resize:vertical;
                             border:1.5px solid {{ $errors->has('admin_notes') ? 'var(--color-error)' : 'var(--color-outline-variant)' }};
                             background:var(--color-surface-container-low); font-size:0.875rem; color:var(--color-on-surface); font-family:inherit;">{{ old('admin_notes', $profile->admin_notes) }}</textarea>
        </div>

    </div>

    <div style="display:flex; justify-content:flex-end; gap:1rem; padding-top:1.5rem; border-top:1px solid var(--color-surface-container);">
        <a href="{{ route('admin.parents.show', $parent) }}"
           style="height:44px; display:inline-flex; align-items:center; padding:0 1.5rem; border-radius:0.75rem;
                  background:var(--color-surface-container-high); color:var(--color-on-surface); text-decoration:none;
                  font-size:0.875rem; font-weight:600;">
            Annuler
        </a>
        <button type="submit"
                style="height:44px; padding:0 1.5rem; border-radius:0.75rem; border:none; cursor:pointer;
                       background:var(--color-primary-container); color:var(--color-on-primary);
                       font-size:0.875rem; font-weight:700; box-shadow:0 3px 10px rgba(228,0,102,0.3);">
            Enregistrer le dossier
        </button>
    </div>
</form>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('childrenManager', () => ({
            childrenList: [
                @foreach($parent->children as $child)
                {
                    id: '{{ $child->id }}',
                    relationship: '{{ addslashes($child->pivot->relationship ?? "") }}'
                },
                @endforeach
            ],
            addChild() {
                this.childrenList.push({ id: '', relationship: '' });
            },
            removeChild(index) {
                this.childrenList.splice(index, 1);
            }
        }));
    });
</script>

@endsection
