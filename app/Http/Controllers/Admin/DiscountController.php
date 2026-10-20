<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\Fee;
use App\Models\StudentProfile;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    public function index(Request $request)
    {
        $query = Discount::with('studentProfile.user', 'fee');

        if ($request->filled('student_id')) {
            $query->where('student_profile_id', $request->student_id);
        }

        $discounts = $query->orderBy('created_at', 'desc')->paginate(15);
        $students = StudentProfile::with('user')->get()->sortBy(fn ($s) => $s->user->last_name ?? '');

        return view('admin.discounts.index', compact('discounts', 'students'));
    }

    public function create()
    {
        $students = StudentProfile::with('user')->get()->sortBy(fn ($s) => $s->user->last_name ?? '');
        $fees = Fee::with('studentProfile.user')->get();

        return view('admin.discounts.create', compact('students', 'fees'));
    }

    public function store(Request $request)
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
