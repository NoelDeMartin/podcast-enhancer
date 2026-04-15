<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedJobBatch extends Model
{
    public $timestamps = false;

    protected $fillable = ['feed_id', 'batch_id'];

    protected $with = ['jobBatch'];

    public function feed(): BelongsTo
    {
        return $this->belongsTo(Feed::class);
    }

    public function jobBatch(): BelongsTo
    {
        return $this->belongsTo(JobBatch::class, 'batch_id');
    }
}
