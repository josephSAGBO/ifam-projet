@extends('layouts.admin')
@section('title', 'Notifications')
@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Notifications
            @if($unreadCount > 0)
            <span style="display:inline-flex; align-items:center; justify-content:center; width:24px; height:24px; border-radius:50%; background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.6875rem; font-weight:800; vertical-align:middle; margin-left:0.5rem;">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
            @endif
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">Vos messages et alertes.</p>
    </div>
    <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
        @if($unreadCount > 0)
        <form method="POST" action="{{ route('admin.notifications.markAllRead') }}">
            @csrf @method('PATCH')
            <button type="submit" style="height:40px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1rem; border-radius:0.75rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container); color:var(--color-on-surface); font-size:0.875rem; font-weight:600; cursor:pointer;">
                <span class="material-symbols-outlined" style="font-size:18px;">done_all</span> Tout lire
            </button>
        </form>
        @endif
        <a href="{{ route('admin.notifications.create') }}"
           style="height:40px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1.25rem; border-radius:0.75rem; background:var(--color-primary-container); color:var(--color-on-primary); text-decoration:none; font-size:0.875rem; font-weight:700; box-shadow:0 3px 10px rgba(228,0,102,0.3);">
            <span class="material-symbols-outlined" style="font-size:18px;">send</span> Envoyer
        </a>
    </div>
</div>

{{-- Filters --}}
<div style="background:var(--color-surface-container-low); padding:1rem 1.5rem; border-radius:1rem; margin-bottom:1.5rem;">
    <form action="{{ route('admin.notifications.index') }}" method="GET" style="display:flex; gap:1rem; align-items:flex-end; flex-wrap:wrap;">
        <div>
            <label style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">Type</label>
            <select name="type" onchange="this.form.submit()" style="height:38px; padding:0 1rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-lowest); font-size:0.875rem;">
                <option value="">Tous</option>
                <option value="info" {{ request('type')=='info'?'selected':'' }}>Info</option>
                <option value="success" {{ request('type')=='success'?'selected':'' }}>Succès</option>
                <option value="warning" {{ request('type')=='warning'?'selected':'' }}>Avertissement</option>
                <option value="danger" {{ request('type')=='danger'?'selected':'' }}>Urgent</option>
            </select>
        </div>
        <div>
            <label style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">Statut</label>
            <select name="unread" onchange="this.form.submit()" style="height:38px; padding:0 1rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-lowest); font-size:0.875rem;">
                <option value="">Toutes</option>
                <option value="1" {{ request('unread')=='1'?'selected':'' }}>Non lues seulement</option>
            </select>
        </div>
        @if(request()->hasAny(['type','unread']))
        <a href="{{ route('admin.notifications.index') }}" style="height:38px; display:inline-flex; align-items:center; color:var(--color-primary); text-decoration:none; font-size:0.875rem; font-weight:600;">Réinitialiser</a>
        @endif
    </form>
</div>

@php
$typeConfig = [
    'info'    => ['bg'=>'#eff6ff','text'=>'#1e40af','border'=>'#bfdbfe','icon'=>'info'],
    'success' => ['bg'=>'#ecfdf5','text'=>'#065f46','border'=>'#a7f3d0','icon'=>'check_circle'],
    'warning' => ['bg'=>'#fffbeb','text'=>'#92400e','border'=>'#fde68a','icon'=>'warning'],
    'danger'  => ['bg'=>'#fef2f2','text'=>'#991b1b','border'=>'#fecaca','icon'=>'error'],
];
@endphp

<div style="display:flex; flex-direction:column; gap:0.75rem;">
    @if($notifications->isEmpty())
    <div style="background:var(--color-surface-container-lowest); border-radius:1rem; padding:4rem 2rem; text-align:center; color:var(--color-on-surface-variant); box-shadow:0 1px 6px rgba(11,28,48,0.04);">
        <span class="material-symbols-outlined" style="font-size:48px; opacity:0.25; display:block; margin-bottom:1rem;">notifications_none</span>
        <p style="margin:0;">Aucune notification.</p>
    </div>
    @else
        @foreach($notifications as $notif)
        @php $cfg = $typeConfig[$notif->type] ?? $typeConfig['info']; @endphp
        <div style="background:{{ $notif->isRead() ? 'var(--color-surface-container-lowest)' : $cfg['bg'] }};
                    border:1px solid {{ $notif->isRead() ? 'var(--color-outline-variant)' : $cfg['border'] }};
                    border-radius:1rem; padding:1rem 1.25rem;
                    display:flex; align-items:flex-start; gap:1rem; transition:all 0.2s;
                    box-shadow:{{ $notif->isRead() ? '0 1px 3px rgba(11,28,48,0.04)' : '0 2px 8px rgba(11,28,48,0.08)' }};">

            {{-- Icon --}}
            <div style="width:40px; height:40px; border-radius:0.75rem; background:{{ $cfg['border'] }}; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <span class="material-symbols-outlined" style="color:{{ $cfg['text'] }}; font-size:20px;">{{ $notif->icon }}</span>
            </div>

            {{-- Content --}}
            <div style="flex:1; min-width:0;">
                <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.25rem;">
                    @if(!$notif->isRead())
                    <span style="width:8px; height:8px; border-radius:50%; background:{{ $cfg['text'] }}; flex-shrink:0;"></span>
                    @endif
                    <span style="font-weight:700; font-size:0.9375rem; color:{{ $cfg['text'] }};">{{ $notif->title }}</span>
                </div>
                <p style="margin:0 0 0.5rem; font-size:0.875rem; color:var(--color-on-surface-variant); line-height:1.5;">{{ $notif->message }}</p>
                <div style="font-size:0.75rem; color:var(--color-on-surface-variant); display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
                    <span>{{ $notif->created_at->diffForHumans() }}</span>
                    @if($notif->sender)
                    <span>· De : <strong>{{ $notif->sender->last_name ?? '' }} {{ $notif->sender->first_name ?? '' }}</strong></span>
                    @endif
                    @if($notif->link)
                    <a href="{{ $notif->link }}" style="color:{{ $cfg['text'] }}; font-weight:600; text-decoration:none;">
                        Voir →
                    </a>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div style="display:flex; align-items:center; gap:0.375rem; flex-shrink:0;">
                @if(!$notif->isRead())
                <form method="POST" action="{{ route('admin.notifications.markRead', $notif) }}">
                    @csrf @method('PATCH')
                    <button type="submit" title="Marquer comme lu"
                            style="width:32px; height:32px; border-radius:0.5rem; border:none; cursor:pointer; background:{{ $cfg['border'] }}; color:{{ $cfg['text'] }}; display:flex; align-items:center; justify-content:center;">
                        <span class="material-symbols-outlined" style="font-size:16px;">done</span>
                    </button>
                </form>
                @endif
                <form method="POST" action="{{ route('admin.notifications.destroy', $notif) }}" onsubmit="return confirm('Supprimer cette notification ?')">
                    @csrf @method('DELETE')
                    <button type="submit" title="Supprimer"
                            style="width:32px; height:32px; border-radius:0.5rem; border:none; cursor:pointer; background:var(--color-surface-container); color:#9ca3af; display:flex; align-items:center; justify-content:center;">
                        <span class="material-symbols-outlined" style="font-size:16px;">close</span>
                    </button>
                </form>
            </div>
        </div>
        @endforeach

        @if($notifications->hasPages())
        <div style="padding:1rem; display:flex; justify-content:center;">
            {{ $notifications->links('vendor.pagination.simple-default') }}
        </div>
        @endif
    @endif
</div>
@endsection
