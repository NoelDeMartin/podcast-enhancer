<?php

namespace App\Models;

use App\Concerns\HasImageUrl;
use App\Concerns\HasSlug;
use App\Models\Scopes\UserScope;
use Database\Factories\FeedFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[ScopedBy([UserScope::class])]
class Feed extends Model
{
    /** @use HasFactory<FeedFactory> */
    use HasFactory, HasImageUrl, HasSlug;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'rss_url',
        'image_url',
        'last_synced_at',
    ];

    protected $appends = [
        'absolute_image_url',
        'image_is_external',
    ];

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

    public function scopeFilter($query, array $filters): void
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->whereLike('title', "%{$search}%");
        });
    }
}
