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