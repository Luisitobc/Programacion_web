<?php
/** vistas/detalle.php  -  READ de un solo registro */
$etiquetas = [];
foreach ($tallas as $t) {
    if (in_array((int) $t['id_talla'], $elegidas, true)) {
        $etiquetas[] = $t['etiqueta'];
    }
}
?>

<section class="seccion">
    <nav class="migas">
        <a href="index.php?accion=catalogo">Catálogo</a> / <span><?= e($producto['nombre']) ?></span>
    </nav>

    <div class="ficha">
        <div class="ficha__imagen">
            <img src="<?= e(rutaImagen($producto['imagen'])) ?>" alt="<?= e($producto['nombre']) ?>">
        </div>

        <div class="ficha__datos">
            <p class="tarjeta__codigo"><?= e($producto['codigo']) ?> &middot; <?= e($producto['categoria']) ?></p>
            <h2><?= e($producto['nombre']) ?></h2>
            <p class="precio precio--grande"><?= precio($producto['precio']) ?></p>
            <p class="ficha__texto"><?= e($producto['descripcion']) ?></p>

            <dl class="datos">
                <div><dt>Género</dt><dd><?= e($producto['genero']) ?></dd></div>
                <div><dt>Color</dt><dd><?= e($producto['color'] ?: '—') ?></dd></div>
                <div><dt>Stock</dt><dd><?= (int) $producto['stock'] ?> unidades</dd></div>
                <div><dt>Tallas</dt><dd><?= $etiquetas === [] ? '—' : e(implode(', ', $etiquetas)) ?></dd></div>
                <div><dt>Registrado</dt><dd><?= e(date('d/m/Y', strtotime((string) $producto['fecha_registro']))) ?></dd></div>
                <div><dt>Estado</dt><dd><?= (int) $producto['activo'] === 1 ? 'Visible' : 'Oculto' ?></dd></div>
            </dl>

            <div class="formulario__acciones">
                <a class="boton boton--principal" href="index.php?accion=editar&id=<?= (int) $producto['id_producto'] ?>">Editar</a>
                <a class="boton boton--linea" href="index.php?accion=catalogo">Volver al catálogo</a>
            </div>
        </div>
    </div>
</section>
