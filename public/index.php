<?php
/**
 * Punto de entrada principal para Laragon
 * Maneja tanto SOAP como rutas web de Laravel
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Capturar request UNA sola vez
$request = Illuminate\Http\Request::capture();
$path = $request->path();

// Manejar ruta SOAP específica
if ($path === 'soap-server.php' || $path === 'public/soap-server.php') {
    require_once __DIR__ . '/soap-server.php'; // Ruta CORREGIDA
    exit;
}

// Para todas las demás rutas, usar Laravel normal
require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request); // Usar la misma request
$response->send();
$kernel->terminate($request, $response);
?>
