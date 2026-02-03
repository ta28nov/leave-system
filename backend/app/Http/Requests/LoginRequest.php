<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * LoginRequest
 * 
 * Validation cho API đăng nhập.
 * Chỉ validate format, không check user tồn tại (để AuthService xử lý).
 */
class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * 
     * Login API là public.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * Rules đơn giản:
     * - email: Bắt buộc, định dạng email
     * - password: Bắt buộc
     * 
     * Lưu ý: Không check email exists vì:
     * 1. Giảm query không cần thiết
     * 2. Bảo mật: không leak thông tin email có tồn tại hay không
     */
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

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email là bắt buộc. / Email is required.',
            'email.email' => 'Email không đúng định dạng. / Invalid email format.',
            'password.required' => 'Mật khẩu là bắt buộc. / Password is required.',
            'password.string' => 'Mật khẩu phải là chuỗi ký tự. / Password must be a string.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'email' => 'email',
            'password' => 'mật khẩu / password',
        ];
    }
}
