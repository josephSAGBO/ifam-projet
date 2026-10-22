@extends('layouts.admin')
@section('title', 'Paiements')
@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.75rem;">
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">Paiements</h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">Historique de tous les versements effectués pour les inscriptions.</p>
    </div>
    <a href="{{ route('admin.payments.create') }}"
       style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.625rem 1.25rem;
              background:var(--color-primary-container); color:var(--color-on-primary); border-radius:0.75rem;
              text-decoration:none; font-size:0.875rem; font-weight:700; box-shadow:0 3px 10px rgba(228,0,102,0.3);">
        <span class="material-symbols-outlined" style="font-size:20px;">add_circle</span> Nouveau versement
    </a>
</div>

<div class="payment-filters" style="background:var(--color-surface-container-low); padding:1rem 1.5rem; border-radius:1rem; margin-bottom:1.5rem; border:1px solid var(--color-outline-variant);">
    <form action="{{ route('admin.payments.index') }}" method="GET" style="display:flex; gap:1rem; align-items:flex-end; flex-wrap:wrap;">
        @includeWhen($sites->isNotEmpty(), 'admin.partials.site-filter')
        <div>
            <label style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">Étudiant</label>
            <select name="student_id" style="height:38px; padding:0 1rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-lowest); font-size:0.875rem; color:var(--color-on-surface);">
                <option value="">Tous les étudiants</option>
                @foreach($students as $s)
                    <option value="{{ $s->id }}" {{ request('student_id') == $s->id ? 'selected' : '' }}>
                        {{ $s->user->last_name ?? '' }} {{ $s->user->first_name ?? '' }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">Mode de règlement</label>
            <select name="mode" style="height:38px; padding:0 1rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-lowest); font-size:0.875rem; color:var(--color-on-surface);">
                <option value="">Tous les modes</option>
                <option value="especes" {{ request('mode')=='especes'?'selected':'' }}>Espèces</option>
                <option value="virement" {{ request('mode')=='virement'?'selected':'' }}>Virement</option>
                <option value="carte" {{ request('mode')=='carte'?'selected':'' }}>Carte</option>
                <option value="cheque" {{ request('mode')=='cheque'?'selected':'' }}>Chèque</option>
                <option value="autre" {{ request('mode')=='autre'?'selected':'' }}>Autre</option>
            </select>
        </div>
        <div>
            <label for="date_from" style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">Du</label>
            <input id="date_from" type="date" name="date_from" value="{{ request('date_from') }}" style="height:38px; padding:0 0.75rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-lowest); color:var(--color-on-surface);">
        </div>
        <div>
            <label for="date_to" style="display:block; font-size:0.75rem; font-weight:600; color:var(--color-on-surface-variant); margin-bottom:0.375rem;">Au</label>
            <input id="date_to" type="date" name="date_to" value="{{ request('date_to') }}" style="height:38px; padding:0 0.75rem; border-radius:0.5rem; border:1px solid var(--color-outline-variant); background:var(--color-surface-container-lowest); color:var(--color-on-surface);">
        </div>
        <button type="submit" style="height:38px; padding:0 1rem; border:0; border-radius:0.5rem; background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.875rem; font-weight:700; cursor:pointer;">Filtrer</button>
        @if(request()->hasAny(['student_id', 'mode', 'date_from', 'date_to']))
        <a href="{{ route('admin.payments.index') }}" style="height:38px; display:inline-flex; align-items:center; color:var(--color-primary); text-decoration:none; font-size:0.875rem; font-weight:600;">Réinitialiser</a>
        @endif
        @error('date_from')
            <span style="color:#b91c1c; font-size:0.8125rem;">{{ $message }}</span>
        @enderror
        @error('date_to')
            <span style="color:#b91c1c; font-size:0.8125rem;">{{ $message }}</span>
        @enderror
    </form>
</div>

<div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; padding:1rem 1.25rem; margin-bottom:1rem; border:1px solid var(--color-outline-variant); border-radius:0.75rem; background:var(--color-surface-container-lowest);">
    <div>
        <div style="font-size:0.875rem; font-weight:700; color:var(--color-on-surface);">
            {{ $payments->total() }} paiement{{ $payments->total() > 1 ? 's' : '' }} trouvé{{ $payments->total() > 1 ? 's' : '' }}
        </div>
        <div style="font-size:0.8125rem; color:var(--color-on-surface-variant);">
            Total de la sélection : <strong>{{ number_format($filteredTotal, 0, ',', ' ') }} FCFA</strong>
        </div>
    </div>
    <a href="{{ route('admin.payments.print', request()->only(['student_id', 'mode', 'date_from', 'date_to'])) }}" target="_blank" rel="noopener"
       style="display:inline-flex; align-items:center; justify-content:center; gap:0.5rem; min-height:40px; padding:0 1rem; border-radius:0.5rem; background:var(--color-primary-container); color:var(--color-on-primary); text-decoration:none; font-size:0.875rem; font-weight:700;">
        <span class="material-symbols-outlined" style="font-size:18px;">print</span>
        Imprimer les paiements filtrés
    </a>
</div>

<div style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden; border:1px solid var(--color-outline-variant);">
    @if($payments->isEmpty())
    <div style="padding:4rem 2rem; text-align:center; color:var(--color-on-surface-variant);">
        <span class="material-symbols-outlined" style="font-size:48px; opacity:0.25; display:block; margin-bottom:1rem;">payments</span>
        <p style="margin:0;">Aucun versement trouvé.</p>
    </div>
    @else
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
            <thead>
                <tr style="background:var(--color-surface-container-low); border-bottom:1px solid var(--color-outline-variant);">
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Date</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Étudiant</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Formation</th>
                    <th style="text-align:right; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Montant versé</th>
                    <th style="text-align:center; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Mode</th>
                    <th style="text-align:center; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Statut</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Référence</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Enregistré par</th>
                    <th style="padding:0.875rem 1rem;"></th>
                </tr>
            </thead>
            <tbody>
                @php
                $modeLabels = ['especes'=>'Espèces','virement'=>'Virement','carte'=>'Carte','cheque'=>'Chèque','autre'=>'Autre','cash'=>'Espèces','bank_transfer'=>'Virement','card'=>'Carte','check'=>'Chèque','other'=>'Autre'];
                $modeColors = ['especes'=>'#ecfdf5','virement'=>'#eff6ff','carte'=>'#faf5ff','cheque'=>'#fffbeb','autre'=>'#f3f4f6','cash'=>'#ecfdf5','bank_transfer'=>'#eff6ff','card'=>'#faf5ff','check'=>'#fffbeb','other'=>'#f3f4f6'];
                $modeText   = ['especes'=>'#065f46','virement'=>'#1e40af','carte'=>'#6b21a8','cheque'=>'#92400e','autre'=>'#374151','cash'=>'#065f46','bank_transfer'=>'#1e40af','card'=>'#6b21a8','check'=>'#92400e','other'=>'#374151'];
                $canValidate = auth()->user()?->hasRole('administrateur') || auth()->user()?->can('validate financial operations');
                $canPublishReceipts = auth()->user()?->hasRole('administrateur') || auth()->user()?->can('print receipts');
                @endphp
                @foreach($payments as $payment)
                @php
                    $studentUser = $payment->enrollment->studentProfile->user ?? ($payment->studentProfile->user ?? null);
                    $formation = $payment->enrollment->formation ?? null;
                    $mode = $payment->mode ?? $payment->method ?? 'autre';
                    $dateP = $payment->date_paiement ?? $payment->payment_date;
                    $montantVal = $payment->montant ?? $payment->amount_paid ?? 0;
                @endphp
                <tr style="border-bottom:1px solid var(--color-surface-container); transition:background 0.15s;"
                    onmouseover="this.style.background='var(--color-surface-container-low)'"
                    onmouseout="this.style.background=''">
                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">
                        {{ $dateP ? $dateP->format('d/m/Y') : '—' }}
                    </td>
                    <td style="padding:0.875rem 1rem; font-weight:600; color:var(--color-on-surface);">
                        {{ $studentUser->full_name ?? '—' }}
                    </td>
                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface-variant);">
                        @if($payment->enrollment)
                            <a href="{{ route('admin.enrollments.show', $payment->enrollment) }}" style="color:var(--color-primary); text-decoration:none; font-weight:500;">
                                {{ $formation->name ?? 'Inscription #' . $payment->inscription_id }}
                            </a>
                        @else
                            {{ $payment->fee->title ?? '—' }}
                        @endif
                    </td>
                    <td style="padding:0.875rem 1rem; text-align:right; font-weight:700; color:#16a34a;">
                        {{ number_format($montantVal, 2, ',', ' ') }} FCFA
                    </td>
                    <td style="padding:0.875rem 1rem; text-align:center;">
                        <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700;
                                     background:{{ $modeColors[$mode] ?? '#f3f4f6' }};
                                     color:{{ $modeText[$mode] ?? '#374151' }};">
                            {{ $modeLabels[$mode] ?? ucfirst($mode) }}
                        </span>
                    </td>
                    <td style="padding:0.875rem 1rem; text-align:center;">
                        @if($payment->estValide())
                            <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:#dcfce7; color:#166534;" title="Validé{{ $payment->validateur ? ' par ' . $payment->validateur->full_name : '' }}">
                                Validé
                            </span>
                        @elseif($payment->estEnAttente())
                            <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:#fef3c7; color:#92400e;">
                                En attente
                            </span>
                        @else
                            <span style="padding:0.25rem 0.625rem; border-radius:9999px; font-size:0.6875rem; font-weight:700; background:#fee2e2; color:#991b1b;">
                                Rejeté
                            </span>
                        @endif
                    </td>
                    <td style="padding:0.875rem 1rem; font-family:monospace; color:var(--color-on-surface-variant);">
                        {{ $payment->reference ?? '—' }}
                    </td>
                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface-variant); font-size:0.8125rem;">
                        {{ $payment->enregistrePar->full_name ?? ($payment->enregistrePar->name ?? 'Système') }}
                    </td>
                    <td style="padding:0.875rem 1rem;">
                        <div style="display:flex; align-items:center; gap:0.375rem; justify-content:flex-end;">
                            <a href="{{ route('admin.payments.receipt', $payment) }}" title="Voir / imprimer le reçu"
                               style="width:34px; height:34px; display:inline-flex; align-items:center; justify-content:center; border-radius:0.5rem; background:var(--color-surface-container); color:var(--color-primary); text-decoration:none;"
                               onmouseover="this.style.background='var(--color-primary-container)'; this.style.color='var(--color-on-primary)'"
                               onmouseout="this.style.background='var(--color-surface-container)'; this.style.color='var(--color-primary)'">
                                <span class="material-symbols-outlined" style="font-size:18px;">receipt</span>
                            </a>
                            @if($canPublishReceipts && $payment->receipt_published_at)
                                <form method="POST" action="{{ route('admin.payments.receipt.unpublish', $payment) }}" style="display:inline;" onsubmit="return confirm('Retirer ce reçu des espaces de l’étudiant et de ses parents ?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" title="Retirer la publication du reçu" style="width:34px; height:34px; border-radius:0.5rem; border:none; cursor:pointer; background:#dcfce7; color:#166534;">
                                        <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                                    </button>
                                </form>
                            @elseif($canPublishReceipts && $payment->estValide())
                                <form method="POST" action="{{ route('admin.payments.receipt.publish', $payment) }}" style="display:inline;">
                                    @csrf @method('PATCH')
                                    <button type="submit" title="Publier le reçu dans les espaces étudiant et parent" style="width:34px; height:34px; border-radius:0.5rem; border:none; cursor:pointer; background:#e0f2fe; color:#0369a1;">
                                        <span class="material-symbols-outlined" style="font-size:18px;">publish</span>
                                    </button>
                                </form>
                            @endif
                            @if($canValidate && $payment->estEnAttente())
                                <form method="POST" action="{{ route('admin.payments.validate', $payment) }}" style="display:inline;">
                                    @csrf @method('PATCH')
                                    <button type="submit" title="Valider le versement" style="width:34px; height:34px; border-radius:0.5rem; border:none; cursor:pointer; background:#dcfce7; color:#166534;" onmouseover="this.style.background='#bbf7d0'" onmouseout="this.style.background='#dcfce7'">
                                        <span class="material-symbols-outlined" style="font-size:18px;">check_circle</span>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.payments.reject', $payment) }}" style="display:inline;" onsubmit="return confirm('Confirmer le rejet de ce paiement ?');">
                                    @csrf @method('PATCH')
                                    <button type="submit" title="Rejeter le versement" style="width:34px; height:34px; border-radius:0.5rem; border:none; cursor:pointer; background:#fee2e2; color:#991b1b;" onmouseover="this.style.background='#fecaca'" onmouseout="this.style.background='#fee2e2'">
                                        <span class="material-symbols-outlined" style="font-size:18px;">cancel</span>
                                    </button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('admin.payments.destroy', $payment) }}" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce versement ?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Supprimer" style="width:34px; height:34px; border-radius:0.5rem; border:none; cursor:pointer; background:var(--color-surface-container); color:#dc2626;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='var(--color-surface-container)'">
                                    <span class="material-symbols-outlined" style="font-size:18px;">delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($payments->hasPages())
    <div style="padding:1rem; border-top:1px solid var(--color-surface-container);">
        {{ $payments->links() }}
    </div>
    @endif
    @endif
</div>

@endsection
