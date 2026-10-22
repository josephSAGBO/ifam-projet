@props([
    'alumnis',
    'title',
    'showAllLink' => true,
    'featured' => false,
])

<section class="student-dashboard-panel" aria-label="{{ $title }}"
         style="margin-top:1.5rem; background:{{ $featured ? 'linear-gradient(135deg, #fff7ed 0%, #fff 55%, #f5f3ff 100%)' : 'var(--color-surface-container-lowest)' }}; border:{{ $featured ? '1px solid #fed7aa' : '1px solid transparent' }}; border-radius:1rem; padding:1.5rem; box-shadow:{{ $featured ? '0 8px 24px rgba(124, 45, 18, 0.08)' : '0 1px 6px rgba(11,28,48,0.04)' }};">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1.25rem;">
        <div>
            @if($featured)
                <div style="display:flex; align-items:center; gap:0.375rem; margin-bottom:0.3rem; color:#c2410c; font-size:0.6875rem; font-weight:800; text-transform:uppercase; letter-spacing:0.09em;">
                    <span class="material-symbols-outlined" aria-hidden="true" style="font-size:16px;">auto_awesome</span>
                    Parcours inspirants
                </div>
            @endif
            <h2 style="font-size:{{ $featured ? '1.25rem' : '1.125rem' }}; font-weight:800; color:var(--color-on-surface); margin:0;">{{ $title }}</h2>
        </div>
        @if($showAllLink)
            <a href="{{ route('alumni.directory.index') }}"
               style="display:inline-flex; align-items:center; gap:0.375rem; color:var(--color-primary); font-size:0.8125rem; font-weight:700; text-decoration:none;">
                Voir tous les alumni
                <span class="material-symbols-outlined" aria-hidden="true" style="font-size:18px;">arrow_forward</span>
            </a>
        @endif
    </div>

    @if($alumnis->isEmpty())
        <p style="margin:0; color:var(--color-on-surface-variant); font-size:0.875rem;">Aucun profil alumni visible pour le moment.</p>
    @else
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(min(100%, 250px), 1fr)); gap:1rem;">
            @foreach($alumnis as $alumni)
                <article style="display:flex; flex-direction:column; gap:0.75rem; padding:1rem; border:1px solid var(--color-outline-variant); border-radius:0.875rem; background:var(--color-surface-container-lowest);">
                    <div style="display:flex; align-items:center; gap:0.75rem;">
                        @if($alumni->photo_path)
                            <img src="{{ Storage::url($alumni->photo_path) }}" alt="Photo de {{ $alumni->full_name }}"
                                 style="width:3.25rem; height:3.25rem; border-radius:50%; object-fit:cover;">
                        @else
                            <div aria-hidden="true" class="student-dashboard-avatar student-dashboard-avatar-fallback"
                                 style="width:3.25rem; height:3.25rem; min-width:3.25rem; font-size:0.875rem;">
                                {{ $alumni->initials }}
                            </div>
                        @endif
                        <div style="min-width:0;">
                            <h3 style="margin:0; color:var(--color-on-surface); font-size:0.9375rem; font-weight:700;">{{ $alumni->full_name }}</h3>
                            <p style="margin:0.2rem 0 0; color:var(--color-on-surface-variant); font-size:0.8125rem;">
                                {{ $alumni->formation?->name ?? 'Formation IFAM' }}
                                @if($alumni->promotion_year)
                                    <span aria-hidden="true">·</span> {{ $alumni->promotion_year }}
                                @endif
                            </p>
                        </div>
                    </div>

                    @php
                        $activity = $alumni->poste_actuel ?: $alumni->current_position;
                        $company = $alumni->entreprise_actuelle ?: $alumni->company;
                    @endphp
                    @if($activity || $company || $alumni->statut_actuel)
                        <p style="margin:0; color:var(--color-on-surface); font-size:0.8125rem; font-weight:600;">
                            {{ $activity ?: $alumni->statut_actuel }}@if(($activity || $alumni->statut_actuel) && $company) · @endif{{ $company }}
                        </p>
                    @endif

                    @if($alumni->bio)
                        <p style="margin:0; color:var(--color-on-surface-variant); font-size:0.8125rem; line-height:1.5;">{{ \Illuminate\Support\Str::limit($alumni->bio, 240) }}</p>
                    @endif

                    @if($alumni->posts->isNotEmpty())
                        <div>
                            <p style="margin:0 0 0.375rem; color:var(--color-on-surface); font-size:0.75rem; font-weight:700;">Créations</p>
                            <ul style="margin:0; padding-left:1.125rem; color:var(--color-on-surface-variant); font-size:0.8125rem;">
                                @foreach($alumni->posts as $creation)
                                    <li><a href="{{ route('alumni.public.posts.show', $creation->slug) }}" style="color:var(--color-primary);">{{ $creation->title }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    @endif
</section>
