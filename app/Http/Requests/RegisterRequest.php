<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Trang đăng ký dành cho khách (middleware guest trên route)
        return true;
    }

    protected function prepareForValidation(): void
    {
        // loại bỏ khoảng trắng thừa ở họ tên
        $this->merge([
            'name' => trim(preg_replace('/\s+/u', ' ', (string) $this->name)),
            'email' => trim((string) $this->email),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            //  email duy nhất trong các user chưa bị xóa
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            //  tối thiểu 8 ký tự, phải nhập lại để xác nhận
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'họ tên',
            'email' => 'email',
            'password' => 'mật khẩu',
        ];
    }
}
