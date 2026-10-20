<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Parcours extends Model
{
    use HasFactory;

    protected $table = 'parcours';

    protected $fillable = [
        'nom',
    ];

    public function formations(): HasMany
    {
        return $this->hasMany(Formation::class, 'parcours_id')->orderBy('ordre');
    }
}
