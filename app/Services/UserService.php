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
    public function __construct(
        private UserRepositoryInterface $users,
        private RoleRepositoryInterface $roles,
    ) {}

    public function paginate(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return $this->users->paginate($filters, $perPage);
    }

    // Truy vấn cho Export, áp dụng cùng bộ lọc với danh sách (BR-09)
    public function exportQuery(array $filters): Builder
    {
        return $this->users->filteredQuery($filters);
    }

    public function roleNames(): Collection
    {
        return $this->roles->names();
    }

    // Tạo người dùng và gán vai trò trong Transaction (UC-03)
    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = $this->users->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'status' => (bool) ($data['status'] ?? false),
            ]);

            $this->users->assignRole($user, $data['role']);

            return $user;
        });
    }

    // Cập nhật người dùng và đồng bộ vai trò (UC-04)
    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $attributes = [
                'name' => $data['name'],
                'email' => $data['email'],
                'status' => (bool) ($data['status'] ?? false),
            ];

            // Nếu để trống mật khẩu mới thì giữ nguyên mật khẩu cũ (AC-07)
            if (! empty($data['password'])) {
                $attributes['password'] = Hash::make($data['password']);
            }

            $this->users->update($user, $attributes);
            $this->users->syncRole($user, $data['role']);

            return $user;
        });
    }

    // Xóa mềm người dùng (UC-05 / BR-07)
    public function delete(User $user): bool
    {
        if ($user->trashed()) {
            return false;
        }

        $this->users->delete($user);

        return true;
    }
}
