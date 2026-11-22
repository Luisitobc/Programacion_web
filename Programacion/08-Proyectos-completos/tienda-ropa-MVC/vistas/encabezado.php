<?php
/** vistas/encabezado.php  -  ENCABEZADO común a todas las páginas */
$accionActual = $_GET['accion'] ?? 'catalogo';
$flash        = obtenerMensaje();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo) ?> | Estilo Andino</title>
    <meta name="description" content="Tienda de ropa Estilo Andino: catálogo de prendas y administración de productos.">
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>

<!-- ============================ ENCABEZADO ============================ -->
<header class="cabecera">
    <div class="cabecera__barra contenedor">
        <a class="marca" href="index.php?accion=catalogo">
            <span class="marca__icono" aria-hidden="true">
                <svg viewBox="0 0 32 32" width="26" height="26">
                    <path d="M12 4h8l6 5-4 4v15H10V13l-4-4 6-5Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                    <path d="M12 4a4 4 0 0 0 8 0" fill="none" stroke="currentColor" stroke-width="2"/>
                </svg>
            </span>
            <span class="marca__texto">
                <strong>Estilo Andino</strong>
                <small>Tienda de ropa &middot; Guaranda</small>
            </span>
        </a>

        <nav class="menu" aria-label="Navegación principal">
            <a class="menu__enlace <?= $accionActual === 'catalogo' ? 'activo' : '' ?>"
               href="index.php?accion=catalogo">Catálogo</a>
            <a class="menu__enlace <?= in_array($accionActual, ['panel', 'editar'], true) ? 'activo' : '' ?>"
               href="index.php?accion=panel">Administrar</a>
            <a class="menu__boton <?= $accionActual === 'nuevo' ? 'activo' : '' ?>"
               href="index.php?accion=nuevo">+ Nuevo producto</a>
        </nav>
    </div>
</header>

<!-- ============================== CUERPO ============================== -->
<main class="cuerpo contenedor">

<?php if ($flash !== null): ?>
    <div class="aviso aviso--<?= e($flash['tipo']) ?>" role="status" data-autocerrar>
        <span><?= e($flash['texto']) ?></span>
        <button type="button" class="aviso__cerrar" aria-label="Cerrar aviso">&times;</button>
    </div>
<?php endif; ?>
