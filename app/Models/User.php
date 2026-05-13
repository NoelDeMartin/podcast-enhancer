<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'credits', 'plan'])]
#[Hidden(['password', 'remember_token'])]
#[Appends(['avatar'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected function avatar(): Attribute
    {
        return Attribute::get(function () {
            $hash = md5(strtolower(trim($this->email)));

            return "https://www.gravatar.com/avatar/{$hash}?s=128&d=mp";
        });
    }

    public function isPro(): bool
    {
        return $this->plan === 'pro';
    }

    public function feeds(): HasMany
    {
        return $this->hasMany(Feed::class);
    }

    public function creditUsages(): HasMany
    {
        return $this->hasMany(CreditUsage::class);
    }

    public function creditTopUps(): HasMany
    {
        return $this->hasMany(CreditTopUp::class);
    }
}
