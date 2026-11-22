<?php /** vistas/panel.php  -  READ + accesos a UPDATE y DELETE */ ?>

<section class="seccion">
    <div class="seccion__cabeza">
        <div>
            <h2>Administrar productos</h2>
            <p class="seccion__ayuda">Crear, modificar y eliminar registros de la tabla <code>producto</code>.</p>
        </div>
        <form class="filtros" method="get" action="index.php">
            <input type="hidden" name="accion" value="panel">
            <input type="search" name="q" value="<?= e($busqueda) ?>" placeholder="Buscar por nombre o código…">
            <button class="boton boton--principal" type="submit">Buscar</button>
            <a class="boton boton--linea" href="index.php?accion=nuevo">+ Nuevo</a>
        </form>
    </div>

    <div class="indicadores">
        <div class="indicador">
            <span>Productos registrados</span>
            <strong><?= (int) $resumen['total'] ?></strong>
        </div>
        <div class="indicador">
            <span>Unidades en stock</span>
            <strong><?= (int) $resumen['stock'] ?></strong>
        </div>
        <div class="indicador">
            <span>Valor del inventario</span>
            <strong><?= precio($resumen['valor']) ?></strong>
        </div>
        <div class="indicador">
            <span>Prendas agotadas</span>
            <strong><?= (int) $resumen['agotados'] ?></strong>
        </div>
    </div>

    <?php if ($productos === []): ?>
        <p class="vacio">No hay productos que coincidan con la búsqueda.</p>
    <?php else: ?>
    <div class="tabla__marco">
        <table class="tabla">
            <caption class="tabla__titulo"><?= count($productos) ?> registro(s) encontrados</caption>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Género</th>
                    <th>Tallas</th>
                    <th class="der">Precio</th>
                    <th class="cen">Stock</th>
                    <th class="cen">Estado</th>
                    <th class="cen">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($productos as $p): ?>
                <tr>
                    <td><code><?= e($p['codigo']) ?></code></td>
                    <td>
                        <strong><?= e($p['nombre']) ?></strong>
                        <small class="gris"><?= e($p['color']) ?></small>
                    </td>
                    <td><?= e($p['categoria']) ?></td>
                    <td><?= e($p['genero']) ?></td>
                    <td><small><?= e((string) ($p['tallas'] ?? '—')) ?></small></td>
                    <td class="der"><?= precio($p['precio']) ?></td>
                    <td class="cen <?= (int) $p['stock'] === 0 ? 'rojo' : '' ?>"><?= (int) $p['stock'] ?></td>
                    <td class="cen">
                        <?php if ((int) $p['activo'] === 1): ?>
                            <span class="etiqueta etiqueta--verde">Visible</span>
                        <?php else: ?>
                            <span class="etiqueta etiqueta--gris">Oculto</span>
                        <?php endif; ?>
                    </td>
                    <td class="cen">
                        <div class="acciones">
                            <a class="boton boton--pequeno" href="index.php?accion=editar&id=<?= (int) $p['id_producto'] ?>">Editar</a>

                            <!-- DELETE por POST para que no se ejecute desde un enlace -->
                            <form method="post" action="index.php?accion=eliminar"
                                  data-confirmar="¿Eliminar &quot;<?= e($p['nombre']) ?>&quot;? Esta acción no se puede deshacer.">
                                <input type="hidden" name="id_producto" value="<?= (int) $p['id_producto'] ?>">
                                <button class="boton boton--peligro boton--pequeno" type="submit">Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>
