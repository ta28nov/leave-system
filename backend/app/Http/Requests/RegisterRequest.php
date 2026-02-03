<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * RegisterRequest
 * 
 * Validation cho API đăng ký user mới.
 * Tất cả validation rules được áp dụng TRƯỚC khi vào Controller.
 * 
 * Quy trình:
 * 1. Request đến RegisterRequest
 * 2. authorize() check xem có quyền không
 * 3. rules() validate dữ liệu
 * 4. Nếu fail → tự động trả về 422 với errors
 * 5. Nếu pass → tiếp tục vào Controller
 */
class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * 
     * Register API là public, ai cũng có thể đăng ký.
     * Nếu cần giới hạn (vd: chỉ Admin tạo user), thay đổi logic ở đây.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * Rules giải thích:
     * - name: Bắt buộc, chuỗi, tối đa 255 ký tự
     * - email: Bắt buộc, định dạng email, unique trong bảng users
     * - password: Bắt buộc, tối thiểu 6 ký tự, phải có password_confirmation khớp
     * - type: Không bắt buộc (default = 2), phải là 0, 1, hoặc 2
     */
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
                'unique:users,email', // Không trùng email trong bảng users
            ],
            'password' => [
                'required',
                'string',
                'min:6',
                'confirmed', // Yêu cầu field password_confirmation và phải khớp
            ],
            'type' => [
                'sometimes', // Chỉ validate nếu có trong request
                'integer',
                'in:0,1,2', // 0: Admin, 1: Manager, 2: Employee
            ],
        ];
    }

    /**
     * Get custom messages for validation errors.
     * 
     * Định dạng: Song ngữ (Vietnamese / English)
     * Giúp frontend dễ dàng hiển thị lỗi cho user.
     */
    public function messages(): array
    {
        return [
            // name
            'name.required' => 'Họ tên là bắt buộc. / Name is required.',
            'name.string' => 'Họ tên phải là chuỗi ký tự. / Name must be a string.',
            'name.max' => 'Họ tên không được vượt quá 255 ký tự. / Name must not exceed 255 characters.',
            
            // email
            'email.required' => 'Email là bắt buộc. / Email is required.',
            'email.email' => 'Email không đúng định dạng. / Invalid email format.',
            'email.unique' => 'Email đã được sử dụng. / Email already exists.',
            
            // password
            'password.required' => 'Mật khẩu là bắt buộc. / Password is required.',
            'password.string' => 'Mật khẩu phải là chuỗi ký tự. / Password must be a string.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự. / Password must be at least 6 characters.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp. / Password confirmation does not match.',
            
            // type
            'type.integer' => 'Loại người dùng phải là số nguyên. / User type must be an integer.',
            'type.in' => 'Loại người dùng không hợp lệ. Chỉ chấp nhận: 0 (Admin), 1 (Manager), 2 (Employee). / Invalid user type.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
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
