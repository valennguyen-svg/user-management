<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Kiểm tra định dạng và kích thước tệp
        return [
            'file' => [
                'required',
                'file',
                'extensions:xlsx,xls,csv',
                'mimes:xlsx,xls,csv,txt',
                'max:2048',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Vui lòng chọn tệp để import.',
            'file.extensions' => 'Chỉ nhập tệp .xlsx, .xls hoặc .csv',
            'file.mimes' => 'Nội dung tệp không phải bảng tính hợp lệ.',
            'file.max' => 'Tệp tối đa 2 MB.',
        ];
    }
}
