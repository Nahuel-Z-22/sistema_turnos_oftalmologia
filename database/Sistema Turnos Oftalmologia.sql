-- ============================================
-- SISTEMA DE GESTIÓN DE TURNOS - CLÍNICA OFTALMOLÓGICA
-- Base normalizada (3FN) · 10 tablas
-- Versión para XAMPP (MariaDB / MySQL)
-- ============================================
-- Cómo cargarlo: phpMyAdmin > pestaña "Importar" > elegir este archivo > Continuar.
--
-- Usuario inicial para el LOGIN:
--   usuario: admin   contraseña: admin123   (cambiarla después)
--
-- Glosario rápido:
--   PRIMARY KEY     = identificador único de cada fila (no se repite ni puede ser NULL)
--   AUTO_INCREMENT  = la base pone el número sola (1, 2, 3...)
--   FOREIGN KEY     = "apunta" a una fila de otra tabla; no deja guardar un id que no exista
--   UNIQUE          = ese valor no se puede repetir en la tabla
--   NOT NULL        = el campo es obligatorio
--   DEFAULT         = valor que se guarda si no mandamos ninguno
-- ============================================

DROP DATABASE IF EXISTS sistema_turnos_oftalmologia;   -- si la base ya existe, la borra (¡se pierden los datos!)
CREATE DATABASE sistema_turnos_oftalmologia            -- crea la base de nuevo, vacía
CHARACTER SET utf8mb4                                  -- permite acentos, ñ y cualquier carácter
COLLATE utf8mb4_spanish_ci;                            -- ordena y compara textos en español, sin distinguir mayúsculas

USE sistema_turnos_oftalmologia;                       -- todo lo que sigue se crea dentro de esta base


-- ============================================
-- TABLAS
-- (se crean en este orden porque una tabla con FOREIGN KEY
--  necesita que la tabla a la que apunta ya exista)
-- ============================================

-- Roles de los usuarios del sistema: Administrador, Recepción, Profesional
CREATE TABLE roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,             -- identificador del rol
    nombre VARCHAR(30) NOT NULL UNIQUE,                -- nombre del rol, no puede haber dos iguales
    descripcion VARCHAR(100)                           -- explicación opcional de qué puede hacer
);

-- Usuarios que pueden entrar al sistema con el LOGIN (personal de la clínica)
CREATE TABLE usuarios_sistema (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,         -- identificador del usuario
    usuario VARCHAR(50) NOT NULL UNIQUE,               -- nombre con el que inicia sesión, único
    clave_hash VARCHAR(255) NOT NULL,                  -- contraseña encriptada con password_hash() de PHP (nunca en texto plano)
    nombre_completo VARCHAR(150) NOT NULL,             -- nombre y apellido de la persona
    email VARCHAR(150) UNIQUE,                         -- email, no se puede repetir
    id_rol INT NOT NULL,                               -- qué rol tiene (apunta a roles)
    activo TINYINT(1) NOT NULL DEFAULT 1,              -- 1 = puede entrar, 0 = dado de baja (no puede iniciar sesión)
    ultimo_acceso DATETIME NULL,                       -- fecha y hora del último login, se actualiza desde PHP
    fecha_alta TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,   -- cuándo se creó, se completa solo
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol)      -- el rol tiene que existir en la tabla roles
);

-- Pacientes de la clínica (en la consigna se llaman "clientes")
CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,         -- identificador del paciente
    nombre VARCHAR(100) NOT NULL,                      -- nombre
    apellido VARCHAR(100) NOT NULL,                    -- apellido
    dni VARCHAR(15) NOT NULL UNIQUE,                   -- DNI, no puede haber dos pacientes con el mismo
    telefono VARCHAR(30),                              -- teléfono de contacto (opcional)
    email VARCHAR(150),                                -- email para confirmaciones (opcional)
    fecha_nacimiento DATE NULL,                        -- fecha de nacimiento (opcional)
    activo TINYINT(1) NOT NULL DEFAULT 1,              -- 1 = activo, 0 = baja lógica (no se borra para no perder sus turnos)
    fecha_alta TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP    -- cuándo se registró, se completa solo
);

