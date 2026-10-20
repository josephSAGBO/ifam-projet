<?php

namespace App\Models;

use App\Models\Concerns\AssignsDefaultSite;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AcademicSession extends Model
{
    use AssignsDefaultSite, HasFactory;

    public const STATUSES = ['en_cours', 'terminee'];

    protected $attributes = [
        'status' => 'en_cours',
    ];

    protected $fillable = [
        'name',
        'site_id',
        'start_date',
        'end_date',
        'status',
        'is_current',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => 'string',
        'is_current' => 'boolean',
    ];

    public function setNameAttribute(string $value): void
    {
        $normalizedName = Str::squish($value);
        $this->attributes['name'] = $normalizedName;
        $this->attributes['normalized_name'] = Str::lower($normalizedName);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
