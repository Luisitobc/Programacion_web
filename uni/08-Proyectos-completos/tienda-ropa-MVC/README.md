# Tienda de ropa "Estilo Andino" — CRUD con PHP y PDO

Aplicación web de catálogo y administración de productos para una tienda de ropa.
El corazón de la práctica es el **CRUD en PHP usando PDO** (Create, Read, Update, Delete)
con **archivos separados**, **manejo de errores** y **sentencias preparadas**.

---

## 1. Instalación (XAMPP)

1. Copie la carpeta `tienda_ropa` dentro de `C:\xampp\htdocs\`.
2. Inicie **Apache** y **MySQL** desde el panel de XAMPP.
3. Abra <http://localhost/phpmyadmin> → pestaña **Importar** → seleccione
   `sql/bd_tienda_ropa.sql` → **Continuar**.
   (También funciona desde consola: `mysql -u root < sql\bd_tienda_ropa.sql`)
4. Si su MySQL tiene contraseña, edítela en `config/conexion.php` (constante `DB_CLAVE`).
5. Ingrese a <http://localhost/tienda_ropa/>.

Prueba automática del CRUD desde la consola:

```
cd C:\xampp\htdocs\tienda_ropa
php pruebas\pruebas_crud.php
```

---

## 2. Estructura de archivos

```
tienda_ropa/
├── index.php                      Punto de entrada único (enrutador)
├── config/
│   ├── conexion.php               a) Conexión PDO + manejo de errores
│   └── utilidades.php             Funciones de apoyo (escape, precio, mensajes)
├── modelo/
│   ├── ProductoModelo.php         CRUD completo con sentencias preparadas
│   └── CatalogoModelo.php         Consultas de categorías y tallas
├── controlador/
│   └── ProductoControlador.php    Valida, llama al modelo y elige la vista
├── vistas/
│   ├── encabezado.php             b) ENCABEZADO (header + menú)
│   ├── catalogo.php               b) CUERPO: catálogo de 10 productos
│   ├── panel.php                  b) CUERPO: tabla de administración
│   ├── formulario.php             b) CUERPO: alta y edición
│   ├── detalle.php                b) CUERPO: ficha de un producto
│   └── pie.php                    b) PIE (footer)
├── css/estilos.css                c) Hoja de estilos
├── js/validaciones.js             d) Validaciones y filtro instantáneo
├── img/                           11 imágenes SVG del catálogo
├── sql/bd_tienda_ropa.sql         Modelo físico + datos de prueba
└── pruebas/pruebas_crud.php       d) Pruebas automáticas del CRUD
```

El patrón usado es **MVC simple**: la vista no consulta la base de datos y el modelo
no imprime HTML.

---

## 3. Modelado de la base de datos

### 3.1 Usuarios del sistema

| Usuario | Qué hace con el sistema |
|---|---|
| **Cliente / visitante** | Consulta el catálogo público, filtra por categoría, busca prendas y abre la ficha de un producto. No modifica datos. |
| **Vendedor** | Actualiza precios y stock de las prendas existentes. |
| **Administrador** | Registra, modifica y elimina productos; decide qué prendas se muestran en el catálogo. |

### 3.2 Requerimientos

**Funcionales**

| N° | Requerimiento |
|---|---|
| RF1 | Registrar un producto con código, nombre, descripción, precio, stock, color, género, categoría y tallas. |
| RF2 | Listar los productos con su categoría y sus tallas disponibles. |
| RF3 | Buscar por nombre o código y filtrar por categoría. |
| RF4 | Modificar los datos de un producto existente. |
| RF5 | Eliminar un producto del sistema. |
| RF6 | Mostrar el catálogo público solo con los productos marcados como visibles. |
| RF7 | Mostrar totales de inventario (productos, unidades, valor, agotados). |

**No funcionales**

| N° | Requerimiento |
|---|---|
| RNF1 | El acceso a datos se hace con **PDO** y sentencias preparadas (previene inyección SQL). |
| RNF2 | Los errores de base de datos se capturan con `try/catch` y se registran con `error_log()`; al usuario se le muestra un mensaje claro. |
| RNF3 | El código se separa en conexión, modelo, controlador y vistas. |
| RNF4 | La interfaz es responsiva (escritorio, tablet y celular). |
| RNF5 | Todo texto que proviene de la base de datos se escapa con `htmlspecialchars()`. |

### 3.3 Modelo Entidad–Relación

Entidades y cardinalidades:

```
        ┌───────────────┐                        ┌───────────────┐
        │   CATEGORIA   │                        │    USUARIO    │
        ├───────────────┤                        ├───────────────┤
        │ id_categoria  │PK                      │ id_usuario    │PK
        │ nombre        │                        │ usuario       │
        │ descripcion   │                        │ clave_hash    │
        └───────┬───────┘                        │ nombre        │
                │ 1                              │ rol           │
                │                                │ activo        │
                │ clasifica                      └───────────────┘
                │                                 (administra el catálogo)
                │ N
        ┌───────┴────────────────────┐            ┌───────────────┐
        │          PRODUCTO          │ N       M  │     TALLA     │
        ├────────────────────────────┤────────────├───────────────┤
        │ id_producto  PK            │  está      │ id_talla   PK │
        │ codigo (UNIQUE)            │ disponible │ etiqueta      │
        │ nombre, descripcion        │    en      └───────────────┘
        │ precio, stock, color       │
        │ genero, imagen, activo     │
        │ fecha_registro             │
        │ id_categoria FK            │
        └────────────────────────────┘
