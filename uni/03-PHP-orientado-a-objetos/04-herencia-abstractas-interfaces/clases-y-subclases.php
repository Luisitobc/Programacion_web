<?php

class Arma {
    private $nombre;
    private $danio;
    private $rareza;
}

class RifleAsalto extends Arma {
    private $tipoMunicion;
    private $capacidadCargador;
    private $cadenciaDisparo;

    public function disparar() {
    }
}

class EstiloTresEspadasZoro extends Arma {
    private $cargasEmbestida;
    private $comboGolpes;
    private $tiempoReutilizacion;

    public function usarEmbestida() {
    }
}

?>
