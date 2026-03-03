<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\LeaveApplication;
use Illuminate\Support\Facades\Auth;

/**
 * Rule kiểm tra ngày nghỉ có trùng lặp với đơn khác không.
 * 
 * Thuật toán: Hai khoảng [A1, A2] và [B1, B2] trùng nếu A1 <= B2 AND A2 >= B1
 * Chỉ kiểm tra đơn đang active (new, pending, approved).
 */
class NoOverlapDates implements ValidationRule
{
    protected $startDate;
    protected $endDate;
    protected $excludeId; // ID đơn cần loại trừ khi update

    public function __construct($startDate, $endDate, $excludeId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->excludeId = $excludeId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $userId = Auth::id() ?? request()->input('user_id');

        if (!$userId || !$this->startDate || !$this->endDate) {
            return;
        }

        // Tìm đơn trùng ngày: start_date <= endDate AND end_date >= startDate
        $query = LeaveApplication::where('user_id', $userId)
            ->where(function ($q) {
                $q->where(function ($query) {
                    $query->where('start_date', '<=', $this->endDate)
                          ->where('end_date', '>=', $this->startDate);
                });
            })
            ->whereIn('status', ['new', 'pending', 'approved'])
            ->whereNull('deleted_at');

        // Loại trừ đơn đang sửa (khi update)
        if ($this->excludeId) {
            $query->where('id', '!=', $this->excludeId);
        }

        if ($query->count() > 0) {
            $fail('Ngày nghỉ bị trùng lặp với đơn nghỉ phép khác. / The leave dates overlap with another leave application.');
        }
    }
}
