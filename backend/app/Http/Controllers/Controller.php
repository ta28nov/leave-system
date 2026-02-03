<?php

/**
 * Base Controller
 * 
 * Abstract base controller cho tất cả controllers trong ứng dụng.
 * Các controllers khác sẽ extends class này.
 * 
 * Traits:
 * - AuthorizesRequests: Cho phép sử dụng $this->authorize() để check Policy
 * 
 * @see AuthController
 * @see LeaveApplicationController
 */

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    use AuthorizesRequests;
}
