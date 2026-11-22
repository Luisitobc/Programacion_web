<?php
/**
 * controlador/ProductoControlador.php
 * ---------------------------------------------------------------
 * Recibe la acción solicitada, valida los datos del formulario,
 * llama al modelo y decide qué vista se muestra.
 * ---------------------------------------------------------------
 */
declare(strict_types=1);

class ProductoControlador
{
    private ProductoModelo $productos;
    private CatalogoModelo $catalogo;

    public function __construct(PDO $pdo)
    {
        $this->productos = new ProductoModelo($pdo);
        $this->catalogo  = new CatalogoModelo($pdo);
    }

    /** Enrutador simple: index.php?accion=... */
    public function manejar(string $accion): void
    {
        match ($accion) {
            'catalogo'   => $this->catalogo(),
            'panel'      => $this->panel(),
            'nuevo'      => $this->nuevo(),
            'editar'     => $this->editar(),
            'guardar'    => $this->guardar(),
            'actualizar' => $this->actualizar(),
            'eliminar'   => $this->eliminar(),
            'ver'        => $this->ver(),
            default      => $this->catalogo(),
        };
    }

    // ---------------------------------------------------------------
    //  READ
    // ---------------------------------------------------------------

    /** Catálogo público: los 10 productos en tarjetas. */
    private function catalogo(): void
    {
        $busqueda    = trim((string) ($_GET['q'] ?? ''));
        $idCategoria = isset($_GET['cat']) && $_GET['cat'] !== '' ? (int) $_GET['cat'] : null;

        $this->render('catalogo', [
            'titulo'     => 'Catálogo',
            'productos'  => $this->productos->listar($busqueda, $idCategoria, true),
            'categorias' => $this->catalogo->categorias(),
            'busqueda'   => $busqueda,
            'catActual'  => $idCategoria,
        ]);
    }

    /** Panel de administración: tabla con acciones de editar y eliminar. */
    private function panel(): void
    {
        $busqueda = trim((string) ($_GET['q'] ?? ''));

        $this->render('panel', [
            'titulo'    => 'Administrar productos',
            'productos' => $this->productos->listar($busqueda),
            'resumen'   => $this->productos->resumen(),
            'busqueda'  => $busqueda,
        ]);
    }

    /** Ficha de un solo producto. */
    private function ver(): void
    {
        $id       = (int) ($_GET['id'] ?? 0);
        $producto = $this->productos->obtener($id);

        if ($producto === null) {
            mensaje('error', 'El producto solicitado no existe.');
            redirigir('catalogo');
        }

        $this->render('detalle', [
            'titulo'   => $producto['nombre'],
            'producto' => $producto,
            'tallas'   => $this->catalogo->tallas(),
            'elegidas' => $this->productos->tallasDe($id),
        ]);
    }

    // ---------------------------------------------------------------
    //  CREATE
    // ---------------------------------------------------------------

    /** Muestra el formulario vacío. */
    private function nuevo(): void
    {
        $this->render('formulario', [
            'titulo'     => 'Nuevo producto',
            'modo'       => 'crear',
            'producto'   => $this->productoVacio(),
            'elegidas'   => [],
            'categorias' => $this->catalogo->categorias(),
            'tallas'     => $this->catalogo->tallas(),
            'errores'    => [],
        ]);
    }

    /** Procesa el POST del formulario de creación. */
    private function guardar(): void
    {
        $datos   = $this->leerFormulario();
        $tallas  = array_map('intval', (array) ($_POST['tallas'] ?? []));
        $errores = $this->validar($datos);

        if ($errores !== []) {
            $this->render('formulario', [
                'titulo'     => 'Nuevo producto',
                'modo'       => 'crear',
                'producto'   => $datos,
                'elegidas'   => $tallas,
                'categorias' => $this->catalogo->categorias(),
                'tallas'     => $this->catalogo->tallas(),
                'errores'    => $errores,
            ]);
            return;
        }

        $id = $this->productos->crear($datos, $tallas);

        if ($id > 0) {
            mensaje('exito', 'Producto "' . $datos['nombre'] . '" registrado correctamente.');
        } else {
            mensaje('error', 'No se pudo registrar el producto. Revise el log del servidor.');
        }
        redirigir('panel');
    }

    // ---------------------------------------------------------------
    //  UPDATE
    // ---------------------------------------------------------------

    /** Muestra el formulario con los datos actuales del producto. */
    private function editar(): void
    {
        $id       = (int) ($_GET['id'] ?? 0);
        $producto = $this->productos->obtener($id);

        if ($producto === null) {
            mensaje('error', 'No existe el producto que intenta editar.');
            redirigir('panel');
        }

        $this->render('formulario', [
            'titulo'     => 'Editar producto',
            'modo'       => 'editar',
            'producto'   => $producto,
            'elegidas'   => $this->productos->tallasDe($id),
            'categorias' => $this->catalogo->categorias(),
            'tallas'     => $this->catalogo->tallas(),
            'errores'    => [],
        ]);
    }

