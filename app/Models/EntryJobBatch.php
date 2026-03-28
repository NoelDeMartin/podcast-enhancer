<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntryJobBatch extends Model
{
    public $timestamps = false;

    protected $fillable = ['entry_id', 'batch_id'];

    protected $with = ['jobBatch'];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(Entry::class);
    }

    public function jobBatch(): BelongsTo
    {
        return $this->belongsTo(JobBatch::class, 'batch_id');
    }
}
