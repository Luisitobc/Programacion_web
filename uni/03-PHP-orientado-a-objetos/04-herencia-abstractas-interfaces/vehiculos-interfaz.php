<?php

// ---------- INTERFAZ ----------
interface IDisparar
{
    public function cargar();
    public function apuntar();
    public function disparar();
}

// ---------- CLASE PADRE ----------
class VehiculoMilitar
{
    public function avanzar(){}
}

// ---------- IMPLEMENTAN LA INTERFAZ ----------
class FusilGarand implements IDisparar
{
    public function cargar(){}
    public function apuntar(){}
    public function disparar(){}

    // Método propio
    public function expulsarCargador(){}
}

class FrancotiradorSpringfield implements IDisparar
{
    public function cargar(){}
    public function apuntar(){}
    public function disparar(){}

    // Métodos propios
    public function usarMirilla(){}
    public function montarTripode(){}
}

// ---------- HEREDA E IMPLEMENTA ----------
class TanqueSherman extends VehiculoMilitar implements IDisparar
{
    public function cargar(){}
    public function apuntar(){}
    public function disparar(){}

    // Métodos propios
    public function girarTorreta(){}
    public function lanzarHumo(){}
}

// ---------- PRUEBA ----------
$garand        = new FusilGarand();
$francotirador = new FrancotiradorSpringfield();
$tanque        = new TanqueSherman();

// Polimorfismo: todos son IDisparar
$armas = [$garand, $francotirador, $tanque];

foreach ($armas as $arma) {
    $arma->cargar();
    $arma->apuntar();
    $arma->disparar();
    echo "-----\n";
}

// Métodos propios de cada clase
$garand->expulsarCargador();
$francotirador->usarMirilla();
$francotirador->montarTripode();
$tanque->girarTorreta();
$tanque->lanzarHumo();

// Método heredado de VehiculoMilitar
echo $tanque->avanzar() . "\n";