    /** Procesa el POST del formulario de edición. */
    private function actualizar(): void
    {
        $id = (int) ($_POST['id_producto'] ?? 0);

        if ($this->productos->obtener($id) === null) {
            mensaje('error', 'El producto ya no existe.');
            redirigir('panel');
        }

        $datos   = $this->leerFormulario();
        $tallas  = array_map('intval', (array) ($_POST['tallas'] ?? []));
        $errores = $this->validar($datos, $id);

        if ($errores !== []) {
            $datos['id_producto'] = $id;
            $this->render('formulario', [
                'titulo'     => 'Editar producto',
                'modo'       => 'editar',
                'producto'   => $datos,
                'elegidas'   => $tallas,
                'categorias' => $this->catalogo->categorias(),
                'tallas'     => $this->catalogo->tallas(),
                'errores'    => $errores,
            ]);
            return;
        }

        if ($this->productos->actualizar($id, $datos, $tallas)) {
            mensaje('exito', 'Producto "' . $datos['nombre'] . '" actualizado.');
        } else {
            mensaje('error', 'No se pudo actualizar el producto.');
        }
        redirigir('panel');
    }

    // ---------------------------------------------------------------
    //  DELETE
    // ---------------------------------------------------------------

    private function eliminar(): void
    {
        $id = (int) ($_POST['id_producto'] ?? $_GET['id'] ?? 0);

        if ($id <= 0) {
            mensaje('error', 'Identificador inválido.');
            redirigir('panel');
        }

        if ($this->productos->eliminar($id)) {
            mensaje('exito', 'Producto eliminado del catálogo.');
        } else {
            mensaje('error', 'No se pudo eliminar el producto (puede que ya no exista).');
        }
        redirigir('panel');
    }

    // ---------------------------------------------------------------
    //  Validación y apoyo
    // ---------------------------------------------------------------

    /** Toma los campos del POST y los limpia. */
    private function leerFormulario(): array
    {
        return [
            'codigo'       => strtoupper(trim((string) ($_POST['codigo'] ?? ''))),
            'nombre'       => trim((string) ($_POST['nombre'] ?? '')),
            'descripcion'  => trim((string) ($_POST['descripcion'] ?? '')),
            'precio'       => str_replace(',', '.', trim((string) ($_POST['precio'] ?? ''))),
            'stock'        => (int) ($_POST['stock'] ?? 0),
            'color'        => trim((string) ($_POST['color'] ?? '')),
            'genero'       => (string) ($_POST['genero'] ?? 'Unisex'),
            'imagen'       => trim((string) ($_POST['imagen'] ?? '')),
            'activo'       => isset($_POST['activo']) ? 1 : 0,
            'id_categoria' => (int) ($_POST['id_categoria'] ?? 0),
        ];
    }

    /**
     * Validación del lado del servidor (la del navegador se puede saltar).
     * @return array<string, string> campo => mensaje de error
     */
    private function validar(array $d, int $idActual = 0): array
    {
        $errores = [];
        $generos = ['Hombre', 'Mujer', 'Unisex', 'Niño'];

        if ($d['codigo'] === '') {
            $errores['codigo'] = 'El código es obligatorio.';
        } elseif (!preg_match('/^[A-Z]{3}-\d{3}$/', $d['codigo'])) {
            $errores['codigo'] = 'Formato esperado: tres letras, guion y tres dígitos (ej. ROP-011).';
        } elseif ($this->productos->codigoRepetido($d['codigo'], $idActual)) {
            $errores['codigo'] = 'Ya existe otro producto con ese código.';
        }

        if (mb_strlen($d['nombre']) < 3) {
            $errores['nombre'] = 'El nombre debe tener al menos 3 caracteres.';
        }

        if (!is_numeric($d['precio']) || (float) $d['precio'] <= 0) {
            $errores['precio'] = 'El precio debe ser un número mayor que cero.';
        } elseif ((float) $d['precio'] > 999999.99) {
            $errores['precio'] = 'El precio excede el máximo permitido.';
        }

        if ($d['stock'] < 0) {
            $errores['stock'] = 'El stock no puede ser negativo.';
        }

        if (!in_array($d['genero'], $generos, true)) {
            $errores['genero'] = 'Seleccione un género válido.';
        }

        if ($d['id_categoria'] <= 0) {
            $errores['id_categoria'] = 'Seleccione una categoría.';
        }

        if (mb_strlen($d['descripcion']) > 400) {
            $errores['descripcion'] = 'La descripción no debe pasar de 400 caracteres.';
        }

        return $errores;
    }

    private function productoVacio(): array
    {
        return [
            'id_producto'  => 0,
            'codigo'       => '',
            'nombre'       => '',
            'descripcion'  => '',
            'precio'       => '',
            'stock'        => 0,
            'color'        => '',
            'genero'       => 'Unisex',
            'imagen'       => '',
            'activo'       => 1,
            'id_categoria' => 0,
        ];
    }

    /** Dibuja una vista dentro de la plantilla (encabezado + cuerpo + pie). */
    private function render(string $vista, array $datos = []): void
    {
        extract($datos, EXTR_SKIP);
        $titulo = $datos['titulo'] ?? 'Estilo Andino';

        require __DIR__ . '/../vistas/encabezado.php';
        require __DIR__ . '/../vistas/' . $vista . '.php';
        require __DIR__ . '/../vistas/pie.php';
    }
}
