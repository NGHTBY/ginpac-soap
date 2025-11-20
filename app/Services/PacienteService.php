<?php
namespace App\Services;

class PacienteService
{
    private $xmlFile;

    public function __construct()
    {
        $this->xmlFile = public_path('pacientes.xml');
    }

    public function registrarPaciente($cedula, $nombres, $apellidos, $telefono, $fechaNacimiento)
    {
        $xml = simplexml_load_file($this->xmlFile);
        foreach ($xml->paciente as $p) {
            if ((string)$p->cedula === $cedula) {
                return "Ya existe un paciente con esa cédula.";
            }
        }
        $paciente = $xml->addChild('paciente');
        $paciente->addChild('cedula', $cedula);
        $paciente->addChild('nombres', $nombres);
        $paciente->addChild('apellidos', $apellidos);
        $paciente->addChild('telefono', $telefono);
        $paciente->addChild('fechaNacimiento', $fechaNacimiento);
        $xml->asXML($this->xmlFile);
        return "Paciente registrado correctamente.";
    }

    public function listarPacientes()
    {
        $xml = simplexml_load_file($this->xmlFile);
        $data = [];
        foreach ($xml->paciente as $p) {
            $data[] = [
                'cedula' => (string)$p->cedula,
                'nombres' => (string)$p->nombres,
                'apellidos' => (string)$p->apellidos,
                'telefono' => (string)$p->telefono,
                'fechaNacimiento' => (string)$p->fechaNacimiento,
            ];
        }
        return $data;
    }

    public function buscarPaciente($cedula)
    {
        $xml = simplexml_load_file($this->xmlFile);
        foreach ($xml->paciente as $p) {
            if ((string)$p->cedula === $cedula) {
                return [
                    'cedula' => (string)$p->cedula,
                    'nombres' => (string)$p->nombres,
                    'apellidos' => (string)$p->apellidos,
                    'telefono' => (string)$p->telefono,
                    'fechaNacimiento' => (string)$p->fechaNacimiento,
                ];
            }
        }
        return null;
    }

    public function actualizarPaciente($cedula, $nombres, $apellidos, $telefono, $fechaNacimiento)
    {
        $xml = simplexml_load_file($this->xmlFile);
        foreach ($xml->paciente as $p) {
            if ((string)$p->cedula === $cedula) {
                $p->nombres = $nombres;
                $p->apellidos = $apellidos;
                $p->telefono = $telefono;
                $p->fechaNacimiento = $fechaNacimiento;
                $xml->asXML($this->xmlFile);
                return "Paciente actualizado correctamente.";
            }
        }
        return "Paciente no encontrado.";
    }

    public function eliminarPaciente($cedula)
    {
        $xml = simplexml_load_file($this->xmlFile);
        $i = 0;
        foreach ($xml->paciente as $p) {
            if ((string)$p->cedula === $cedula) {
                unset($xml->paciente[$i]);
                $xml->asXML($this->xmlFile);
                return "Paciente eliminado correctamente.";
            }
            $i++;
        }
        return "Paciente no encontrado.";
    }
}
