<?php

class Pweb {
    private $nombre;
    private $calificacion;
    private $fechanacimiento;

    public function __construct($nombre = "Luis", $calificacion = 9, $fechanacimiento = "25-12-4") {
        $this->nombre = $nombre;
        $this->calificacion = $calificacion;
        $this->fechanacimiento = $fechanacimiento;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function getCalificacion() {
        return $this->calificacion;
    }

    public function setCalificacion($calificacion) {
        $this->calificacion = $calificacion;
    }

    public function getFechanacimiento() {
        return $this->fechanacimiento;
    }

    public function setFechanacimiento($fechanacimiento) {
        $this->fechanacimiento = $fechanacimiento;
    }
}

$Luis = new Pweb();

echo "Nombre: " . $Luis->getNombre() . "<br>";
echo "Calificación: " . $Luis->getCalificacion() . "<br>";
echo "Fecha de nacimiento: " . $Luis->getFechanacimiento() . "<br>";