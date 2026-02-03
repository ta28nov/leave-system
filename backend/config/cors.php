<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    |CORS Configuration
    |
    | Config CORS cho phép Frontend gọi API từ domain khác.
    | Quan trọng khi Frontend và Backend chạy trên khác port/domain.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | CORS Paths
    |--------------------------------------------------------------------------
    |
    | Các paths sẽ được áp dụng CORS headers.
    | 'api/*' - Tất cả routes bắt đầu bằng /api/
    |
    */
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Methods
    |--------------------------------------------------------------------------
    |
    | HTTP methods được phép gọi.
    | '*' = tất cả methods (GET, POST, PUT, PATCH, DELETE, OPTIONS)
    |
    */
    'allowed_methods' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    |
    | Các domain được phép gọi API.
    | 
    | Development: '*' (tất cả)
    | Production: ['https://your-frontend-domain.com']
    |
    */
    'allowed_origins' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins Patterns
    |--------------------------------------------------------------------------
    |
    | Regex patterns cho allowed origins.
    | Ví dụ: ['*.example.com']
    |
    */
    'allowed_origins_patterns' => [],

    /*
    |--------------------------------------------------------------------------
    | Allowed Headers
    |--------------------------------------------------------------------------
    |
    | HTTP headers được phép gửi trong request.
    | '*' = tất cả headers
    |
    | Quan trọng: Phải cho phép 'Authorization' header cho JWT token
    |
    */
    'allowed_headers' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Exposed Headers
    |--------------------------------------------------------------------------
    |
    | Headers mà browser được phép đọc từ response.
    |
    */
    'exposed_headers' => [],

    /*
    |--------------------------------------------------------------------------
    | Max Age
    |--------------------------------------------------------------------------
    |
    | Thời gian cache preflight request (giây).
    | 0 = không cache
    |
    */
    'max_age' => 0,

    /*
    |--------------------------------------------------------------------------
    | Supports Credentials
    |--------------------------------------------------------------------------
    |
    | Cho phép gửi cookies trong cross-origin requests.
    | Nếu true, 'allowed_origins' không được dùng '*'
    |
    */
    'supports_credentials' => false,

];
