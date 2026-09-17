<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(private RoleRepositoryInterface $roles) {}

    public function filteredQuery(array $filters): Builder
    {

        $query = User::query()->with('roles');

        // Tìm theo tên or email
        if (! empty($filters['keyword'])) {
            $keyword = trim($filters['keyword']);

            $query->where(function (Builder $q) use ($keyword) {
                $q->where('name', 'ilike', "%{$keyword}%")
                    ->orWhere('email', 'ilike', "%{$keyword}%");
            });
        }

        if (! empty($filters['role'])) {
            if ($this->roles->exists($filters['role'])) {
                $query->role($filters['role']);
            } else {
                // Role không tồn tại: trả về danh sách rỗng thay vì báo lỗi
                $query->whereRaw('1 = 0');
            }
        }

        // Lọc trạng thái
        if (isset($filters['status']) && in_array($filters['status'], ['0', '1'], true)) {
            $query->where('status', $filters['status'] === '1');
        }

        // Sắp xếp theo ngày tạo trùng thời điểm thì theo ID
        $direction = ($filters['sort'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        return $query->orderBy('created_at', $direction)->orderBy('id', $direction);
    }

    public function paginate(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return $this->filteredQuery($filters)
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $attributes): User
    {
        return User::create($attributes);
    }

    public function update(User $user, array $attributes): User
    {
        $user->fill($attributes)->save();

        return $user;
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    public function assignRole(User $user, string $role): void
    {
        $user->assignRole($role);
    }

    public function syncRole(User $user, string $role): void
    {
        $user->syncRoles([$role]);
    }
}
