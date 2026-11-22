<?php
/** vistas/formulario.php  -  CREATE y UPDATE comparten el mismo formulario */
$esEdicion = ($modo === 'editar');
$destino   = $esEdicion ? 'index.php?accion=actualizar' : 'index.php?accion=guardar';
?>

<section class="seccion seccion--angosta">
    <nav class="migas">
        <a href="index.php?accion=panel">Administrar</a> / <span><?= e($titulo) ?></span>
    </nav>

    <h2><?= e($titulo) ?></h2>
    <p class="seccion__ayuda">
        <?= $esEdicion
            ? 'Modifique los datos y guarde los cambios (UPDATE).'
            : 'Complete los datos de la nueva prenda (INSERT).' ?>
    </p>

    <?php if ($errores !== []): ?>
        <div class="aviso aviso--error">
            <div>
                <strong>Revise los siguientes campos:</strong>
                <ul class="lista-errores">
                    <?php foreach ($errores as $mensajeError): ?>
                        <li><?= e($mensajeError) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <form class="formulario" method="post" action="<?= $destino ?>" id="formProducto" novalidate>
        <?php if ($esEdicion): ?>
            <input type="hidden" name="id_producto" value="<?= (int) $producto['id_producto'] ?>">
        <?php endif; ?>

        <div class="campos">
            <div class="campo">
                <label for="codigo">Código <span class="req">*</span></label>
                <input type="text" id="codigo" name="codigo" maxlength="15" required
                       pattern="[A-Za-z]{3}-\d{3}" placeholder="ROP-011"
                       value="<?= e((string) $producto['codigo']) ?>"
                       <?= isset($errores['codigo']) ? 'aria-invalid="true"' : '' ?>>
                <small class="pista">Tres letras, guion y tres dígitos.</small>
                <?php if (isset($errores['codigo'])): ?><small class="error"><?= e($errores['codigo']) ?></small><?php endif; ?>
            </div>

            <div class="campo campo--ancho">
                <label for="nombre">Nombre de la prenda <span class="req">*</span></label>
                <input type="text" id="nombre" name="nombre" maxlength="100" required minlength="3"
                       value="<?= e((string) $producto['nombre']) ?>"
                       <?= isset($errores['nombre']) ? 'aria-invalid="true"' : '' ?>>
                <?php if (isset($errores['nombre'])): ?><small class="error"><?= e($errores['nombre']) ?></small><?php endif; ?>
            </div>

            <div class="campo">
                <label for="precio">Precio (USD) <span class="req">*</span></label>
                <input type="number" id="precio" name="precio" step="0.01" min="0.01" required
                       value="<?= e((string) $producto['precio']) ?>"
                       <?= isset($errores['precio']) ? 'aria-invalid="true"' : '' ?>>
                <?php if (isset($errores['precio'])): ?><small class="error"><?= e($errores['precio']) ?></small><?php endif; ?>
            </div>

            <div class="campo">
                <label for="stock">Stock</label>
                <input type="number" id="stock" name="stock" min="0" step="1"
                       value="<?= (int) $producto['stock'] ?>">
                <?php if (isset($errores['stock'])): ?><small class="error"><?= e($errores['stock']) ?></small><?php endif; ?>
            </div>

            <div class="campo">
                <label for="id_categoria">Categoría <span class="req">*</span></label>
                <select id="id_categoria" name="id_categoria" required>
                    <option value="">Seleccione…</option>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= (int) $c['id_categoria'] ?>"
                            <?= (int) $producto['id_categoria'] === (int) $c['id_categoria'] ? 'selected' : '' ?>>
                            <?= e($c['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errores['id_categoria'])): ?><small class="error"><?= e($errores['id_categoria']) ?></small><?php endif; ?>
            </div>

            <div class="campo">
                <label for="genero">Género</label>
                <select id="genero" name="genero">
                    <?php foreach (['Hombre', 'Mujer', 'Unisex', 'Niño'] as $g): ?>
                        <option value="<?= $g ?>" <?= $producto['genero'] === $g ? 'selected' : '' ?>><?= $g ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="campo">
                <label for="color">Color</label>
                <input type="text" id="color" name="color" maxlength="30"
                       value="<?= e((string) $producto['color']) ?>" placeholder="Azul">
            </div>

            <div class="campo">
                <label for="imagen">Imagen (archivo en /img)</label>
                <input type="text" id="imagen" name="imagen" maxlength="120"
                       value="<?= e((string) $producto['imagen']) ?>" placeholder="p11.svg">
                <small class="pista">Si se deja vacío se usa una imagen genérica.</small>
            </div>

            <div class="campo campo--completo">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="3" maxlength="400"
                          placeholder="Material, confección y detalles de la prenda."><?= e((string) $producto['descripcion']) ?></textarea>
                <small class="pista"><span id="contadorTexto">0</span>/400 caracteres</small>
                <?php if (isset($errores['descripcion'])): ?><small class="error"><?= e($errores['descripcion']) ?></small><?php endif; ?>
            </div>

            <div class="campo campo--completo">
                <span class="label">Tallas disponibles</span>
                <div class="casillas">
                    <?php foreach ($tallas as $t): ?>
                        <label class="casilla">
                            <input type="checkbox" name="tallas[]" value="<?= (int) $t['id_talla'] ?>"
                                <?= in_array((int) $t['id_talla'], $elegidas, true) ? 'checked' : '' ?>>
                            <span><?= e($t['etiqueta']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="campo campo--completo">
                <label class="casilla">
                    <input type="checkbox" name="activo" value="1"
                        <?= (int) $producto['activo'] === 1 ? 'checked' : '' ?>>
                    <span>Mostrar este producto en el catálogo público</span>
                </label>
            </div>
        </div>

        <div class="formulario__acciones">
            <button class="boton boton--principal" type="submit">
                <?= $esEdicion ? 'Guardar cambios' : 'Registrar producto' ?>
            </button>
            <a class="boton boton--linea" href="index.php?accion=panel">Cancelar</a>
        </div>
    </form>
</section>
