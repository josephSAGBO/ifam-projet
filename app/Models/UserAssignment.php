<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_profile_id',
        'site_id',
        'level',
        'is_department_head',
        'department_name',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_department_head' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function serviceProfile(): BelongsTo
    {
        return $this->belongsTo(ServiceProfile::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function scopeForSite(Builder $query, ?int $siteId): Builder
    {
        return $query->where(function (Builder $q) use ($siteId) {
            $q->whereNull('site_id');
            if ($siteId !== null) {
                $q->orWhere('site_id', $siteId);
            }
        });
    }

    public function scopeForService(Builder $query, string $serviceCode): Builder
    {
        return $query->whereHas('serviceProfile', fn (Builder $q) => $q->where('code', $serviceCode));
    }

    public function scopeResponsables(Builder $query): Builder
    {
        return $query->where('level', 'responsable');
    }

    public function scopeAgents(Builder $query): Builder
    {
        return $query->where('level', 'agent');
    }
}
