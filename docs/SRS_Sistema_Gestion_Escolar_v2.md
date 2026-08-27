# SRS v2 — Sistema de Gestión Escolar Personalizado
## Unidad Educativa San Bernardo — Esquema corregido y consolidado

> **Entorno de Base de Datos:** MySQL 8.0+ administrado mediante **phpMyAdmin** (XAMPP/Laragon).
> Motor InnoDB · `utf8mb4` / `utf8mb4_unicode_ci` · FK habilitadas · eliminación lógica (`deleted_at`).
> Los bloques SQL del Anexo A pueden ejecutarse desde la pestaña **SQL** o **Importar** de phpMyAdmin.

---

## 0. Entorno de Trabajo — MySQL + phpMyAdmin

| Ítem | Valor |
|------|-------|
| Gestor BD | **MySQL 8.0+** |
| Administración | **phpMyAdmin** (XAMPP / Laragon) |
| Nombre de BD | `sanber` |
| Charset / Collation | `utf8mb4` / `utf8mb4_unicode_ci` |
| Motor | InnoDB |
| Eliminación | Lógica (`deleted_at`) en tablas principales |

### Configuración Laravel `.env`
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sanber
DB_USERNAME=root
DB_PASSWORD=
```

> **Nota Laravel:** el esquema usa `correo` / `nombre` / `apellido` (no las convenciones
> `email` / `name`). El modelo `User` ya está adaptado en la rama `HU01-inicio-de-sesion`.

### ⚠️ Orden de ejecución del SQL (Anexo A)
Por la FK `monthly_payments.factura_id → electronic_invoices`, **`electronic_invoices`
debe crearse ANTES que `monthly_payments`** (en el diagrama v1 aparecía al revés).
El Anexo A ya está ordenado respetando dependencias.

---

## 1. Registro de Correcciones (v1 → v2)

| # | Tipo | Corrección | Estado |
|---|------|------------|--------|
| C01 | Backlog | Totales por sprint recalculados (v1 tenía sumas incorrectas). Total real: **178 pts** | ✅ Aplicado |
| C02 | DB/HU-01 | `users`: agregados `intentos_fallidos` y `bloqueado_hasta` (bloqueo tras 5 intentos) | ✅ Aplicado |
| C03 | DB/HU-13 | `sections.responsable_id`: docente responsable del paralelo | ✅ Aplicado |
| C04 | DB/HU-03 | Tabla `user_roles` **eliminada** → `users.rol_id` (1 usuario = 1 rol). Criterio y BD ahora coinciden | ✅ Aplicado |
| C05 | DB | `año_nacimiento` renombrado a `anio_nacimiento` (sin ñ en identificadores) | ✅ Aplicado |
| C06 | DB | `complemento NOT NULL DEFAULT ''` en students/parents_guardians/teachers: los NULL burlan índices UNIQUE en MySQL | ✅ Aplicado |
| C07 | DB | `uk_correo` UNIQUE (nullable) en students y parents_guardians (exigía HU-08) | ✅ Aplicado |
| C08 | DB | `announcements.fecha_publicacion` nullable sin default (BORRADOR sin fecha) | ✅ Aplicado |
| C09 | SFE | Nuevas tablas: `daily_cufd`, `significant_events`, `invoice_sequences` (contingencia y numeración SIN) | ✅ Aplicado |
| C10 | SFE/RNF11 | Trazabilidad pago↔factura vía `monthly_payments.factura_id` | ✅ Aplicado |
| C11 | RUDE | `student_rude_records` ampliado (turno, transporte, zona, procedencia, idioma originario, salud/discapacidad); HU-TRIB-04 reestimada a 8 pts | ✅ Aplicado |
| C12 | RF31 | Sin HU que lo cubra: creada **HU-36** (datos médicos, Épica 2, Sprint 2) | ✅ Aplicado |
| C13 | RF26 | Sin HU explícita: creada **HU-TRIB-05** (RC-IVA, Épica 11, Sprint 6) | ✅ Aplicado |
| C14 | RF30 | Fotografía agregada a criterios de HU-06 | ✅ Aplicado |

## 2. Pendientes menores (no bloquean)

| Detalle | Decisión sugerida |
|---------|-------------------|
| `teachers.correo` sin UNIQUE (students/parents sí lo tienen) | Agregar UNIQUE en HU-10 |
| Encabezado v1 decía "eliminación lógica por campo `activo`" | La implementación real es `deleted_at` + enums `estado` |
| Migración Laravel `grade_scores` (singular) vs doc `grades_scores` (plural) | Unificar nombre en Sprint 4 |
| `idx_correo` en `users` redundante (el UNIQUE ya indexa) | Cosmético; opcional quitar |

---

## 3. Requisitos Funcionales (RF)

| Código | Descripción | Categoría |
|--------|-------------|-----------|
| RF01 | Iniciar sesión mediante usuario y contraseña | Auth |
| RF02 | Administrar usuarios (registrar, modificar, activar, desactivar) | Usuarios |
| RF03 | Asignar roles a los usuarios (1 usuario = 1 rol) | Usuarios |
| RF04 | Registrar estudiantes con datos personales | Estudiantes |
| RF05 | Modificar y consultar información de estudiantes | Estudiantes |
| RF06 | Registrar padres o tutores | Padres/Tutores |
| RF07 | Relacionar estudiantes con padres/tutores | Padres/Tutores |
| RF08 | Registrar docentes | Docentes |
| RF09 | Administrar niveles, grados y paralelos | Catálogos académicos |
| RF10 | Administrar materias | Catálogos académicos |
| RF11 | Asignar docentes a materias y cursos | Catálogos académicos |
| RF12 | Registrar inscripciones y matrículas | Inscripciones/Matrículas |
| RF13 | Registrar calificaciones | Calificaciones |
| RF14 | Consultar calificaciones | Calificaciones |
| RF15 | Registrar asistencia | Asistencia |
| RF16 | Consultar historial de asistencia | Asistencia |
| RF17 | Administrar horarios | Horarios |
| RF18 | Publicar comunicados | Comunicados |
| RF19 | Generar reportes | Reportes |
| RF20 | Controlar acceso según permisos del usuario | Seguridad |
| RF21 | Almacenar historial de operaciones importantes (auditoría) | Auditoría |
| RF22 | Activar/desactivar usuarios | Usuarios |
| RF23 | Emitir facturas electrónicas SFE, generar CUF/CUFD/CUIS, enviar XML a SIN | Facturación SFE |
| RF24 | Facturar servicios oficiales EXENTOS (IVA 0%, IT 0%) con leyendas obligatorias | Facturación SFE |
| RF25 | Contingencia facturación offline (CUF precalculado, cola reenvío auto) | Facturación SFE |
| RF26 | Calcular/registrar retenciones RC-IVA 13% + comprobantes | Facturación SFE |
| RF27 | Capturar formulario RUDE RM 0684/2025 completo (secciones I-VI) con validaciones | RUDE |
| RF28 | Almacenar código RUDE (solo lectura) + código SIE UE, exportar formato oficial MinEdu | RUDE |
| RF29 | Catálogo productos/servicios con tratamiento tributario | Facturación SFE |
| RF30 | Registrar fotografía del estudiante (imagen, tamaño máx. configurable) | Estudiantes |
| RF31 | Registrar datos médicos (grupo sanguíneo, alergias, medicamentos, contacto emergencia) | Estudiantes |
| RF32 | Control de deudas pendientes de mensualidades | Inscripciones/Matrículas |

## 4. Requisitos No Funcionales (RNF)

| Código | Descripción | Categoría |
|--------|-------------|-----------|
| RNF01 | Interfaz sencilla y fácil de utilizar | Usabilidad |
| RNF02 | Proteger información mediante autenticación | Seguridad |
| RNF03 | Contraseñas almacenadas con hash bcrypt (mínimo 8 caracteres) | Seguridad |
| RNF04 | Controlar permisos según rol asignado | Seguridad |
| RNF05 | Búsquedas rápidas de información | Rendimiento |
| RNF06 | Mantener integridad de datos almacenados | Fiabilidad |
| RNF07 | Copias de seguridad de información (export .sql desde phpMyAdmin) | Fiabilidad |
| RNF08 | Navegadores modernos (Chrome, Firefox, Edge, Safari) | Portabilidad |
| RNF09 | Diseño responsivo (PC, tablets, celulares) | Portabilidad/Usabilidad |
| RNF10 | Preparado para futuras ampliaciones | Mantenibilidad/Escalabilidad |
| RNF11 | Integridad y trazabilidad facturas electrónicas (CUF inmutable, log envíos, contingencia auditable) | Facturación SFE |
| RNF12 | Validación tiempo real formatos tributarios bolivianos (NIT, CI, CUF/CUFD/CUIS) | Facturación SFE |
| RNF13 | Facturación < 3 seg online, emisión offline inmediata | Rendimiento |
| RNF14 | Cumplir esquema XSD Factura Sector Educativo vigente | Facturación SFE |

---

## 5. Product Backlog (totales corregidos)

### Épica 1: Autenticación y Seguridad — Sprint 1
| ID | Historia de Usuario | Prioridad | Puntos | Estado |
|----|--------------------|-----------|--------|--------|
| HU-01 | Como usuario, quiero iniciar sesión con usuario y contraseña, para acceder al sistema según mi rol. | Alto | 5 | Pendiente |
| HU-02 | Como administrador, quiero gestionar usuarios (crear, editar, activar, desactivar), para controlar el acceso al sistema. | Alto | 5 | Pendiente |
| HU-03 | Como administrador, quiero asignar roles a los usuarios (un rol por usuario), para definir sus permisos en el sistema. | Alto | 3 | Pendiente |
| HU-04 | Como usuario, quiero que mi contraseña se almacene de forma segura (hash bcrypt, mín. 8 caracteres), para proteger mi credencial. | Alto | 3 | Pendiente |
| HU-05 | Como usuario, quiero que el sistema valide mis permisos según mi rol, para acceder solo a mis funciones. | Alto | 3 | Pendiente |

### Épica 2: Gestión de Información Personal — Sprint 2
| ID | Historia de Usuario | Prioridad | Puntos | Estado |
|----|--------------------|-----------|--------|--------|
| HU-06 | Como secretaría, quiero registrar estudiantes con sus datos personales y fotografía, para tener su información en el sistema. *(RF30)* | Alto | 5 | Pendiente |
| HU-07 | Como secretaría, quiero modificar y consultar información de estudiantes, para mantener datos actualizados. | Alto | 3 | Pendiente |
| HU-08 | Como secretaría, quiero registrar padres o tutores (correo único), para asociarlos a los estudiantes. | Alto | 3 | Pendiente |
| HU-09 | Como secretaría, quiero relacionar estudiantes con sus padres/tutores, para vincular la información familiar. | Alto | 3 | Pendiente |
| HU-10 | Como secretaría, quiero registrar docentes con sus datos, para tener su información disponible. | Alto | 3 | Pendiente |
| HU-36 | Como secretaría, quiero registrar los datos médicos de cada estudiante (grupo sanguíneo, alergias, medicamentos, contacto de emergencia), para actuar ante emergencias. *(RF31)* | Medio | 3 | Pendiente |

### Épica 3: Configuración Académica — Sprints 1-2
| ID | Historia de Usuario | Prioridad | Puntos | Sprint | Estado |
|----|--------------------|-----------|--------|--------|--------|
| HU-11 | Administrar niveles educativos (Inicial, Primaria, Secundaria). | Alto | 3 | 1 | Pendiente |
| HU-12 | Administrar grados por nivel. | Alto | 3 | 1 | Pendiente |
| HU-13 | Administrar paralelos por grado con docente responsable (`sections.responsable_id`). | Alto | 3 | 1 | Pendiente |
| HU-14 | Administrar materias. | Alto | 3 | 2 | Pendiente |
| HU-15 | Configurar períodos académicos. | Alto | 3 | 2 | Pendiente |
| HU-16 | Asignar docentes a materias y cursos. | Alto | 5 | 2 | Pendiente |

### Épica 4: Inscripciones y Matrículas — Sprint 3
HU-17 (5), HU-18 (5), HU-19 (5), HU-20 (3)

### Épica 5: Evaluaciones y Calificaciones — Sprint 4
HU-21 (5), HU-22 (5), HU-23 (3), HU-24 (3), HU-25 (3)

### Épica 6: Asistencia — Sprint 4
HU-26 (5), HU-27 (3)

### Épica 7: Horarios — Sprint 4
HU-28 (5), HU-29 (3)

### Épica 8: Comunicados — Sprint 5
HU-30 (5), HU-31 (3)

### Épica 9: Reportes — Sprint 5
HU-32 (5), HU-33 (5), HU-34 (3)

### Épica 10: Auditoría — Sprint 5
HU-35 (3)

### Épica 11: Cumplimiento Tributario y RUDE
| ID | Historia de Usuario | Prioridad | Puntos | Sprint | Estado |
|----|--------------------|-----------|--------|--------|--------|
| HU-TRIB-01 | Como administrador, quiero configurar parámetros tributarios fijos (CON_FINES_LUCRO, exentos IVA/IT TRUE, modalidad COMPUTARIZADA_EN_LINEA, leyendas obligatorias), para cumplir normativa S.A. NIT 408460020. | Alto | 5 | 1 | Pendiente |
| HU-TRIB-02 | Emitir facturas EXENTAS (matrícula, mensualidad, transporte) con leyendas obligatorias, receptor Padre/Tutor, vinculadas al pago (`monthly_payments.factura_id`). | Alto | 5 | 6 | Pendiente |
| HU-TRIB-03 | Implementar contingencia SFE (CUF precalculado offline, cola persistente, eventos significativos ante SIN, reenvío automático, log auditoría). | Alto | 5 | 6 | Pendiente |
| HU-TRIB-04 | Formulario RUDE RM 0684/2025 completo (secciones I-VI) con validaciones CI/certificado nacimiento, código SIE UE y export formato oficial MinEdu. | Alto | 8 | 2 | Pendiente |
| HU-TRIB-05 | Como contador, registrar retenciones RC-IVA 13% a empleados/proveedores sin factura, con comprobante. *(RF26)* | Alto | 3 | 6 | Pendiente |

---

## 6. Planificación de Sprints (corregida — C01)

| Sprint | Puntos | Enfoque |
|--------|--------|---------|
| 1 | **33** | Auth, roles, catálogos base, config tributaria S.A. |
| 2 | **39** | Personas, materias, RUDE completo, datos médicos |
| 3 | **18** | Inscripciones, matrículas, pagos |
| 4 | **35** | Calificaciones, asistencia, horarios |
| 5 | **34** | Comunicados, reportes, auditoría, UX |
| 6 | **19** | Facturación SFE producción, RC-IVA |
| **Total** | **178** | |

Detalle Sprint 1 (33): HU-01 (5) + HU-04 (3) + HU-05 (3) + HU-02 (5) + HU-03 (3) + HU-11 (3) + HU-12 (3) + HU-13 (3) + TRIB-01 (5).
Detalle Sprint 2 (39): HU-06 (5) + HU-07 (3) + HU-08 (3) + HU-09 (3) + HU-10 (3) + HU-36 (3) + HU-14 (3) + HU-15 (3) + HU-16 (5) + TRIB-04 (8).
Detalle Sprint 6 (19): TRIB-02 (5) + TRIB-03 (5) + catálogo SIN (3) + sandbox→producción (3) + TRIB-05 (3).

---

## 7. Criterios de Aceptación Modificados

### HU-01: Iniciar sesión
- Login con correo y contraseña; error si son incorrectas.
- Redirección al Dashboard según el rol del usuario.
- **Bloqueo tras 5 intentos fallidos** (`users.intentos_fallidos`, `users.bloqueado_hasta`).

### HU-03: Asignar roles (consistente con `users.rol_id`)
- Cada usuario tiene **exactamente un rol**: Administrador, Director, Secretaría, Docente, Padre/Tutor, Estudiante.
- El cambio de rol refleja inmediatamente sus permisos.

### HU-04: Contraseña segura
- Almacenamiento con hash **bcrypt** (nunca texto plano).
- Mínimo **8 caracteres** para nuevas contraseñas (`Password::defaults()`).

### HU-06: Registrar estudiantes
- Campos obligatorios + validación de documento duplicado + código único automático.
- **Fotografía del estudiante** (imagen, tamaño máx. configurable). *(RF30)*

### HU-13: Administrar paralelos
- Paralelos por grado (A, B, C...) con capacidad.
- **Docente responsable** por paralelo (`sections.responsable_id`).
- No eliminar paralelos con estudiantes matriculados.

### HU-TRIB-03: Contingencia SFE
- Emisión offline con CUF precalculado.
- Registro de **Evento Significativo** ante SIN (`significant_events`) con CUFD vigente (`daily_cufd`).
- Cola persistente (`contingency_queues`), reenvío automático y log auditable.

---

## Anexo A — Esquema SQL Consolidado (para phpMyAdmin)

> Ordenado por dependencias de FK. Ejecutar sobre la BD `sanber`.

```sql
CREATE DATABASE IF NOT EXISTS sanber CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sanber;

