<?php /** vistas/catalogo.php  -  READ público: catálogo de 10 productos */ ?>

<section class="portada">
    <div class="portada__texto">
        <p class="portada__etiqueta">Temporada 2026</p>
        <h1>Ropa con identidad andina</h1>
        <p class="portada__bajada">
            Diez prendas seleccionadas: algodón peinado, mezclilla elástica, lana de oveja y
            fibra de alpaca. Todo el catálogo se administra desde esta misma aplicación.
        </p>
        <div class="portada__acciones">
            <a class="boton boton--principal" href="#catalogo">Ver catálogo</a>
            <a class="boton boton--linea" href="index.php?accion=panel">Administrar productos</a>
        </div>
    </div>
    <div class="portada__cifras">
        <div><strong><?= count($productos) ?></strong><span>prendas en línea</span></div>
        <div><strong><?= count($categorias) ?></strong><span>categorías</span></div>
        <div><strong>PDO</strong><span>acceso a datos</span></div>
    </div>
</section>

<section id="catalogo" class="seccion">
    <div class="seccion__cabeza">
        <div>
            <h2>Catálogo de productos</h2>
            <p class="seccion__ayuda">Filtre por categoría o escriba para buscar al instante.</p>
        </div>

        <form class="filtros" method="get" action="index.php">
            <input type="hidden" name="accion" value="catalogo">
            <input type="search" name="q" id="filtroVivo" value="<?= e($busqueda) ?>"
                   placeholder="Buscar prenda o código…" autocomplete="off">
            <select name="cat" onchange="this.form.submit()">
                <option value="">Todas las categorías</option>
                <?php foreach ($categorias as $c): ?>
                    <option value="<?= (int) $c['id_categoria'] ?>"
                        <?= $catActual === (int) $c['id_categoria'] ? 'selected' : '' ?>>
                        <?= e($c['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="boton boton--principal" type="submit">Buscar</button>
        </form>
    </div>

    <p class="contador" id="contador" hidden></p>

    <?php if ($productos === []): ?>
        <p class="vacio">No se encontraron productos con esos criterios.</p>
    <?php else: ?>
        <div class="rejilla" id="rejilla">
            <?php foreach ($productos as $p): ?>
                <article class="tarjeta"
                         data-nombre="<?= e(mb_strtolower($p['nombre'] . ' ' . $p['codigo'] . ' ' . $p['categoria'])) ?>">
                    <div class="tarjeta__imagen">
                        <img src="<?= e(rutaImagen($p['imagen'])) ?>" alt="<?= e($p['nombre']) ?>" loading="lazy">
                        <span class="insignia"><?= e($p['categoria']) ?></span>
                        <?php if ((int) $p['stock'] === 0): ?>
                            <span class="insignia insignia--agotado">Agotado</span>
                        <?php endif; ?>
                    </div>

                    <div class="tarjeta__cuerpo">
                        <p class="tarjeta__codigo"><?= e($p['codigo']) ?> &middot; <?= e($p['genero']) ?></p>
                        <h3><?= e($p['nombre']) ?></h3>
                        <p class="tarjeta__texto"><?= e($p['descripcion']) ?></p>

                        <?php if (!empty($p['tallas'])): ?>
                            <p class="tallas">
                                <?php foreach (explode(', ', (string) $p['tallas']) as $t): ?>
                                    <span class="talla"><?= e($t) ?></span>
                                <?php endforeach; ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="tarjeta__pie">
                        <div>
                            <span class="precio"><?= precio($p['precio']) ?></span>
                            <small class="stock">Stock: <?= (int) $p['stock'] ?></small>
                        </div>
                        <a class="boton boton--pequeno" href="index.php?accion=ver&id=<?= (int) $p['id_producto'] ?>">
                            Ver ficha
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
