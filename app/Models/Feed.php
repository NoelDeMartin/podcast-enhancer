<?php

namespace App\Models;

use App\Models\Scopes\UserScope;
use Database\Factories\FeedFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

#[ScopedBy([UserScope::class])]
class Feed extends Model
{
    /** @use HasFactory<FeedFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'rss_url',
        'image_url',
        'last_synced_at',
        'sync_frequency',
    ];

    protected $appends = [
        'absolute_image_url',
        'image_is_external',
    ];

    protected function absoluteImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->image_url && ! $this->image_is_external
                ? asset(Storage::disk('public')->url($this->image_url))
                : $this->image_url,
        );
    }

    protected function imageIsExternal(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->image_url && filter_var($this->image_url, FILTER_VALIDATE_URL),
        );
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
        ];
    }

    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class)->latest('published_at');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobBatches(): HasMany
    {
        return $this->hasMany(FeedJobBatch::class);
    }

    public function latestJobBatch(): HasOne
    {
        return $this->hasOne(FeedJobBatch::class)->latestOfMany();
    }
}
