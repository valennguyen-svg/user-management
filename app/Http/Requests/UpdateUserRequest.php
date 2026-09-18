<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Quyền đã được chặn bằng middleware permission:users.update trên route
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user');
        $userId = is_object($user) ? $user->id : $user;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            // Bỏ qua chính user đang sửa khi kiểm tra trùng email
            'email' => [
                'required',
                'string',
                'max:255',
                'email:filter',
                Rule::unique('users', 'email')->ignore($userId)->whereNull('deleted_at'),
                function ($attribute, $value, $fail) {
                    if (! preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $value)) {
                        $fail('Trường :attribute không đúng định dạng (phải có dạng user@domain.com).');
                    }
                },
            ],
            // Để trống thì giữ mật khẩu cũ
            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
            'role' => [
                'required',
                'string',
                Rule::exists('roles', 'name'),
            ],
            'status' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'họ tên',
            'email' => 'email',
            'password' => 'mật khẩu',
            'role' => 'vai trò',
            'status' => 'trạng thái',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        // Không cho tự chuyển tài khoản của mình sang không hoạt động
        $validator->after(function (Validator $validator) {
            $user = $this->route('user');
            $userId = is_object($user) ? $user->id : $user;

            if ($userId == $this->user()?->id && ! $this->boolean('status')) {
                $validator->errors()->add('status', 'Bạn không thể tự khóa tài khoản của mình.');
            }
        });
    }
}
