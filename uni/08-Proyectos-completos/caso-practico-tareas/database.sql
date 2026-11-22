-- Base de datos del gestor de tareas.
-- Importar en phpMyAdmin (pestaña "Importar") o desde consola:
--   C:\xampp\mysql\bin\mysql.exe -u root < database.sql

-- Este archivo está en UTF-8: así se importan bien las tildes desde cualquier consola.
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS gestor_tareas
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE gestor_tareas;

CREATE TABLE IF NOT EXISTS tareas (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  nombre      VARCHAR(100) NOT NULL,
  descripcion TEXT,
  prioridad   ENUM('baja', 'media', 'alta') NOT NULL,
  estado      ENUM('pendiente', 'atrasada', 'completada') NOT NULL DEFAULT 'pendiente',
  fecha_fin   DATE NOT NULL
);

-- Datos de ejemplo. La primera tarea ya venció: al abrir index.php pasa a "atrasada".
INSERT INTO tareas (nombre, descripcion, prioridad, fecha_fin) VALUES
  ('Entregar informe de Programación Web', 'Subir el PDF al aula virtual', 'alta',  CURDATE() - INTERVAL 2 DAY),
  ('Estudiar normalización',               'Repasar 1FN, 2FN y 3FN',       'media', CURDATE() + INTERVAL 3 DAY),
  ('Instalar XAMPP en la laptop',          '',                             'baja',  CURDATE() + INTERVAL 7 DAY);
