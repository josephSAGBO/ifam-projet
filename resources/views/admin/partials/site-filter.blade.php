<div>
    <label for="site_id" style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">
        Site
    </label>
    <select id="site_id" name="site_id" onchange="this.form.submit()" style="height:40px; min-width:190px; padding:0 0.75rem; border:1px solid var(--color-outline-variant); border-radius:0.5rem; background:var(--color-surface-container-lowest); color:var(--color-on-surface);">
        <option value="">Tous les sites accessibles</option>
        @foreach($sites as $site)
            <option value="{{ $site->id }}" @selected($selectedSiteId === $site->id)>{{ $site->nom }}</option>
        @endforeach
    </select>
</div>
