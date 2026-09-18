<?php

namespace App\Exports;

use App\Imports\UsersImport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersTemplateExport implements FromArray, ShouldAutoSize, WithHeadings
{
    public function array(): array
    {
        return [
            [
                'Nguyễn Văn A',
                'nguyenvana@example.com',
                'password123',
                'staff',
                'active',
            ],
            [
                'Trần Thị B',
                'tranthib@example.com',
                'password123',
                'staff',
                'inactive',
            ],

        ];
    }

    public function headings(): array
    {
        // name, email, password, role, status
        return UsersImport::COLUMNS;
    }
}
