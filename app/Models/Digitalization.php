<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Digitalization extends Model
{
    //

    protected $fillable = [
        'original_file_path',
        'transcription_file_path',
        'user_id',
        'title'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function batch()
    {
        return $this->belongsTo(DigitalizationBatch::class, 'digitalization_batch_id');
    }
}
