```php
<?php

abstract class Figura {
    abstract public function calcularArea();
    abstract public function calcularPerimetro();
}

class Cuadrado extends Figura {
    private $lado;

    public function __construct($lado) {
        $this->lado = $lado;
    }

    public function calcularArea() {
        return $this->lado * $this->lado;
    }

    public function calcularPerimetro() {
        return $this->lado * 4;
    }
}

class Circulo extends Figura {
    private $radio;

    public function __construct($radio) {
        $this->radio = $radio;
    }

    public function calcularArea() {
        return pi() * ($this->radio * $this->radio);
    }

    public function calcularPerimetro() {
        return 2 * pi() * $this->radio;
    }
}


$cuadrado = new Cuadrado(4);
echo "Cuadrado\n";
echo "Área: " . $cuadrado->calcularArea() . "\n";
echo "Perímetro: " . $cuadrado->calcularPerimetro() . "\n\n";

$circulo = new Circulo(3);
echo "Círculo\n";
echo "Área: " . round($circulo->calcularArea(), 2) . "\n";
echo "Perímetro: " . round($circulo->calcularPerimetro(), 2) . "\n";

?>



```