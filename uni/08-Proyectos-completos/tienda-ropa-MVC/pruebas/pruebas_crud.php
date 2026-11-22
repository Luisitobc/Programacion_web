<?php
/**
 * pruebas/pruebas_crud.php
 * ---------------------------------------------------------------
 * Prueba automática de las cuatro operaciones del CRUD.
 * Se ejecuta desde la consola:
 *
 *      cd C:\xampp\htdocs\tienda_ropa
 *      php pruebas\pruebas_crud.php
 *
 * No modifica los 10 productos del catálogo: crea uno de prueba
 * (ZZZ-999), lo consulta, lo actualiza y finalmente lo elimina.
 * ---------------------------------------------------------------
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../modelo/ProductoModelo.php';
require_once __DIR__ . '/../modelo/CatalogoModelo.php';

$aprobadas = 0;
$fallidas  = 0;

function comprobar(string $descripcion, bool $condicion): void
{
    global $aprobadas, $fallidas;
    if ($condicion) {
        $aprobadas++;
        echo "  [OK]    $descripcion\n";
    } else {
        $fallidas++;
        echo "  [FALLA] $descripcion\n";
    }
}

echo "\n=== PRUEBAS DEL CRUD - Tienda Estilo Andino ===\n\n";

$pdo       = obtenerConexion();
$productos = new ProductoModelo($pdo);
$catalogo  = new CatalogoModelo($pdo);

// ---------------------------------------------------------------
echo "1) Lectura del catálogo (READ)\n";
$lista = $productos->listar();
comprobar('La tabla producto devuelve 10 registros iniciales', count($lista) >= 10);
comprobar('Cada producto trae el nombre de su categoría', isset($lista[0]['categoria']));
comprobar('Las categorías se consultan correctamente', count($catalogo->categorias()) === 5);
comprobar('Las tallas se consultan correctamente', count($catalogo->tallas()) === 6);

// ---------------------------------------------------------------
echo "\n2) Inserción (CREATE)\n";
$nuevo = [
    'codigo'       => 'ZZZ-999',
    'nombre'       => 'Producto de prueba automática',
    'descripcion'  => 'Registro temporal creado por pruebas_crud.php',
    'precio'       => '15.75',
    'stock'        => 7,
    'color'        => 'Negro',
    'genero'       => 'Unisex',
    'imagen'       => '',
    'activo'       => 1,
    'id_categoria' => 1,
];
$id = $productos->crear($nuevo, [2, 3]);
comprobar('crear() devuelve un ID mayor que cero', $id > 0);

$guardado = $productos->obtener($id);
comprobar('El producto se encuentra por su ID', $guardado !== null);
comprobar('El precio se guardó con dos decimales', $guardado !== null && (float) $guardado['precio'] === 15.75);
comprobar('Se registraron las 2 tallas seleccionadas', count($productos->tallasDe($id)) === 2);
comprobar('codigoRepetido() detecta el código duplicado', $productos->codigoRepetido('ZZZ-999') === true);
comprobar('codigoRepetido() ignora al propio producto', $productos->codigoRepetido('ZZZ-999', $id) === false);

// ---------------------------------------------------------------
echo "\n3) Modificación (UPDATE)\n";
$cambios = array_merge($nuevo, [
    'nombre' => 'Producto de prueba MODIFICADO',
    'precio' => '29.99',
    'stock'  => 3,
]);
comprobar('actualizar() responde verdadero', $productos->actualizar($id, $cambios, [4]) === true);

$modificado = $productos->obtener($id);
comprobar('El nombre cambió en la base de datos', $modificado['nombre'] === 'Producto de prueba MODIFICADO');
comprobar('El precio cambió a 29.99', (float) $modificado['precio'] === 29.99);
comprobar('Las tallas se reemplazaron (queda 1)', count($productos->tallasDe($id)) === 1);

// ---------------------------------------------------------------
echo "\n4) Búsqueda filtrada (READ con parámetros)\n";
comprobar('Buscar "MODIFICADO" devuelve 1 resultado', count($productos->listar('MODIFICADO')) === 1);
comprobar('Buscar un texto inexistente devuelve 0', count($productos->listar('xyzxyz')) === 0);
comprobar('Filtrar por categoría 4 devuelve 2 vestidos', count($productos->listar('', 4)) === 2);

// Intento de inyección SQL: debe tratarse como texto, no como instrucción
$peligroso = "' OR 1=1 --";
comprobar('Una cadena con comillas no rompe la consulta (sentencias preparadas)',
    is_array($productos->listar($peligroso)));

// ---------------------------------------------------------------
echo "\n5) Eliminación (DELETE)\n";
comprobar('eliminar() responde verdadero', $productos->eliminar($id) === true);
comprobar('El producto ya no existe', $productos->obtener($id) === null);
comprobar('Las tallas se borraron en cascada', count($productos->tallasDe($id)) === 0);
comprobar('Eliminar un ID inexistente responde falso', $productos->eliminar(999999) === false);
comprobar('El catálogo vuelve a tener 10 productos', count($productos->listar()) === 10);

// ---------------------------------------------------------------
echo "\n=================================================\n";
echo "  Aprobadas: $aprobadas    Fallidas: $fallidas\n";
echo "=================================================\n\n";

exit($fallidas === 0 ? 0 : 1);