-- Especialidades oftalmológicas (Retina, Glaucoma, Córnea...)
CREATE TABLE especialidades (
    id_especialidad INT AUTO_INCREMENT PRIMARY KEY,    -- identificador de la especialidad
    nombre VARCHAR(60) NOT NULL UNIQUE,                -- nombre, no se repite
    descripcion VARCHAR(150)                           -- descripción opcional
);

-- Médicos oftalmólogos que atienden en la clínica
CREATE TABLE profesionales (
    id_profesional INT AUTO_INCREMENT PRIMARY KEY,     -- identificador del profesional
    nombre VARCHAR(100) NOT NULL,                      -- nombre
    apellido VARCHAR(100) NOT NULL,                    -- apellido
    dni VARCHAR(15) NOT NULL UNIQUE,                   -- DNI, único
    matricula VARCHAR(30) NOT NULL UNIQUE,             -- matrícula profesional, única
    telefono VARCHAR(30),                              -- teléfono (opcional)
    email VARCHAR(150),                                -- email (opcional)
    id_especialidad INT NOT NULL,                      -- su especialidad (apunta a especialidades)
    activo TINYINT(1) NOT NULL DEFAULT 1,              -- 1 = atiende, 0 = baja lógica
    FOREIGN KEY (id_especialidad) REFERENCES especialidades(id_especialidad)   -- la especialidad tiene que existir
);

-- Consultas y estudios que ofrece la clínica
CREATE TABLE servicios (
    id_servicio INT AUTO_INCREMENT PRIMARY KEY,        -- identificador del servicio
    nombre VARCHAR(100) NOT NULL UNIQUE,               -- nombre del servicio, único
    descripcion VARCHAR(255),                          -- descripción que se muestra en el turno
    precio DECIMAL(10,2) NOT NULL,                     -- precio ACTUAL (hasta 8 enteros y 2 decimales)
    duracion_min SMALLINT NOT NULL DEFAULT 20,         -- cuántos minutos dura; sirve para calcular la hora de fin del turno
    activo TINYINT(1) NOT NULL DEFAULT 1               -- 1 = se ofrece, 0 = baja lógica
);

-- Qué servicios hace cada profesional (relación muchos a muchos)
CREATE TABLE profesional_servicio (
    id_profesional INT NOT NULL,                       -- el profesional
    id_servicio INT NOT NULL,                          -- un servicio que realiza
    PRIMARY KEY (id_profesional, id_servicio),         -- clave compuesta: el mismo par no se puede cargar dos veces
    FOREIGN KEY (id_profesional) REFERENCES profesionales(id_profesional) ON DELETE CASCADE,  -- si se borra el profesional, se borran estas filas
    FOREIGN KEY (id_servicio) REFERENCES servicios(id_servicio) ON DELETE CASCADE              -- si se borra el servicio, se borran estas filas
);

-- Estados posibles de un turno: 1 Pendiente, 2 Confirmado, 3 Atendido, 4 Cancelado
CREATE TABLE estados_turno (
    id_estado INT AUTO_INCREMENT PRIMARY KEY,          -- identificador del estado (el código PHP usa estos números)
    nombre VARCHAR(30) NOT NULL UNIQUE,                -- nombre del estado, único
    descripcion VARCHAR(100)                           -- qué significa
);

-- CABECERA del turno: quién, con quién, cuándo y en qué estado
CREATE TABLE turnos (
    id_turno INT AUTO_INCREMENT PRIMARY KEY,           -- número de turno
    id_cliente INT NOT NULL,                           -- el paciente (apunta a clientes)
    id_usuario INT NULL,                               -- quién lo cargó; NULL = lo reservó el paciente desde la web
    id_profesional INT NOT NULL,                       -- el médico que atiende (apunta a profesionales)
    id_estado INT NOT NULL DEFAULT 1,                  -- estado del turno; arranca en 1 = Pendiente
    fecha DATE NOT NULL,                               -- día del turno
    hora_inicio TIME NOT NULL,                         -- hora en que empieza
    hora_fin TIME NOT NULL,                            -- hora en que termina (inicio + duración de los servicios)
    observaciones VARCHAR(255),                        -- notas, por ejemplo el motivo de una cancelación
    fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,   -- cuándo se sacó el turno, se completa solo
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),                 -- sin CASCADE: no deja borrar un paciente con turnos
    FOREIGN KEY (id_usuario) REFERENCES usuarios_sistema(id_usuario),         -- si no es NULL, el usuario tiene que existir
    FOREIGN KEY (id_profesional) REFERENCES profesionales(id_profesional),    -- sin CASCADE: no deja borrar un médico con turnos
    FOREIGN KEY (id_estado) REFERENCES estados_turno(id_estado)               -- el estado tiene que existir
);

