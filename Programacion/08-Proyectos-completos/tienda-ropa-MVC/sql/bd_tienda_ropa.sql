-- =====================================================================
--  MODELO FÍSICO  |  Base de datos: tienda_ropa
--  Tienda de ropa "Estilo Andino"  -  Práctica CRUD con PHP + PDO
--  Motor: MySQL / MariaDB (XAMPP)   Charset: utf8mb4
-- =====================================================================

DROP DATABASE IF EXISTS tienda_ropa;
CREATE DATABASE tienda_ropa
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;
USE tienda_ropa;

-- ---------------------------------------------------------------------
-- 1) CATEGORIA   (1 categoría ----- N productos)
-- ---------------------------------------------------------------------
CREATE TABLE categoria (
    id_categoria INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre       VARCHAR(50)  NOT NULL,
    descripcion  VARCHAR(150) NULL,
    CONSTRAINT pk_categoria PRIMARY KEY (id_categoria),
    CONSTRAINT uq_categoria_nombre UNIQUE (nombre)
) ENGINE = InnoDB;

-- ---------------------------------------------------------------------
-- 2) TALLA   (N productos ----- M tallas)
-- ---------------------------------------------------------------------
CREATE TABLE talla (
    id_talla INT UNSIGNED NOT NULL AUTO_INCREMENT,
    etiqueta VARCHAR(10)  NOT NULL,   -- XS, S, M, L, XL, 28, 30...
    CONSTRAINT pk_talla PRIMARY KEY (id_talla),
    CONSTRAINT uq_talla_etiqueta UNIQUE (etiqueta)
) ENGINE = InnoDB;

-- ---------------------------------------------------------------------
-- 3) USUARIO   (quien administra el catálogo)
-- ---------------------------------------------------------------------
CREATE TABLE usuario (
    id_usuario  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario     VARCHAR(30)  NOT NULL,
    clave_hash  VARCHAR(255) NOT NULL,          -- password_hash(), nunca texto plano
    nombre      VARCHAR(80)  NOT NULL,
    rol         ENUM('administrador','vendedor') NOT NULL DEFAULT 'vendedor',
    activo      TINYINT(1)   NOT NULL DEFAULT 1,
    CONSTRAINT pk_usuario PRIMARY KEY (id_usuario),
    CONSTRAINT uq_usuario_usuario UNIQUE (usuario)
) ENGINE = InnoDB;

-- ---------------------------------------------------------------------
-- 4) PRODUCTO   (entidad central del CRUD)
-- ---------------------------------------------------------------------
CREATE TABLE producto (
    id_producto     INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    codigo          VARCHAR(15)   NOT NULL,      -- SKU: ROP-001
    nombre          VARCHAR(100)  NOT NULL,
    descripcion     VARCHAR(400)  NULL,
    precio          DECIMAL(8,2)  NOT NULL,
    stock           INT UNSIGNED  NOT NULL DEFAULT 0,
    color           VARCHAR(30)   NULL,
    genero          ENUM('Hombre','Mujer','Unisex','Niño') NOT NULL DEFAULT 'Unisex',
    imagen          VARCHAR(120)  NULL,          -- nombre del archivo en /img
    activo          TINYINT(1)    NOT NULL DEFAULT 1,
    fecha_registro  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_categoria    INT UNSIGNED  NOT NULL,
    CONSTRAINT pk_producto PRIMARY KEY (id_producto),
    CONSTRAINT uq_producto_codigo UNIQUE (codigo),
    CONSTRAINT fk_producto_categoria
        FOREIGN KEY (id_categoria) REFERENCES categoria (id_categoria)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT ck_producto_precio CHECK (precio > 0),
    INDEX idx_producto_nombre (nombre)
) ENGINE = InnoDB;

