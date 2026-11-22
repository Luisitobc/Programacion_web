<?php

class Carro {
    private $color;
    private $marca;
    private $modelo;

    public function __construct($marca, $modelo, $color = "Negro") {
        $this->color = $color;
        $this->marca = $marca;
        $this->modelo = $modelo;
    }

    public function __destruct() {
        echo "<br>";
        echo "El carro " . $this->marca . " " . $this->modelo . " ha sido destruido.<br>";
    }

    public function getColor() {
        return $this->color;
    }

    public function setColor($color) {
        $this->color = $color;
    }

    public function getMarca() {
        return $this->marca;
    }

    public function setMarca($marca) {
        $this->marca = $marca;
    }

    public function getModelo() {
        return $this->modelo;
    }

    public function setModelo($modelo) {
        $this->modelo = $modelo;
    }
}

$carro = new Carro("Ferrari", "488 GTB");

$carro->setColor("Verde");
echo "Color: " . $carro->getColor() . "<br>";
echo "Marca: " . $carro->getMarca() . "<br>";
echo "Modelo: " . $carro->getModelo() . "<br>";