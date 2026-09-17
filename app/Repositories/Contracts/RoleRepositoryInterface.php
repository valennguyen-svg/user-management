<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface RoleRepositoryInterface
{
    /** Danh sách tên vai trò đang có. */
    public function names(): Collection;

    public function exists(string $name): bool;
}
