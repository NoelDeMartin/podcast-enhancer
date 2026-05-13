<?php

namespace App\Models;

use Database\Factories\CreditTopUpFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditTopUp extends Model
{
    /** @use HasFactory<CreditTopUpFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'credits',
        'description',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
