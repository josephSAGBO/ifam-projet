<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\Site;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

trait HasSiteFilter
{
    /**
     * @return array{sites: Collection<int, Site>, selectedSiteId: ?int}
     */
    protected function siteFilter(Request $request): array
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }
        $canFilterSites = $user->hasAnyRole(['administrateur', 'direction_daf', 'direction_daa']);
        $sites = Site::query()
            ->where('est_actif', true)
            ->when(! $canFilterSites, fn ($query) => $query->whereRaw('1 = 0'))
            ->orderBy('nom')
            ->get();

        $validated = $request->validate([
            'site_id' => ['nullable', 'integer', Rule::exists('sites', 'id')->where('est_actif', true)],
        ]);
        $selectedSiteId = isset($validated['site_id']) ? (int) $validated['site_id'] : null;

        if ($selectedSiteId !== null && (! $canFilterSites || ! $sites->contains('id', $selectedSiteId))) {
            abort(403, 'Accès refusé : vous ne pouvez pas filtrer les données de ce site.');
        }

        return compact('sites', 'selectedSiteId');
    }
}
