<?php
/**
 * Punto de entrada principal para Laragon
 * Este archivo simula el comportamiento de Laravel para el proyecto SOAP
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Simular el funcionamiento básico de Laravel para el proyecto
$request = Illuminate\Http\Request::capture();

// Manejar rutas básicas
$path = $request->path();

if ($path === 'soap-server.php') {
    require_once __DIR__ . '/../soap/SoapServer.php';
    exit;
}

// Para otras rutas, usar el sistema de rutas de Laravel
require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
?>