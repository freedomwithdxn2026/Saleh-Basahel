<?php

$expected = trim(stream_get_contents(STDIN));

chdir('/var/www/salehbasahel');

require '/var/www/salehbasahel/vendor/autoload.php';
$app = require __DIR__ . '/../var/www/salehbasahel/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$actual = (string) config('admin.password');

echo ($expected === $actual ? 'match' : 'mismatch') . ' ' . strlen($expected) . ' ' . strlen($actual) . PHP_EOL;