-- DETALLE del turno: los servicios que se hace el paciente en ese turno (uno o varios)
-- El TOTAL no se guarda: se calcula con SUM(cantidad * precio_unitario) agrupando por id_turno
CREATE TABLE detalle_turno (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,         -- identificador de la fila del detalle
    id_turno INT NOT NULL,                             -- a qué turno pertenece (apunta a turnos)
    id_servicio INT NOT NULL,                          -- qué servicio es (apunta a servicios)
    cantidad SMALLINT NOT NULL DEFAULT 1,              -- cuántas veces (casi siempre 1)
    precio_unitario DECIMAL(10,2) NOT NULL,            -- precio copiado del servicio AL SACAR el turno, así no cambia si después sube el precio
    FOREIGN KEY (id_turno) REFERENCES turnos(id_turno) ON DELETE CASCADE,      -- si se borra el turno, se borra su detalle
    FOREIGN KEY (id_servicio) REFERENCES servicios(id_servicio)                -- el servicio tiene que existir
);


-- ============================================
-- DATOS INICIALES
-- (se cargan en orden: primero las tablas que no apuntan a nadie)
-- ============================================

-- Roles (quedan con id 1, 2 y 3)
INSERT INTO roles (nombre, descripcion) VALUES
('Administrador', 'Acceso total, incluye el ABM de usuarios'),   -- id_rol = 1
('Recepción', 'Gestiona pacientes y turnos'),                    -- id_rol = 2
('Profesional', 'Consulta su agenda de turnos');                 -- id_rol = 3

-- Usuario administrador para el primer login
-- La clave es admin123, guardada como hash (se verifica con password_verify() en PHP)
INSERT INTO usuarios_sistema (usuario, clave_hash, nombre_completo, email, id_rol) VALUES
('admin', '$2y$10$1bm9sGVkArnVnNKibyAh3.SEPxonenw09tbjnmXtgPk7dzHHVPPUi', 'Administrador del sistema', 'admin@clinica.local', 1);   -- rol 1 = Administrador

-- Estados del turno (el orden importa: el código PHP usa estos id)
INSERT INTO estados_turno (nombre, descripcion) VALUES
('Pendiente', 'Reservado, falta confirmar'),         -- id_estado = 1
('Confirmado', 'Confirmado por recepción'),          -- id_estado = 2
('Atendido', 'El paciente ya fue atendido'),         -- id_estado = 3
('Cancelado', 'Cancelado: libera el horario');       -- id_estado = 4

-- Especialidades
INSERT INTO especialidades (nombre, descripcion) VALUES
('Retina', 'Enfermedades de retina y mácula'),                   -- id 1
('Glaucoma', 'Control y tratamiento del glaucoma'),              -- id 2
('Córnea', 'Córnea, queratocono y lentes de contacto'),          -- id 3
('Pediatría', 'Oftalmología infantil'),                          -- id 4
('General', 'Oftalmología general');                             -- id 5

-- Servicios con precio y duración en minutos (precios de ejemplo)
INSERT INTO servicios (nombre, descripcion, precio, duracion_min) VALUES
('Consulta oftalmológica', 'Control general de la vista', 25000, 20),                     -- id 1
('Fondo de ojo', 'Examen de retina con pupila dilatada', 18000, 30),                      -- id 2
('Tomografía OCT', 'Estudio de retina y nervio óptico, sin contacto', 45000, 30),         -- id 3
('Campo visual', 'Campo visual computarizado', 32000, 40),                                -- id 4
('Topografía corneal', 'Mapa de la superficie de la córnea', 30000, 20),                  -- id 5
('Adaptación de lentes de contacto', 'Prueba y adaptación de lentes', 28000, 45);         -- id 6

