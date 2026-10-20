<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Formation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'prix',
        'parcours_id',
        'ordre',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'prix' => 'decimal:2',
            'ordre' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function parcours(): BelongsTo
    {
        return $this->belongsTo(Parcours::class, 'parcours_id');
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'formation_module');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }
}
