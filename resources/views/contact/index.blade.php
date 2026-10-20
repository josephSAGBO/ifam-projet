@extends('layouts.role')

@section('title', 'Contacter l\'Administration')
@section('role_label', auth()->user()->getRoleNames()->first() ?? 'Utilisateur')
@section('home_route', match(true) {
    auth()->user()->hasRole('etudiant') => route('student.dashboard'),
    auth()->user()->hasRole('enseignant') => route('teacher.dashboard'),
    auth()->user()->hasRole('parent') => route('parent.dashboard'),
    auth()->user()->hasRole('direction') => route('direction.dashboard'),
    default => '/',
})

@section('nav_links')
    @if(auth()->user()->hasRole('etudiant'))
        @include('student.partials.nav')
    @elseif(auth()->user()->hasRole('enseignant'))
        @include('teacher.partials.nav')
    @elseif(auth()->user()->hasRole('parent'))
        @include('parent.partials.nav')
    @endif
@endsection

@section('content')

<div style="max-width:54rem; margin:0 auto;">
    <div style="margin-bottom:2rem;">
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.375rem;">
            Contacter l'Administration
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Un problème pédagogique, administratif ou une question ? Envoyez directement votre message aux administrateurs de l'IFAM.
        </p>
    </div>

    @if($errors->any())
    <div style="padding:1rem; border-radius:0.75rem; background:#fef2f2; border:1px solid #fecaca; color:#991b1b; margin-bottom:1.5rem; font-size:0.875rem;">
        <div style="font-weight:700; margin-bottom:0.5rem; display:flex; align-items:center; gap:0.5rem;">
            <span class="material-symbols-outlined" style="font-size:20px;">error</span>
            Veuillez corriger les erreurs suivantes :
        </div>
        <ul style="margin:0; padding-left:1.25rem;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Formulaire d'envoi --}}
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); padding:2rem; margin-bottom:2.5rem;">
        <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1.25rem; display:flex; align-items:center; gap:0.5rem;">
            <span class="material-symbols-outlined" style="color:var(--color-primary-container);">outgoing_mail</span>
            Nouveau message
        </h2>

        <form method="POST" action="{{ route('contact.store') }}">
            @csrf

            <div style="display:flex; flex-direction:column; gap:1.25rem;">
                <div>
                    <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                        Objet du message <span style="color:#e40066;">*</span>
                    </label>
                    <input type="text" name="subject" value="{{ old('subject') }}" required placeholder="Ex. : Question sur mon inscription, Demande d'attestation…"
                           style="width:100%; height:44px; padding:0 0.875rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; color:var(--color-on-surface);">
                </div>

                <div>
                    <label style="display:block; font-size:0.875rem; font-weight:600; color:var(--color-on-surface); margin-bottom:0.5rem;">
                        Votre message <span style="color:#e40066;">*</span>
                    </label>
                    <textarea name="message" rows="5" required placeholder="Détaillez clairement votre situation ou votre demande…"
                              style="width:100%; padding:0.75rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface); font-size:0.875rem; color:var(--color-on-surface);">{{ old('message') }}</textarea>
                </div>

                <div style="display:flex; justify-content:flex-end;">
                    <button type="submit"
                            style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.5rem; border-radius:0.5rem; border:none;
                                   background:var(--color-primary-container); color:var(--color-on-primary); font-weight:700; font-size:0.875rem; cursor:pointer;
                                   box-shadow:0 3px 10px rgba(228,0,102,0.3); transition:all 0.2s;"
                            onmouseover="this.style.transform='translateY(-1px)'"
                            onmouseout="this.style.transform=''">
                        <span class="material-symbols-outlined" style="font-size:18px;">send</span>
                        Envoyer le message
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Historique des messages --}}
    <div>
        <h2 style="font-size:1.125rem; font-weight:700; color:var(--color-on-surface); margin:0 0 1rem; display:flex; align-items:center; gap:0.5rem;">
            <span class="material-symbols-outlined" style="color:var(--color-on-surface-variant);">history</span>
            Historique de vos échanges avec l'administration
        </h2>

        @if($messages->isEmpty())
        <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:3rem 1.5rem; text-align:center; color:var(--color-on-surface-variant); box-shadow:0 1px 6px rgba(11,28,48,0.04);">
            <span class="material-symbols-outlined" style="font-size:40px; opacity:0.3; display:block; margin-bottom:0.75rem;">inbox</span>
            <p style="margin:0; font-size:0.875rem;">Vous n'avez pas encore envoyé de message.</p>
        </div>
        @else
        <div style="display:flex; flex-direction:column; gap:0.875rem;">
            @foreach($messages as $msg)
            <div style="background:var(--color-surface-container-lowest); border-radius:0.75rem; padding:1.25rem; box-shadow:0 1px 4px rgba(11,28,48,0.04); border-left:4px solid {{ $msg->is_read ? '#059669' : '#e40066' }};">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.5rem;">
                    <strong style="font-size:0.9375rem; color:var(--color-on-surface);">{{ $msg->subject }}</strong>
                    <div style="display:flex; align-items:center; gap:0.5rem;">
                        @if($msg->is_read)
                            <span style="font-size:0.6875rem; font-weight:700; padding:0.1875rem 0.5rem; border-radius:9999px; background:#ecfdf5; color:#065f46;">
                                Lu par l'administration ({{ $msg->read_at ? $msg->read_at->diffForHumans() : '' }})
                            </span>
                        @else
                            <span style="font-size:0.6875rem; font-weight:700; padding:0.1875rem 0.5rem; border-radius:9999px; background:#fff1f2; color:#be123c;">
                                En attente de lecture
                            </span>
                        @endif
                        <span style="font-size:0.75rem; color:var(--color-on-surface-variant);">
                            {{ $msg->created_at->format('d/m/Y à H:i') }}
                        </span>
                    </div>
                </div>
                <p style="font-size:0.875rem; color:var(--color-on-surface); margin:0; white-space:pre-wrap; line-height:1.5;">{{ $msg->message }}</p>
            </div>
            @endforeach
        </div>

        @if($messages->hasPages())
        <div style="margin-top:1rem;">
            {{ $messages->links() }}
        </div>
        @endif
        @endif
    </div>
</div>

@endsection
