<?php

namespace App\Exports;

use App\Services\UserService;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    use Exportable;

    // truyền tham số qua constructor
    public function __construct(private array $filters = []) {}

    // FromQuery tự đọc theo từng chunk
    public function query(): Builder
    {
        return app(UserService::class)->exportQuery($this->filters);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Họ tên',
            'Email',
            'Vai trò',
            'Trạng thái',
            'Ngày tạo',
        ];
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->getRoleNames()->implode(','),
            $user->status ? 'Hoạt đông' : 'Không hoạt đông',
            $user->created_at?->format('d/m/Y H:i:s'),
        ];
    }
}
