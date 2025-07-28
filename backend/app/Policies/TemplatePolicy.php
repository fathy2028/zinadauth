<?php

namespace App\Policies;

use App\Enums\UserTypeEnum;
use App\Models\Template;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TemplatePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        // use the roles to authorize till we know what permissions for each role.
        return in_array($user->type, [
            UserTypeEnum::ADMIN->value,
            UserTypeEnum::FACILITATOR->value,
            UserTypeEnum::PARTICIPANT->value
        ]);
    }

    public function view(User $user, Template $template): bool
    {
        return $user->type === UserTypeEnum::ADMIN->value;
    }

    public function create(User $user): bool
    {
        return $user->type === UserTypeEnum::ADMIN->value;
    }

    public function edit(User $user, Template $workshop): bool
    {
        return $user->type === UserTypeEnum::ADMIN->value;
    }

    public function delete(User $user, Template $workshop): bool
    {
        return $user->type === UserTypeEnum::ADMIN->value;
    }
}
