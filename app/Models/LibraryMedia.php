<?php

namespace App\Models;

use Database\Factories\LibraryMediaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

/**
 * @use HasFactory<LibraryMediaFactory>
 */
#[Fillable([
    'library_item_id',
    'media_type',
    'storage_path',
    'mime_type',
    'file_size_bytes',
    'alt_text',
    'sort_order',
    'is_primary',
    'thumbnail_path',
    'caption',
    'duration_seconds',
    'uploaded_by',
])]
class LibraryMedia extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'sort_order' => 'integer',
            'file_size_bytes' => 'integer',
            'duration_seconds' => 'integer',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(LibraryItem::class);
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(LibraryItem::class, 'library_item_media')
            ->withPivot(['sort_order', 'is_primary', 'caption'])
            ->withTimestamps();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function temporaryUrl(): string
    {
        return Storage::disk('s3')->temporaryUrl(
            $this->storage_path,
            now()->addMinutes((int) config('services.media.signed_url_minutes', 15)),
        );
    }
}