-- ===================== SEGURIDAD =====================
CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO roles (nombre, descripcion) VALUES
('Administrador', 'Control total del sistema'),
('Director', 'Gestión académica y administrativa'),
('Secretaría', 'Gestión de students, enrollments, pagos'),
('Docente', 'Registro de grades_scores y asistencia'),
('Padre/Tutor', 'Consulta de información de sus hijos'),
('Estudiante', 'Consulta de información propia');

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol_id BIGINT UNSIGNED NOT NULL,
    telefono VARCHAR(20) NULL,
    estado ENUM('ACTIVO', 'INACTIVO') DEFAULT 'ACTIVO',
    intentos_fallidos TINYINT UNSIGNED DEFAULT 0,
    bloqueado_hasta TIMESTAMP NULL,
    ultimo_acceso TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE RESTRICT,
    INDEX idx_estado (estado),
    INDEX idx_rol (rol_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE operation_histories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NOT NULL,
    accion VARCHAR(50) NOT NULL,
    modulo VARCHAR(50) NOT NULL,
    modelo VARCHAR(100) NULL,
    modelo_id BIGINT UNSIGNED NULL,
    detalle TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_usuario (usuario_id),
    INDEX idx_modulo (modulo),
    INDEX idx_fecha (created_at),
    INDEX idx_modelo (modelo, modelo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================== PERSONAS =====================
CREATE TABLE students (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NULL,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nombres VARCHAR(150) NOT NULL,
    apellidos VARCHAR(150) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    lugar_nacimiento VARCHAR(150) NULL,
    sexo ENUM('M', 'F') NOT NULL,
    tipo_documento ENUM('CI', 'PASAPORTE', 'OTRO') DEFAULT 'CI',
    numero_documento VARCHAR(20) NOT NULL,
    complemento VARCHAR(5) NOT NULL DEFAULT '',
    direccion VARCHAR(255) NULL,
    telefono VARCHAR(20) NULL,
    correo VARCHAR(150) NULL,
    fotografia VARCHAR(500) NULL,
    idioma VARCHAR(50) DEFAULT 'Castellano',
    cultura VARCHAR(50) NULL,
    discapacidad VARCHAR(100) NULL,
    estado ENUM('ACTIVO', 'INACTIVO', 'EGRESADO', 'TRASLADADO') DEFAULT 'ACTIVO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uk_documento (numero_documento, complemento),
    UNIQUE KEY uk_correo (correo),
    INDEX idx_codigo (codigo),
    INDEX idx_nombre (nombres, apellidos),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE parents_guardians (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NULL,
    nombres VARCHAR(150) NOT NULL,
    apellidos VARCHAR(150) NOT NULL,
    tipo_documento ENUM('CI', 'PASAPORTE', 'OTRO') DEFAULT 'CI',
    numero_documento VARCHAR(20) NOT NULL,
    complemento VARCHAR(5) NOT NULL DEFAULT '',
    telefono VARCHAR(20) NULL,
    correo VARCHAR(150) NULL,
    direccion VARCHAR(255) NULL,
    ocupacion VARCHAR(100) NULL,
    estado ENUM('ACTIVO', 'INACTIVO') DEFAULT 'ACTIVO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uk_documento (numero_documento, complemento),
    UNIQUE KEY uk_correo (correo),
    INDEX idx_nombre (nombres, apellidos),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE student_parent (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    estudiante_id BIGINT UNSIGNED NOT NULL,
    padre_tutor_id BIGINT UNSIGNED NOT NULL,
    tipo_relacion ENUM('PADRE', 'MADRE', 'TUTOR', 'OTRO') NOT NULL,
    es_principal TINYINT(1) DEFAULT 0,
    convive TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (padre_tutor_id) REFERENCES parents_guardians(id) ON DELETE CASCADE,
    UNIQUE KEY uk_student_parent (estudiante_id, padre_tutor_id),
    INDEX idx_padre (padre_tutor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE teachers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NULL,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nombres VARCHAR(150) NOT NULL,
    apellidos VARCHAR(150) NOT NULL,
    especialidad VARCHAR(150) NULL,
    tipo_documento ENUM('CI', 'PASAPORTE', 'OTRO') DEFAULT 'CI',
    numero_documento VARCHAR(20) NOT NULL,
    complemento VARCHAR(5) NOT NULL DEFAULT '',
    telefono VARCHAR(20) NULL,
    correo VARCHAR(150) NULL,
    estado ENUM('ACTIVO', 'INACTIVO') DEFAULT 'ACTIVO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uk_documento (numero_documento, complemento),
    INDEX idx_codigo (codigo),
    INDEX idx_nombre (nombres, apellidos),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================== ACADÉMICO =====================
CREATE TABLE academic_years (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    estado ENUM('ACTIVA', 'CERRADA', 'PROXIMA') DEFAULT 'PROXIMA',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE levels (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NULL,
    orden TINYINT UNSIGNED NOT NULL,
    estado ENUM('ACTIVO', 'INACTIVO') DEFAULT 'ACTIVO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_orden (orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO levels (nombre, descripcion, orden) VALUES
('Inicial', 'Educación Inicial (0 a 5 años)', 1),
('Primaria', 'Educación Primaria (1ro a 6to)', 2),
('Secundaria', 'Educación Secundaria (1ro a 6to)', 3);

CREATE TABLE grades (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nivel_id BIGINT UNSIGNED NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    orden TINYINT UNSIGNED NOT NULL,
    estado ENUM('ACTIVO', 'INACTIVO') DEFAULT 'ACTIVO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (nivel_id) REFERENCES levels(id) ON DELETE RESTRICT,
    UNIQUE KEY uk_nivel_grado (nivel_id, nombre),
    INDEX idx_orden (orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sections (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    grado_id BIGINT UNSIGNED NOT NULL,
    nombre VARCHAR(10) NOT NULL,
    responsable_id BIGINT UNSIGNED NULL,
    capacidad TINYINT UNSIGNED DEFAULT 40,
    estado ENUM('ACTIVO', 'INACTIVO') DEFAULT 'ACTIVO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (grado_id) REFERENCES grades(id) ON DELETE RESTRICT,
    FOREIGN KEY (responsable_id) REFERENCES teachers(id) ON DELETE SET NULL,
    UNIQUE KEY uk_grado_paralelo (grado_id, nombre),
    INDEX idx_responsable (responsable_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE subjects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255) NULL,
    nivel_id BIGINT UNSIGNED NOT NULL,
    horas_semanales TINYINT UNSIGNED NULL,
    estado ENUM('ACTIVO', 'INACTIVO') DEFAULT 'ACTIVO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (nivel_id) REFERENCES levels(id) ON DELETE RESTRICT,
    INDEX idx_nivel (nivel_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE courses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    grado_id BIGINT UNSIGNED NOT NULL,
    paralelo_id BIGINT UNSIGNED NOT NULL,
    gestion_id BIGINT UNSIGNED NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    estado ENUM('ACTIVO', 'INACTIVO') DEFAULT 'ACTIVO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (grado_id) REFERENCES grades(id) ON DELETE RESTRICT,
    FOREIGN KEY (paralelo_id) REFERENCES sections(id) ON DELETE RESTRICT,
    FOREIGN KEY (gestion_id) REFERENCES academic_years(id) ON DELETE RESTRICT,
    UNIQUE KEY uk_grado_paralelo_gestion (grado_id, paralelo_id, gestion_id),
    INDEX idx_gestion (gestion_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE course_subject (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    materia_id BIGINT UNSIGNED NOT NULL,
    curso_id BIGINT UNSIGNED NOT NULL,
    docente_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (materia_id) REFERENCES subjects(id) ON DELETE RESTRICT,
    FOREIGN KEY (curso_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (docente_id) REFERENCES teachers(id) ON DELETE RESTRICT,
    UNIQUE KEY uk_course_subject_docente (materia_id, curso_id, docente_id),
    INDEX idx_curso (curso_id),
    INDEX idx_docente (docente_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================== INSCRIPCIONES / MATRÍCULAS =====================
CREATE TABLE enrollments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    estudiante_id BIGINT UNSIGNED NOT NULL,
    gestion_id BIGINT UNSIGNED NOT NULL,
    fecha DATE NOT NULL,
    estado ENUM('PENDIENTE', 'COMPLETADA', 'ANULADA') DEFAULT 'PENDIENTE',
    observaciones TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante_id) REFERENCES students(id) ON DELETE RESTRICT,
    FOREIGN KEY (gestion_id) REFERENCES academic_years(id) ON DELETE RESTRICT,
    UNIQUE KEY uk_estudiante_gestion (estudiante_id, gestion_id),
    INDEX idx_gestion (gestion_id),
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE registrations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    inscripcion_id BIGINT UNSIGNED NOT NULL,
    curso_id BIGINT UNSIGNED NOT NULL,
    fecha DATE NOT NULL,
    monto_mensual DECIMAL(10, 2) NOT NULL,
    estado ENUM('ACTIVA', 'INACTIVA', 'CANCELADA') DEFAULT 'ACTIVA',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (inscripcion_id) REFERENCES enrollments(id) ON DELETE RESTRICT,
    FOREIGN KEY (curso_id) REFERENCES courses(id) ON DELETE RESTRICT,
    INDEX idx_curso (curso_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE student_documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    estudiante_id BIGINT UNSIGNED NOT NULL,
    tipo_documento VARCHAR(50) NOT NULL,
    archivo VARCHAR(500) NOT NULL,
    estado ENUM('PENDIENTE', 'ENTREGADO', 'VERIFICADO') DEFAULT 'PENDIENTE',
    observaciones TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante_id) REFERENCES students(id) ON DELETE CASCADE,
    INDEX idx_estudiante (estudiante_id),
    INDEX idx_tipo (tipo_documento),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================== FACTURACIÓN SFE =====================
-- NOTA: electronic_invoices DEBE crearse antes que monthly_payments
-- (FK monthly_payments.factura_id -> electronic_invoices.id)

CREATE TABLE electronic_invoices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nro_factura INT UNSIGNED NOT NULL,
    cuf VARCHAR(100) NOT NULL UNIQUE,
    cufd VARCHAR(100) NULL,
    cuis VARCHAR(20) NULL,
    fecha_emision DATE NOT NULL,
    hora_emision TIME NOT NULL,
    tipo_factura ENUM('VENTA', 'NOTA_VENTA') DEFAULT 'VENTA',
    tipo_emision ENUM('ONLINE', 'CONTINGENCIA') DEFAULT 'ONLINE',
    modalidad VARCHAR(50) NOT NULL,
    codigo_sucursal VARCHAR(10) DEFAULT '0',
    nit_emisor VARCHAR(20) NOT NULL,
    razon_social_emisor VARCHAR(200) NOT NULL,
    nit_receptor VARCHAR(20) NULL,
    tipo_doc_receptor VARCHAR(20) NULL,
    nro_doc_receptor VARCHAR(20) NULL,
    complemento_receptor VARCHAR(5) NULL,
    razon_social_receptor VARCHAR(200) NULL,
    direccion_receptor VARCHAR(255) NULL,
    email_receptor VARCHAR(150) NULL,
    total_exento DECIMAL(10, 2) DEFAULT 0,
    total_descuentos DECIMAL(10, 2) DEFAULT 0,
    total_iva DECIMAL(10, 2) DEFAULT 0,
    total_it DECIMAL(10, 2) DEFAULT 0,
    total_factura DECIMAL(10, 2) NOT NULL,
    total_factura_texto VARCHAR(255) NULL,
    estado ENUM('ENVIADA', 'AUTORIZADA', 'RECHAZADA', 'CONTINGENCIA', 'ANULADA') DEFAULT 'ENVIADA',
    xml_enviado TEXT NULL,
    xml_respuesta TEXT NULL,
    leyenda1 VARCHAR(255) NULL,
    leyenda2 VARCHAR(255) NULL,
    leyenda3 VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nro_factura (nro_factura),
    INDEX idx_fecha (fecha_emision),
    INDEX idx_estado (estado),
    INDEX idx_receptor (nit_receptor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE invoice_details (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    factura_id BIGINT UNSIGNED NOT NULL,
    nro_registro INT UNSIGNED NOT NULL,
    codigo_servicio VARCHAR(20) NOT NULL,
    descripcion VARCHAR(200) NOT NULL,
    cantidad DECIMAL(10, 2) DEFAULT 1,
    unidad_medida VARCHAR(20) NOT NULL,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    monto_descuento DECIMAL(10, 2) DEFAULT 0,
    sub_total DECIMAL(10, 2) NOT NULL,
    iva_tipo ENUM('EXENTO') DEFAULT 'EXENTO',
    iva_tasa DECIMAL(5, 2) DEFAULT 0,
    iva_monto DECIMAL(10, 2) DEFAULT 0,
    it_tipo ENUM('EXENTO') DEFAULT 'EXENTO',
    it_tasa DECIMAL(5, 2) DEFAULT 0,
    it_monto DECIMAL(10, 2) DEFAULT 0,
    leyenda VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (factura_id) REFERENCES electronic_invoices(id) ON DELETE CASCADE,
    INDEX idx_codigo (codigo_servicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE monthly_payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    matricula_id BIGINT UNSIGNED NOT NULL,
    factura_id BIGINT UNSIGNED NULL,
    mes TINYINT UNSIGNED NOT NULL,
    anio SMALLINT UNSIGNED NOT NULL,
    monto_esperado DECIMAL(10, 2) NOT NULL,
    monto_pagado DECIMAL(10, 2) DEFAULT 0,
    fecha_pago DATE NULL,
    metodo_pago ENUM('EFECTIVO', 'TRANSFERENCIA', 'QR', 'OTRO') NULL,
    estado ENUM('PENDIENTE', 'PAGO_PARCIAL', 'PAGADO', 'VENCIDO') DEFAULT 'PENDIENTE',
    observaciones TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (matricula_id) REFERENCES registrations(id) ON DELETE RESTRICT,
    FOREIGN KEY (factura_id) REFERENCES electronic_invoices(id) ON DELETE SET NULL,
    UNIQUE KEY uk_matricula_mes_anio (matricula_id, mes, anio),
    INDEX idx_estado (estado),
    INDEX idx_fecha (anio, mes)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tax_configurations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nit_colegio VARCHAR(20) NOT NULL,
    razon_social VARCHAR(200) NOT NULL,
    tipo_entidad ENUM('CON_FINES_LUCRO', 'SIN_FINES_LUCRO') DEFAULT 'CON_FINES_LUCRO',
    exento_iva_educacion TINYINT(1) DEFAULT 1,
    exento_it TINYINT(1) DEFAULT 1,
    iue_exento TINYINT(1) DEFAULT 0,
    modalidad_sfe ENUM('COMPUTARIZADA_EN_LINEA', 'ELECTRONICA_EN_LINEA', 'PORTAL_WEB') DEFAULT 'COMPUTARIZADA_EN_LINEA',
    cuis VARCHAR(20) NULL,
    codigo_sucursal VARCHAR(10) DEFAULT '0',
    agente_retencion_rc_iva TINYINT(1) DEFAULT 1,
    leyenda_iva_exento VARCHAR(255) NOT NULL,
    leyenda_it_exento VARCHAR(255) NOT NULL,
    leyenda_ley070 VARCHAR(255) NOT NULL,
    estado ENUM('ACTIVO', 'INACTIVO') DEFAULT 'ACTIVO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tax_configurations (
    nit_colegio, razon_social, tipo_entidad, modalidad_sfe,
    leyenda_iva_exento, leyenda_it_exento, leyenda_ley070
) VALUES (
    '408460020',
    'SOCIEDAD EDUCATIVA SAN BERNARDO DE TARIJA S.A.',
    'CON_FINES_LUCRO',
    'COMPUTARIZADA_EN_LINEA',
    'EXENTO - NO GENERA DERECHO A CRÉDITO FISCAL',
    'EXENTO IT - ART. 13 INC. F LEY 843',
    'LEY 070 - EDUCACION EXENTA IVA ART. 13 LEY 843'
);

CREATE TABLE educational_services (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo_sin VARCHAR(20) NOT NULL UNIQUE,
    descripcion VARCHAR(200) NOT NULL,
    unidad_medida VARCHAR(20) DEFAULT 'Unidad (B1)',
    iva_tipo ENUM('EXENTO') DEFAULT 'EXENTO',
    iva_tasa DECIMAL(5, 2) DEFAULT 0,
    it_tipo ENUM('EXENTO') DEFAULT 'EXENTO',
    it_tasa DECIMAL(5, 2) DEFAULT 0,
    leyenda_factura VARCHAR(255) NULL,
    aplica_exento_educacion TINYINT(1) DEFAULT 1,
    estado ENUM('ACTIVO', 'INACTIVO') DEFAULT 'ACTIVO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO educational_services (codigo_sin, descripcion, leyenda_factura) VALUES
('11111', 'Matrícula Escolar', 'LEY 070 - EDUCACION EXENTA IVA ART. 13 LEY 843'),
('11112', 'Mensualidad Escolar', 'LEY 070 - EDUCACION EXENTA IVA ART. 13 LEY 843'),
('11113', 'Transporte Escolar', 'LEY 070 - EDUCACION EXENTA IVA ART. 13 LEY 843');

CREATE TABLE invoice_sequences (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo_sucursal VARCHAR(10) DEFAULT '0',
    punto_venta VARCHAR(10) DEFAULT '0',
    ultimo_numero INT UNSIGNED DEFAULT 0,
    estado ENUM('ACTIVO', 'INACTIVO') DEFAULT 'ACTIVO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_sucursal_punto (codigo_sucursal, punto_venta),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE daily_cufd (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cufd VARCHAR(100) NOT NULL UNIQUE,
    codigo_control VARCHAR(50) NOT NULL,
    fecha_vigencia DATE NOT NULL,
    motivo VARCHAR(200) NULL,
    estado ENUM('ACTIVO', 'VENCIDO', 'ANULADO') DEFAULT 'ACTIVO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_fecha (fecha_vigencia),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE significant_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipo_evento VARCHAR(50) NOT NULL,
    fecha_inicio TIMESTAMP NOT NULL,
    fecha_fin TIMESTAMP NULL,
    motivo TEXT NOT NULL,
    documentos_soporte TEXT NULL,
    cufd_inicio VARCHAR(100) NULL,
    estado ENUM('ABIERTO', 'CERRADO', 'REPORTADO') DEFAULT 'ABIERTO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_estado (estado),
    INDEX idx_fecha_inicio (fecha_inicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contingency_queues (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    factura_id BIGINT UNSIGNED NOT NULL,
    intentos TINYINT UNSIGNED DEFAULT 0,
    max_intentos TINYINT UNSIGNED DEFAULT 5,
    estado ENUM('PENDIENTE', 'ENVIANDO', 'COMPLETADO', 'FALLIDO') DEFAULT 'PENDIENTE',
    ultimo_error TEXT NULL,
    proximo_intento TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (factura_id) REFERENCES electronic_invoices(id) ON DELETE CASCADE,
    INDEX idx_estado (estado),
    INDEX idx_proximo_intento (proximo_intento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE rc_iva_withholdings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    tipo_sujeto ENUM('EMPLEADO', 'PROVEEDOR') NOT NULL,
    sujeto_id BIGINT UNSIGNED NOT NULL,
    sujeto_nombre VARCHAR(200) NOT NULL,
    sujeto_documento VARCHAR(20) NOT NULL,
    nro_factura_origen VARCHAR(50) NULL,
    base_imponible DECIMAL(10, 2) NOT NULL,
    retencion_monto DECIMAL(10, 2) NOT NULL,
    comprobante_nro VARCHAR(50) NULL,
    estado ENUM('REGISTRADA', 'CONFIRMADA', 'ANULADA') DEFAULT 'REGISTRADA',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_fecha (fecha),
    INDEX idx_tipo_sujeto (tipo_sujeto, sujeto_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================== EVALUACIONES / ASISTENCIA / HORARIOS =====================
CREATE TABLE assessments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    materia_id BIGINT UNSIGNED NOT NULL,
    gestion_id BIGINT UNSIGNED NOT NULL,
    peso DECIMAL(5, 2) DEFAULT 1.00,
    fecha DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (materia_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (gestion_id) REFERENCES academic_years(id) ON DELETE RESTRICT,
    INDEX idx_materia (materia_id),
    INDEX idx_gestion (gestion_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE grades_scores (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    evaluacion_id BIGINT UNSIGNED NOT NULL,
    estudiante_id BIGINT UNSIGNED NOT NULL,
    nota DECIMAL(5, 2) NOT NULL CHECK (nota >= 0 AND nota <= 100),
    observaciones TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (evaluacion_id) REFERENCES assessments(id) ON DELETE CASCADE,
    FOREIGN KEY (estudiante_id) REFERENCES students(id) ON DELETE CASCADE,
    UNIQUE KEY uk_evaluacion_estudiante (evaluacion_id, estudiante_id),
    INDEX idx_estudiante (estudiante_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE attendances (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    estudiante_id BIGINT UNSIGNED NOT NULL,
    curso_id BIGINT UNSIGNED NOT NULL,
    fecha DATE NOT NULL,
    estado ENUM('PRESENTE', 'FALTA', 'ATRASO', 'PERMISO', 'JUSTIFICADO') NOT NULL,
    justificacion TEXT NULL,
    registrado_por BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (curso_id) REFERENCES courses(id) ON DELETE RESTRICT,
    FOREIGN KEY (registrado_por) REFERENCES users(id) ON DELETE RESTRICT,
    UNIQUE KEY uk_estudiante_fecha_curso (estudiante_id, fecha, curso_id),
    INDEX idx_curso (curso_id),
    INDEX idx_fecha (fecha),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE schedules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    curso_id BIGINT UNSIGNED NOT NULL,
    materia_id BIGINT UNSIGNED NOT NULL,
    docente_id BIGINT UNSIGNED NOT NULL,
    dia ENUM('LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO') NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    aula VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (curso_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (materia_id) REFERENCES subjects(id) ON DELETE RESTRICT,
    FOREIGN KEY (docente_id) REFERENCES teachers(id) ON DELETE RESTRICT,
    CHECK (hora_fin > hora_inicio),
    INDEX idx_curso (curso_id),
    INDEX idx_docente (docente_id),
    INDEX idx_dia (dia),
    INDEX idx_aula (aula)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================== COMUNICADOS =====================
CREATE TABLE announcements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    contenido TEXT NOT NULL,
    autor_id BIGINT UNSIGNED NOT NULL,
    fecha_publicacion TIMESTAMP NULL,
    estado ENUM('BORRADOR', 'PUBLICADO', 'ARCHIVADO') DEFAULT 'BORRADOR',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (autor_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_autor (autor_id),
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha_publicacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE announcement_recipients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    comunicado_id BIGINT UNSIGNED NOT NULL,
    tipo_destinatario ENUM('TODOS', 'DOCENTES', 'ESTUDIANTES', 'PADRES', 'CURSO_ESPECIFICO') NOT NULL,
    curso_id BIGINT UNSIGNED NULL,
    usuario_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (comunicado_id) REFERENCES announcements(id) ON DELETE CASCADE,
    FOREIGN KEY (curso_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_tipo (tipo_destinatario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE announcement_reads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    comunicado_id BIGINT UNSIGNED NOT NULL,
    usuario_id BIGINT UNSIGNED NOT NULL,
    leido TINYINT(1) DEFAULT 0,
    fecha_lectura TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (comunicado_id) REFERENCES announcements(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uk_comunicado_usuario (comunicado_id, usuario_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_leido (leido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================== RUDE / MÉDICOS =====================
CREATE TABLE student_rude_records (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    estudiante_id BIGINT UNSIGNED NOT NULL,
    codigo_rude VARCHAR(20) NULL UNIQUE,
    codigo_sie_ue VARCHAR(20) NULL,
    turno ENUM('MANANA', 'TARDE', 'NOCTURNO') NULL,
    transporte TINYINT(1) DEFAULT 0,
    zona_residencial ENUM('URBANA', 'RURAL', 'PERIFERIA') NULL,
    procedencia_ie VARCHAR(150) NULL,
    nuevo_ingreso TINYINT(1) DEFAULT 1,
    idioma_originario VARCHAR(50) NULL,
    salud_discapacidad VARCHAR(200) NULL,
    oficialia_nacimiento VARCHAR(100) NULL,
    libro_nacimiento VARCHAR(50) NULL,
    partida_nacimiento VARCHAR(50) NULL,
    folio_nacimiento VARCHAR(50) NULL,
    anio_nacimiento VARCHAR(10) NULL,
    estado ENUM('PENDIENTE', 'ENVIADO', 'APROBADO', 'RECHAZADO') DEFAULT 'PENDIENTE',
    fecha_envio DATE NULL,
    fecha_respuesta DATE NULL,
    observaciones TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante_id) REFERENCES students(id) ON DELETE CASCADE,
    UNIQUE KEY uk_estudiante (estudiante_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE medical_records (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    estudiante_id BIGINT UNSIGNED NOT NULL,
    grupo_sanguineo VARCHAR(5) NULL,
    alergias TEXT NULL,
    medicamentos TEXT NULL,
    condiciones_especiales TEXT NULL,
    contacto_emergencia_nombre VARCHAR(150) NULL,
    contacto_emergencia_telefono VARCHAR(20) NULL,
    contacto_emergencia_parentesco VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (estudiante_id) REFERENCES students(id) ON DELETE CASCADE,
    UNIQUE KEY uk_estudiante (estudiante_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Anexo B — Convención de Ramas

Formato: `HUxx-nombre-de-la-historia` (minúsculas, separadas por guiones, basadas en `develop`).

| HU | Rama | Base |
|----|------|------|
| HU-01 | `HU01-inicio-de-sesion` | develop |
| HU-03 | `HU03-asignar-roles` | develop |
| HU-04 | `HU04-contrasena-segura` | develop |
| HU-11 | `HU11-administrar-niveles-educativos` | develop |
| HU-TRIB-01 | `HUTRIB01-configuracion-tributaria` | develop |

Flujo: `develop` ← feature branch → Pull Request → revisión → merge a `develop`.
Un commit conciso (en español) por cada HU.

---
*Documento v2 consolidado: 2026-08-25 — Motor: MySQL 8.0+ vía phpMyAdmin*
