<?php
/**
 * modelo/ProductoModelo.php
 * ---------------------------------------------------------------
 * Capa de acceso a datos de la entidad PRODUCTO.
 * Aquí viven las 4 operaciones del CRUD:
 *      crear()      -> INSERT   (Create)
 *      listar() / obtener()  -> SELECT   (Read)
 *      actualizar() -> UPDATE   (Update)
 *      eliminar()   -> DELETE   (Delete)
 *
 * Todas las consultas usan SENTENCIAS PREPARADAS con parámetros
 * nombrados (:nombre), lo que evita la inyección SQL.
 * ---------------------------------------------------------------
 */
declare(strict_types=1);

class ProductoModelo
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // =================================================================
    //  READ
    // =================================================================

    /**
     * Lista los productos con su categoría y sus tallas.
     *
     * @param string   $busqueda     texto a buscar en código o nombre
     * @param int|null $idCategoria  filtro por categoría (null = todas)
     * @param bool     $soloActivos  true = solo productos visibles en el catálogo
     * @return array<int, array<string, mixed>>
     */
    public function listar(string $busqueda = '', ?int $idCategoria = null, bool $soloActivos = false): array
    {
        $sql = 'SELECT  p.id_producto, p.codigo, p.nombre, p.descripcion, p.precio,
                        p.stock, p.color, p.genero, p.imagen, p.activo, p.fecha_registro,
                        p.id_categoria, c.nombre AS categoria,
                        GROUP_CONCAT(t.etiqueta ORDER BY t.id_talla SEPARATOR ", ") AS tallas
                FROM        producto p
                INNER JOIN  categoria c       ON c.id_categoria = p.id_categoria
                LEFT  JOIN  producto_talla pt ON pt.id_producto = p.id_producto
                LEFT  JOIN  talla t           ON t.id_talla     = pt.id_talla
                WHERE 1 = 1';

        $parametros = [];

        if ($busqueda !== '') {
            // Un marcador por cada uso: con sentencias preparadas reales
            // (ATTR_EMULATE_PREPARES = false) no se puede repetir el mismo nombre.
            $sql .= ' AND (p.nombre LIKE :busqueda_nombre OR p.codigo LIKE :busqueda_codigo)';
            $parametros[':busqueda_nombre'] = '%' . $busqueda . '%';
            $parametros[':busqueda_codigo'] = '%' . $busqueda . '%';
        }
        if ($idCategoria !== null) {
            $sql .= ' AND p.id_categoria = :id_categoria';
            $parametros[':id_categoria'] = $idCategoria;
        }
        if ($soloActivos) {
            $sql .= ' AND p.activo = 1';
        }

        $sql .= ' GROUP BY p.id_producto ORDER BY p.codigo ASC';

        try {
            $sentencia = $this->pdo->prepare($sql);
            $sentencia->execute($parametros);
            return $sentencia->fetchAll();
        } catch (PDOException $e) {
            error_log('[ProductoModelo::listar] ' . $e->getMessage());
            return [];
        }
    }

    /** Devuelve un producto por su ID, o null si no existe. */
    public function obtener(int $id): ?array
    {
        $sql = 'SELECT p.*, c.nombre AS categoria
                FROM producto p
                INNER JOIN categoria c ON c.id_categoria = p.id_categoria
                WHERE p.id_producto = :id
                LIMIT 1';
        try {
            $sentencia = $this->pdo->prepare($sql);
            $sentencia->bindValue(':id', $id, PDO::PARAM_INT);
            $sentencia->execute();
            $fila = $sentencia->fetch();
            return $fila === false ? null : $fila;
        } catch (PDOException $e) {
            error_log('[ProductoModelo::obtener] ' . $e->getMessage());
            return null;
        }
    }

    /** IDs de las tallas asociadas a un producto. @return int[] */
    public function tallasDe(int $idProducto): array
    {
        try {
            $sentencia = $this->pdo->prepare(
                'SELECT id_talla FROM producto_talla WHERE id_producto = :id'
            );
            $sentencia->bindValue(':id', $idProducto, PDO::PARAM_INT);
            $sentencia->execute();
            return array_map('intval', $sentencia->fetchAll(PDO::FETCH_COLUMN));
        } catch (PDOException $e) {
            error_log('[ProductoModelo::tallasDe] ' . $e->getMessage());
            return [];
        }
    }

    /** true si el código ya está usado por OTRO producto (regla UNIQUE). */
    public function codigoRepetido(string $codigo, int $idExcluir = 0): bool
    {
        try {
            $sentencia = $this->pdo->prepare(
                'SELECT COUNT(*) FROM producto WHERE codigo = :codigo AND id_producto <> :id'
            );
            $sentencia->execute([':codigo' => $codigo, ':id' => $idExcluir]);
            return (int) $sentencia->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('[ProductoModelo::codigoRepetido] ' . $e->getMessage());
            return false;
        }
    }

    /** Totales para el panel: cantidad de productos, stock y valor del inventario. */
    public function resumen(): array
    {
        $vacio = ['total' => 0, 'stock' => 0, 'valor' => 0.0, 'agotados' => 0];
        try {
            $sql = 'SELECT COUNT(*) AS total,
                           COALESCE(SUM(stock), 0) AS stock,
                           COALESCE(SUM(precio * stock), 0) AS valor,
                           SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END) AS agotados
                    FROM producto';
            $fila = $this->pdo->query($sql)->fetch();
            return $fila === false ? $vacio : $fila;
        } catch (PDOException $e) {
            error_log('[ProductoModelo::resumen] ' . $e->getMessage());
            return $vacio;
        }
    }

    // =================================================================
    //  CREATE
    // =================================================================

    /**
     * Inserta un producto y sus tallas dentro de una TRANSACCIÓN.
     *
     * @param array $d      datos ya validados del formulario
     * @param int[] $tallas IDs de tallas seleccionadas
     * @return int ID generado, o 0 si falló
     */
    public function crear(array $d, array $tallas = []): int
    {
        $sql = 'INSERT INTO producto
                    (codigo, nombre, descripcion, precio, stock, color, genero, imagen, activo, id_categoria)
                VALUES
                    (:codigo, :nombre, :descripcion, :precio, :stock, :color, :genero, :imagen, :activo, :id_categoria)';
        try {
            $this->pdo->beginTransaction();

            $sentencia = $this->pdo->prepare($sql);
            $sentencia->bindValue(':codigo',       $d['codigo']);
            $sentencia->bindValue(':nombre',       $d['nombre']);
            $sentencia->bindValue(':descripcion',  $d['descripcion']);
            $sentencia->bindValue(':precio',       $d['precio']);
            $sentencia->bindValue(':stock',        $d['stock'],       PDO::PARAM_INT);
            $sentencia->bindValue(':color',        $d['color']);
            $sentencia->bindValue(':genero',       $d['genero']);
            $sentencia->bindValue(':imagen',       $d['imagen']);
            $sentencia->bindValue(':activo',       $d['activo'],       PDO::PARAM_INT);
            $sentencia->bindValue(':id_categoria', $d['id_categoria'], PDO::PARAM_INT);
            $sentencia->execute();

            $id = (int) $this->pdo->lastInsertId();
            $this->guardarTallas($id, $tallas);

            $this->pdo->commit();
            return $id;
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();          // deshace todo si algo falló
            }
            error_log('[ProductoModelo::crear] ' . $e->getMessage());
            return 0;
        }
    }

    // =================================================================
    //  UPDATE
    // =================================================================

    public function actualizar(int $id, array $d, array $tallas = []): bool
    {
        $sql = 'UPDATE producto SET
                    codigo       = :codigo,
                    nombre       = :nombre,
                    descripcion  = :descripcion,
                    precio       = :precio,
                    stock        = :stock,
                    color        = :color,
                    genero       = :genero,
                    imagen       = :imagen,
                    activo       = :activo,
                    id_categoria = :id_categoria
                WHERE id_producto = :id';
        try {
            $this->pdo->beginTransaction();

            $sentencia = $this->pdo->prepare($sql);
            $sentencia->bindValue(':codigo',       $d['codigo']);
            $sentencia->bindValue(':nombre',       $d['nombre']);
            $sentencia->bindValue(':descripcion',  $d['descripcion']);
            $sentencia->bindValue(':precio',       $d['precio']);
            $sentencia->bindValue(':stock',        $d['stock'],        PDO::PARAM_INT);
            $sentencia->bindValue(':color',        $d['color']);
            $sentencia->bindValue(':genero',       $d['genero']);
            $sentencia->bindValue(':imagen',       $d['imagen']);
            $sentencia->bindValue(':activo',       $d['activo'],       PDO::PARAM_INT);
            $sentencia->bindValue(':id_categoria', $d['id_categoria'], PDO::PARAM_INT);
            $sentencia->bindValue(':id',           $id,                PDO::PARAM_INT);
            $sentencia->execute();

            // Se reemplaza el conjunto de tallas del producto
            $borrar = $this->pdo->prepare('DELETE FROM producto_talla WHERE id_producto = :id');
            $borrar->bindValue(':id', $id, PDO::PARAM_INT);
            $borrar->execute();
            $this->guardarTallas($id, $tallas);

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('[ProductoModelo::actualizar] ' . $e->getMessage());
            return false;
        }
    }

    // =================================================================
    //  DELETE
    // =================================================================

    /** Borrado físico. Las filas de producto_talla se eliminan en cascada. */
    public function eliminar(int $id): bool
    {
        try {
            $sentencia = $this->pdo->prepare('DELETE FROM producto WHERE id_producto = :id');
            $sentencia->bindValue(':id', $id, PDO::PARAM_INT);
            $sentencia->execute();
            return $sentencia->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('[ProductoModelo::eliminar] ' . $e->getMessage());
            return false;
        }
    }

    // =================================================================
    //  Apoyo
    // =================================================================

    /** Inserta las tallas del producto reutilizando una sola sentencia preparada. */
    private function guardarTallas(int $idProducto, array $tallas): void
    {
        if ($tallas === []) {
            return;
        }
        $sentencia = $this->pdo->prepare(
            'INSERT INTO producto_talla (id_producto, id_talla) VALUES (:p, :t)'
        );
        foreach (array_unique(array_map('intval', $tallas)) as $idTalla) {
            $sentencia->execute([':p' => $idProducto, ':t' => $idTalla]);
        }
    }
}
