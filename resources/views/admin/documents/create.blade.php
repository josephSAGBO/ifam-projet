@extends('layouts.admin')
@section('title', 'Ajouter un Document')
@section('content')

<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.75rem;">
    <a href="{{ route('admin.documents.index') }}" style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container); display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">Ajouter un Document</h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">PDF, images, Word, Excel — max 20 Mo</p>
    </div>
</div>

<form action="{{ route('admin.documents.store') }}" method="POST" enctype="multipart/form-data"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:2rem; max-width:800px; box-shadow:0 1px 6px rgba(11,28,48,0.04);">
    @csrf
    <div style="display:grid; gap:1.5rem;">

        <div>
            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Titre du document <span style="color:#dc2626;">*</span></label>
            <input type="text" name="title" value="{{ old('title') }}" required placeholder="ex: CNI de l'étudiant, Diplôme du bac…"
                   style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
            @error('title') <span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span> @enderror
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Type <span style="color:#dc2626;">*</span></label>
                <select name="type" required style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
                    <option value="">Sélectionner</option>
                    @foreach($typeLabels as $key => $label)
                        <option value="{{ $key }}" {{ old('type')==$key?'selected':'' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('type') <span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span> @enderror
            </div>
            <div>
                <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Appartient à <span style="color:#dc2626;">*</span></label>
                <select name="owner_type" id="ownerType" onchange="updateOwners()" required
                        style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
                    <option value="">Sélectionner le type</option>
                    <option value="student" {{ old('owner_type')=='student'?'selected':'' }}>Étudiant</option>
                    <option value="teacher" {{ old('owner_type')=='teacher'?'selected':'' }}>Enseignant</option>
                </select>
            </div>
        </div>

        <div>
            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Personne concernée <span style="color:#dc2626;">*</span></label>
            <select name="owner_id" id="ownerSelect" required
                    style="width:100%; box-sizing:border-box; height:44px; padding:0 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem;">
                <option value="">Sélectionnez d'abord le type</option>
            </select>
            @error('owner_id') <span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span> @enderror
        </div>

        {{-- Drop zone --}}
        <div>
            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Fichier <span style="color:#dc2626;">*</span></label>
            <div id="dropZone"
                 onclick="document.getElementById('fileInput').click()"
                 ondragover="event.preventDefault(); this.style.borderColor='var(--color-primary)'"
                 ondragleave="this.style.borderColor='var(--color-outline-variant)'"
                 ondrop="handleDrop(event)"
                 style="border:2px dashed var(--color-outline-variant); border-radius:0.875rem; padding:2.5rem; text-align:center; cursor:pointer; transition:all 0.2s;">
                <span class="material-symbols-outlined" style="font-size:40px; color:var(--color-on-surface-variant); display:block; margin-bottom:0.75rem;">cloud_upload</span>
                <p style="margin:0 0 0.25rem; font-weight:600; color:var(--color-on-surface);">Glisser-déposer un fichier ici</p>
                <p style="margin:0; font-size:0.8125rem; color:var(--color-on-surface-variant);">ou cliquez pour parcourir — PDF, images, Word, Excel (max 20 Mo)</p>
                <div id="fileInfo" style="display:none; margin-top:1rem; padding:0.625rem 1rem; border-radius:0.625rem; background:var(--color-surface-container); font-size:0.8125rem; font-weight:600; color:var(--color-on-surface);"></div>
            </div>
            <input type="file" id="fileInput" name="file" class="sr-only" style="display:none;"
                   accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" onchange="showFileInfo(this)">
            @error('file') <span style="color:#dc2626; font-size:0.75rem;">{{ $message }}</span> @enderror
        </div>

        <div>
            <label style="display:block; font-size:0.875rem; font-weight:600; margin-bottom:0.5rem;">Notes</label>
            <textarea name="notes" rows="2" style="width:100%; box-sizing:border-box; padding:0.75rem 1rem; border-radius:0.625rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-low); font-size:0.9375rem; resize:vertical;">{{ old('notes') }}</textarea>
        </div>
    </div>

    <div style="margin-top:2rem; display:flex; justify-content:flex-end; gap:1rem;">
        <a href="{{ route('admin.documents.index') }}" style="height:44px; display:inline-flex; align-items:center; padding:0 1.25rem; border-radius:0.75rem; color:var(--color-on-surface-variant); font-weight:600; text-decoration:none;">Annuler</a>
        <button type="submit" style="height:44px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1.5rem; border-radius:0.75rem; border:none; cursor:pointer; background:var(--color-primary-container); color:var(--color-on-primary); font-weight:700; box-shadow:0 3px 10px rgba(228,0,102,0.3);">
            <span class="material-symbols-outlined" style="font-size:20px;">upload</span> Uploader
        </button>
    </div>
</form>

<script>
const students = @json($students->values()->map(fn($s) => ['id' => $s->id, 'name' => ($s->user->last_name ?? '') . ' ' . ($s->user->first_name ?? '')]));
const teachers = @json($teachers->values()->map(fn($t) => ['id' => $t->id, 'name' => ($t->user->last_name ?? '') . ' ' . ($t->user->first_name ?? '')]));

function updateOwners() {
    const type = document.getElementById('ownerType').value;
    const select = document.getElementById('ownerSelect');
    select.innerHTML = '<option value="">Sélectionner</option>';
    const list = type === 'student' ? students : (type === 'teacher' ? teachers : []);
    list.forEach(item => {
        const opt = document.createElement('option');
        opt.value = item.id;
        opt.textContent = item.name.trim() || 'N/A';
        select.appendChild(opt);
    });
}

function showFileInfo(input) {
    if (input.files && input.files[0]) {
        const f = input.files[0];
        const size = f.size > 1048576 ? (f.size/1048576).toFixed(1)+' Mo' : Math.round(f.size/1024)+' Ko';
        document.getElementById('fileInfo').style.display = 'block';
        document.getElementById('fileInfo').textContent = '📎 ' + f.name + ' (' + size + ')';
    }
}

function handleDrop(event) {
    event.preventDefault();
    document.getElementById('dropZone').style.borderColor = 'var(--color-outline-variant)';
    const file = event.dataTransfer.files[0];
    if (file) {
        const input = document.getElementById('fileInput');
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
        showFileInfo(input);
    }
}
</script>
@endsection
