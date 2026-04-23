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
     * Determine whether the user can produce the model.
     */
    public function produce(User $user, Entry $entry): bool
    {
        return $user->id === $entry->feed()->withoutGlobalScope(UserScope::class)->first()?->user_id;
    }
}
