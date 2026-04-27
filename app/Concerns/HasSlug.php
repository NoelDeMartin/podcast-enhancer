<?php

namespace App\Concerns;

use Illuminate\Support\Str;

trait HasSlug
{
    public static function generateUniqueSlug(string $value): string
    {
        $base = Str::of($value)->slug()->limit(240, '')->toString();

        do {
            $slug = $base.'-'.bin2hex(random_bytes(3));
        } while (static::withoutGlobalScopes()->where('slug', $slug)->exists());

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
