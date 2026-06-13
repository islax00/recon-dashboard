<?php

namespace Modules\Reconnaissance\Policies;

use App\Models\User;
use Modules\Reconnaissance\Models\Scan;

class ScanPolicy
{
    public function view(User $user, Scan $scan): bool
    {
        return $user->id === $scan->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }
}
