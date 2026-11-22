<?php
/**
 * index.php  -  Punto de entrada único de la aplicación
 * ---------------------------------------------------------------
 * Tienda de ropa "Estilo Andino"
 * Práctica: CRUD con PHP + PDO (MySQL)
 *
 * Flujo:  index.php?accion=...  ->  ProductoControlador  ->  modelo  ->  vista
 * ---------------------------------------------------------------
 */
declare(strict_types=1);

session_start();

// Mostrar errores solo en desarrollo
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config/conexion.php';
require_once __DIR__ . '/config/utilidades.php';
require_once __DIR__ . '/modelo/ProductoModelo.php';
require_once __DIR__ . '/modelo/CatalogoModelo.php';
require_once __DIR__ . '/controlador/ProductoControlador.php';

$pdo         = obtenerConexion();
$controlador = new ProductoControlador($pdo);

$accion = (string) ($_GET['accion'] ?? 'catalogo');
$controlador->manejar($accion);
