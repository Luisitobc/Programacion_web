-- ClinAgenda · esquema de la base
-- Funciona en MySQL 8 y en el MariaDB que trae XAMPP.
--
-- No hace falta importarlo a mano: lib/instalador.php lo ejecuta solo la
-- primera vez que alguien abre el sistema. Si prefiere hacerlo usted, use
-- phpMyAdmin o:
--   mysql -u root --default-character-set=utf8mb4 < sql/01_esquema.sql
--
-- Tres reglas que se respetan en todo el sistema:
--   1. Nunca se borra nada. Las cosas se cancelan o se marcan inactivas.
--   2. Todo cambio pasa por una transacción.
--   3. Todo cambio queda escrito en la tabla auditoria.

CREATE DATABASE IF NOT EXISTS clinagenda CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE clinagenda;

CREATE TABLE clinicas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(80) NOT NULL UNIQUE,
  direccion VARCHAR(160) NULL,
  telefono VARCHAR(20) NULL,
  hora_apertura TIME NOT NULL DEFAULT '08:00:00',
  hora_cierre   TIME NOT NULL DEFAULT '18:00:00',
  activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  usuario VARCHAR(40) NOT NULL UNIQUE,
  clave_hash VARCHAR(255) NOT NULL,
  rol ENUM('admin','doctora','recepcion') NOT NULL,
  clinica_id INT NULL,                    -- solo recepción trabaja en una clínica fija
  modo_simple TINYINT(1) NOT NULL DEFAULT 0,  -- letra y botones grandes
  activo TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (clinica_id) REFERENCES clinicas(id)
) ENGINE=InnoDB;

CREATE TABLE medicos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombres VARCHAR(120) NOT NULL,
  especialidad VARCHAR(80) NULL,
  duracion_cita_min INT NOT NULL DEFAULT 30,
  activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- Un médico puede atender en varias clínicas, cada una con su horario.
-- Que una clínica "tenga" a un médico se deduce de aquí: no hay otra tabla.
CREATE TABLE medico_horario (
  id INT AUTO_INCREMENT PRIMARY KEY,
  medico_id INT NOT NULL,
  clinica_id INT NOT NULL,
  dia_semana TINYINT NOT NULL,            -- 1 lunes ... 7 domingo
  hora_inicio TIME NOT NULL,
  hora_fin TIME NOT NULL,
  UNIQUE KEY uk_horario (medico_id, clinica_id, dia_semana, hora_inicio),
  FOREIGN KEY (medico_id)  REFERENCES medicos(id),
  FOREIGN KEY (clinica_id) REFERENCES clinicas(id)
) ENGINE=InnoDB;

-- clinica_id NULL quiere decir que el feriado es para las tres.
CREATE TABLE feriados (
  id INT AUTO_INCREMENT PRIMARY KEY,
  fecha DATE NOT NULL,
  clinica_id INT NULL,
  descripcion VARCHAR(120) NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  KEY ix_fecha (fecha),
  FOREIGN KEY (clinica_id) REFERENCES clinicas(id)
) ENGINE=InnoDB;

-- El código que ve la gente (PAC-000123) sale del id, así que no se guarda
-- aparte: nunca cambia y nunca se repite.
CREATE TABLE pacientes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tipo_documento ENUM('CEDULA','PASAPORTE','SIN_DOCUMENTO') NOT NULL,
  documento VARCHAR(20) NULL,             -- NULL si no tiene
  nombres   VARCHAR(120) NOT NULL,
  apellidos VARCHAR(120) NOT NULL,
  nombre_busqueda VARCHAR(250) NOT NULL,  -- mayúsculas, sin tildes, un espacio
  fecha_nacimiento DATE NULL,
  telefono VARCHAR(20) NULL,
  correo   VARCHAR(120) NULL,
  representante VARCHAR(160) NULL,        -- para niños y adultos mayores
  representante_telefono VARCHAR(20) NULL,
  nota VARCHAR(255) NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  -- MySQL trata cada NULL como distinto: puede haber cien pacientes sin
  -- documento, pero nunca dos con la misma cédula.
  UNIQUE KEY uk_documento (tipo_documento, documento),
  KEY ix_busqueda (nombre_busqueda),
  KEY ix_telefono (telefono)
) ENGINE=InnoDB;

CREATE TABLE citas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  clinica_id  INT NOT NULL,
  medico_id   INT NOT NULL,
  paciente_id INT NOT NULL,
  inicio DATETIME NOT NULL,
  fin    DATETIME NOT NULL,
  estado ENUM('AGENDADA','ATENDIDA','CANCELADA','NO_ASISTIO') NOT NULL DEFAULT 'AGENDADA',
  motivo VARCHAR(160) NULL,
  motivo_cancelacion VARCHAR(160) NULL,
  cancelada_por INT NULL,
  cancelada_en DATETIME NULL,
  creada_por INT NOT NULL,
  creada_en  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  -- Viaja en el formulario. Si la misma cita llega dos veces (doble clic, o
  -- la cola sin conexión la reenvía), la segunda choca aquí y no se duplica.
  token VARCHAR(32) NULL,

  -- La pieza central. Una sola cita viva por médico y hora, y una sola por
  -- paciente y hora. Si la cita se cancela, el slot queda en NULL y la hora
  -- vuelve a estar libre sin borrar nada.
  -- uk_medico_slot no lleva clinica_id a propósito: la hora es del médico,
  -- no del local, así que no puede estar en dos clínicas a la vez.
  slot_medico   DATETIME GENERATED ALWAYS AS (IF(estado = 'CANCELADA', NULL, inicio)) STORED,
  slot_paciente DATETIME GENERATED ALWAYS AS (IF(estado = 'CANCELADA', NULL, inicio)) STORED,
  UNIQUE KEY uk_medico_slot   (medico_id, slot_medico),
  UNIQUE KEY uk_paciente_slot (paciente_id, slot_paciente),
  UNIQUE KEY uk_token (token),
  KEY ix_agenda (clinica_id, inicio),
  KEY ix_medico_dia (medico_id, inicio),
  FOREIGN KEY (clinica_id)  REFERENCES clinicas(id),
  FOREIGN KEY (medico_id)   REFERENCES medicos(id),
  FOREIGN KEY (paciente_id) REFERENCES pacientes(id),
  FOREIGN KEY (creada_por)  REFERENCES usuarios(id),
  FOREIGN KEY (cancelada_por) REFERENCES usuarios(id)
) ENGINE=InnoDB;

-- Solo se le agregan filas. No hay pantalla ni función para cambiarla.
CREATE TABLE auditoria (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NULL,
  usuario_nombre VARCHAR(120) NOT NULL,
  accion  VARCHAR(40) NOT NULL,           -- AGENDAR, CANCELAR, REAGENDAR, CREAR_PACIENTE...
  entidad VARCHAR(40) NOT NULL,           -- citas, pacientes, usuarios...
  entidad_id INT NULL,
  detalle VARCHAR(255) NOT NULL,          -- la frase que lee una persona
  antes   JSON NULL,
  despues JSON NULL,
  ip VARCHAR(45) NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_entidad (entidad, entidad_id),
  KEY ix_fecha (creado_en)
) ENGINE=InnoDB;
