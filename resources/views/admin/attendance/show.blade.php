@extends('layouts.admin')

@section('title', 'Faire l\'appel - ' . $course->display_name)

@section('content')

<div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.75rem;">
    <a href="{{ route('admin.attendance.index') }}"
       style="width:40px; height:40px; border-radius:0.75rem; background:var(--color-surface-container);
              display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--color-on-surface-variant);
              transition:all 0.15s;"
       onmouseover="this.style.background='var(--color-surface-container-high)'"
       onmouseout="this.style.background='var(--color-surface-container)'">
        <span class="material-symbols-outlined">arrow_back</span>
    </a>
    <div>
        <h1 style="font-family:var(--font-display); font-size:1.5rem; font-weight:700; color:var(--color-on-surface); margin:0 0 0.25rem;">
            Appel : {{ $course->display_name }}
        </h1>
        <p style="font-size:0.875rem; color:var(--color-on-surface-variant); margin:0;">
            Date : {{ $date->format('d/m/Y') }}
        </p>
    </div>
</div>

<form action="{{ route('admin.attendance.store', $course) }}" method="POST"
      style="background:var(--color-surface-container-lowest); border-radius:1rem; box-shadow:0 1px 6px rgba(11,28,48,0.04); overflow:hidden;">
    @csrf
    <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">

    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
            <thead>
                <tr style="background:var(--color-surface-container-low); border-bottom:1px solid var(--color-outline-variant);">
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Étudiant</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant); width:200px;">Statut</th>
                    <th style="text-align:left; padding:0.875rem 1rem; font-size:0.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--color-on-surface-variant);">Remarques (Optionnel)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                @php 
                    $att = $attendances->get($student->id);
                    $status = $att ? $att->status : 'present';
                @endphp
                <tr style="border-bottom:1px solid var(--color-surface-container); transition:background 0.15s;"
                    onmouseover="this.style.background='var(--color-surface-container-low)'"
                    onmouseout="this.style.background=''">
                    
                    <td style="padding:0.875rem 1rem; color:var(--color-on-surface); font-weight:600;">
                        {{ $student->user->last_name ?? '' }} {{ $student->user->first_name ?? '' }}
                    </td>
                    
                    <td style="padding:0.875rem 1rem;">
                        <div style="display:flex; gap:0.5rem;">
                            {{-- Present --}}
                            <label style="display:inline-flex; align-items:center; gap:0.25rem; cursor:pointer;">
                                <input type="radio" name="attendances[{{ $student->id }}][status]" value="present"
                                       {{ $status === 'present' ? 'checked' : '' }}
                                       style="accent-color:#059669;">
                                <span style="font-size:0.8125rem; color:#065f46; background:#ecfdf5; padding:0.125rem 0.5rem; border-radius:1rem;">Présent</span>
                            </label>
                            
                            {{-- Absent --}}
                            <label style="display:inline-flex; align-items:center; gap:0.25rem; cursor:pointer;">
                                <input type="radio" name="attendances[{{ $student->id }}][status]" value="absent"
                                       {{ $status === 'absent' ? 'checked' : '' }}
                                       style="accent-color:#dc2626;">
                                <span style="font-size:0.8125rem; color:#991b1b; background:#fef2f2; padding:0.125rem 0.5rem; border-radius:1rem;">Absent</span>
                            </label>
                            
                            {{-- Late --}}
                            <label style="display:inline-flex; align-items:center; gap:0.25rem; cursor:pointer;">
                                <input type="radio" name="attendances[{{ $student->id }}][status]" value="late"
                                       {{ $status === 'late' ? 'checked' : '' }}
                                       style="accent-color:#d97706;">
                                <span style="font-size:0.8125rem; color:#92400e; background:#fffbeb; padding:0.125rem 0.5rem; border-radius:1rem;">Retard</span>
                            </label>
                        </div>
                    </td>

                    <td style="padding:0.875rem 1rem;">
                        <input type="text" name="attendances[{{ $student->id }}][remarks]" 
                               value="{{ $att ? $att->remarks : '' }}"
                               placeholder="Motif..."
                               style="width:100%; box-sizing:border-box; height:36px; padding:0 0.75rem; border-radius:0.5rem;
                                      border:1px solid var(--color-outline-variant); background:var(--color-surface-container-lowest);
                                      font-size:0.875rem; color:var(--color-on-surface);">
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" style="padding:2rem; text-align:center; color:var(--color-on-surface-variant);">
                        Aucun étudiant trouvé.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($students->isNotEmpty())
    <div style="padding:1.5rem; background:var(--color-surface-container-low); border-top:1px solid var(--color-outline-variant); display:flex; justify-content:flex-end;">
        <button type="submit"
                style="height:44px; display:inline-flex; align-items:center; gap:0.5rem; padding:0 1.5rem; border-radius:0.75rem; border:none; cursor:pointer;
                       background:var(--color-primary-container); color:var(--color-on-primary); font-size:0.9375rem; font-weight:700;
                       box-shadow:0 3px 10px rgba(228,0,102,0.3); transition:all 0.15s;"
                onmouseover="this.style.transform='translateY(-1px)'"
                onmouseout="this.style.transform=''">
            <span class="material-symbols-outlined" style="font-size:20px;">save</span>
            Enregistrer la présence
        </button>
    </div>
    @endif
</form>

@endsection
