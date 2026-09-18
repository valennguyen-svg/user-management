<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Excel as ExcelType;
use Maatwebsite\Excel\HeadingRowImport;

class UsersImport implements ToCollection, WithHeadingRow
{
    // Các cột bắt buộc theo spec
    public const COLUMNS = [
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

    // Giá trị status công bố trong tệp mẫu
    public const STATUSES = [
        'active' => true,
        'inactive' => false,
    ];

    public const MAX_ROWS = 1000;

    public int $total = 0;

    public int $success = 0;

    public array $errors = [];

    public bool $tooManyRows = false;

    public static function readerType(UploadedFile $file): string
    {
        return match (strtolower($file->getClientOriginalExtension())) {
            'xls' => ExcelType::XLS,
            'csv' => ExcelType::CSV,
            default => ExcelType::XLSX,
        };
    }

    public static function missingColumns(UploadedFile $file, string $readerType): array
    {
        $headings = (new HeadingRowImport)->toArray($file, null, $readerType)[0][0] ?? [];

        return array_values(array_diff(self::COLUMNS, $headings));
    }

    public function collection(Collection $rows): void
    {
        $processedRows = collect();

        foreach ($rows as $index => $row) {
            $lineNum = $index + 2; // Dòng 1 là tiêu đề

            $cleanedRow = [
                'excel_line' => $lineNum,
                'name' => trim(preg_replace('/\s+/u', ' ', (string) ($row['name'] ?? ''))),
                'email' => mb_strtolower(trim((string) ($row['email'] ?? ''))),
                'password' => (string) ($row['password'] ?? ''),
                'role' => trim((string) ($row['role'] ?? '')),
                'status' => mb_strtolower(trim((string) ($row['status'] ?? ''))),
            ];

            // Bỏ qua dòng trống hoàn toàn
            $checkEmpty = array_diff_key($cleanedRow, ['excel_line' => '']);
            if (implode('', $checkEmpty) !== '') {
                $processedRows->push($cleanedRow);
            }
        }

        if ($processedRows->count() > self::MAX_ROWS) {
            $this->tooManyRows = true;

            return;
        }

        $this->total = $processedRows->count();

        // Validate dữ liệu nghiêm ngặt
        $validator = Validator::make($processedRows->toArray(), [
            '*.name' => ['required', 'string', 'max:255'],
            '*.email' => [
                'required',
                'string',
                'max:255',
                'email:filter', // Rule mặc định kiểm tra email chuẩn
                'distinct:ignore_case', // Không trùng giữa các dòng trong file
                Rule::unique('users', 'email')->whereNull('deleted_at'), // Không trùng CSDL
                function ($attribute, $value, $fail) {
                    // Yêu cầu email có đuôi domain hợp lệ (dạng user@domain.com, user@domain.vn,...)
                    if (! preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $value)) {
                        $fail('Email không đúng định dạng (phải có dạng user@domain.com).');
                    }
                },
            ],
            '*.password' => [
                'required',
                'string',
                'min:8',
            ], // Bắt buộc, tối thiểu 8 ký tự
            '*.role' => [
                'required',
                Rule::exists('roles', 'name'),
            ], // Bắt buộc tồn tại trong CSDL
            '*.status' => [
                'required',
                Rule::in(array_keys(self::STATUSES)),
            ], // Chỉ nhận active/inactive
        ], $this->messages());

        // Gom nhóm lỗi theo dòng trong Excel
        $rowErrors = [];
        foreach ($validator->errors()->messages() as $key => $messages) {
            $arrayIndex = (int) explode('.', $key, 2)[0];
            $rowErrors[$arrayIndex] = array_merge($rowErrors[$arrayIndex] ?? [], $messages);
        }

        // Thực hiện thêm tài khoản cho các dòng hợp lệ
        foreach ($processedRows as $arrayIndex => $row) {
            $line = $row['excel_line'];

            // Nếu thiếu thông tin hoặc sai chuẩn -> Từ chối import dòng này
            if (isset($rowErrors[$arrayIndex])) {
                $this->addError($line, implode(' ', $rowErrors[$arrayIndex]));

                continue;
            }

            try {
                DB::transaction(function () use ($row) {
                    // Tạo tài khoản mới hợp lệ
                    $user = User::create([
                        'name' => $row['name'],
                        'email' => $row['email'],
                        'password' => Hash::make($row['password']),
                        'status' => self::STATUSES[$row['status']],
                    ]);

                    $user->assignRole($row['role']);
                });

                $this->success++;
            } catch (\Throwable $e) {
                report($e);
                $this->addError($line, 'Lỗi hệ thống khi ghi dòng này, dữ liệu của dòng đã được hoàn tác.');
            }
        }
    }

    public function result(): array
    {
        $errors = collect($this->errors)->sortBy('row')->values()->all();

        return [
            'total' => $this->total,
            'success' => $this->success,
            'failed' => count($errors),
            'errors' => $errors,
        ];
    }

    private function addError(int $line, string $message): void
    {
        $this->errors[] = ['row' => $line, 'message' => $message];
    }

    private function messages(): array
    {
        return [
            '*.name.required' => 'Họ tên không được để trống.',
            '*.name.max' => 'Họ tên tối đa 255 ký tự.',
            '*.email.required' => 'Email không được để trống.',
            '*.email.email' => 'Email phải đầy đủ định dạng dạng user@domain.com.',
            '*.email.max' => 'Email tối đa 255 ký tự.',
            '*.email.distinct' => 'Email bị trùng với dòng khác trong tệp.',
            '*.email.unique' => 'Email đã tồn tại trong hệ thống.',
            '*.password.required' => 'Mật khẩu không được để trống.',
            '*.password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            '*.role.required' => 'Vai trò không được để trống.',
            '*.role.exists' => 'Vai trò không tồn tại trong hệ thống.',
            '*.status.required' => 'Trạng thái không được để trống.',
            '*.status.in' => 'Trạng thái chỉ nhận giá trị active hoặc inactive.',
        ];
    }
}
