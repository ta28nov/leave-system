<?php

return [

    // Đường dẫn áp dụng CORS
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // Phương thức HTTP cho phép
    'allowed_methods' => ['*'],

    // Domain cho phép gọi API (⚠️ Giới hạn domain cụ thể khi lên production)
    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    // Header cho phép (bao gồm Authorization cho JWT)
    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
