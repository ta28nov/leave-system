<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * RejectLeaveRequest
 * 
 * Validation cho API từ chối đơn nghỉ phép.
 * Yêu cầu phải có lý do từ chối.
 */
class RejectLeaveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * 
     * Authorization được check bởi middleware 'role:admin,manager'.
     * Nếu đến được đây nghĩa là đã có quyền.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * Rules:
     * - reason: Bắt buộc, chuỗi, tối đa 1000 ký tự
     */
    public function rules(): array
    {
        return [
            'reason' => [
                'required',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'reason.required' => 'Lý do từ chối là bắt buộc. / Rejection reason is required.',
            'reason.string' => 'Lý do phải là chuỗi ký tự. / Reason must be a string.',
            'reason.max' => 'Lý do không được vượt quá 1000 ký tự. / Reason must not exceed 1000 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'reason' => 'lý do từ chối / rejection reason',
        ];
    }
}
