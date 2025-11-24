 <?php
/**
 * Servidor SOAP para el Gestor Interno de Pacientes (GINPAC-SOAP)
 */

// Habilitar errores para debugging en desarrollo
ini_set('display_errors', 1);
error_reporting(E_ALL);

class PacienteSOAPService {
    private $xmlFile;
    
    public function __construct() {
        $this->xmlFile = __DIR__ . '/pacientes.xml';
        $this->initializeXML();
    }
    
    private function initializeXML() {
        if (!file_exists($this->xmlFile)) {
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><pacientes></pacientes>');
            $xml->asXML($this->xmlFile);
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
            
        } catch (Exception $e) {
            error_log("Error en crearPaciente: " . $e->getMessage());
            return false;
        }
    }
    
    public function buscarPaciente($cedula) {
        try {
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
            
        } catch (Exception $e) {
            error_log("Error en buscarPaciente: " . $e->getMessage());
            return null;
        }
    }
    
    public function listarPacientes() {
        try {
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
            
        } catch (Exception $e) {
            error_log("Error en listarPacientes: " . $e->getMessage());
            return [];
        }
    }
    
    public function actualizarPaciente($cedulaOriginal, $nuevaCedula, $nombres, $apellidos, $telefono, $fecha_nacimiento) {
        try {
            $xml = simplexml_load_file($this->xmlFile);
            $encontrado = false;
            
            foreach ($xml->paciente as $paciente) {
                if ((string)$paciente->cedula === $cedulaOriginal) {
                    // Actualizar todos los campos incluyendo cédula
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
            
        } catch (Exception $e) {
            error_log("Error en actualizarPaciente: " . $e->getMessage());
            return false;
        }
    }
    
    public function eliminarPaciente($cedula) {
        try {
            $xml = simplexml_load_file($this->xmlFile);
            $encontrado = false;
            
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
                return $xml->asXML($this->xmlFile);
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Error en eliminarPaciente: " . $e->getMessage());
            return false;
        }
    }
}

// Manejo de solicitudes SOAP
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['wsdl'])) {
    header('Content-Type: text/xml; charset=utf-8');
    $wsdlFile = __DIR__ . '/pacientes.wsdl';
    
    if (file_exists($wsdlFile)) {
        readfile($wsdlFile);
    } else {
        header('HTTP/1.1 404 Not Found');
        echo 'WSDL file not found';
    }
    exit;
    
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $wsdlFile = __DIR__ . '/pacientes.wsdl';
        
        if (!file_exists($wsdlFile)) {
            throw new Exception("WSDL file not found");
        }
        
        $options = [
            'uri' => 'http://' . $_SERVER['HTTP_HOST'] . '/soap-server.php',
            'location' => 'http://' . $_SERVER['HTTP_HOST'] . '/soap-server.php'
        ];
        
        $server = new SoapServer($wsdlFile, $options);
        $server->setClass('PacienteSOAPService');
        $server->handle();
        
    } catch (Exception $e) {
        error_log("SOAP Server Error: " . $e->getMessage());
        header('HTTP/1.1 500 Internal Server Error');
        echo 'SOAP Server Error: ' . $e->getMessage();
    }
    
} else {
    header('HTTP/1.1 405 Method Not Allowed');
    echo 'Method Not Allowed';
}
?>
