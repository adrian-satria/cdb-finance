<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/admin/project/store', 'POST', [
    '_token' => csrf_token(),
    'kode_project' => 'ERR-TEST-2',
    'nama_project' => 'Test Error 2',
    'areas' => [],
]);

$response = $kernel->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
$response->send();
$kernel->terminate($request, $response);
