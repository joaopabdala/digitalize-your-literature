<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DigitalizationBatch extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'folder_path',
        'page_count'
    ];

    public const DIGITALIZATION_DIR = 'digitalizations/';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function digitalizations(): HasMany
    {
        return $this->hasMany(Digitalization::class);
    }

    public function getImageUrlCover(): string
    {
        return $this->digitalizations[0]->original_file_path ?? '';
    }

}
