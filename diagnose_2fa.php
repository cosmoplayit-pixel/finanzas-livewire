<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Buscamos todos los usuarios que tengan algo de 2FA
$users = \App\Models\User::all();

echo "=== Reporte de Usuarios y 2FA ===" . PHP_EOL;
foreach ($users as $u) {
    echo "Email: " . $u->email . PHP_EOL;
    echo "  - Secreto: " . ($u->two_factor_secret ? 'SÍ' : 'NO') . PHP_EOL;
    echo "  - Códigos: " . ($u->two_factor_recovery_codes ? 'SÍ' : 'NO') . PHP_EOL;
    echo "  - Confirmado: " . ($u->two_factor_confirmed_at ? 'SÍ' : 'NO') . PHP_EOL;
    echo "---------------------------" . PHP_EOL;
}
