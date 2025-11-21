<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Controlador principal para la gestión de pacientes
 */
class PacienteController extends Controller
{
    private $soapClient;
    
    public function __construct()
    {
        // Usar el cliente directo que lee el XML directamente
        $this->soapClient = $this->createDirectClient();
    }
    
    private function createDirectClient()
    {
        return new class() {
            private $xmlFile;
            
            public function __construct() {
                $this->xmlFile = public_path('pacientes.xml');
                $this->initializeXML();
            }
            
            private function initializeXML() {
                if (!file_exists($this->xmlFile)) {
                    $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><pacientes></pacientes>');
                    $xml->asXML($this->xmlFile);
                }
            }
            
            public function crearPaciente($cedula, $nombres, $apellidos, $telefono, $fecha_nacimiento) {
                try {
                    $xml = simplexml_load_file($this->xmlFile);
                    
                    foreach ($xml->paciente as $paciente) {
                        if ((string)$paciente->cedula === $cedula) {
                            return false;
                        }
                    }
                    
                    $nuevoPaciente = $xml->addChild('paciente');
                    $nuevoPaciente->addChild('cedula', htmlspecialchars($cedula));
                    $nuevoPaciente->addChild('nombres', htmlspecialchars($nombres));
                    $nuevoPaciente->addChild('apellidos', htmlspecialchars($apellidos));
                    $nuevoPaciente->addChild('telefono', htmlspecialchars($telefono));
                    $nuevoPaciente->addChild('fecha_nacimiento', htmlspecialchars($fecha_nacimiento));
                    
                    return $xml->asXML($this->xmlFile);
                } catch (\Exception $e) {
                    error_log("Error al crear paciente: " . $e->getMessage());
                    return false;
                }
            }
            
            public function buscarPaciente($cedula) {
                try {
                    if (!file_exists($this->xmlFile)) {
                        return null;
                    }
                    
                    $xml = simplexml_load_file($this->xmlFile);
                    
                    foreach ($xml->paciente as $paciente) {
                        if ((string)$paciente->cedula === $cedula) {
                            return [
                                'cedula' => (string)$paciente->cedula,
                                'nombres' => (string)$paciente->nombres,
                                'apellidos' => (string)$paciente->apellidos,
                                'telefono' => (string)$paciente->telefono,
                                'fecha_nacimiento' => $this->getFechaNacimiento($paciente)
                            ];
                        }
                    }
                    
                    return null;
                } catch (\Exception $e) {
                    error_log("Error al buscar paciente: " . $e->getMessage());
                    return null;
                }
            }
            
            public function listarPacientes() {
    try {
        if (!file_exists($this->xmlFile)) {
            return [];
        }

        $xml = simplexml_load_file($this->xmlFile);
        $pacientes = [];

        foreach ($xml->paciente as $paciente) {
            $pacientes[] = [
                'cedula' => (string)$paciente->cedula,
                'nombres' => (string)$paciente->nombres,
                'apellidos' => (string)$paciente->apellidos,
                'telefono' => (string)$paciente->telefono,
                'fecha_nacimiento' => $this->getFechaNacimiento($paciente)
            ];
        }

        return $pacientes;
        
    } catch (\Exception $e) {
        error_log("Error al listar pacientes: " . $e->getMessage());
        return [];
    }
}
           
            
            public function actualizarPaciente($cedulaOriginal, $nuevaCedula, $nombres, $apellidos, $telefono, $fecha_nacimiento) {
                try {
                    if (!file_exists($this->xmlFile)) {
                        return false;
                    }
                    
                    $xml = simplexml_load_file($this->xmlFile);
                    $encontrado = false;
                    
                    foreach ($xml->paciente as $paciente) {
                        if ((string)$paciente->cedula === $cedulaOriginal) {
                            // Actualizar la cédula si cambió
                            $paciente->cedula = htmlspecialchars($nuevaCedula);
                            $paciente->nombres = htmlspecialchars($nombres);
                            $paciente->apellidos = htmlspecialchars($apellidos);
                            $paciente->telefono = htmlspecialchars($telefono);
                            
                            // Actualizar fecha_nacimiento
                            if (isset($paciente->fechaNacimiento)) {
                                unset($paciente->fechaNacimiento);
                            }
                           if (isset($paciente->fechaNacimiento)) {
    unset($paciente->fechaNacimiento);
}

$paciente->fecha_nacimiento = htmlspecialchars($fecha_nacimiento);
                            }
                            
                            $encontrado = true;
                            break;
                        }
                    }
                    
                    if ($encontrado) {
                        return $xml->asXML($this->xmlFile);
                    }
                    
                    return false;
                } catch (\Exception $e) {
                    error_log("Error al actualizar paciente: " . $e->getMessage());
                    return false;
                }
            }
            
            public function eliminarPaciente($cedula) {
                try {
                    if (!file_exists($this->xmlFile)) {
                        return false;
                    }
                    
                    $xml = simplexml_load_file($this->xmlFile);
                    $encontrado = false;
                    $i = 0;
                    
                    foreach ($xml->paciente as $paciente) {
                        if ((string)$paciente->cedula === $cedula) {
                            unset($xml->paciente[$i]);
                            $encontrado = true;
                            break;
                        }
                        $i++;
                    }
                    
                    if ($encontrado) {
                        return $xml->asXML($this->xmlFile);
                    }
                    
                    return false;
                } catch (\Exception $e) {
                    error_log("Error al eliminar paciente: " . $e->getMessage());
                    return false;
                }
            }
            
            /**
             * Método auxiliar para obtener la fecha de nacimiento
             * Maneja ambos nombres de campo: fechaNacimiento y fecha_nacimiento
             */
            private function getFechaNacimiento($paciente) {
                if (isset($paciente->fecha_nacimiento) && !empty((string)$paciente->fecha_nacimiento)) {
                    return (string)$paciente->fecha_nacimiento;
                } elseif (isset($paciente->fechaNacimiento) && !empty((string)$paciente->fechaNacimiento)) {
                    return (string)$paciente->fechaNacimiento;
                } else {
                    return '';
                }
            }
            
            public function testConnection() {
                return true;
            }
        };
    }
    
    public function index()
    {
        return view('index');
    }
    
    public function create()
    {
        return view('crear');
    }
    
    public function store(Request $request)
{
    $request->validate([
        'cedula' => 'required|string|max:20',
        'nombres' => 'required|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
        'apellidos' => 'required|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
        'telefono' => 'required|string|max:15|regex:/^[0-9+\-\s()]+$/',
        'fecha_nacimiento' => 'required|date|before:today'
    ]);

    // Validación personalizada de cédula
    if (!$this->validateCedula($request->cedula)) {
        return back()->with('error', 'La cédula debe contener solo números y tener entre 8-12 dígitos.')
                    ->withInput();
    }

    // Validar que el paciente no sea menor de 18 años
    $fechaNacimiento = new \DateTime($request->fecha_nacimiento);
    $hoy = new \DateTime();
    $edad = $hoy->diff($fechaNacimiento)->y;
    
    if ($edad < 18) {
        return back()->with('error', 'El paciente debe ser mayor de 18 años.')
                    ->withInput();
    }

    try {
        $result = $this->soapClient->crearPaciente(
            $request->cedula,
            trim($request->nombres),
            trim($request->apellidos),
            $request->telefono,
            $request->fecha_nacimiento
        );
        
        if ($result) {
            // Log de actividad exitosa
            $this->logActivity('Paciente creado exitosamente', "Cédula: {$request->cedula}, Nombre: {$request->nombres} {$request->apellidos}");
            
            return redirect()->route('pacientes.list')
                ->with('success', 'Paciente registrado exitosamente.');
        } else {
            // Log de error por cédula duplicada
            $this->logActivity('Intento de crear paciente con cédula duplicada', "Cédula: {$request->cedula}");
            
            return back()->with('error', 'Error al registrar paciente. La cédula ya existe en el sistema.')
                        ->withInput();
        }
    } catch (\Exception $e) {
        // Log de excepción
        $this->logActivity('Excepción al crear paciente', "Error: " . $e->getMessage());
        
        return back()->with('error', 'Error de conexión con el servidor: ' . $e->getMessage())
                    ->withInput();
    }
}

