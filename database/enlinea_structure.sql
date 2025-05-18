-- Archivo: database/enlinea_structure.sql
-- Estructura de base de datos para En Línea Telefónica

CREATE DATABASE IF NOT EXISTS enlinea_telefonica;
USE enlinea_telefonica;

-- Tabla de clientes/usuarios
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rut VARCHAR(12) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    direccion VARCHAR(200) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    telefono VARCHAR(15) NOT NULL,
    plan ENUM('normal', 'bueno', 'excelente', 'oferta') NOT NULL DEFAULT 'normal',
    estatus ENUM('Activo', 'Inactivo') NOT NULL DEFAULT 'Activo',
    usuario VARCHAR(50) UNIQUE NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    es_agente TINYINT(1) DEFAULT 0    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de roles
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('agente', 'cliente') NOT NULL,
    usuario_id INT NOT NULL,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES clientes(id) ON DELETE CASCADE,
    UNIQUE KEY (usuario_id, tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear usuario admin inicial (opcional)
-- Contraseña: Admin123$ (deberías cambiarla después de la instalación)
INSERT INTO clientes (rut, nombre, direccion, email, telefono, plan, usuario, contrasena, es_agente) 
VALUES ('12.345.678-9', 'Administrador', 'Dirección Admin', 'admin@enlinea.cl', '912345678', 'excelente', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

INSERT INTO roles (tipo, usuario_id) VALUES ('agente', LAST_INSERT_ID());

-- Índices adicionales para mejorar el rendimiento
CREATE INDEX idx_clientes_usuario ON clientes(usuario);
CREATE INDEX idx_clientes_email ON clientes(email);
CREATE INDEX idx_roles_tipo ON roles(tipo);