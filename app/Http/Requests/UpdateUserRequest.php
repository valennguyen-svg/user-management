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

        return [
            'name' => ['required', 'string', 'max:255'],
            // Bỏ qua chính user đang sửa khi kiểm tra trùng email
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user->id)->whereNull('deleted_at'),
            ],
            // để trống thì giữ mật khẩu cũ
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::exists('roles', 'name')],
            'status' => ['required', 'boolean'],
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
        // không cho tự chuyển tài khoản của mình sang không hoạt động
        $validator->after(function (Validator $validator) {
            $user = $this->route('user');

            if ($user->id === $this->user()->id && ! $this->boolean('status')) {
                $validator->errors()->add('status', 'Bạn không thể tự khóa tài khoản của mình.');
            }
        });
    }
}
