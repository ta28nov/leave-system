<?php

/**
 * NoOverlapDates - Custom Validation Rule
 * 
 * Kiểm tra xem khoảng thời gian nghỉ phép có trùng với đơn nghỉ khác không.
 * Áp dụng cho cùng user, chỉ check các đơn chưa bị hủy/từ chối.
 * 
 * Thuật toán overlap:
 * Hai khoảng [A1, A2] và [B1, B2] overlap nếu: A1 <= B2 AND A2 >= B1
 * 
 * Sử dụng:
 * - CreateLeaveApplicationRequest: new NoOverlapDates($start, $end)
 * - UpdateLeaveApplicationRequest: new NoOverlapDates($start, $end, $excludeId)
 * 
 * @see CreateLeaveApplicationRequest
 * @see UpdateLeaveApplicationRequest
 */

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\LeaveApplication;
use Illuminate\Support\Facades\Auth;

class NoOverlapDates implements ValidationRule
{
    /**
     * @var string|null Ngày bắt đầu cần check
     */
    protected $startDate;

    /**
     * @var string|null Ngày kết thúc cần check
     */
    protected $endDate;

    /**
     * @var string|null ID đơn cần exclude (khi update)
     */
    protected $excludeId;

    /**
     * Create a new rule instance.
     * 
     * @param string|null $startDate Ngày bắt đầu
     * @param string|null $endDate Ngày kết thúc
     * @param string|null $excludeId ID đơn cần loại trừ (cho update)
     */
    public function __construct($startDate, $endDate, $excludeId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->excludeId = $excludeId;
    }

    /**
     * Run the validation rule.
     * 
     * Logic:
     * 1. Lấy user_id từ auth hoặc request
     * 2. Query các đơn của user với status chưa bị hủy/từ chối
     * 3. Check overlap với khoảng thời gian mới
     * 4. Nếu có overlap → fail validation
     * 
     * @param string $attribute Tên field đang validate
     * @param mixed $value Giá trị của field
     * @param Closure $fail Callback để báo lỗi
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Lấy user_id - ưu tiên từ Auth, fallback từ request
        $userId = Auth::id() ?? request()->input('user_id');

        // Không validate nếu thiếu thông tin
        if (!$userId || !$this->startDate || !$this->endDate) {
            return;
        }

        // Query tìm đơn overlap
        // Overlap formula: (start1 <= end2) AND (end1 >= start2)
        $query = LeaveApplication::where('user_id', $userId)
            ->where(function ($q) {
                $q->where(function ($query) {
                    $query->where('start_date', '<=', $this->endDate)
                          ->where('end_date', '>=', $this->startDate);
                });
            })
            // Chỉ check các đơn đang active (không bị hủy/từ chối)
            ->whereIn('status', ['new', 'pending', 'approved'])
            ->whereNull('deleted_at');

        // Exclude đơn hiện tại khi update
        if ($this->excludeId) {
            $query->where('id', '!=', $this->excludeId);
        }

        $overlappingLeaves = $query->count();

        // Nếu có overlap → fail
        if ($overlappingLeaves > 0) {
            $fail('Ngày nghỉ bị trùng lặp với đơn nghỉ phép khác. / The leave dates overlap with another leave application.');
        }
    }
}
