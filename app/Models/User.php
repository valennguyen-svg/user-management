<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    use HasRoles;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Bộ lọc dùng chung cho trang danh sách và Export.

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        // Tìm theo tên hoặc email
        if (! empty($filters['keyword'])) {
            $keyword = trim($filters['keyword']);

            $query->where(function (Builder $q) use ($keyword) {
                $q->where('name', 'ilike', "%{$keyword}%")
                    ->orWhere('email', 'ilike', "%{$keyword}%");
            });
        }

        // Returns only users with the role 'writer'
        if (! empty($filters['role'])) {
            if (Role::where('name', $filters['role'])->exists()) {
                $query->role($filters['role']);
            } else {
                // Role không tồn tại: trả về danh sách rỗng thay vì báo lỗi
                $query->whereRaw('1 = 0');
            }
        }

        // Lọc trạng thái: 1 = Hoạt động, 0 = Không hoạt động
        if (isset($filters['status']) && in_array($filters['status'], ['0', '1'], true)) {
            $query->where('status', $filters['status'] === '1');
        }

        // Sắp xếp theo ngày tạo
        $direction = ($filters['sort'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return $query->orderBy('created_at', $direction)->orderBy('id', $direction);
    }
}
