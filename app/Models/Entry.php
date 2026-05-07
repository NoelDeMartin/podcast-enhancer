<?php

namespace App\Models;

use App\Concerns\HasImageUrl;
use App\Concerns\HasSlug;
use App\Facades\Media;
use Database\Factories\EntryFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Entry extends Model
{
    /** @use HasFactory<EntryFactory> */
    use HasFactory, HasImageUrl, HasSlug;

    protected $fillable = [
        'feed_id',
        'name',
        'slug',
        'audio_url',
        'duration',
        'image_url',
        'transcription_path',
        'summary',
        'original_summary',
        'chapters',
        'published_at',
    ];

    protected $appends = [
        'absolute_audio_url',
        'absolute_image_url',
        'audio_is_external',
        'image_is_external',
        'audio_file_size',
        'transcription',
    ];

    public function shouldUpdateAudio(array $validated): bool
    {
        return array_key_exists('file', $validated)
            || ! empty($validated['delete_file'])
            || (array_key_exists('audio_url', $validated) && $validated['audio_url'] !== $this->audio_url);
    }

    protected function casts(): array
    {
        return [
            'chapters' => 'json',
            'published_at' => 'datetime',
        ];
    }

    protected function transcription(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->transcription_path ? Storage::get($this->transcription_path) : null,
        );
    }

    protected function absoluteAudioUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => Media::url($this->audio_url),
        );
    }

    protected function audioIsExternal(): Attribute
    {
        return Attribute::make(
            get: fn () => Media::isExternal($this->audio_url),
        );
    }

    protected function audioFileSize(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->audio_url && ! $this->audio_is_external && Storage::disk('public')->exists($this->audio_url)
                ? Storage::disk('public')->size($this->audio_url)
                : 0,
        );
    }

    protected function rssDescription(): Attribute
    {
        return Attribute::make(
            get: fn () => trim(view('feeds.entry-description', ['entry' => $this])->render()),
        );
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

    public function creditUsages(): HasMany
    {
        return $this->hasMany(CreditUsage::class);
    }

    public function scopeFilter($query, array $filters): void
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->whereLike('name', "%{$search}%");
        });
    }
}
