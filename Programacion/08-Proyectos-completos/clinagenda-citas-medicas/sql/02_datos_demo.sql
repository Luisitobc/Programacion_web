-- ClinAgenda · datos para la demostración
-- Todo es inventado: no hay personas reales aquí.
-- Las contraseñas de las cuentas están en INSTRUCCIONES.md; aquí solo va su hash.

USE clinagenda;

INSERT INTO clinicas (id, nombre, direccion, telefono, hora_apertura, hora_cierre) VALUES
 (1, 'Clínica Centro', 'Av. Primera y Olmedo', '032960111', '08:00', '18:00'),
 (2, 'Clínica Norte',  'Av. Norte 123',        '032960222', '08:00', '18:00'),
 (3, 'Clínica Sur',    'Vía al Sur km 4',      '032960333', '09:00', '17:00');

INSERT INTO usuarios (id, nombre, usuario, clave_hash, rol, clinica_id, modo_simple) VALUES
 (1, 'Administración',   'admin',    '$2y$10$6Gt3aV1Z5IK8q6Fj/Ssc9eguIj62uWtt.bG/1nZtsE7q5SjVCTJ3e', 'admin',     NULL, 0),
 (2, 'Dra. Ana Paredes', 'aparedes', '$2y$10$AsIsOlHxR9PROFWdUCmN8ebWqLUa76LCVdizwkdrkRSinTuDC88Ie', 'doctora',   NULL, 0),
 (3, 'Rosa Chimbo',      'rosa',     '$2y$10$rLadXdlXJzJwUDt.ZeSXZuAQWOP5u8dpJfB11lYVzBDPSN8yA3FuS', 'recepcion', 1,    1),
 (4, 'María Tenorio',    'maria',    '$2y$10$kpFvDKg1G3TyzLTkt5NmR.SyHTFSq5F10ElCqLBrZ82ZyRSMM31Ae', 'recepcion', 1,    0),
 (5, 'Tania Quinga',     'tania',    '$2y$10$sv13QkYm7jfXsiAlF0Q/B.4exWUEyx8TjUvrneRKleQQX6DW7.LN6', 'recepcion', 3,    0);

INSERT INTO medicos (id, nombres, especialidad, duracion_cita_min) VALUES
 (1,  'Dra. Ana Paredes',    'Medicina general', 30),
 (2,  'Dr. Luis Salazar',    'Traumatología',    30),
 (3,  'Dra. Marta Cevallos', 'Pediatría',        20),
 (4,  'Dr. Jorge Mendoza',   'Dermatología',     30),
 (5,  'Dra. Elena Bustos',   'Ginecología',      40),
 (6,  'Dr. Pablo Vera',      'Medicina general', 30),
 (7,  'Dra. Sofía Zambrano', 'Cardiología',      40),
 (8,  'Dr. Diego Peña',      'Medicina general', 30),
 (9,  'Dra. Rocío Álvarez',  'Pediatría',        20),
 (10, 'Dr. Hugo Tapia',      'Oftalmología',     20),
 (11, 'Lcda. Nelly Guamán',  'Nutrición',        40),
 (12, 'Dr. Iván Moreta',     'Medicina general', 30);

-- Horario normal de cada clínica, con la pausa del almuerzo.
-- Es una tabla de paso: solo sirve para no escribir cien filas a mano.
CREATE TEMPORARY TABLE bloques (clinica_id INT, dia TINYINT, ini TIME, fin TIME);
INSERT INTO bloques VALUES
 (1,1,'08:00','13:00'),(1,1,'14:00','18:00'),(1,2,'08:00','13:00'),(1,2,'14:00','18:00'),
 (1,3,'08:00','13:00'),(1,3,'14:00','18:00'),(1,4,'08:00','13:00'),(1,4,'14:00','18:00'),
 (1,5,'08:00','13:00'),(1,5,'14:00','18:00'),(1,6,'08:00','12:00'),
 (2,1,'08:00','13:00'),(2,1,'14:00','18:00'),(2,2,'08:00','13:00'),(2,2,'14:00','18:00'),
 (2,3,'08:00','13:00'),(2,3,'14:00','18:00'),(2,4,'08:00','13:00'),(2,4,'14:00','18:00'),
 (2,5,'08:00','13:00'),(2,5,'14:00','18:00'),(2,6,'08:00','12:00'),
 (3,1,'09:00','13:00'),(3,1,'14:00','17:00'),(3,2,'09:00','13:00'),(3,2,'14:00','17:00'),
 (3,3,'09:00','13:00'),(3,3,'14:00','17:00'),(3,4,'09:00','13:00'),(3,4,'14:00','17:00'),
 (3,5,'09:00','13:00'),(3,5,'14:00','17:00');

