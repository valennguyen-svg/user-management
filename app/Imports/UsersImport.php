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

// Laravel Excel - Imports:
// Importing to collections (ToCollection) + Heading row (WithHeadingRow)
// + Row Validation - Row Validation without ToModel
class UsersImport implements ToCollection, WithHeadingRow
{
    // các cột bắt buộc
    public const COLUMNS = ['name', 'email', 'password', 'role', 'status'];

    // Giá trị status được công bố trong tệp mẫu
    public const STATUSES = ['active' => true, 'inactive' => false];

    //  ngưỡng số dòng cho một lần import
    public const MAX_ROWS = 1000;

    public int $total = 0;

    public int $success = 0;

    public array $errors = [];

    public bool $tooManyRows = false;

    /*
     * Excel::import(new UsersImport, 'users.xlsx', 's3', \Maatwebsite\Excel\Excel::XLSX);
     * Chọn reader theo đuôi tệp (xlsx / xls / csv).
     */
    public static function readerType(UploadedFile $file): string
    {
        return match (strtolower($file->getClientOriginalExtension())) {
            'xls' => ExcelType::XLS,
            'csv' => ExcelType::CSV,
            default => ExcelType::XLSX,
        };
    }

    /**
     * "The headings array contains an array of headings per sheet" -> lấy [0][0] = sheet đầu, hàng tiêu đề.
     * Trả về danh sách cột còn thiếu (AC-12).
     */
    public static function missingColumns(UploadedFile $file, string $readerType): array
    {
        $headings = (new HeadingRowImport)->toArray($file, null, $readerType)[0][0] ?? [];

        return array_values(array_diff(self::COLUMNS, $headings));
    }

    public function collection(Collection $rows): void
    {
        // Chuẩn hóa dữ liệu, giữ nguyên chỉ số dòng để tính số dòng trong Excel
        $rows = $rows
            ->map(fn ($row) => [
                'name' => trim(preg_replace('/\s+/u', ' ', (string) ($row['name'] ?? ''))),
                'email' => trim((string) ($row['email'] ?? '')),
                'password' => (string) ($row['password'] ?? ''),
                'role' => trim((string) ($row['role'] ?? '')),
                'status' => mb_strtolower(trim((string) ($row['status'] ?? ''))),
            ])
            // Bỏ dòng trống
            ->filter(fn (array $row) => implode('', $row) !== '');

        if ($rows->count() > self::MAX_ROWS) {
            $this->tooManyRows = true;

            return;
        }

        $this->total = $rows->count();

        // Ở đây không gọi ->validate() (sẽ dừng cả tệp), mà lấy lỗi để xử lý từng dòng (BR-10)
        $validator = Validator::make($rows->toArray(), [
            '*.name' => ['required', 'string', 'max:255'],
            // distinct = không trùng trong tệp (docs: chỉ chạy với ToCollection / WithBatchInserts)
            '*.email' => [
                'required', 'email', 'max:255', 'distinct:ignore_case',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            '*.password' => ['required', 'string', 'min:8'],
            '*.role' => ['required', Rule::exists('roles', 'name')],
            '*.status' => ['required', Rule::in(array_keys(self::STATUSES))],
        ], $this->messages());

        // Gom lỗi theo từng dòng: khóa "3.email" -> dòng có chỉ số 3
        $rowErrors = [];
        foreach ($validator->errors()->messages() as $key => $messages) {
            $index = (int) explode('.', $key, 2)[0];
            $rowErrors[$index] = array_merge($rowErrors[$index] ?? [], $messages);
        }

        foreach ($rows as $index => $row) {
            // Heading row nằm ở dòng 1, dữ liệu bắt đầu từ dòng 2
            $line = $index + 2;

            // dòng không hợp lệ thì không tạo tài khoản
            if (isset($rowErrors[$index])) {
                $this->addError($line, implode(' ', $rowErrors[$index]));

                continue;
            }

            // Chính sách giao dịch: mỗi dòng hợp lệ được ghi trong 1 transaction riêng.
            // Lỗi hệ thống ở dòng nào thì chỉ hoàn tác dòng đó.
            try {
                DB::transaction(function () use ($row) {
                    $user = User::create([
                        'name' => $row['name'],
                        'email' => $row['email'],
                        'password' => Hash::make($row['password']),
                        'status' => self::STATUSES[$row['status']],
                    ]);

                    // Spatie - Assigning Roles: $user->assignRole('writer');
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
            '*.email.email' => 'Email không đúng định dạng.',
            '*.email.max' => 'Email tối đa 255 ký tự.',
            '*.email.distinct' => 'Email bị trùng với dòng khác trong tệp.',
            '*.email.unique' => 'Email đã tồn tại trong hệ thống.',
            '*.password.required' => 'Mật khẩu không được để trống.',
            '*.password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            '*.role.required' => 'Vai trò không được để trống.',
            '*.role.exists' => 'Vai trò không tồn tại trong hệ thống.',
            '*.status.required' => 'Trạng thái không được để trống.',
            '*.status.in' => 'Trạng thái chỉ nhận active hoặc inactive.',
        ];
    }
}
