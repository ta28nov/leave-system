<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\ResponseHelper;
use App\Enums\UserType;

/**
 * Middleware kiểm tra quyền truy cập theo role.
 * 
 * Sử dụng: Route::middleware('role:admin,manager')
 * User types: 0 = Admin, 1 = Manager, 2 = Employee
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return ResponseHelper::error('Chưa xác thực. / Unauthenticated.', null, 401);
        }

        // Admin luôn được phép truy cập
        if ((int) $user->type === UserType::ADMIN->value) {
            return $next($request);
        }

        $allowed_types = $this->mapRolesToTypes($roles);
        
        if (!in_array((int) $user->type, $allowed_types, true)) {
            return ResponseHelper::error('Không có quyền truy cập. / Access denied.', null, 403);
        }

        return $next($request);
    }

    /** Chuyển tên role (admin, manager, employee) thành giá trị số (0, 1, 2) */
    protected function mapRolesToTypes(array $roles): array
    {
        $type_map = [
            'admin' => UserType::ADMIN->value,
            'manager' => UserType::MANAGER->value,
            'employee' => UserType::EMPLOYEE->value,
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
