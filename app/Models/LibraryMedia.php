<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryMedia extends Model
{
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

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class,'uploaded_by');
    }
}
