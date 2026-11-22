<?php

namespace App\Policies;

use App\Models\SecurityPost;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $authenticatedUser): bool
    {
        return $authenticatedUser->isAdmin();
    }

    public function view(User $authenticatedUser, User $targetUser): bool
    {
        return $authenticatedUser->isAdmin() || $authenticatedUser->id === $targetUser->id;
    }

    public function assignSecurityPost(User $authenticatedUser, User $targetUser, ?SecurityPost $securityPost = null): bool
    {
        if (! $authenticatedUser->isAdmin()) {
            return false;
        }

        if (! $targetUser->canManageReturns()) {
            return false;
        }

        if ($securityPost !== null && ! $securityPost->is_active) {
            return false;
        }

        return true;
    }
}
