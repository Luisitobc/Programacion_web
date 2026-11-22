<?php
/**
 * Proyecto: Calculadora (backend)
 * Archivo: calculadora/model/Operacion.php
 *
 * La clase Operacion guarda los dos operandos (campos privados con set y get)
 * y ofrece un método por cada operación aritmética.
 */
class Operacion
{
    // Campos
    private $num1 = 0;
    private $num2 = 0;

    // Constructores
    public function __construct($num1 = 0, $num2 = 0)
    {
        $this->num1 = $num1;
        $this->num2 = $num2;
    }

    // Métodos set y get
    public function getNum1()
    {
        return $this->num1;
    }

    public function setNum1($num1)
    {
        $this->num1 = $num1;
    }

    public function getNum2()
    {
        return $this->num2;
    }

    public function setNum2($num2)
    {
        $this->num2 = $num2;
    }

    // Operaciones
    public function suma()
    {
        return $this->num1 + $this->num2;
    }

    public function resta()
    {
        return $this->num1 - $this->num2;
    }

    public function multiplicacion()
    {
        return $this->num1 * $this->num2;
    }

    // Retorna null cuando el divisor es cero (el frontend muestra un aviso)
    public function division()
    {
        if ($this->num2 == 0) {
            return null;
        }
        return $this->num1 / $this->num2;
    }
}