-- Qué médico atiende en qué clínica con el horario completo de esa clínica.
INSERT INTO medico_horario (medico_id, clinica_id, dia_semana, hora_inicio, hora_fin)
SELECT p.medico_id, b.clinica_id, b.dia, b.ini, b.fin
FROM (SELECT 1 medico_id, 1 clinica_id UNION ALL SELECT 1, 2 UNION ALL SELECT 3, 1
      UNION ALL SELECT 4, 1  UNION ALL SELECT 5, 1  UNION ALL SELECT 6, 2
      UNION ALL SELECT 7, 2  UNION ALL SELECT 8, 2  UNION ALL SELECT 9, 3
      UNION ALL SELECT 10, 3 UNION ALL SELECT 11, 3 UNION ALL SELECT 12, 1
      UNION ALL SELECT 12, 2 UNION ALL SELECT 12, 3) p
JOIN bloques b ON b.clinica_id = p.clinica_id;

DROP TEMPORARY TABLE bloques;

-- El caso del enunciado: el Dr. Salazar dijo "lunes a miércoles en la
-- mañana estoy disponible" y las dos clínicas lo anotaron. Por eso los
-- lunes y miércoles aparece libre en Centro y en Sur a la misma hora.
INSERT INTO medico_horario (medico_id, clinica_id, dia_semana, hora_inicio, hora_fin) VALUES
 (2, 1, 1, '08:00', '13:00'), (2, 1, 2, '08:00', '13:00'), (2, 1, 3, '08:00', '13:00'),
 (2, 3, 1, '09:00', '13:00'), (2, 3, 3, '09:00', '13:00'),
 (2, 3, 4, '14:00', '17:00'), (2, 3, 5, '14:00', '17:00');

INSERT INTO feriados (fecha, clinica_id, descripcion) VALUES
 (CONCAT(YEAR(CURDATE()), '-12-25'),     NULL, 'Navidad'),
 (CONCAT(YEAR(CURDATE()) + 1, '-01-01'), NULL, 'Año nuevo');

INSERT INTO pacientes (id, tipo_documento, documento, nombres, apellidos, nombre_busqueda,
                       fecha_nacimiento, telefono, correo, representante, representante_telefono, nota) VALUES
 (1, 'CEDULA', '0602154874', 'Carlos', 'Yánez', 'CARLOS YANEZ', '1985-04-12', '0991234567', 'carlos.yanez@correo.com', NULL, NULL, NULL),
 (2, 'CEDULA', '0601122336', 'Juan', 'Pérez', 'JUAN PEREZ', '1979-11-03', '0987654321', NULL, NULL, NULL, NULL),
 (3, 'CEDULA', '1711223345', 'Juan', 'Pérez', 'JUAN PEREZ', '1996-02-20', '0976543210', NULL, NULL, NULL, 'Se llama igual que PAC-000002, pero es otra persona.'),
 (4, 'CEDULA', '0604433227', 'María', 'López', 'MARIA LOPEZ', '1990-07-30', '0998887766', NULL, NULL, NULL, NULL),
 (5, 'CEDULA', '0603344557', 'Rosa Elena', 'Veloz', 'ROSA ELENA VELOZ', '1941-03-09', NULL, NULL, 'Hija: Patricia Veloz', '0998877665', 'No usa celular. Llamar a la hija.'),
 (6, 'PASAPORTE', 'AV8821345', 'Yeison', 'Rodríguez', 'YEISON RODRIGUEZ', '1993-09-15', '0962223344', NULL, NULL, NULL, 'Paciente extranjero.'),
 (7, 'SIN_DOCUMENTO', NULL, 'Mateo', 'Quishpe', 'MATEO QUISHPE', CURDATE() - INTERVAL 1 MONTH, NULL, NULL, 'Madre: Diana Quishpe', '0993334455', 'Recién nacido, todavía sin cédula.'),
 (8, 'SIN_DOCUMENTO', NULL, 'Valentina', 'Guamán', 'VALENTINA GUAMAN', '2022-08-14', NULL, NULL, 'Abuela: Carmen Guamán', '0981112233', 'La trae la abuela. Completar la cédula en la próxima visita.'),
 (9, 'CEDULA', '0609988779', 'Emilia', 'Caiza', 'EMILIA CAIZA', '2019-05-18', NULL, NULL, 'Padre: Marco Caiza', '0995556677', NULL),
 (10, 'CEDULA', '1714455662', 'Pedro', 'Shigla', 'PEDRO SHIGLA', '2001-12-01', '0984445566', NULL, NULL, NULL, NULL);

