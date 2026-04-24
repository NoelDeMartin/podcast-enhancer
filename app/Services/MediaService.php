<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaService
{
    public function update(string $directory, ?string $oldPath, UploadedFile|string|null $newFile, bool $delete = false): ?string
    {
        $isReplacingFile = $newFile && $oldPath !== $newFile;

        if ($delete || $isReplacingFile) {
            $this->delete($oldPath);
        }

        if ($delete) {
            return null;
        }

        if ($newFile instanceof UploadedFile) {
            return $newFile->store($directory, 'public');
        }

        return $newFile;
    }

    public function delete(?string $path): void
    {
        if ($path && ! $this->isExternal($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if ($this->isExternal($path)) {
            return $path;
        }

        return asset(Storage::disk('public')->url($path));
    }

    public function isExternal(?string $path): bool
    {
        return $path && (bool) filter_var($path, FILTER_VALIDATE_URL);
    }
}
