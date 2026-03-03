<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation cho API đăng nhập.
 * Chỉ validate format, không check user tồn tại (bảo mật: không leak thông tin email).
 */
class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
            ],
            'password' => [
                'required',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email là bắt buộc. / Email is required.',
            'email.email' => 'Email không đúng định dạng. / Invalid email format.',
            'password.required' => 'Mật khẩu là bắt buộc. / Password is required.',
            'password.string' => 'Mật khẩu phải là chuỗi ký tự. / Password must be a string.',
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'email',
            'password' => 'mật khẩu / password',
        ];
    }
}
