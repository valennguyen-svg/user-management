<?php

namespace App\Models;

use Illuminate\Support\Str;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    // Tự động viết hoa chữ cái đầu khi gọi $role->name
    public function getNameAttribute($value): string
    {
        return Str::ucfirst($value);
    }
}
