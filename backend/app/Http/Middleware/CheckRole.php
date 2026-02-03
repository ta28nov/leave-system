<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\ResponseHelper;
use App\Enums\UserType;

/**
 * CheckRole Middleware
 * 
 * Middleware kiểm tra quyền truy cập dựa trên role của user.
 * Được apply cho các routes cần phân quyền cụ thể.
 * 
 * Cách sử dụng trong routes:
 * - Route::middleware('role:admin') → Chỉ Admin
 * - Route::middleware('role:manager') → Chỉ Manager
 * - Route::middleware('role:admin,manager') → Admin hoặc Manager
 * 
 * User types :
 * - 0: Admin (Toàn quyền)
 * - 1: Manager (Duyệt đơn)
 * - 2: Employee (Tạo đơn)
 */
class CheckRole
{
    /**
     * Handle an incoming request.
     * 
     * Logic:
     * 1. Lấy user từ auth (đã verify bởi auth:api middleware)
     * 2. Parse roles được phép từ parameter
     * 3. Check user type có nằm trong danh sách được phép không
     * 4. Nếu không → 403 Forbidden
     * 5. Nếu có → tiếp tục request
     * 
     * @param Request $request
     * @param Closure $next
     * @param string ...$roles - Các roles được phép (admin, manager, employee)
     * @return Response
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        
        // Nếu chưa đăng nhập (shouldn't happen vì đã có auth:api trước)
        if (!$user) {
            return ResponseHelper::error(
                'Chưa xác thực. / Unauthenticated.',
                null,
                401
            );
        }
        // Nếu là Admin (type 0), cho qua luôn, không cần check gì nữa
        if ((int) $user->type === UserType::ADMIN->value) {
        return $next($request);
    }

        // Map role names to type values
        $allowed_types = $this->mapRolesToTypes($roles);
        
        // Check user type co duoc phep khong
        // Cast sang int de dam bao so sanh chinh xac
        if (!in_array((int) $user->type, $allowed_types, true)) {
            return ResponseHelper::error(
                'Khong co quyen truy cap. / Access denied.',
                null,
                403 // HTTP 403 Forbidden
            );
        }

        return $next($request);
    }

    /**
     * Map role names thành type values
     * 
     * @param array $roles - ['admin', 'manager', 'employee']
     * @return array - [0, 1, 2]
     */
    protected function mapRolesToTypes(array $roles): array
    {
        $type_map = [
            'admin' => UserType::ADMIN->value,       // 0
            'manager' => UserType::MANAGER->value,   // 1
            'employee' => UserType::EMPLOYEE->value, // 2
        ];

        $types = [];
        foreach ($roles as $role) {
            $role = strtolower(trim($role));
            if (isset($type_map[$role])) {
                $types[] = $type_map[$role];
            }
        }

        return $types;
    }
}
