<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.view');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('users.view');
    }

    public function create(User $user): bool
    {
        return $user->can('users.create');
    }

    public function update(User $user, User $model): Response
    {
        if (! $user->can('users.update')) {
            return Response::deny('Bạn không có quyền cập nhật người dùng.');
        }

        return Response::allow();
    }

    public function delete(User $user, User $model): Response
    {
        if (! $user->can('users.delete')) {
            return Response::deny('Bạn không có quyền xóa người dùng.');
        }

        //  không được tự xóa tài khoản đang đăng nhập
        if ($user->id === $model->id) {
            return Response::deny('Bạn không thể tự xóa tài khoản của mình.');
        }

        return Response::allow();
    }

    public function export(User $user): bool
    {
        return $user->can('users.export');
    }

    public function import(User $user): bool
    {
        return $user->can('users.import');
    }

    /** Chỉ dùng nếu đã làm tính năng khôi phục người dùng đã xóa. */
    public function restore(User $user, User $model): bool
    {
        return $user->can('users.delete');
    }
}
