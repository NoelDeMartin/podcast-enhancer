<?php

namespace App\Policies;

use App\Models\Entry;
use App\Models\Scopes\UserScope;
use App\Models\User;

class EntryPolicy
{
    public function view(?User $user, Entry $entry): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Entry $entry): bool
    {
        return $this->isOwner($user, $entry);
    }

    public function delete(User $user, Entry $entry): bool
    {
        return $this->isOwner($user, $entry);
    }

    public function produce(User $user, Entry $entry): bool
    {
        if (! $this->isOwner($user, $entry)) {
            return false;
        }

        return ! $entry->transcription_path;
    }

    public function regenerate(User $user, Entry $entry): bool
    {
        if (! $this->isOwner($user, $entry)) {
            return false;
        }

        if (! $entry->transcription_path) {
            return false;
        }

        return $user->isPro();
    }

    public function uploadFiles(User $user): bool
    {
        return $user->isPro();
    }

    private function isOwner(User $user, Entry $entry): bool
    {
        $feed = $entry->relationLoaded('feed')
            ? $entry->feed
            : $entry->feed()->withoutGlobalScope(UserScope::class)->first();

        return $user->id === $feed?->user_id;
    }
}
