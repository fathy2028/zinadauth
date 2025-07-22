<?php

namespace App\Policies;

use App\Enums\UserTypeEnum;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkshopPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        // use the roles to authorize till we know what permissions for each role.
        return $user->hasRole(UserTypeEnum::ADMIN);
    }

    public function view(User $user, Workshop $workshop): bool
    {
        return $this->isOwnerOrAdmin($user, $workshop);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserTypeEnum::ADMIN) || $user->hasRole(UserTypeEnum::FACILITATOR);
    }

    public function edit(User $user, Workshop $workshop): bool
    {
        return $this->isOwnerOrAdmin($user, $workshop);
    }

    public function delete(User $user, Workshop $workshop): bool
    {
        return $this->isOwnerOrAdmin($user, $workshop);
    }

    protected function isOwnerOrAdmin(User $user, Workshop $workshop): bool
    {
        return ($user->hasRole(UserTypeEnum::FACILITATOR) && $workshop->creator->id === $user->id)
        || $user->hasRole(UserTypeEnum::ADMIN);
    }
}
