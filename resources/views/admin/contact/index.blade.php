@extends('layouts.admin')

@section('title', 'Messages de contact')

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Boîte de réception
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Gérez les messages de contact des utilisateurs de la plateforme.
        </p>
    </div>
</div>

<div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden;">
    @if($messages->isEmpty())
    <div style="padding:4rem 2rem; text-align:center; color:var(--color-on-surface-variant);">
        <span class="material-symbols-outlined" style="font-size:48px; opacity:0.25; display:block; margin-bottom:1rem;">inbox</span>
        <p style="font-size:0.9375rem; margin:0;">Aucun message reçu pour le moment.</p>
    </div>
    @else
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
            <thead>
                <tr style="background:var(--color-surface-container-low); border-bottom:1px solid var(--color-outline-variant);">
                    <th style="width:40px; padding:0.875rem 1rem;"></th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Expéditeur</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Sujet</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Date</th>
                    <th style="padding:0.875rem 1rem;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($messages as $msg)
                <tr style="border-bottom:1px solid var(--color-surface-container); transition:background 0.15s; background:{{ $msg->is_read ? 'transparent' : 'var(--color-surface-container-lowest)' }}; font-weight:{{ $msg->is_read ? '400' : '700' }};"
                    onmouseover="this.style.background='var(--color-surface-container-low)'"
                    onmouseout="this.style.background='{{ $msg->is_read ? 'transparent' : 'var(--color-surface-container-lowest)' }}'">
                    
                    <td style="padding:0.875rem 1rem; text-align:center;">
                        @if(!$msg->is_read)
                            <div style="width:10px; height:10px; border-radius:50%; background:var(--color-primary); margin:auto;"></div>
                        @endif
                    </td>

                    <td style="padding:0.875rem 1rem;">
                        <div style="display:flex; align-items:center; gap:0.5rem;">
                            <span class="material-symbols-outlined" style="font-size:16px; color:var(--color-on-surface-variant);">account_circle</span>
                            {{ $msg->user->full_name ?? 'Utilisateur inconnu' }}
                        </div>
                    </td>
                    
                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface);">
                        {{ Str::limit($msg->subject, 60) }}
                    </td>

                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface-variant); font-size:0.8125rem;">
                        {{ $msg->created_at->format('d/m/Y H:i') }}
                    </td>

                    <td style="padding:0.875rem 1rem;">
                        <div style="display:flex; align-items:center; gap:0.5rem; justify-content:flex-end;">
                            <a href="{{ route('admin.contact.show', $msg) }}"
                               title="Lire le message"
                               style="height:34px; padding:0 0.75rem; border-radius:0.5rem; display:inline-flex; align-items:center; gap:0.375rem;
                                      background:var(--color-surface-container); color:var(--color-on-surface); text-decoration:none; font-weight:600; font-size:0.8125rem; transition:all 0.15s;"
                               onmouseover="this.style.background='var(--color-surface-container-high)'"
                               onmouseout="this.style.background='var(--color-surface-container)'">
                                <span class="material-symbols-outlined" style="font-size:18px;">mail</span>
                                Lire
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($messages->hasPages())
    <div style="padding:1rem 1.25rem; border-top:1px solid var(--color-surface-container); display:flex; align-items:center; justify-content:space-between;">
        <span style="font-size:0.8125rem; color:var(--color-on-surface-variant);">
            Affichage {{ $messages->firstItem() }}–{{ $messages->lastItem() }} sur {{ $messages->total() }}
        </span>
        <div style="display:flex; gap:0.375rem;">
            @if($messages->onFirstPage())
            <span style="padding:0.375rem 0.75rem; border-radius:0.5rem; background:var(--color-surface-container); color:var(--color-on-surface-variant); font-size:0.8125rem; opacity:0.5;">← Préc.</span>
            @else
            <a href="{{ $messages->previousPageUrl() }}"
               style="padding:0.375rem 0.75rem; border-radius:0.5rem; background:var(--color-surface-container); color:var(--color-on-surface); font-size:0.8125rem; text-decoration:none;">← Préc.</a>
            @endif
            @if($messages->hasMorePages())
            <a href="{{ $messages->nextPageUrl() }}"
               style="padding:0.375rem 0.75rem; border-radius:0.5rem; background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.8125rem; font-weight:600; text-decoration:none;">Suiv. →</a>
            @else
            <span style="padding:0.375rem 0.75rem; border-radius:0.5rem; background:var(--color-surface-container); color:var(--color-on-surface-variant); font-size:0.8125rem; opacity:0.5;">Suiv. →</span>
            @endif
        </div>
    </div>
    @endif
    @endif
</div>
@endsection
