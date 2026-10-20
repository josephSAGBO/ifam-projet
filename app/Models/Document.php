<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'is_verified',
        'notes',
        'uploaded_by',
        'documentable_id',
        'documentable_type',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'file_size' => 'integer',
    ];

    // Relation polymorphe : l'entité propriétaire du document
    public function documentable()
    {
        return $this->morphTo();
    }

    // Uploadeur
    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Helpers
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size ?? 0;
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1).' Mo';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 1).' Ko';
        }

        return $bytes.' o';
    }

    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    public static function typeLabels(): array
    {
        return [
            'identity' => "Pièce d'identité",
            'diploma' => 'Diplôme',
            'transcript' => 'Relevé de notes',
            'enrollment' => "Certificat d'inscription",
            'medical' => 'Certificat médical',
            'contract' => 'Contrat',
            'invoice' => 'Facture',
            'other' => 'Autre',
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return self::typeLabels()[$this->type] ?? $this->type;
    }
}