-- Profesionales (el último número es id_especialidad)
INSERT INTO profesionales (nombre, apellido, dni, matricula, telefono, email, id_especialidad) VALUES
('Laura', 'Méndez', '25111222', 'MN 45210', '11 4000-1001', 'lmendez@clinica.local', 1),  -- id 1 · Retina
('Andrés', 'Paz', '27333444', 'MN 49330', '11 4000-1002', 'apaz@clinica.local', 1),       -- id 2 · Retina
('Pablo', 'Ríos', '23555666', 'MN 38877', '11 4000-1003', 'prios@clinica.local', 2),      -- id 3 · Glaucoma
('Inés', 'Ferrer', '29777888', 'MN 51406', '11 4000-1004', 'iferrer@clinica.local', 3),   -- id 4 · Córnea
('Tomás', 'Vera', '31999000', 'MN 47019', '11 4000-1005', 'tvera@clinica.local', 4);      -- id 5 · Pediatría

-- Qué servicios hace cada profesional: (id_profesional, id_servicio)
INSERT INTO profesional_servicio (id_profesional, id_servicio) VALUES
(1, 1), (1, 2), (1, 3),     -- Méndez: consulta, fondo de ojo, OCT
(2, 1), (2, 2), (2, 3),     -- Paz: consulta, fondo de ojo, OCT
(3, 1), (3, 3), (3, 4),     -- Ríos: consulta, OCT, campo visual
(4, 1), (4, 5), (4, 6),     -- Ferrer: consulta, topografía, lentes de contacto
(5, 1), (5, 2);             -- Vera: consulta, fondo de ojo

-- Pacientes de ejemplo
INSERT INTO clientes (nombre, apellido, dni, telefono, email, fecha_nacimiento) VALUES
('Juan', 'Pérez', '30123456', '11 4567-8901', 'jperez@mail.com', '1985-03-12'),    -- id 1
('Ana', 'Gómez', '35902118', '11 5566-2010', 'agomez@mail.com', '1991-07-25'),     -- id 2
('Lucía', 'Sosa', '41230667', '11 6120-0934', 'lsosa@mail.com', '1998-11-02'),     -- id 3
('Martín', 'Díaz', '28441905', '11 4321-7788', 'mdiaz@mail.com', '1980-01-30'),    -- id 4
('Hugo', 'Romero', '22871334', '11 3988-4512', 'hromero@mail.com', '1972-05-18');  -- id 5

-- Turnos de ejemplo (cabecera)
-- Orden de los valores: paciente, usuario que lo cargó, profesional, estado, fecha, inicio, fin, observaciones
INSERT INTO turnos (id_cliente, id_usuario, id_profesional, id_estado, fecha, hora_inicio, hora_fin, observaciones) VALUES
(2, 1, 3, 3, '2026-11-16', '08:20:00', '08:40:00', NULL),                  -- turno 1 · Gómez con Ríos · Atendido
(3, 1, 1, 1, '2026-11-17', '10:00:00', '10:30:00', NULL),                  -- turno 2 · Sosa con Méndez · Pendiente
(4, 1, 3, 4, '2026-11-17', '11:20:00', '12:00:00', 'Avisó por teléfono'),  -- turno 3 · Díaz con Ríos · Cancelado
(1, NULL, 1, 1, '2026-11-18', '09:40:00', '11:00:00', 'Reserva online'),   -- turno 4 · Pérez con Méndez · reservado por la web (id_usuario NULL)
(5, 1, 4, 2, '2026-11-19', '09:00:00', '09:20:00', NULL);                  -- turno 5 · Romero con Ferrer · Confirmado

-- Detalle de cada turno: (turno, servicio, cantidad, precio en ese momento)
INSERT INTO detalle_turno (id_turno, id_servicio, cantidad, precio_unitario) VALUES
(1, 1, 1, 25000),     -- turno 1: consulta
(2, 2, 1, 18000),     -- turno 2: fondo de ojo
(3, 4, 1, 32000),     -- turno 3: campo visual
(4, 1, 1, 25000),     -- turno 4: consulta
(4, 2, 1, 18000),     -- turno 4: fondo de ojo
(4, 3, 1, 45000),     -- turno 4: OCT  -> total del turno 4 = 88000
(5, 1, 1, 25000);     -- turno 5: consulta
