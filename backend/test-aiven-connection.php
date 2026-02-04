<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== KIEM TRA KET NOI AIVEN ===\n\n";

echo "Config Database:\n";
echo "Host: " . config('database.connections.mysql.host') . "\n";
echo "Port: " . config('database.connections.mysql.port') . "\n";
echo "Database: " . config('database.connections.mysql.database') . "\n";
echo "Username: " . config('database.connections.mysql.username') . "\n";
echo "Password: " . substr(config('database.connections.mysql.password'), 0, 10) . "...\n\n";

echo "SSL Options:\n";
$options = config('database.connections.mysql.options');
foreach ($options as $key => $value) {
    echo "  Option [$key]: " . (is_bool($value) ? ($value ? 'TRUE' : 'FALSE') : $value) . "\n";
}

echo "\nThu ket noi database...\n";

try {
    DB::connection()->getPdo();
    echo "\n[THANH CONG] Ket noi thanh cong toi Aiven!\n";
} catch (\Exception $e) {
    echo "\n[LOI] Khong the ket noi: " . $e->getMessage() . "\n";
    echo "\nChi tiet loi day du:\n";
    echo $e->getTraceAsString() . "\n";
}
