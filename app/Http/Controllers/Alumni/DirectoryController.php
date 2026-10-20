<?php

namespace App\Http\Controllers\Alumni;

use App\Http\Controllers\Controller;
use App\Models\Alumni;
use App\Models\Formation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DirectoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'formation_id' => ['nullable', 'integer', 'exists:formations,id'],
            'year' => ['nullable', 'integer', 'digits:4'],
        ]);

        $alumniQuery = Alumni::query()
            ->showcaseData()
            ->publiclyVisible();

        if (! empty($filters['formation_id'])) {
            $alumniQuery->where('formation_id', $filters['formation_id']);
        }

        if (! empty($filters['year'])) {
            $alumniQuery->where(function ($query) use ($filters): void {
                $query->where('promotion_year', $filters['year'])
                    ->orWhereYear('date_diplome', $filters['year']);
            });
        }

        $alumnis = $alumniQuery
            ->orderByDesc('date_diplome')
            ->orderByDesc('promotion_year')
            ->paginate(6)
            ->withQueryString();

        $formations = Formation::query()->select(['id', 'name'])->orderBy('name')->get();
        $years = $this->availableYears();

        return view('alumni.directory.index', [
            'alumnis' => $alumnis,
            'formations' => $formations,
            'years' => $years,
            'formationId' => $filters['formation_id'] ?? null,
            'year' => $filters['year'] ?? null,
        ]);
    }

    /**
     * @return Collection<int, int>
     */
    private function availableYears(): Collection
    {
        $promotionYears = Alumni::publiclyVisible()
            ->whereNotNull('promotion_year')
            ->distinct()
            ->pluck('promotion_year');

        $graduationYears = Alumni::publiclyVisible()
            ->whereNull('promotion_year')
            ->whereNotNull('date_diplome')
            ->distinct()
            ->pluck('date_diplome')
            ->map(fn (string $date): int => (int) substr($date, 0, 4));

        return $promotionYears
            ->merge($graduationYears)
            ->unique()
            ->sortDesc()
            ->values();
    }
}
