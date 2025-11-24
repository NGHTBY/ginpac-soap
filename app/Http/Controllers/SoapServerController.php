<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SoapServerController extends Controller
{
    public function handle(Request $request)
    {
        // Limpia los buffers de salida (evita errores SOAP)
        while (ob_get_level()) ob_end_clean();

        $wsdl = public_path('pacientes.wsdl');

        if (!file_exists($wsdl)) {
            return response("WSDL no encontrado en: $wsdl", 404);
        }

        try {
            // Crea el servidor SOAP
            $server = new \SoapServer($wsdl, [
                'cache_wsdl' => WSDL_CACHE_NONE,
            ]);

            // Usar la clase del controlador directamente
            $server->setClass(\App\Http\Controllers\PacienteController::class);

            // Captura y devuelve la respuesta SOAP
            ob_start();
            $server->handle();
            $response = ob_get_clean();

            return response($response)->header('Content-Type', 'text/xml');
        } catch (\SoapFault $e) {
            return response("Error SOAP Server: " . $e->getMessage(), 500);
        }
    }
}
