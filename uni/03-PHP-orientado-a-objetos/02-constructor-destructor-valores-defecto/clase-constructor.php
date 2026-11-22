<?php

class Pweb {
    private $nombre;
    private $calificacion;
    private $fechanacimiento;

    public function __construct($nombre, $calificacion, $fechanacimiento) {
        $this->nombre = $nombre;
        $this->calificacion = $calificacion;
        $this->fechanacimiento = $fechanacimiento;
    }

    public function __destruct() {
        echo "El objeto ha sido destruido: " . $this->nombre . "<br>";
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


$Alejandro = new Pweb("Alejandro", 9, "12-03-1947");
$Luis = new Pweb("Luis", 8, "25-12-1944");

unset($Luis);

echo "Nombre: " . $Alejandro->getNombre() . "<br>";
echo "Calificación: " . $Alejandro->getCalificacion() . "<br>";
echo "Fecha de nacimiento: " . $Alejandro->getFechanacimiento() . "<br>";

echo "<br>";

// echo "Nombre: " . $Luis->getNombre() . "<br>";
// echo "Calificación: " . $Luis->getCalificacion() . "<br>";
// echo "Fecha de nacimiento: " . $Luis->getFechanacimiento() . "<br>";