```

* **CATEGORIA (1) — (N) PRODUCTO**: una categoría agrupa muchos productos; cada producto
  pertenece a una sola categoría.
* **PRODUCTO (N) — (M) TALLA**: una prenda se ofrece en varias tallas y una talla aparece
  en muchas prendas. Esta relación se resuelve con la tabla intermedia `producto_talla`.

### 3.4 Modelo relacional

Subrayado = clave primaria, (FK) = clave foránea.

```
categoria      ( id_categoria, nombre, descripcion )
                 ─────────────

talla          ( id_talla, etiqueta )
                 ────────

usuario        ( id_usuario, usuario, clave_hash, nombre, rol, activo )
                 ──────────

producto       ( id_producto, codigo, nombre, descripcion, precio, stock,
                 ───────────
                 color, genero, imagen, activo, fecha_registro, id_categoria(FK) )

producto_talla ( id_producto(FK), id_talla(FK) )
                 ───────────────  ────────────
```

Reglas de integridad:

* `producto.id_categoria` → `categoria.id_categoria` — `ON DELETE RESTRICT`
  (no se borra una categoría que tenga productos).
* `producto_talla.id_producto` → `producto.id_producto` — `ON DELETE CASCADE`
  (al borrar la prenda desaparecen sus tallas).
* `codigo` es **UNIQUE**; `precio > 0` se valida con un **CHECK** y también en PHP.

### 3.5 Normalización

| Forma | Cómo se cumple |
|---|---|
| **1FN** | Todos los atributos son atómicos. Las tallas no se guardan como "S, M, L" en una celda: se pasaron a la tabla `producto_talla`. |
| **2FN** | Está en 1FN y toda columna depende de la clave completa. En `producto_talla` la clave es `(id_producto, id_talla)` y no hay atributos que dependan solo de una parte. |
| **3FN** | No hay dependencias transitivas: el nombre de la categoría no se repite en `producto`, solo se guarda `id_categoria`. De igual forma la etiqueta de talla vive únicamente en `talla`. |

Dependencias funcionales principales:

```
id_producto  →  codigo, nombre, descripcion, precio, stock, color, genero,
                imagen, activo, fecha_registro, id_categoria
codigo       →  id_producto          (clave candidata)
id_categoria →  nombre, descripcion  (por eso está en su propia tabla)
id_talla     →  etiqueta
```

### 3.6 Modelo físico

Está en `sql/bd_tienda_ropa.sql`: motor **InnoDB** (soporta claves foráneas y
transacciones), juego de caracteres **utf8mb4** (acentos y la "ñ"), más los
`INSERT` de 5 categorías, 6 tallas, 2 usuarios y **10 productos**.

---

## 4. a) Conexión PDO y CRUD

**`config/conexion.php`** abre una sola conexión y define tres atributos clave:

```php
PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION  // los errores lanzan excepción
PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC        // resultados como arreglo asociativo
PDO::ATTR_EMULATE_PREPARES   => false                   // sentencias preparadas reales
```

Si la conexión falla se captura `PDOException`, el detalle técnico va a `error_log()`
y el usuario ve un mensaje en lugar de una pantalla en blanco.

**`modelo/ProductoModelo.php`** contiene las cuatro operaciones:

| Operación | Método | SQL |
|---|---|---|
| **C**reate | `crear()` | `INSERT INTO producto (...) VALUES (:codigo, :nombre, …)` |
| **R**ead | `listar()`, `obtener()` | `SELECT … INNER JOIN categoria … LEFT JOIN talla` |
| **U**pdate | `actualizar()` | `UPDATE producto SET … WHERE id_producto = :id` |
| **D**elete | `eliminar()` | `DELETE FROM producto WHERE id_producto = :id` |

Detalles usados en la práctica:

* **Sentencias preparadas** con parámetros nombrados y `bindValue()` con tipo
  (`PDO::PARAM_INT`), nunca concatenando texto del usuario.
* **Transacciones** (`beginTransaction / commit / rollBack`) al guardar un producto y
  sus tallas: si falla la segunda inserción, no queda el producto a medias.
* **`try/catch` por método**: el error se registra y se devuelve un valor seguro
  (`0`, `false`, `[]`) para que la aplicación no se caiga.

> Nota aprendida en las pruebas: con `ATTR_EMULATE_PREPARES = false` **no se puede repetir
> el mismo marcador** en una consulta. Por eso la búsqueda usa `:busqueda_nombre` y
> `:busqueda_codigo` en lugar de un solo `:busqueda` dos veces.

Flujo de una petición:

```
index.php?accion=guardar
      ↓
