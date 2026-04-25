<?php

namespace App\Concerns;

use App\Facades\Media;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasImageUrl
{
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
