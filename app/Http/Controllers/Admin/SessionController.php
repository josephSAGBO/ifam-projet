<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SessionController extends Controller
{
    public function index()
    {
        $sessions = AcademicSession::latest('start_date')->paginate(15);

        return view('admin.sessions.index', compact('sessions'));
    }

    public function create()
    {
        return view('admin.sessions.create', [
            'sites' => Site::activeSites(),
            'defaultSiteId' => Site::defaultActiveId(),
        ]);
    }

    public function store(Request $request)
    {
        $this->normalizeSessionName($request);
        $request->merge([
            'site_id' => $request->input('site_id', Site::defaultActiveId()),
        ]);

        $validated = $request->validate([
            'site_id' => ['required', Rule::exists('sites', 'id')->where('est_actif', true)],
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => ['required', Rule::in(AcademicSession::STATUSES)],
            'is_current' => 'nullable|boolean',
        ]);

        if ($this->sessionNameExists($validated['name'])) {
            return back()->withInput()->withErrors([
                'name' => 'Une session académique portant ce nom existe déjà.',
            ]);
        }

        AcademicSession::create([
            'name' => $validated['name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $validated['status'],
            'is_current' => $request->boolean('is_current'),
            'site_id' => $validated['site_id'],
        ]);

        return redirect()->route('admin.sessions.index')
            ->with('success', 'Session créée avec succès.');
    }

    public function edit(AcademicSession $session)
    {
        return view('admin.sessions.edit', compact('session'));
    }

    public function update(Request $request, AcademicSession $session)
    {
        $this->normalizeSessionName($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => ['required', Rule::in(AcademicSession::STATUSES)],
            'is_current' => 'nullable|boolean',
        ]);

        if ($this->sessionNameExists($validated['name'], $session)) {
            return back()->withInput()->withErrors([
                'name' => 'Une session académique portant ce nom existe déjà.',
            ]);
        }

        $session->update([
            'name' => $validated['name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => $validated['status'],
            'is_current' => $request->boolean('is_current'),
        ]);

        return redirect()->route('admin.sessions.index')
            ->with('success', 'Session mise à jour avec succès.');
    }

    public function destroy(AcademicSession $session)
    {
        $session->delete();

        return redirect()->route('admin.sessions.index')
            ->with('success', 'Session supprimée avec succès.');
    }

    private function normalizeSessionName(Request $request): void
    {
        $name = $request->input('name');

        if (is_string($name)) {
            $request->merge(['name' => Str::squish($name)]);
        }
    }

    private function sessionNameExists(string $name, ?AcademicSession $except = null): bool
    {
        return AcademicSession::query()
            ->where('normalized_name', Str::lower(Str::squish($name)))
            ->when($except, fn ($query) => $query->where('id', '!=', $except->getKey()))
            ->exists();
    }
}
