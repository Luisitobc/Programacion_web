
<?php

class Rectangulo
{
    private $base = 0;
    private $altura = 0;

    // getters n setters
    public function setBase($base) {
        $this->base = $base;
    }

    public function setAltura($altura) {
        $this->altura = $altura;
    }

    public function getBase() {
        return $this->base;
    }

    public function getAltura() {
        return $this->altura;
    }

    // metodos
    public function obtenerArea() {
        return $this->base * $this->altura;
    }

    public function obtenerPerimetro() {
        return 2 * ($this->base + $this->altura);
    }
}

$rectangulo = new Rectangulo();

$rectangulo->setBase(5);
$rectangulo->setAltura(3);

echo "Base: " . $rectangulo->getBase() . "<br>";
echo "Altura: " . $rectangulo->getAltura() . "<br>";
echo "Area: " . $rectangulo->obtenerArea() . "<br>";
echo "Perimetro: " . $rectangulo->obtenerPerimetro();

?>