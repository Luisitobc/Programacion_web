<?php

class CambioDivisa
{
    public $valorDolares;

    const PESO_COLOMBIANO = 3184.71;
    const BOLIVARES = 887.48;
    const YUAN = 6.65;

    public function cambiar_peso($valor)
    {
        return $valor * self::PESO_COLOMBIANO;
    }

    public function cambiar_bolivares($valor)
    {
        return $valor * self::BOLIVARES;
    }

    public function cambiar_yuan($valor)
    {
        return $valor * self::YUAN;
    }
}

$valor = rand(1, 100);

$cambio = new CambioDivisa();

echo "Valor aleatorio en dólares: $" . $valor . "<br>";
echo "Pesos colombianos: " . $cambio->cambiar_peso($valor) . "<br>";
echo "Bolívares: " . $cambio->cambiar_bolivares($valor) . "<br>";
echo "Yuanes: " . $cambio->cambiar_yuan($valor) . "<br>";

?>