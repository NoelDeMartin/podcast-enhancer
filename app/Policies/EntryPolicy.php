<?php

namespace App\Policies;

use App\Models\Entry;
use App\Models\Scopes\UserScope;
use App\Models\User;

class EntryPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Entry $entry): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Entry $entry): bool
    {
        return $user->id === $entry->feed()->withoutGlobalScope(UserScope::class)->first()?->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Entry $entry): bool
    {
        return $user->id === $entry->feed()->withoutGlobalScope(UserScope::class)->first()?->user_id;
    }

    /**
     * Determine whether the user can produce the model for the first time.
     */
    public function produce(User $user, Entry $entry): bool
    {
        if ($user->id !== $entry->feed()->withoutGlobalScope(UserScope::class)->first()?->user_id) {
            return false;
        }

        return ! $entry->transcription_path;
    }

    /**
     * Determine whether the user can regenerate enhancements.
     */
    public function regenerate(User $user, Entry $entry): bool
    {
        if ($user->id !== $entry->feed()->withoutGlobalScope(UserScope::class)->first()?->user_id) {
            return false;
        }

        if (! $entry->transcription_path) {
            return false;
        }

        return $user->isPro();
    }

    /**
     * Determine whether the user can upload files.
     */
    public function uploadFiles(User $user): bool
    {
        return $user->isPro();
    }
}
