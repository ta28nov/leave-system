<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation cho API đăng ký user mới.
 */
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'string',
                'min:6',
                'confirmed', // Yêu cầu field password_confirmation khớp
            ],
            'type' => [
                'sometimes',
                'integer',
                'in:0,1,2', // 0: Admin, 1: Manager, 2: Employee
            ],
        ];
    }

    /** Thông báo lỗi song ngữ Việt/Anh */
    public function messages(): array
    {
        return [
            'name.required' => 'Họ tên là bắt buộc. / Name is required.',
            'name.string' => 'Họ tên phải là chuỗi ký tự. / Name must be a string.',
            'name.max' => 'Họ tên không được vượt quá 255 ký tự. / Name must not exceed 255 characters.',
            'email.required' => 'Email là bắt buộc. / Email is required.',
            'email.email' => 'Email không đúng định dạng. / Invalid email format.',
            'email.unique' => 'Email đã được sử dụng. / Email already exists.',
            'password.required' => 'Mật khẩu là bắt buộc. / Password is required.',
            'password.string' => 'Mật khẩu phải là chuỗi ký tự. / Password must be a string.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự. / Password must be at least 6 characters.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp. / Password confirmation does not match.',
            'type.integer' => 'Loại người dùng phải là số nguyên. / User type must be an integer.',
            'type.in' => 'Loại người dùng không hợp lệ. Chỉ chấp nhận: 0 (Admin), 1 (Manager), 2 (Employee). / Invalid user type.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'họ tên / name',
            'email' => 'email',
            'password' => 'mật khẩu / password',
            'type' => 'loại người dùng / user type',
        ];
    }
}
