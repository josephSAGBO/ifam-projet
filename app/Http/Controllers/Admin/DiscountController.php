<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HasSiteFilter;
use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\Fee;
use App\Models\StudentProfile;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    use HasSiteFilter;

    public function index(Request $request)
    {
        ['sites' => $sites, 'selectedSiteId' => $selectedSiteId] = $this->siteFilter($request);
        if (auth()->check() && ! auth()->user()->can('view discounts') && ! auth()->user()->hasAnyRole(['administrateur', 'direction', 'direction_daf'])) {
            abort(403, 'Accès interdit : vous n\'avez pas les permissions pour consulter les bourses et réductions.');
        }

        $query = Discount::with('studentProfile.user', 'fee');
        if ($selectedSiteId !== null) {
            $query->where(function ($q) use ($selectedSiteId) {
                $q->whereHas('studentProfile.enrollments', fn ($eq) => $eq->where('site_id', $selectedSiteId))
                    ->orWhereHas('fee.academicSession', fn ($sq) => $sq->where('site_id', $selectedSiteId));
            });
        }

        if ($request->filled('student_id')) {
            $query->where('student_profile_id', $request->student_id);
        }

        $discounts = $query->orderBy('created_at', 'desc')->paginate(15);
        $studentsQuery = StudentProfile::query()->with('user');
        if ($selectedSiteId !== null) {
            $studentsQuery->whereHas('enrollments', fn ($q) => $q->where('site_id', $selectedSiteId));
        }
        $students = $studentsQuery->get()->sortBy(fn ($s) => $s->user->last_name ?? '');

        return view('admin.discounts.index', compact('discounts', 'students', 'sites', 'selectedSiteId'));
    }

    public function create()
    {
        if (auth()->check() && ! auth()->user()->can('manage discounts') && ! auth()->user()->hasAnyRole(['administrateur', 'direction', 'direction_daf'])) {
            abort(403, 'Accès interdit : vous n\'avez pas les permissions pour créer des réductions.');
        }

        $students = StudentProfile::with('user')->get()->sortBy(fn ($s) => $s->user->last_name ?? '');
        $fees = Fee::with('studentProfile.user')->get();

        return view('admin.discounts.create', compact('students', 'fees'));
    }

    public function store(Request $request)
    {
        if (auth()->check() && ! auth()->user()->can('manage discounts') && ! auth()->user()->hasAnyRole(['administrateur', 'direction', 'direction_daf'])) {
            abort(403, 'Accès interdit : vous n\'avez pas les permissions pour enregistrer des réductions.');
        }
        $validated = $request->validate([
            'student_profile_id' => 'required|exists:student_profiles,id',
            'fee_id' => 'nullable|exists:fees,id',
            'label' => 'required|string|max:255',
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'reason' => 'nullable|string',
        ]);

        Discount::create($validated);

        return redirect()->route('admin.discounts.index')
            ->with('success', 'Réduction enregistrée avec succès.');
    }

    public function edit(Discount $discount)
    {
        $students = StudentProfile::with('user')->get()->sortBy(fn ($s) => $s->user->last_name ?? '');
        $fees = Fee::with('studentProfile.user')->get();

        return view('admin.discounts.edit', compact('discount', 'students', 'fees'));
    }

    public function update(Request $request, Discount $discount)
    {
        $validated = $request->validate([
            'student_profile_id' => 'required|exists:student_profiles,id',
            'fee_id' => 'nullable|exists:fees,id',
            'label' => 'required|string|max:255',
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'reason' => 'nullable|string',
        ]);

        $discount->update($validated);

        return redirect()->route('admin.discounts.index')
            ->with('success', 'Réduction mise à jour avec succès.');
    }

    public function destroy(Discount $discount)
    {
        $discount->delete();

        return redirect()->route('admin.discounts.index')
            ->with('success', 'Réduction supprimée.');
    }
}
