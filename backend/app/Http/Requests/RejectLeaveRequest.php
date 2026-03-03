<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation cho API từ chối đơn nghỉ phép.
 * Bắt buộc phải có lý do từ chối.
 */
class RejectLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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

    public function messages(): array
    {
        return [
            'reason.required' => 'Lý do từ chối là bắt buộc. / Rejection reason is required.',
            'reason.string' => 'Lý do phải là chuỗi ký tự. / Reason must be a string.',
            'reason.max' => 'Lý do không được vượt quá 1000 ký tự. / Reason must not exceed 1000 characters.',
        ];
    }

    public function attributes(): array
    {
        return [
            'reason' => 'lý do từ chối / rejection reason',
        ];
    }
}
