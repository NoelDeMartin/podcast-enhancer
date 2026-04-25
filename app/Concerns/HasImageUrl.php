<?php

namespace App\Concerns;

use App\Facades\Media;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasImageUrl
{
    public function shouldUpdateImage(array $validated): bool
    {
        return array_key_exists('image_file', $validated)
            || ! empty($validated['delete_image_file'])
            || (array_key_exists('image_url', $validated) && $validated['image_url'] !== $this->image_url);
    }

    protected function absoluteImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => Media::url($this->image_url),
        );
    }

    protected function imageIsExternal(): Attribute
    {
        return Attribute::make(
            get: fn () => Media::isExternal($this->image_url),
        );
    }
}
