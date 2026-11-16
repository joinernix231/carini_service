<?php

namespace App\Http\Requests\Concerns;

use App\Models\User;

trait AuthorizesRoles
{
    protected function currentUser(): ?User
    {
        return session('user');
    }

    protected function userHasRole(string ...$roles): bool
    {
        $user = $this->currentUser();

        return $user !== null && in_array($user->role, $roles, true);
    }
}
