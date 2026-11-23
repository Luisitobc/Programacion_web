CREATE DATABASE IF NOT EXISTS inventario_tienda CHARACTER SET utf8mb4;
USE inventario_tienda;

CREATE TABLE IF NOT EXISTS productos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  descripcion TEXT,
  categoria ENUM('alimentos','limpieza','tecnologia') NOT NULL,
  precio DECIMAL(10,2) NOT NULL,
  stock INT NOT NULL DEFAULT 0,
  stock_minimo INT NOT NULL DEFAULT 0,
  estado ENUM('disponible','stock_bajo','agotado') NOT NULL DEFAULT 'disponible'
);

-- Datos de prueba: uno por cada estado
INSERT INTO productos (nombre, descripcion, categoria, precio, stock, stock_minimo, estado) VALUES
('Arroz 1 kg',      'Arroz blanco de grano largo', 'alimentos',  1.25, 50, 10, 'disponible'),
('Detergente 500 g','Detergente en polvo',          'limpieza',   2.40,  4,  5, 'stock_bajo'),
('Mouse USB',       'Mouse óptico alámbrico',       'tecnologia', 6.90,  0,  3, 'agotado');
