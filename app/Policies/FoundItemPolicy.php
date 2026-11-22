<?php

namespace App\Policies;

use App\Models\FoundItem;
use App\Models\User;

class FoundItemPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, FoundItem $foundItem): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function publish(User $user, FoundItem $foundItem): bool
    {
        return $user->isAdmin();
    }

    public function archive(User $user, FoundItem $foundItem): bool
    {
        return $user->isAdmin();
    }
}

