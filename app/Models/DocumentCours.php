<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentCours extends Model
{
    use HasFactory;

    protected $table = 'documents_cours';

    protected $fillable = [
        'cours_id',
        'title',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'uploaded_by',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'cours_id');
    }

    public function cours(): BelongsTo
    {
        return $this->course();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
