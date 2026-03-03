<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\LeaveApplicationType;
use App\Rules\NoOverlapDates;

/**
 * Validation cho việc tạo đơn nghỉ phép mới.
 * Tất cả validation chạy TRƯỚC khi request vào Controller.
 */
class CreateLeaveApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $startDate = $this->input('start_date');
        $endDate = $this->input('end_date');

        return [
            'start_date' => [
                'required',
                'date',
                'after_or_equal:today',
                new NoOverlapDates($startDate, $endDate),
            ],
            'end_date' => [
                'required',
                'date',
                'after_or_equal:start_date',
            ],
            'reason' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'type' => [
                'required',
                'string',
                'in:' . implode(',', LeaveApplicationType::values()),
            ],
        ];
    }

    /** Thông báo lỗi song ngữ Việt/Anh */
    public function messages(): array
    {
        return [
            'start_date.required' => 'Ngày bắt đầu nghỉ là bắt buộc. / Start date is required.',
            'start_date.date' => 'Ngày bắt đầu phải là định dạng ngày hợp lệ. / Start date must be a valid date.',
            'start_date.after_or_equal' => 'Ngày bắt đầu phải từ hôm nay trở đi. / Start date must be today or later.',
            'end_date.required' => 'Ngày kết thúc nghỉ là bắt buộc. / End date is required.',
            'end_date.date' => 'Ngày kết thúc phải là định dạng ngày hợp lệ. / End date must be a valid date.',
            'end_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu. / End date must be after or equal to start date.',
            'reason.string' => 'Lý do nghỉ phải là chuỗi ký tự. / Reason must be a string.',
            'reason.max' => 'Lý do nghỉ không được vượt quá 1000 ký tự. / Reason must not exceed 1000 characters.',
            'type.required' => 'Loại nghỉ phép là bắt buộc. / Leave type is required.',
            'type.string' => 'Loại nghỉ phép phải là chuỗi ký tự. / Leave type must be a string.',
            'type.in' => 'Loại nghỉ phép không hợp lệ. Chỉ chấp nhận: annual, sick, unpaid. / Invalid leave type. Only accept: annual, sick, unpaid.',
        ];
    }

    public function attributes(): array
    {
        return [
            'start_date' => 'ngày bắt đầu / start date',
            'end_date' => 'ngày kết thúc / end date',
            'reason' => 'lý do / reason',
            'type' => 'loại nghỉ phép / leave type',
        ];
    }
}
