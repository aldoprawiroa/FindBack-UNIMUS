<?php

namespace App\Policies;

use App\Models\ReturnRecord;
use App\Models\User;

class ReturnRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageReturns();
    }

    public function create(User $user): bool
    {
        return $user->canManageReturns();
    }
}