-- Fechas relativas a hoy, para que la demo siempre tenga citas por delante.
-- DAYOFWEEK: 1 domingo ... 7 sábado. @d1 y @d2 son los dos próximos días hábiles.
SET @d1 = CURDATE() + INTERVAL (CASE DAYOFWEEK(CURDATE()) WHEN 6 THEN 3 WHEN 7 THEN 2 ELSE 1 END) DAY;
SET @d2 = @d1 + INTERVAL (CASE DAYOFWEEK(@d1) WHEN 6 THEN 3 ELSE 1 END) DAY;
SET @lunes = CURDATE() + INTERVAL ((9 - DAYOFWEEK(CURDATE())) % 7 + IF(DAYOFWEEK(CURDATE()) = 2, 7, 0)) DAY;
SET @antes = CURDATE() - INTERVAL 7 DAY;

INSERT INTO citas (clinica_id, medico_id, paciente_id, inicio, fin, estado, motivo, creada_por) VALUES
 (1, 1, 1, TIMESTAMP(@d1, '08:00'), TIMESTAMP(@d1, '08:30'), 'AGENDADA', 'Control de presión', 3),
 (1, 1, 2, TIMESTAMP(@d1, '08:30'), TIMESTAMP(@d1, '09:00'), 'AGENDADA', 'Dolor de cabeza', 3),
 (1, 1, 4, TIMESTAMP(@d1, '09:00'), TIMESTAMP(@d1, '09:30'), 'AGENDADA', 'Chequeo general', 4),
 (1, 3, 9, TIMESTAMP(@d1, '08:00'), TIMESTAMP(@d1, '08:20'), 'AGENDADA', 'Control de niña sana', 3),
 (1, 3, 7, TIMESTAMP(@d1, '08:20'), TIMESTAMP(@d1, '08:40'), 'AGENDADA', 'Control de recién nacido', 3),
 (1, 5, 4, TIMESTAMP(@d2, '10:00'), TIMESTAMP(@d2, '10:40'), 'AGENDADA', 'Control anual', 4),
 (3, 9, 8, TIMESTAMP(@d2, '09:00'), TIMESTAMP(@d2, '09:20'), 'AGENDADA', 'Control de crecimiento', 5),
 -- El Dr. Salazar ya tiene esta cita en la Sur. Si alguien de Centro intenta
 -- darle la misma hora ese lunes, el sistema no lo deja.
 (3, 2, 6, TIMESTAMP(@lunes, '10:00'), TIMESTAMP(@lunes, '10:30'), 'AGENDADA', 'Dolor de rodilla', 5),
 (1, 1, 1, TIMESTAMP(@antes, '08:00'), TIMESTAMP(@antes, '08:30'), 'ATENDIDA', 'Control anterior', 3),
 (1, 1, 2, TIMESTAMP(@antes, '08:30'), TIMESTAMP(@antes, '09:00'), 'NO_ASISTIO', 'Revisión de exámenes', 3);

-- Una cita cancelada: la hora quedó libre, pero la cita sigue ahí con su motivo.
INSERT INTO citas (clinica_id, medico_id, paciente_id, inicio, fin, estado, motivo,
                   motivo_cancelacion, cancelada_por, cancelada_en, creada_por) VALUES
 (1, 1, 5, TIMESTAMP(@d1, '09:30'), TIMESTAMP(@d1, '10:00'), 'CANCELADA', 'Chequeo general',
  'La hija llamó: la paciente no puede ir ese día', 3, NOW(), 3);

INSERT INTO auditoria (usuario_id, usuario_nombre, accion, entidad, detalle) VALUES
 (NULL, 'Sistema', 'CARGA', 'sistema', 'Se cargaron los datos de demostración.');
