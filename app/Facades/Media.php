<?php

namespace App\Facades;

use App\Services\MediaService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string|null update(string $directory, ?string $oldPath, \Illuminate\Http\UploadedFile|string|null $newFile, bool $delete = false)
 * @method static void delete(?string $path)
 * @method static bool isExternal(?string $path)
 * @method static string|null url(?string $path)
 *
 * @see MediaService
 */
class Media extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'media';
    }
}
