<?php

use App\Http\Controllers\PacienteController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas para el sistema GINPAC-SOAP
 * 
 * RF-06: Navegación y Vista Principal - Define el flujo de vistas
 * RA-04: Cliente (Frontend) - Rutas que consumen el servicio SOAP
 */

// RF-06: Vista Principal - Página de inicio del sistema
Route::get('/', [PacienteController::class, 'index'])->name('pacientes.index');

// RF-07: Flujo de Creación - Rutas para registrar pacientes
Route::get('/crear', [PacienteController::class, 'create'])->name('pacientes.create');
Route::post('/crear', [PacienteController::class, 'store'])->name('pacientes.store');

// RF-08: Flujo de Listado - Ruta para ver todos los pacientes
Route::get('/listar', [PacienteController::class, 'list'])->name('pacientes.list');

// RF-09: Flujo de Edición - Rutas para editar pacientes
Route::get('/editar/{cedula}', [PacienteController::class, 'edit'])->name('pacientes.edit');
Route::put('/editar/{cedula}', [PacienteController::class, 'update'])->name('pacientes.update');

// RF-10: Flujo de Eliminación - Ruta para eliminar pacientes
Route::delete('/eliminar/{cedula}', [PacienteController::class, 'destroy'])->name('pacientes.destroy');

// Ruta directa al servidor SOAP (para testing y WSDL)
Route::get('/soap-server', function() {
    require_once public_path('soap-server.php');
});

// Ruta para ver el WSDL directamente
Route::get('/wsdl', function() {
    $wsdlFile = public_path('pacientes.wsdl');
    if (file_exists($wsdlFile)) {
        return response(file_get_contents($wsdlFile), 200)
            ->header('Content-Type', 'text/xml');
    }
    abort(404, 'WSDL no encontrado');  
});

Route::get('/exportar-pacientes', [PacienteController::class, 'exportPacientes'])
    ->name('pacientes.export');
?>
