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
public/
soapclient.php:
<?php
/**
 * Cliente SOAP para consumir los servicios del Gestor Interno de Pacientes
 * 
 * RA-04: Cliente (Frontend) - Aplicación cliente que consume el servicio SOAP
 * RA-02: Contrato (WSDL) - Utiliza el WSDL para conocer las operaciones disponibles
 * 
 * Esta clase encapsula todas las operaciones CRUD definidas en el WSDL
 * y proporciona una interfaz simple para el controlador de Laravel.
 */

class PacienteSOAPClient {
    private $client;
    
    /**
     * Constructor - Inicializa el cliente SOAP
     * 
     * RA-04: Se conecta al servicio SOAP usando el WSDL
     * RA-02: Carga el contrato WSDL para conocer las operaciones disponibles
     * 
     * @throws Exception Si no puede conectarse al servicio SOAP
     */
    public function __construct() {
        try {
            // Obtener la ruta base del proyecto
            $baseUrl = $this->getBaseUrl();
            
            // RA-02: Usar archivo WSDL local para definir el contrato
            $wsdlFile = __DIR__ . '/pacientes.wsdl';
            
            if (file_exists($wsdlFile)) {
                // RA-04: Crear cliente SOAP usando el WSDL local
                $this->client = new SoapClient(
                    $wsdlFile,
                    [
                        'location' => $baseUrl . '/soap-server.php',
                        'uri' => $baseUrl . '/soap-server.php',
                        'trace' => 1, // Para debugging
                        'exceptions' => true, // Lanzar excepciones en errores
                        'cache_wsdl' => WSDL_CACHE_NONE, // No cachear WSDL en desarrollo
                        'connection_timeout' => 15 // Timeout de conexión
                    ]
                );
            } else {
                throw new Exception("Archivo WSDL no encontrado: " . $wsdlFile);
            }
            
        } catch (SoapFault $e) {
            throw new Exception("No se pudo conectar al servicio SOAP: " . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Error: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener la URL base automáticamente según el entorno
     * 
     * RA-04: Determina la URL correcta para la conexión SOAP
     * 
     * @return string URL base del proyecto
     */
    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        
        // Determinar si estamos en localhost o en un dominio virtual
        if (strpos($host, 'ginpac-soap.test') !== false) {
            return $protocol . '://' . $host;
        } else {
            return $protocol . '://' . $host . '/ginpac-soap/public';
        }
    }
    
    /**
     * RF-01: Crear - Registra un nuevo paciente en el sistema
     * 
     * RA-04: Consume la operación crearPaciente del servicio SOAP
     * 
     * @param string $cedula Cédula del paciente
     * @param string $nombres Nombres del paciente
     * @param string $apellidos Apellidos del paciente
     * @param string $telefono Teléfono del paciente
     * @param string $fecha_nacimiento Fecha de nacimiento (YYYY-MM-DD)
     * @return bool True si se creó correctamente, False si hubo error
     */
    public function crearPaciente($cedula, $nombres, $apellidos, $telefono, $fecha_nacimiento) {
        try {
            return $this->client->crearPaciente($cedula, $nombres, $apellidos, $telefono, $fecha_nacimiento);
        } catch (SoapFault $e) {
            error_log("Error SOAP al crear paciente: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * RF-02: Leer/Buscar - Busca un paciente por su cédula
     * 
     * RA-04: Consume la operación buscarPaciente del servicio SOAP
     * 
     * @param string $cedula Cédula del paciente a buscar
     * @return array|null Datos del paciente o null si no se encuentra
     */
    public function buscarPaciente($cedula) {
        try {
            return $this->client->buscarPaciente($cedula);
        } catch (SoapFault $e) {
            error_log("Error SOAP al buscar paciente: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * RF-03: Leer/Listar - Obtiene todos los pacientes registrados
     * 
     * RA-04: Consume la operación listarPacientes del servicio SOAP
     * 
     * @return array Lista de todos los pacientes
     */
    public function listarPacientes() {
        try {
            $result = $this->client->listarPacientes();
            return is_array($result) ? $result : [];
        } catch (SoapFault $e) {
            error_log("Error SOAP al listar pacientes: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * RF-04: Actualizar - Modifica los datos de un paciente existente
     * 
     * RA-04: Consume la operación actualizarPaciente del servicio SOAP
     * 
     * @param string $cedula Cédula del paciente a actualizar
     * @param string $nombres Nuevos nombres
     * @param string $apellidos Nuevos apellidos
     * @param string $telefono Nuevo teléfono
     * @param string $fecha_nacimiento Nueva fecha de nacimiento
     * @return bool True si se actualizó correctamente, False si hubo error
     */
    public function actualizarPaciente($cedula, $nombres, $apellidos, $telefono, $fecha_nacimiento) {
        try {
            return $this->client->actualizarPaciente($cedula, $nombres, $apellidos, $telefono, $fecha_nacimiento);
        } catch (SoapFault $e) {
            error_log("Error SOAP al actualizar paciente: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * RF-05: Eliminar - Elimina un paciente del sistema
     * 
     * RA-04: Consume la operación eliminarPaciente del servicio SOAP
     * 
     * @param string $cedula Cédula del paciente a eliminar
     * @return bool True si se eliminó correctamente, False si hubo error
     */
    public function eliminarPaciente($cedula) {
        try {
            return $this->client->eliminarPaciente($cedula);
        } catch (SoapFault $e) {
            error_log("Error SOAP al eliminar paciente: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Método para probar la conexión con el servidor SOAP
     * 
     * RA-04: Verifica que el cliente pueda comunicarse con el servidor
     * 
     * @return bool True si la conexión es exitosa, False en caso contrario
     */
    public function testConnection() {
        try {
            $functions = $this->client->__getFunctions();
            return !empty($functions);
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