ProductoControlador::guardar()      valida los datos del POST
      ↓
ProductoModelo::crear()             INSERT con sentencia preparada
      ↓
redirigir('panel')                  patrón POST-Redirect-GET (evita reenvíos con F5)
      ↓
vistas/panel.php                    muestra el mensaje de éxito
```

## 5. b) HTML y c) CSS

* Estructura semántica: `<header>` (encabezado con marca y menú), `<main>` (cuerpo con
  portada, catálogo, tabla o formulario) y `<footer>` (pie con datos de la tienda).
* El encabezado y el pie están en archivos propios y se incluyen desde el controlador,
  así todas las páginas comparten el mismo diseño.
* CSS con **variables** (`:root`), rejilla **CSS Grid** para las tarjetas
  (`repeat(auto-fill, minmax(255px, 1fr))`), tipografía serif para títulos y
  puntos de corte en 900 px y 620 px para celulares.

## 6. d) JavaScript y pruebas

`js/validaciones.js` realiza:

1. Validación de `codigo`, `nombre`, `precio`, `stock` y `categoría` antes de enviar el
   formulario, con mensajes bajo cada campo.
2. Contador de caracteres de la descripción (máximo 400).
3. Filtro instantáneo del catálogo mientras se escribe.
4. Confirmación antes de eliminar un producto.
5. Cierre automático de los avisos de éxito o error.

> La validación del navegador es solo una ayuda: el controlador vuelve a validar todo en
> el servidor, porque el JavaScript se puede desactivar.

### Pruebas automáticas

`php pruebas/pruebas_crud.php` ejecuta 23 comprobaciones (crea el producto `ZZZ-999`,
lo consulta, lo actualiza y lo elimina). Resultado esperado:

```
  Aprobadas: 23    Fallidas: 0
```

### Lista de comprobación manual

| # | Prueba | Resultado esperado |
|---|---|---|
| 1 | Abrir `/tienda_ropa/` | Se ven las 10 prendas con imagen, precio y tallas. |
| 2 | Escribir "jean" en el buscador | El catálogo se reduce sin recargar la página. |
| 3 | Elegir la categoría "Vestidos" | Quedan 2 productos. |
| 4 | Clic en "Ver ficha" | Se abre el detalle del producto. |
| 5 | "+ Nuevo producto" y enviar vacío | Aparecen los mensajes de campo obligatorio. |
| 6 | Registrar `ROP-011` con datos válidos | Vuelve al panel con el aviso verde y el producto aparece en la lista. |
| 7 | Repetir el código `ROP-001` | Error: "Ya existe otro producto con ese código." |
| 8 | Editar un producto y cambiar el precio | El nuevo precio se ve en el panel y en el catálogo. |
| 9 | Eliminar un producto | Pide confirmación y luego desaparece de la tabla. |
| 10 | Desmarcar "Mostrar en el catálogo" | El producto sigue en el panel pero no en el catálogo público. |
| 11 | Escribir `' OR 1=1 --` en el buscador | No arroja error ni muestra registros extra (sentencias preparadas). |
| 12 | Reducir la ventana a 400 px | El menú y las tarjetas se reacomodan en una columna. |

---

## 7. Posibles mejoras

* Inicio de sesión usando la tabla `usuario` (`password_verify()`), para separar lo que
  ve el cliente de lo que ve el administrador.
* Subida real de imágenes con `move_uploaded_file()` en lugar de escribir el nombre.
* Paginación cuando el catálogo supere las 50 prendas.
* Token CSRF en los formularios de creación, edición y borrado.

---

Universidad Estatal de Bolívar — Práctica de CRUD con PHP y PDO.
