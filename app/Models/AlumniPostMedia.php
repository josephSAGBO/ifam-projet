<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AlumniPostMedia extends Model
{
    use HasFactory;

    protected $table = 'alumni_post_media';

    protected $fillable = [
        'alumni_post_id',
        'file_path',
        'disk',
        'mime_type',
        'file_size',
        'original_name',
        'caption',
        'display_order',
        'is_cover',
    ];

    protected function casts(): array
    {
        return [
            'is_cover' => 'boolean',
            'display_order' => 'integer',
            'file_size' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (AlumniPostMedia $media) {
            $disk = $media->disk ?: 'public';
            if ($media->file_path && Storage::disk($disk)->exists($media->file_path)) {
                Storage::disk($disk)->delete($media->file_path);
            }
        });
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(AlumniPost::class, 'alumni_post_id');
    }

    public function getUrlAttribute(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        $disk = $this->disk ?: 'public';

        return Storage::disk($disk)->url($this->file_path);
    }
}
