<?php
namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function view(?User $user, User $model): bool
    {
        if ($model->published) {
            return true;
        }

        // visitors cannot view unpublished items
        if ($user === null) {
            return false;
        }

        // admin overrides published status
        if ($user->can('users.view')) {
            return true;
        }

        // authors can view their own unpublished posts
        return $user->id == $model->user_id;
    }

    public function create(User $user): bool
    {
        return $user->can('users.create');
    }

    public function update(User $user, User $model)
    {
        if ($user->can('users.update')) {
            return true;
        }

        if ($user->can('users.update')) {
            return $user->id == $model->user_id;
        }
    }

    public function delete(User $user, User $model)
    {
        if ($user->can('users.delete')) {
            return true;
        }

        if ($user->can('users.delete')) {
            return $user->id == $model->user_id;
        }
    }
}