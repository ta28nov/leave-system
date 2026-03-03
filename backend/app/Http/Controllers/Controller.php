<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/** Base Controller — các controller khác kế thừa từ đây */
abstract class Controller
{
    use AuthorizesRequests;
}
