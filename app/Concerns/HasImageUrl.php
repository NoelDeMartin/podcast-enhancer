<?php

namespace App\Concerns;

use App\Facades\Media;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasImageUrl
{
    /**
     * Get the image's absolute URL.
     */
    protected function absoluteImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => Media::url($this->image_url),
        );
    }

    /**
     * Determine if the image is external.
     */
    protected function imageIsExternal(): Attribute
    {
        return Attribute::make(
            get: fn () => Media::isExternal($this->image_url),
        );
    }
}
