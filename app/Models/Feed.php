<?php

namespace App\Models;

use Database\Factories\FeedFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Feed extends Model
{
    /** @use HasFactory<FeedFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class);
    }
}
