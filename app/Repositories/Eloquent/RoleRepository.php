<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

class RoleRepository implements RoleRepositoryInterface
{
    public function names(): Collection
    {
        return Role::all()->pluck('name');
    }

    public function exists(string $name): bool
    {
        return Role::where('name', $name)->exists();
    }
}