/**
 * Valida el formato de la cédula
 * - Solo números
 * - Entre 8 y 12 dígitos
 */
private function validateCedula($cedula)
{
    // Eliminar espacios y caracteres especiales
    $cedulaLimpia = preg_replace('/[^0-9]/', '', $cedula);
    
    // Validar longitud
    if (strlen($cedulaLimpia) < 8 || strlen($cedulaLimpia) > 12) {
        return false;
    }
    
    // Validar que solo contenga números
    if (!ctype_digit($cedulaLimpia)) {
        return false;
    }
    
    return true;
}

/**
 * Registra actividades en el log del sistema
 */
private function logActivity($action, $details = '')
{
    $logMessage = date('Y-m-d H:i:s') . " - IP: " . request()->ip() . " - {$action}";
    if ($details) {
        $logMessage .= " - Detalles: {$details}";
    }
    
    // Crear directorio de logs si no existe
    $logDir = storage_path('logs');
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents(storage_path('logs/ginpac_activity.log'), $logMessage . PHP_EOL, FILE_APPEND);
}
    
    public function update(Request $request, $cedula)
    {
        $request->validate([
            'cedula' => 'required|string|max:20',
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'telefono' => 'required|string|max:15',
            'fecha_nacimiento' => 'required|date'
        ]);

        try {
            // Verificar si la cédula está cambiando
            $nuevaCedula = $request->cedula;
            
            // Si la cédula cambió, verificar que no exista otra igual
            if ($nuevaCedula !== $cedula) {
                $pacienteExistente = $this->soapClient->buscarPaciente($nuevaCedula);
                if ($pacienteExistente) {
                    return back()->with('error', 'La cédula ' . $nuevaCedula . ' ya existe en el sistema.');
                }
            }
            
            // Llamada al servicio SOAP para actualizar paciente
            $result = $this->soapClient->actualizarPaciente(
                $cedula, // Cédula original para buscar
                $nuevaCedula, // Nueva cédula
                $request->nombres,
                $request->apellidos,
                $request->telefono,
                $request->fecha_nacimiento
            );
            
            if ($result) {
                return redirect()->route('pacientes.list')
                    ->with('success', 'Paciente actualizado exitosamente.');
            } else {
                return back()->with('error', 'Error al actualizar paciente.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error de conexión: ' . $e->getMessage());
        }
    }
    
    public function destroy($cedula)
    {
        try {
            $result = $this->soapClient->eliminarPaciente($cedula);
            
            if ($result) {
                return redirect()->route('pacientes.list')
                    ->with('success', 'Paciente eliminado exitosamente.');
            } else {
                return redirect()->route('pacientes.list')
                    ->with('error', 'Error al eliminar paciente.');
            }
        } catch (\Exception $e) {
            error_log('Error al eliminar paciente: ' . $e->getMessage());
            return redirect()->route('pacientes.list')
                ->with('error', 'Error de conexión: ' . $e->getMessage());
        }
    }

    /**
 * Exporta todos los pacientes a formato CSV
 * 
 * @return \Illuminate\Http\Response
 */
public function exportPacientes()
{
    try {
        $pacientes = $this->soapClient->listarPacientes();
        
        // Cabeceras del CSV
        $csvData = "Cédula,Nombres,Apellidos,Teléfono,Fecha Nacimiento,Edad\n";
        
        foreach ($pacientes as $paciente) {
            // Calcular edad
            $fechaNac = new \DateTime($paciente['fecha_nacimiento']);
            $hoy = new \DateTime();
            $edad = $hoy->diff($fechaNac)->y;
            
            // Sanitizar datos para CSV
            $cedula = $this->sanitizeForCSV($paciente['cedula']);
            $nombres = $this->sanitizeForCSV($paciente['nombres']);
            $apellidos = $this->sanitizeForCSV($paciente['apellidos']);
            $telefono = $this->sanitizeForCSV($paciente['telefono']);
            $fechaNacimiento = $this->sanitizeForCSV($paciente['fecha_nacimiento']);
            
            $csvData .= "\"{$cedula}\",\"{$nombres}\",\"{$apellidos}\",\"{$telefono}\",\"{$fechaNacimiento}\",\"{$edad} años\"\n";
        }
        
        // Log de exportación exitosa
        $this->logActivity('Exportación CSV realizada', "Total de pacientes exportados: " . count($pacientes));
        
        // Devolver archivo CSV
        return response($csvData)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="pacientes_clinica_' . date('Y-m-d_H-i') . '.csv"')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
            
    } catch (\Exception $e) {
        // Log de error en exportación
        $this->logActivity('Error en exportación CSV', "Error: " . $e->getMessage());
        
        return redirect()->route('pacientes.list')
            ->with('error', 'Error al exportar pacientes: ' . $e->getMessage());
    }
}

/**
 * Sanitiza datos para formato CSV
 * Escapa comillas y caracteres especiales
 */
private function sanitizeForCSV($data)
{
    if ($data === null) {
        return '';
    }
    
    // Escapar comillas dobles duplicándolas
    $data = str_replace('"', '""', $data);
    
    // Eliminar saltos de línea y retornos de carro
    $data = str_replace(["\r", "\n"], ' ', $data);
    
    // Limpiar espacios en blanco excesivos
    $data = trim(preg_replace('/\s+/', ' ', $data));
    
    return $data;
}

/**
 * Registra actividades en el log del sistema con más detalles
 */
private function logActivity($action, $details = '')
{
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido';
    $method = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
    $uri = $_SERVER['REQUEST_URI'] ?? 'N/A';
    
    $logMessage = sprintf(
        "[%s] IP: %s | Method: %s | URI: %s | Action: %s | Details: %s | User-Agent: %s",
        date('Y-m-d H:i:s'),
        request()->ip(),
        $method,
        $uri,
        $action,
        $details,
        substr($userAgent, 0, 100) // Limitar longitud del user agent
    );
    
    // Crear directorio de logs si no existe
    $logDir = storage_path('logs');
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/ginpac_activity.log';
    
    // Rotación de logs: si el archivo es mayor a 10MB, crear uno nuevo
    if (file_exists($logFile) && filesize($logFile) > 10 * 1024 * 1024) {
        $backupFile = $logDir . '/ginpac_activity_' . date('Y-m-d_His') . '.log';
        rename($logFile, $backupFile);
    }
    
    file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND);
}
}
?>
