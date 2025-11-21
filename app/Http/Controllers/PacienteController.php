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
                    private function validateCedula($cedula)
{
    // Validar que la cédula solo contenga números y tenga entre 8-12 dígitos
    if (!preg_match('/^\d{8,12}$/', $cedula)) {
        return false;
    }
    return true;
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
                            if (isset($paciente->fecha_nacimiento)) {
                                $paciente->fecha_nacimiento = htmlspecialchars($fecha_nacimiento);
                            } else {
                                $paciente->addChild('fecha_nacimiento', htmlspecialchars($fecha_nacimiento));
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
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'telefono' => 'required|string|max:15',
            'fecha_nacimiento' => 'required|date'
        ]);
        
        try {
            $result = $this->soapClient->crearPaciente(
                $request->cedula,
                $request->nombres,
                $request->apellidos,
                $request->telefono,
                $request->fecha_nacimiento
            );
            
            if ($result) {
                return redirect()->route('pacientes.list')
                    ->with('success', 'Paciente registrado exitosamente.');
            } else {
                return back()->with('error', 'Error al registrar paciente. La cédula puede ya existir.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error de conexión: ' . $e->getMessage());
        }
    }
    
    public function list()
    {
        try {
            $pacientes = $this->soapClient->listarPacientes();
            return view('listar', compact('pacientes'));
        } catch (\Exception $e) {
            error_log('Error al listar pacientes: ' . $e->getMessage());
            return view('listar', ['pacientes' => []])
                ->with('error', 'Error al cargar pacientes: ' . $e->getMessage());
        }
    }
    
    public function edit($cedula)
    {
        try {
            $paciente = $this->soapClient->buscarPaciente($cedula);
            
            if (!$paciente) {
                return redirect()->route('pacientes.list')
                    ->with('error', 'Paciente no encontrado.');
            }
            
            return view('editar', compact('paciente'));
        } catch (\Exception $e) {
            error_log('Error al editar paciente: ' . $e->getMessage());
            return redirect()->route('pacientes.list')
                ->with('error', 'Error al cargar paciente: ' . $e->getMessage());
        }
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
}
?>
