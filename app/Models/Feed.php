<?php

namespace App\Models;

use Database\Factories\FeedFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Feed extends Model
{
    /** @use HasFactory<FeedFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'rss_url',
        'image_url',
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class)->latest('published_at');
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
