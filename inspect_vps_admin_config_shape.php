<?php

chdir('/var/www/salehbasahel');
require '/var/www/salehbasahel/vendor/autoload.php';

$app = require '/var/www/salehbasahel/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$value = (string) config('admin.password');
$trimmed = trim($value, " \t\n\r\0\x0B\"'");

function shape(string $value): string
{
    if ($value === '') {
        return 'len=0 first=none last=none';
    }

    return 'len=' . strlen($value)
        . ' first=' . ord($value[0])
        . ' last=' . ord($value[strlen($value) - 1]);
}

echo 'raw ' . shape($value) . PHP_EOL;
echo 'trimmed ' . shape($trimmed) . PHP_EOL;
