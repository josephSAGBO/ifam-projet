<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HasSiteFilter;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    use HasSiteFilter;

    public function index(Request $request)
    {
        ['sites' => $sites, 'selectedSiteId' => $selectedSiteId] = $this->siteFilter($request);
        if (auth()->check() && ! auth()->user()->can('view expenses') && ! auth()->user()->hasAnyRole(['administrateur', 'direction', 'direction_daf'])) {
            abort(403, 'Accès interdit : vous n\'avez pas les permissions pour consulter les dépenses financières.');
        }

        $query = Expense::with('category')
            ->when(auth()->check() && ! auth()->user()->hasAnyRole(['administrateur', 'direction', 'direction_daf']), function ($q) {
                $q->whereIn('site_id', auth()->user()->accessibleSiteIds());
            });
        if ($selectedSiteId !== null) {
            $query->where('site_id', $selectedSiteId);
        }

        if ($request->filled('category_id')) {
            $query->where('expense_category_id', $request->category_id);
        }
        if ($request->filled('from')) {
            $query->where('expense_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('expense_date', '<=', $request->to);
        }

        $expenses = $query->orderBy('expense_date', 'desc')->paginate(15)->withQueryString();
        $categories = ExpenseCategory::orderBy('name')->get();
        $totalAmount = $query->sum('amount');

        return view('admin.expenses.index', compact('expenses', 'categories', 'totalAmount', 'sites', 'selectedSiteId'));
    }

    public function create()
    {
        $categories = ExpenseCategory::orderBy('name')->get();
        $sites = auth()->user()->hasAnyRole(['administrateur', 'direction', 'direction_daf'])
            ? Site::activeSites()
            : auth()->user()->sites()->where('est_actif', true)->get();
        $defaultSiteId = $sites->first()?->id ?? Site::defaultActiveId();

        return view('admin.expenses.create', compact('categories', 'sites', 'defaultSiteId'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'site_id' => $request->input('site_id', Site::defaultActiveId()),
        ]);

        $validated = $request->validate([
            'site_id' => ['required', Rule::exists('sites', 'id')->where('est_actif', true)],
            'expense_category_id' => 'required|exists:expense_categories,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,card,check,other',
            'reference' => 'nullable|string|max:255',
            'vendor' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if (! auth()->user()->hasSiteAccess($validated['site_id'])) {
            abort(403, 'Accès interdit : vous ne pouvez enregistrer une dépense que pour votre site.');
        }

        Expense::create($validated);

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Dépense enregistrée avec succès.');
    }

    public function edit(Expense $expense)
    {
        if ($expense->site_id && ! auth()->user()->hasSiteAccess($expense->site_id)) {
            abort(403, 'Accès interdit : cette dépense ne relève pas de votre site.');
        }

        $categories = ExpenseCategory::orderBy('name')->get();

        return view('admin.expenses.edit', compact('expense', 'categories'));
    }

    public function update(Request $request, Expense $expense)
    {
        if ($expense->site_id && ! auth()->user()->hasSiteAccess($expense->site_id)) {
            abort(403, 'Accès interdit : cette dépense ne relève pas de votre site.');
        }

        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,card,check,other',
            'reference' => 'nullable|string|max:255',
            'vendor' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $expense->update($validated);

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Dépense mise à jour.');
    }

    public function destroy(Expense $expense)
    {
        if ($expense->site_id && ! auth()->user()->hasSiteAccess($expense->site_id)) {
            abort(403, 'Accès interdit : cette dépense ne relève pas de votre site.');
        }

        $expense->delete();

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Dépense supprimée.');
    }
}
