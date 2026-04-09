<?php

namespace App\Models;

use Database\Factories\EntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Entry extends Model
{
    /** @use HasFactory<EntryFactory> */
    use HasFactory;

    protected $fillable = [
        'feed_id',
        'name',
        'audio_url',
        'transcription_path',
        'summary',
        'chapters',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'chapters' => 'json',
            'published_at' => 'datetime',
        ];
    }

    public function feed(): BelongsTo
    {
        return $this->belongsTo(Feed::class);
    }

    public function jobBatches(): HasMany
    {
        return $this->hasMany(EntryJobBatch::class);
    }

    public function latestJobBatch(): HasOne
    {
        return $this->hasOne(EntryJobBatch::class)->latestOfMany();
    }
}