-- ---------------------------------------------------------------------
-- 5) PRODUCTO_TALLA   (tabla intermedia que resuelve la relación N:M)
-- ---------------------------------------------------------------------
CREATE TABLE producto_talla (
    id_producto INT UNSIGNED NOT NULL,
    id_talla    INT UNSIGNED NOT NULL,
    CONSTRAINT pk_producto_talla PRIMARY KEY (id_producto, id_talla),
    CONSTRAINT fk_pt_producto
        FOREIGN KEY (id_producto) REFERENCES producto (id_producto)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_pt_talla
        FOREIGN KEY (id_talla) REFERENCES talla (id_talla)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE = InnoDB;

-- =====================================================================
--  CARGA DE DATOS
-- =====================================================================

INSERT INTO categoria (nombre, descripcion) VALUES
('Camisetas',  'Camisetas y polos de algodón'),
('Pantalones', 'Jeans, joggers y pantalones de vestir'),
('Abrigos',    'Chompas, casacas y abrigos de invierno'),
('Vestidos',   'Vestidos casuales y de fiesta'),
('Accesorios', 'Gorras, bufandas, cinturones y medias');

INSERT INTO talla (etiqueta) VALUES
('XS'), ('S'), ('M'), ('L'), ('XL'), ('Única');

-- Clave en texto plano de los dos usuarios: admin123 / vende123
INSERT INTO usuario (usuario, clave_hash, nombre, rol) VALUES
('admin',     '$2y$10$PitTVqOWxFuRRcF7A7ViUOUF0NkOcGgHxw6iFPQyUr4E396zK/fK6', 'Administrador del sistema', 'administrador'),
('vendedor1', '$2y$10$VL.5H2LUX3fpba.RUSMb2OUu2zQEo622f1sgAAHj83oIrAbsGsMQ2', 'María Fernanda Coles', 'vendedor');

-- ------------------------- 10 PRODUCTOS -----------------------------
INSERT INTO producto (codigo, nombre, descripcion, precio, stock, color, genero, imagen, id_categoria) VALUES
('ROP-001', 'Camiseta básica de algodón',  'Camiseta de algodón peinado 100%, cuello redondo, costuras reforzadas.',            12.50,  40, 'Blanco',   'Unisex', 'p01.svg', 1),
('ROP-002', 'Polo piqué clásico',          'Polo en tejido piqué con puños acanalados y tres botones de nácar.',                 19.90,  25, 'Azul',     'Hombre', 'p02.svg', 1),
('ROP-003', 'Jean slim fit',               'Jean de mezclilla elástica con cinco bolsillos y cierre metálico.',                  32.00,  18, 'Índigo',   'Hombre', 'p03.svg', 2),
('ROP-004', 'Jogger deportivo',            'Jogger de felpa perchada con puños elásticos y bolsillos laterales.',                24.75,  30, 'Gris',     'Unisex', 'p04.svg', 2),
('ROP-005', 'Chompa de lana andina',       'Chompa tejida a mano en lana de oveja con motivos geométricos de Guaranda.',          45.00,  12, 'Café',     'Unisex', 'p05.svg', 3),
('ROP-006', 'Casaca impermeable',          'Casaca cortaviento con capucha desmontable y forro transpirable.',                   58.90,   9, 'Negro',    'Hombre', 'p06.svg', 3),
('ROP-007', 'Vestido floral midi',         'Vestido midi de viscosa con estampado floral y cinturón del mismo tejido.',           39.50,  15, 'Verde',    'Mujer',  'p07.svg', 4),
('ROP-008', 'Vestido de gala satinado',    'Vestido largo en satín con escote en V y abertura lateral.',                          74.00,   6, 'Vino',     'Mujer',  'p08.svg', 4),
('ROP-009', 'Gorra bordada',               'Gorra de gabardina con cierre ajustable y bordado frontal.',                          10.00,  50, 'Beige',    'Unisex', 'p09.svg', 5),
('ROP-010', 'Bufanda de alpaca',           'Bufanda tejida en fibra de alpaca, suave y térmica, 180 cm de largo.',                22.30,  20, 'Terracota','Unisex', 'p10.svg', 5);

-- Tallas disponibles por producto (relación N:M)
INSERT INTO producto_talla (id_producto, id_talla) VALUES
(1,2),(1,3),(1,4),(1,5),
(2,3),(2,4),(2,5),
(3,2),(3,3),(3,4),
(4,3),(4,4),(4,5),
(5,3),(5,4),
(6,4),(6,5),
(7,1),(7,2),(7,3),
(8,2),(8,3),
(9,6),
(10,6);

-- =====================================================================
--  CONSULTAS DE VERIFICACIÓN
-- =====================================================================
-- SELECT p.codigo, p.nombre, p.precio, c.nombre AS categoria,
--        GROUP_CONCAT(t.etiqueta ORDER BY t.id_talla) AS tallas
-- FROM producto p
--      INNER JOIN categoria c ON c.id_categoria = p.id_categoria
--      LEFT  JOIN producto_talla pt ON pt.id_producto = p.id_producto
--      LEFT  JOIN talla t ON t.id_talla = pt.id_talla
-- GROUP BY p.id_producto
-- ORDER BY p.codigo;
