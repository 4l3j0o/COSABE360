-- =====================================================================
-- CSB 360 - Base de datos MySQL local (XAMPP / MariaDB)
-- Jornada 3 - Actividad 06 - Grupo 7
--
-- Ejecutar una sola vez con:  npm run crear-db
-- (o importar este archivo desde phpMyAdmin)
-- =====================================================================

CREATE DATABASE IF NOT EXISTS cosabe360_movil
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE cosabe360_movil;

-- ---------------------------------------------------------------------
-- Tabla de usuarios (reemplaza el arreglo de usuarios del login)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre        VARCHAR(120) NOT NULL,
  email         VARCHAR(150) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  curso         VARCHAR(80)  NOT NULL DEFAULT '',
  codigo        VARCHAR(20)  NOT NULL DEFAULT '',
  creado_en     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuarios_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Entidad principal: pendientes
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pendientes (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id     INT UNSIGNED NOT NULL,
  titulo         VARCHAR(150) NOT NULL,
  materia        VARCHAR(80)  NOT NULL,
  fecha          DATE         NOT NULL,
  prioridad      ENUM('alta','media','baja') NOT NULL DEFAULT 'media',
  hecho          TINYINT(1)   NOT NULL DEFAULT 0,
  creado_en      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_pendientes_usuario (usuario_id, hecho, fecha),
  CONSTRAINT fk_pendientes_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
