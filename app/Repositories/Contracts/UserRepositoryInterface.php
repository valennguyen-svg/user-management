<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Lớp truy cập dữ liệu người dùng.
 * Chỉ đọc/ghi dữ liệu, không chứa quy tắc nghiệp vụ .
 */
interface UserRepositoryInterface
{
    // Truy vấn theo bộ lọc tìm kiếm/lọc/sắp xếp; dùng chung cho danh sách và export
    public function filteredQuery(array $filters): Builder;

    // Danh sách có phân trang, giữ bộ lọc trên link chuyển trang .
    public function paginate(array $filters, int $perPage = 10): LengthAwarePaginator;

    public function create(array $attributes): User;

    public function update(User $user, array $attributes): User;

    // Xóa mềm
    public function delete(User $user): void;

    public function assignRole(User $user, string $role): void;

    public function syncRole(User $user, string $role): void;
}
