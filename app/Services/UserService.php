<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    // Vai trò cho tài khoản tự đăng ký: quyền thấp nhất trong hệ thống
    public const REGISTER_ROLE = 'staff';

    public function __construct(
        private UserRepositoryInterface $users,
        private RoleRepositoryInterface $roles,
    ) {}

    public function paginate(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return $this->users->paginate($filters, $perPage);
    }

    // Truy vấn cho Export, cùng bộ lọc với danh sách
    public function exportQuery(array $filters): Builder
    {
        return $this->users->filteredQuery($filters);
    }

    public function roleNames(): Collection
    {
        return $this->roles->names();
    }

    // Tạo người dùng và gán vai trò .

    public function create(array $data): User
    {
        // Tạo user và gán role trong 1 transaction: lỗi thì không để lại dữ liệu một phần
        return DB::transaction(function () use ($data) {
            $user = $this->users->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']), // BR-02
                'status' => (bool) $data['status'],
            ]);

            $this->users->assignRole($user, $data['role']);

            return $user;
        });
    }

    // Cập nhật người dùng và đồng bộ vai trò
    // Mật khẩu để trống thì giữ nguyên

    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $attributes = [
                'name' => $data['name'],
                'email' => $data['email'],
                'status' => (bool) $data['status'],
            ];

            if (! empty($data['password'])) {
                $attributes['password'] = Hash::make($data['password']);
            }

            $this->users->update($user, $attributes);
            $this->users->syncRole($user, $data['role']);

            return $user;
        });
    }
    // Tự đăng ký: luôn là vai trò thấp nhất

    public function register(array $data): User
    {
        return $this->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => self::REGISTER_ROLE,
            'status' => false,
        ]);
    }

    // Quyền và điều kiện không tự xóa được kiểm tra ở UserPolicy trước khi gọi.

    public function delete(User $user): bool
    {
        if ($user->trashed()) {
            return false;
        }

        $this->users->delete($user);

        return true;
    }
}
