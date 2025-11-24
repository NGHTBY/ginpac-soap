<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controlador principal para la gestión de pacientes
 * Archivo: app/Http/Controllers/PacienteController.php
 */
class PacienteController extends Controller
{
    private $soapClient;

    public function __construct()
    {
        // Usar el cliente directo que lee el XML directamente
        $this->soapClient = $this->createDirectClient();
    }

    /**
     * Cliente "directo" que interactúa con el XML como si fuera el servicio SOAP
     * Devuelve una clase anónima con métodos para CRUD sobre pacientes (XML).
     */
    private function createDirectClient()
    {
        return new class() {
            private $xmlFile;

            public function __construct()
            {
                $this->xmlFile = public_path('pacientes.xml');
                $this->initializeXML();
            }

            private function initializeXML()
            {
                if (!file_exists($this->xmlFile)) {
                    $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><pacientes></pacientes>');
                    $xml->asXML($this->xmlFile);
                }
            }

            public function crearPaciente($cedula, $nombres, $apellidos, $telefono, $fecha_nacimiento)
            {
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

            public function buscarPaciente($cedula)
            {
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

            public function listarPacientes()
            {
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

            public function actualizarPaciente($cedulaOriginal, $nuevaCedula, $nombres, $apellidos, $telefono, $fecha_nacimiento)
            {
                try {
                    if (!file_exists($this->xmlFile)) {
                        return false;
                    }

                    $xml = simplexml_load_file($this->xmlFile);
                    $encontrado = false;

                    foreach ($xml->paciente as $paciente) {
                        if ((string)$paciente->cedula === $cedulaOriginal) {

                            $paciente->cedula = htmlspecialchars($nuevaCedula);
                            $paciente->nombres = htmlspecialchars($nombres);
                            $paciente->apellidos = htmlspecialchars($apellidos);
                            $paciente->telefono = htmlspecialchars($telefono);

                            if (isset($paciente->fechaNacimiento)) {
                                unset($paciente->fechaNacimiento);
                            }

                            $paciente->fecha_nacimiento = htmlspecialchars($fecha_nacimiento);

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

            public function eliminarPaciente($cedula)
            {
                try {
                    if (!file_exists($this->xmlFile)) {
                        return false;
                    }

                    $xml = simplexml_load_file($this->xmlFile);
                    $encontrado = false;

                    // Buscar el paciente por cédula
                    foreach ($xml->paciente as $paciente) {
                        if ((string)$paciente->cedula === $cedula) {
                            // Método correcto para eliminar nodos en SimpleXML
                            $dom = dom_import_simplexml($paciente);
                            $dom->parentNode->removeChild($dom);
                            $encontrado = true;
                            break;
                        }
                    }

                    if ($encontrado) {
                        // Guardar el XML modificado
                        return $xml->asXML($this->xmlFile);
                    }

                    return false;
                    
                } catch (\Exception $e) {
                    error_log("Error al eliminar paciente: " . $e->getMessage());
                    return false;
                }
            }

            private function getFechaNacimiento($paciente)
            {
                if (isset($paciente->fecha_nacimiento) && !empty((string)$paciente->fecha_nacimiento)) {
                    return (string)$paciente->fecha_nacimiento;
                } elseif (isset($paciente->fechaNacimiento) && !empty((string)$paciente->fechaNacimiento)) {
                    return (string)$paciente->fechaNacimiento;
                } else {
                    return '';
                }
            }

            public function testConnection()
            {
                return true;
            }
        };
    }

    /* ========================= Vistas / CRUD (controlador) ========================= */

    /**
     * Página principal / índice
     */
    public function index()
    {
        return view('index');
    }

    /**
     * Vista principal de gestión de backups
     */
    public function backupsView()
    {
        return view('backups');
    }

    /**
     * Form para crear paciente
     */
    public function create()
    {
        return view('crear');
    }

    /**
     * Listar pacientes
     */
    public function list()
    {
        $pacientes = $this->soapClient->listarPacientes();
        return view('listar', compact('pacientes'));
    }

    /**
     * Form para editar paciente
     */
    public function edit($cedula)
    {
        $paciente = $this->soapClient->buscarPaciente($cedula);
        if (!$paciente) {
            return redirect()->route('pacientes.list')->with('error', 'Paciente no encontrado');
        }
        return view('editar', compact('paciente'));
    }

    /**
     * Guarda nuevo paciente (validaciones incluidas)
     */
    public function store(Request $request)
    {
        $request->validate([
            'cedula' => 'required|string|max:20',
            'nombres' => 'required|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'apellidos' => 'required|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'telefono' => 'required|string|max:15|regex:/^[0-9+\-\s()]+$/',
            'fecha_nacimiento' => 'required|date|before:today'
        ]);

        if (!$this->validateCedula($request->cedula)) {
            return back()->with('error', 'La cédula debe contener solo números y tener entre 8-12 dígitos.')
                        ->withInput();
        }

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

                $this->logActivity('Paciente creado exitosamente', "Cédula: {$request->cedula}, Nombre: {$request->nombres} {$request->apellidos}");

                return redirect()->route('pacientes.list')
                    ->with('success', 'Paciente registrado exitosamente.');
            } else {

                $this->logActivity('Intento de crear paciente con cédula duplicada', "Cédula: {$request->cedula}");

                return back()->with('error', 'Error al registrar paciente. La cédula ya existe en el sistema.')
                            ->withInput();
            }
        } catch (\Exception $e) {

            $this->logActivity('Excepción al crear paciente', "Error: " . $e->getMessage());

            return back()->with('error', 'Error de conexión con el servidor: ' . $e->getMessage())
                        ->withInput();
        }
    }

    /**
     * Valida formato de cédula - números solamente y longitud 8-12
     */
    private function validateCedula($cedula)
    {
        $cedulaLimpia = preg_replace('/[^0-9]/', '', $cedula);

        if (strlen($cedulaLimpia) < 8 || strlen($cedulaLimpia) > 12) {
            return false;
        }

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

        $logDir = storage_path('logs');
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }

        file_put_contents(storage_path('logs/ginpac_activity.log'), $logMessage . PHP_EOL, FILE_APPEND);
    }

    /**
     * Actualiza paciente (ruta PUT)
     */
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
            $nuevaCedula = $request->cedula;

            if ($nuevaCedula !== $cedula) {
                $pacienteExistente = $this->soapClient->buscarPaciente($nuevaCedula);
                if ($pacienteExistente) {
                    return back()->with('error', 'La cédula ' . $nuevaCedula . ' ya existe en el sistema.');
                }
            }

            $result = $this->soapClient->actualizarPaciente(
                $cedula,
                $nuevaCedula,
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

    /**
     * Elimina paciente (ruta DELETE)
     */
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
     * Exporta todos los pacientes a formato CSV (CORREGIDO para caracteres especiales)
     */
    public function exportPacientes()
    {
        try {
            $pacientes = $this->soapClient->listarPacientes();

            // Agregar BOM (Byte Order Mark) para UTF-8 en Excel
            $csvData = "\xEF\xBB\xBF"; // BOM para UTF-8
            $csvData .= "Cédula,Nombres,Apellidos,Teléfono,Fecha Nacimiento,Edad\n";

            foreach ($pacientes as $paciente) {
                // manejar caso sin fecha de nacimiento
                $fechaNacStr = $paciente['fecha_nacimiento'] ?? '';
                $edad = 0;
                if (!empty($fechaNacStr)) {
                    try {
                        $fechaNac = new \DateTime($fechaNacStr);
                        $hoy = new \DateTime();
                        $edad = $hoy->diff($fechaNac)->y;
                    } catch (\Exception $ex) {
                        $edad = 0;
                    }
                }

                $cedula = $this->sanitizeForCSV($paciente['cedula'] ?? '');
                $nombres = $this->sanitizeForCSV($paciente['nombres'] ?? '');
                $apellidos = $this->sanitizeForCSV($paciente['apellidos'] ?? '');
                $telefono = $this->sanitizeForCSV($paciente['telefono'] ?? '');
                $fechaNacimiento = $this->sanitizeForCSV($paciente['fecha_nacimiento'] ?? '');

                $csvData .= "\"{$cedula}\",\"{$nombres}\",\"{$apellidos}\",\"{$telefono}\",\"{$fechaNacimiento}\",\"{$edad} años\"\n";
            }

            $this->logActivity('Exportación CSV realizada', "Total de pacientes exportados: " . count($pacientes));

            return response($csvData)
                ->header('Content-Type', 'text/csv; charset=utf-8')
                ->header('Content-Disposition', 'attachment; filename="pacientes_clinica_' . date('Y-m-d_H-i') . '.csv"')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');

        } catch (\Exception $e) {
            $this->logActivity('Error en exportación CSV', "Error: " . $e->getMessage());

            return redirect()->route('pacientes.list')
                ->with('error', 'Error al exportar pacientes: ' . $e->getMessage());
        }
    }

    /* ========================= BLOQUE DE BACKUPS ========================= */

    /**
     * Realiza backup de los datos de pacientes
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function backupPacientes()
    {
        try {
            $pacientes = $this->soapClient->listarPacientes();
            $xmlPath = public_path('pacientes.xml');

            if (!file_exists($xmlPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo de datos pacientes no encontrado.'
                ], 404);
            }

            $xmlContent = file_get_contents($xmlPath);

            // Crear directorio de backups si no existe
            $backupDir = storage_path('backups');
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            // Nombre del archivo de backup con timestamp
            $backupFileName = 'pacientes_backup_' . date('Y-m-d_His') . '.xml';
            $backupFilePath = $backupDir . '/' . $backupFileName;

            // Crear backup
            if (file_put_contents($backupFilePath, $xmlContent) === false) {
                throw new \Exception('No se pudo escribir el archivo de backup');
            }

            // Crear archivo de metadatos del backup (append histórico)
            $metadataFile = $backupDir . '/backup_metadata.json';
            $metadata = [
                'fecha' => date('Y-m-d H:i:s'),
                'total_pacientes' => count($pacientes),
                'archivo' => $backupFileName,
                'tamaño' => filesize($backupFilePath)
            ];

            // Si existe metadata previa, cargarla y agregar el nuevo registro (histórico)
            $history = [];
            if (file_exists($metadataFile)) {
                $raw = file_get_contents($metadataFile);
                $history = json_decode($raw, true) ?? [];
            }
            $history[] = $metadata;
            file_put_contents($metadataFile, json_encode($history, JSON_PRETTY_PRINT));

            // Log de backup exitoso
            $this->logActivity('Backup realizado exitosamente', "Archivo: {$backupFileName}, Pacientes: " . count($pacientes));

            return response()->json([
                'success' => true,
                'message' => 'Backup realizado exitosamente',
                'backup_file' => $backupFileName,
                'total_pacientes' => count($pacientes),
                'fecha' => $metadata['fecha']
            ]);

        } catch (\Exception $e) {
            $this->logActivity('Error en backup', $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al realizar backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lista todos los backups disponibles (API JSON)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listarBackups()
    {
        try {
            $backupDir = storage_path('backups');
            $backups = [];

            if (file_exists($backupDir)) {
                $files = scandir($backupDir);

                foreach ($files as $file) {
                    if ($file === '.' || $file === '..') continue;
                    
                    if (pathinfo($file, PATHINFO_EXTENSION) === 'xml' && strpos($file, 'pacientes_backup_') === 0) {
                        $filePath = $backupDir . '/' . $file;
                        if (file_exists($filePath)) {
                            $backups[] = [
                                'nombre' => $file,
                                'fecha' => date('Y-m-d H:i:s', filemtime($filePath)),
                                'tamaño' => $this->formatBytes(filesize($filePath)),
                                'ruta' => $filePath
                            ];
                        }
                    }
                }

                // Ordenar por fecha (más reciente primero)
                usort($backups, function ($a, $b) {
                    return strtotime($b['fecha']) - strtotime($a['fecha']);
                });
            }

            return response()->json([
                'success' => true,
                'backups' => $backups,
                'total' => count($backups)
            ]);

        } catch (\Exception $e) {
            Log::error('Error al listar backups: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al listar backups: ' . $e->getMessage(),
                'backups' => [],
                'total' => 0
            ], 500);
        }
    }

    /**
     * Restaura un backup específico
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function restaurarBackup(Request $request)
    {
        try {
            $request->validate([
                'backup_file' => 'required|string'
            ]);

            $backupDir = storage_path('backups');
            $backupFile = $backupDir . '/' . $request->backup_file;

            if (!file_exists($backupFile)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo de backup no existe'
                ], 404);
            }

            // Leer el contenido del backup
            $backupContent = file_get_contents($backupFile);

            // Crear backup actual antes de restaurar
            $backupActual = public_path('pacientes.xml');
            if (file_exists($backupActual)) {
                $backupPreRestore = $backupDir . '/pre_restore_' . date('Y-m-d_His') . '.xml';
                copy($backupActual, $backupPreRestore);
            }

            // Restaurar el backup
            if (file_put_contents(public_path('pacientes.xml'), $backupContent) === false) {
                throw new \Exception('No se pudo escribir el archivo de pacientes');
            }

            // Log de restauración exitosa
            $this->logActivity('Backup restaurado exitosamente', "Archivo: {$request->backup_file}");

            return response()->json([
                'success' => true,
                'message' => 'Backup restaurado exitosamente',
                'archivo_restaurado' => $request->backup_file,
                'fecha_restauracion' => date('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            $this->logActivity('Error en restauración de backup', $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descarga un archivo de backup específico
     *
     * @param string $archivo
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
     */
    public function descargarBackup($archivo)
    {
        try {
            $backupDir = storage_path('backups');
            $backupFile = $backupDir . '/' . $archivo;

            // Validar que el archivo existe
            if (!file_exists($backupFile)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo de backup no existe'
                ], 404);
            }

            // Validar que es un archivo XML de backup (seguridad)
            if (pathinfo($archivo, PATHINFO_EXTENSION) !== 'xml' || strpos($archivo, 'pacientes_backup_') !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo no válido'
                ], 400);
            }

            // Log de descarga
            $this->logActivity('Backup descargado', "Archivo: {$archivo}");

            // Descargar el archivo
            return response()->download($backupFile, $archivo, [
                'Content-Type' => 'application/xml',
                'Content-Disposition' => 'attachment; filename="' . $archivo . '"'
            ]);

        } catch (\Exception $e) {
            $this->logActivity('Error al descargar backup', $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al descargar backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Formatea bytes a formato legible
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Realiza backup automático programado
     * Se puede llamar desde tareas cron
     */
    public function backupAutomatico()
    {
        try {
            $result = $this->backupPacientes();

            // Log de backup automático
            $this->logActivity('Backup automático ejecutado', 'Backup programado realizado exitosamente');

            return $result;

        } catch (\Exception $e) {
            $this->logActivity('Error en backup automático', $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error en backup automático: ' . $e->getMessage()
            ], 500);
        }
    }

    /* ========================= BLOQUE DE DASHBOARD Y ESTADÍSTICAS ========================= */

    /**
     * Muestra el dashboard con estadísticas del sistema
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        try {
            $pacientes = $this->soapClient->listarPacientes();

            $totalPacientes = count($pacientes);
            $estadisticasEdad = $this->calcularEstadisticasEdad($pacientes);
            $distribucionEdad = $this->calcularDistribucionEdad($pacientes);
            $pacientesEsteMes = $this->calcularPacientesEsteMes($pacientes);

            $this->logActivity('Acceso al dashboard', "Total pacientes: {$totalPacientes}");

            return view('dashboard', compact(
                'totalPacientes',
                'estadisticasEdad',
                'distribucionEdad',
                'pacientesEsteMes',
                'pacientes'
            ));

        } catch (\Exception $e) {
            $this->logActivity('Error en dashboard', $e->getMessage());
            return view('dashboard')->with('error', 'Error al cargar estadísticas: ' . $e->getMessage());
        }
    }

    /**
     * Calcula estadísticas de edad de los pacientes
     */
    private function calcularEstadisticasEdad($pacientes)
    {
        $edades = [];

        foreach ($pacientes as $paciente) {
            if (!empty($paciente['fecha_nacimiento'])) {
                try {
                    $fechaNac = new \DateTime($paciente['fecha_nacimiento']);
                    $hoy = new \DateTime();
                    $edad = $hoy->diff($fechaNac)->y;
                    $edades[] = $edad;
                } catch (\Exception $ex) {
                    // si la fecha es inválida, se ignora
                }
            }
        }

        if (empty($edades)) {
            return [
                'promedio' => 0,
                'minima' => 0,
                'maxima' => 0,
                'total' => 0
            ];
        }

        return [
            'promedio' => round(array_sum($edades) / count($edades), 1),
            'minima' => min($edades),
            'maxima' => max($edades),
            'total' => count($edades)
        ];
    }

    /**
     * Calcula distribución de pacientes por rangos de edad
     */
    private function calcularDistribucionEdad($pacientes)
    {
        $rangos = [
            '18-25' => 0,
            '26-35' => 0,
            '36-45' => 0,
            '46-55' => 0,
            '56-65' => 0,
            '65+' => 0
        ];

        foreach ($pacientes as $paciente) {
            if (!empty($paciente['fecha_nacimiento'])) {
                try {
                    $fechaNac = new \DateTime($paciente['fecha_nacimiento']);
                    $hoy = new \DateTime();
                    $edad = $hoy->diff($fechaNac)->y;

                    if ($edad >= 18 && $edad <= 25) $rangos['18-25']++;
                    elseif ($edad >= 26 && $edad <= 35) $rangos['26-35']++;
                    elseif ($edad >= 36 && $edad <= 45) $rangos['36-45']++;
                    elseif ($edad >= 46 && $edad <= 55) $rangos['46-55']++;
                    elseif ($edad >= 56 && $edad <= 65) $rangos['56-65']++;
                    elseif ($edad > 65) $rangos['65+']++;
                } catch (\Exception $ex) {
                    // ignorar fechas inválidas
                }
            }
        }

        return $rangos;
    }

    /**
     * Calcula pacientes registrados en el mes actual
     */
    private function calcularPacientesEsteMes($pacientes)
    {
        // Como el XML no tiene fecha real de registro, se simula
        return round(count($pacientes) * 0.3);
    }

    /**
     * Sanitiza datos para formato CSV (MEJORADO para caracteres especiales)
     */
    private function sanitizeForCSV($data)
    {
        if ($data === null) {
            return '';
        }

        // Convertir a UTF-8 si no lo está
        if (!mb_detect_encoding($data, 'UTF-8', true)) {
            $data = utf8_encode($data);
        }

        $data = str_replace('"', '""', $data);
        $data = str_replace(["\r", "\n"], ' ', $data);
        $data = trim(preg_replace('/\s+/', ' ', $data));

        return $data;
    }

} // cierre de la clase PacienteController
