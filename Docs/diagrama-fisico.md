# Diagrama Físico — Base de Datos MySQL

## Sistema de Gestión Escolar Personalizado — Unidad Educativa San Bernardo

---

## Información General

| Campo        | Valor                        |
| ------------ | ---------------------------- |
| Motor        | MySQL 8.0+                   |
| Codificación | UTF-8 (utf8mb4)              |
| Collation    | utf8mb4_unicode_ci           |
| Foreign Keys | Habilitadas                  |
| Auditoría    | Todas las tablas principales |
| Eliminación  | Lógica mediante campo `estado` = 'ELIMINADO' |

---

## Diagrama de Tablas

```text
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              SEGURIDAD Y USUARIOS                               │
├─────────────────────────────────────────────────────────────────────────────────┤
│  roles ──< users                                                                │
│                                                                                │
│  users ──< operation_histories                                                  │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                           GESTIÓN DE PERSONAS                                   │
├─────────────────────────────────────────────────────────────────────────────────┤
│  students ──┬──< student_parent >──┬── parent_guardians                        │
│             │                      │                                            │
│             ├──< enrollments       │                                            │
│             │      └──< registrations                                           │
│             │            └──< monthly_payments                                 │
│             │            └──< student_documents                                │
│             ├──< grade_scores                                                    │
│             ├──< attendances                                                     │
│             ├──< student_rude_records                                            │
│             └──< medical_records                                                 │
│                                                                                │
│  teachers ──┬──< course_subjects                                                  │
│             ├──< schedules                                                       │
│             └──< sections (teacher_id)                                           │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                         CONFIGURACIÓN ACADÉMICA                                 │
├─────────────────────────────────────────────────────────────────────────────────┤
│  academic_years ──┬──< courses                                                  │
│                   └──< assessments                                              │
│                                                                                │
│  levels ──< grades ──< sections ──< courses                                    │
│                                                                                │
│  subjects ──< course_subjects                                                    │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                    FACTURACIÓN ELECTRÓNICA (SFE)                                │
├─────────────────────────────────────────────────────────────────────────────────┤
│  tax_configurations                                                             │
│  educational_services                                                           │
│  invoice_sequences                                                              │
│  daily_cufds                                                                     │
│  significant_events                                                             │
│  electronic_invoices ──< invoice_details                                        │
│  registrations ──< monthly_payments                                             │
│  monthly_payments ──< electronic_invoices                                      │
│  contingency_queues                                                             │
│  rc_iva_withholdings                                                            │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                         COMUNICACIÓN Y REPORTES                                 │
├─────────────────────────────────────────────────────────────────────────────────┤
│  announcements ──< announcement_recipients                                     │
│  announcements ──< announcement_reads                                           │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

# DEFINICIÓN FÍSICA DE TABLAS

## 1. `roles`

```sql
CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NULL,
    estado ENUM('ACTIVO', 'INACTIVO', 'ELIMINADO') DEFAULT 'ACTIVO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO roles (nombre, descripcion) VALUES
('Administrador', 'Control total del sistema'),
('Director', 'Gestión académica y administrativa'),
('Secretaría', 'Gestión de estudiantes, inscripciones y pagos'),
('Docente', 'Registro de calificaciones y asistencia'),
('Padre/Tutor', 'Consulta de información de sus hijos'),
('Estudiante', 'Consulta de información propia');
```

---

## 2. `users`

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    telefono VARCHAR(20) NULL,
    estado ENUM('ACTIVO', 'INACTIVO', 'ELIMINADO') DEFAULT 'ACTIVO',
    intentos_fallidos TINYINT UNSIGNED DEFAULT 0,
    bloqueado_hasta TIMESTAMP NULL,
    ultimo_acceso TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (role_id)
        REFERENCES roles(id)
        ON DELETE RESTRICT,

    INDEX idx_email (email),
    INDEX idx_estado (estado),
    INDEX idx_role (role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 3. `operation_histories`

```sql
CREATE TABLE operation_histories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    accion VARCHAR(50) NOT NULL,
    modulo VARCHAR(50) NOT NULL,
    modelo VARCHAR(100) NULL,
    modelo_id BIGINT UNSIGNED NULL,
    detalle TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE RESTRICT,

    INDEX idx_user (user_id),
    INDEX idx_modulo (modulo),
    INDEX idx_fecha (created_at),
    INDEX idx_modelo (modelo, modelo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 4. `students`

```sql
CREATE TABLE students (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
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
    email VARCHAR(150) NULL,
    fotografia VARCHAR(500) NULL,
    idioma VARCHAR(50) DEFAULT 'Castellano',
    cultura VARCHAR(50) NULL,
    discapacidad VARCHAR(100) NULL,
    quien_es_tutor ENUM('PADRE', 'MADRE', 'TUTOR') DEFAULT 'PADRE',
    estado ENUM('ACTIVO', 'INACTIVO', 'EGRESADO', 'TRASLADADO', 'ELIMINADO') DEFAULT 'ACTIVO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE SET NULL,

    UNIQUE KEY uk_documento (numero_documento, complemento),
    UNIQUE KEY uk_email (email),
    INDEX idx_codigo (codigo),
    INDEX idx_nombre (nombres, apellidos),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 5. `parent_guardians`

```sql
CREATE TABLE parent_guardians (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    nombres VARCHAR(150) NOT NULL,
    apellidos VARCHAR(150) NOT NULL,
    tipo_documento ENUM('CI', 'PASAPORTE', 'OTRO') DEFAULT 'CI',
    numero_documento VARCHAR(20) NOT NULL,
    complemento VARCHAR(5) NOT NULL DEFAULT '',
    telefono VARCHAR(20) NULL,
    email VARCHAR(150) NULL,
    direccion VARCHAR(255) NULL,
    ocupacion VARCHAR(100) NULL,
    estado ENUM('ACTIVO', 'INACTIVO', 'ELIMINADO') DEFAULT 'ACTIVO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE SET NULL,

    UNIQUE KEY uk_documento (numero_documento, complemento),
    UNIQUE KEY uk_email (email),
    INDEX idx_nombre (nombres, apellidos),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 6. `student_parent`

```sql
CREATE TABLE student_parent (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    parent_guardian_id BIGINT UNSIGNED NOT NULL,
    tipo_relacion VARCHAR(50) NULL,
    es_principal TINYINT(1) DEFAULT 0,
    convive TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE,

    FOREIGN KEY (parent_guardian_id)
        REFERENCES parent_guardians(id)
        ON DELETE CASCADE,

    UNIQUE KEY uk_student_parent (student_id, parent_guardian_id),
    INDEX idx_student (student_id),
    INDEX idx_parent_guardian (parent_guardian_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 7. `teachers`

```sql
CREATE TABLE teachers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nombres VARCHAR(150) NOT NULL,
    apellidos VARCHAR(150) NOT NULL,
    especialidad VARCHAR(150) NULL,
    tipo_documento ENUM('CI', 'PASAPORTE', 'OTRO') DEFAULT 'CI',
    numero_documento VARCHAR(20) NOT NULL,
    complemento VARCHAR(5) NOT NULL DEFAULT '',
    telefono VARCHAR(20) NULL,
    email VARCHAR(150) NULL,
    estado ENUM('ACTIVO', 'INACTIVO', 'ELIMINADO') DEFAULT 'ACTIVO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE SET NULL,

    UNIQUE KEY uk_documento (numero_documento, complemento),
    UNIQUE KEY uk_email (email),
    INDEX idx_codigo (codigo),
    INDEX idx_nombre (nombres, apellidos),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 8. `academic_years`

```sql
CREATE TABLE academic_years (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    estado ENUM('ACTIVA', 'CERRADA', 'PROXIMA') DEFAULT 'PROXIMA',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_nombre (nombre),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 9. `levels`

```sql
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
```

---

# 10. `grades`

```sql
CREATE TABLE grades (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    level_id BIGINT UNSIGNED NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    orden TINYINT UNSIGNED NOT NULL,
    estado ENUM('ACTIVO', 'INACTIVO') DEFAULT 'ACTIVO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (level_id)
        REFERENCES levels(id)
        ON DELETE RESTRICT,

    UNIQUE KEY uk_level_grade (level_id, nombre),
    INDEX idx_level (level_id),
    INDEX idx_orden (orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 11. `sections`

```sql
CREATE TABLE sections (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    grade_id BIGINT UNSIGNED NOT NULL,
    nombre VARCHAR(10) NOT NULL,
    teacher_id BIGINT UNSIGNED NULL,
    capacidad TINYINT UNSIGNED DEFAULT 40,
    estado ENUM('ACTIVO', 'INACTIVO') DEFAULT 'ACTIVO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (grade_id)
        REFERENCES grades(id)
        ON DELETE RESTRICT,

    FOREIGN KEY (teacher_id)
        REFERENCES teachers(id)
        ON DELETE SET NULL,

    UNIQUE KEY uk_grade_section (grade_id, nombre),
    INDEX idx_grade (grade_id),
    INDEX idx_teacher (teacher_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 12. `courses`

```sql
CREATE TABLE courses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    grade_id BIGINT UNSIGNED NOT NULL,
    section_id BIGINT UNSIGNED NOT NULL,
    academic_year_id BIGINT UNSIGNED NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    aula VARCHAR(100) NULL,
    estado ENUM('ACTIVO', 'INACTIVO') DEFAULT 'ACTIVO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (grade_id)
        REFERENCES grades(id)
        ON DELETE RESTRICT,

    FOREIGN KEY (section_id)
        REFERENCES sections(id)
        ON DELETE RESTRICT,

    FOREIGN KEY (academic_year_id)
        REFERENCES academic_years(id)
        ON DELETE RESTRICT,

    UNIQUE KEY uk_grade_section_year
        (grade_id, section_id, academic_year_id),

    INDEX idx_academic_year (academic_year_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 13. `subjects`

```sql
CREATE TABLE subjects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255) NULL,
    level_id BIGINT UNSIGNED NOT NULL,
    horas_semanales TINYINT UNSIGNED NULL,
    estado ENUM('ACTIVO', 'INACTIVO', 'ELIMINADO') DEFAULT 'ACTIVO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (level_id)
        REFERENCES levels(id)
        ON DELETE RESTRICT,

    INDEX idx_level (level_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 14. `course_subjects`

```sql
CREATE TABLE course_subjects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subject_id BIGINT UNSIGNED NOT NULL,
    course_id BIGINT UNSIGNED NOT NULL,
    teacher_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (subject_id)
        REFERENCES subjects(id)
        ON DELETE RESTRICT,

    FOREIGN KEY (course_id)
        REFERENCES courses(id)
        ON DELETE CASCADE,

    FOREIGN KEY (teacher_id)
        REFERENCES teachers(id)
        ON DELETE RESTRICT,

    UNIQUE KEY uk_course_subjects_teacher
        (subject_id, course_id, teacher_id),

    INDEX idx_course (course_id),
    INDEX idx_teacher (teacher_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 15. `enrollments`

```sql
CREATE TABLE enrollments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    academic_year_id BIGINT UNSIGNED NOT NULL,
    fecha DATE NOT NULL,
    estado ENUM('PENDIENTE', 'COMPLETADA', 'ANULADA') DEFAULT 'PENDIENTE',
    observaciones TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE RESTRICT,

    FOREIGN KEY (academic_year_id)
        REFERENCES academic_years(id)
        ON DELETE RESTRICT,

    UNIQUE KEY uk_student_year
        (student_id, academic_year_id),

    INDEX idx_academic_year (academic_year_id),
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 16. `registrations`

```sql
CREATE TABLE registrations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    enrollment_id BIGINT UNSIGNED NOT NULL,
    course_id BIGINT UNSIGNED NOT NULL,
    fecha DATE NOT NULL,
    monto_mensual DECIMAL(10, 2) NOT NULL,
    observaciones TEXT NULL,
    estado ENUM('ACTIVA', 'INACTIVA', 'CANCELADA') DEFAULT 'ACTIVA',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (enrollment_id)
        REFERENCES enrollments(id)
        ON DELETE RESTRICT,

    FOREIGN KEY (course_id)
        REFERENCES courses(id)
        ON DELETE RESTRICT,

    INDEX idx_enrollment (enrollment_id),
    INDEX idx_course (course_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 17. `student_documents`

```sql
CREATE TABLE student_documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    tipo_documento VARCHAR(50) NOT NULL,
    archivo VARCHAR(500) NOT NULL,
    estado ENUM('PENDIENTE', 'ENTREGADO', 'VERIFICADO') DEFAULT 'PENDIENTE',
    observaciones TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE,

    INDEX idx_student (student_id),
    INDEX idx_tipo (tipo_documento),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 18. `monthly_payments`

```sql
CREATE TABLE monthly_payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    registration_id BIGINT UNSIGNED NOT NULL,
    electronic_invoice_id BIGINT UNSIGNED NULL,
    mes TINYINT UNSIGNED NOT NULL,
    anio SMALLINT UNSIGNED NOT NULL,
    monto_esperado DECIMAL(10, 2) NOT NULL,
    monto_pagado DECIMAL(10, 2) DEFAULT 0,
    monto DECIMAL(10, 2) NOT NULL,
    fecha_pago DATE NULL,
    fecha DATE NULL,
    metodo_pago ENUM('EFECTIVO', 'TRANSFERENCIA', 'QR', 'OTRO') NULL,
    estado ENUM('PENDIENTE', 'PAGO_PARCIAL', 'PAGADO', 'VENCIDO') DEFAULT 'PENDIENTE',
    observaciones TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (registration_id)
        REFERENCES registrations(id)
        ON DELETE RESTRICT,

    FOREIGN KEY (electronic_invoice_id)
        REFERENCES electronic_invoices(id)
        ON DELETE SET NULL,

    UNIQUE KEY uk_registration_month_year
        (registration_id, mes, anio),

    INDEX idx_registration (registration_id),
    INDEX idx_electronic_invoice (electronic_invoice_id),
    INDEX idx_estado (estado),
    INDEX idx_fecha (anio, mes)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 19. `assessments`

```sql
CREATE TABLE assessments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_subject_id BIGINT UNSIGNED NULL,
    nombre VARCHAR(100) NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    subject_id BIGINT UNSIGNED NOT NULL,
    academic_year_id BIGINT UNSIGNED NOT NULL,
    bimestre TINYINT UNSIGNED NULL COMMENT '1-4',
    peso DECIMAL(5, 2) DEFAULT 1.00,
    fecha DATE NULL,
    descripcion VARCHAR(255) NULL,
    estado ENUM('ACTIVO', 'INACTIVO') DEFAULT 'ACTIVO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (course_subject_id)
        REFERENCES course_subjects(id)
        ON DELETE CASCADE,

    FOREIGN KEY (subject_id)
        REFERENCES subjects(id)
        ON DELETE CASCADE,

    FOREIGN KEY (academic_year_id)
        REFERENCES academic_years(id)
        ON DELETE RESTRICT,

    INDEX idx_course_subject (course_subject_id),
    INDEX idx_subject (subject_id),
    INDEX idx_academic_year (academic_year_id),
    INDEX idx_bimestre (bimestre),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 20. `grade_scores`

```sql
CREATE TABLE grade_scores (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    assessment_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    nota DECIMAL(5, 2) NOT NULL CHECK (nota >= 0 AND nota <= 100),
    promedio_ponderado DECIMAL(5, 2) NULL COMMENT 'Auto-calculado al guardar',
    observaciones TEXT NULL,
    registered_by_user_id BIGINT UNSIGNED NULL,
    registrado_en TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (assessment_id)
        REFERENCES assessments(id)
        ON DELETE CASCADE,

    FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE,

    FOREIGN KEY (registered_by_user_id)
        REFERENCES users(id)
        ON DELETE SET NULL,

    UNIQUE KEY uk_assessment_student
        (assessment_id, student_id),

    INDEX idx_student (student_id),
    INDEX idx_assessment (assessment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 21. `attendances`

```sql
CREATE TABLE attendances (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    course_id BIGINT UNSIGNED NOT NULL,
    fecha DATE NOT NULL,
    estado ENUM('PRESENTE', 'FALTA', 'ATRASO', 'PERMISO', 'JUSTIFICADO') NOT NULL,
    justificacion TEXT NULL,
    registered_by_user_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE,

    FOREIGN KEY (course_id)
        REFERENCES courses(id)
        ON DELETE RESTRICT,

    FOREIGN KEY (registered_by_user_id)
        REFERENCES users(id)
        ON DELETE RESTRICT,

    UNIQUE KEY uk_student_date_course
        (student_id, fecha, course_id),

    INDEX idx_course (course_id),
    INDEX idx_registered_by_user (registered_by_user_id),
    INDEX idx_fecha (fecha),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 22. `schedules`

```sql
CREATE TABLE schedules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id BIGINT UNSIGNED NOT NULL,
    subject_id BIGINT UNSIGNED NOT NULL,
    teacher_id BIGINT UNSIGNED NOT NULL,
    dia ENUM('LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO') NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    aula VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (course_id)
        REFERENCES courses(id)
        ON DELETE CASCADE,

    FOREIGN KEY (subject_id)
        REFERENCES subjects(id)
        ON DELETE RESTRICT,

    FOREIGN KEY (teacher_id)
        REFERENCES teachers(id)
        ON DELETE RESTRICT,

    CHECK (hora_fin > hora_inicio),

    INDEX idx_course (course_id),
    INDEX idx_teacher (teacher_id),
    INDEX idx_dia (dia),
    INDEX idx_aula (aula)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 23. `announcements`

```sql
CREATE TABLE announcements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    contenido TEXT NOT NULL,
    author_id BIGINT UNSIGNED NOT NULL,
    fecha_publicacion TIMESTAMP NULL,
    estado ENUM('BORRADOR', 'PUBLICADO', 'ARCHIVADO') DEFAULT 'BORRADOR',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (author_id)
        REFERENCES users(id)
        ON DELETE RESTRICT,

    INDEX idx_author (author_id),
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha_publicacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 24. `announcement_recipients`

```sql
CREATE TABLE announcement_recipients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    announcement_id BIGINT UNSIGNED NOT NULL,
    tipo_destinatario ENUM(
        'TODOS',
        'DOCENTES',
        'ESTUDIANTES',
        'PADRES',
        'CURSO_ESPECIFICO'
    ) NOT NULL,
    course_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (announcement_id)
        REFERENCES announcements(id)
        ON DELETE CASCADE,

    FOREIGN KEY (course_id)
        REFERENCES courses(id)
        ON DELETE CASCADE,

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    INDEX idx_announcement (announcement_id),
    INDEX idx_course (course_id),
    INDEX idx_user (user_id),
    INDEX idx_tipo (tipo_destinatario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 25. `announcement_reads`

```sql
CREATE TABLE announcement_reads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    announcement_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    leido TINYINT(1) DEFAULT 0,
    fecha_lectura TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (announcement_id)
        REFERENCES announcements(id)
        ON DELETE CASCADE,

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    UNIQUE KEY uk_announcement_user
        (announcement_id, user_id),

    INDEX idx_user (user_id),
    INDEX idx_leido (leido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 26. `tax_configurations`

```sql
CREATE TABLE tax_configurations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nit_colegio VARCHAR(20) NOT NULL,
    razon_social VARCHAR(200) NOT NULL,
    tipo_entidad ENUM('CON_FINES_LUCRO', 'SIN_FINES_LUCRO') DEFAULT 'CON_FINES_LUCRO',
    exento_iva_educacion TINYINT(1) DEFAULT 1,
    exento_it TINYINT(1) DEFAULT 1,
    iue_exento TINYINT(1) DEFAULT 0,
    modalidad_sfe ENUM(
        'COMPUTARIZADA_EN_LINEA',
        'ELECTRONICA_EN_LINEA',
        'PORTAL_WEB'
    ) DEFAULT 'COMPUTARIZADA_EN_LINEA',
    cuis VARCHAR(20) NULL,
    codigo_sucursal VARCHAR(10) DEFAULT '0',
    agente_retencion_rc_iva TINYINT(1) DEFAULT 1,
    leyenda_iva_exento VARCHAR(255) NOT NULL,
    leyenda_it_exento VARCHAR(255) NOT NULL,
    leyenda_ley070 VARCHAR(255) NOT NULL,
    monto_mensualidad DECIMAL(10, 2) DEFAULT 0,
    estado ENUM('ACTIVO', 'INACTIVO') DEFAULT 'ACTIVO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 27. `educational_services`

```sql
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

    INDEX idx_codigo (codigo_sin),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 28. `invoice_sequences`

```sql
CREATE TABLE invoice_sequences (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo_sucursal VARCHAR(10) DEFAULT '0',
    punto_venta VARCHAR(10) DEFAULT '0',
    ultimo_numero INT UNSIGNED DEFAULT 0,
    estado ENUM('ACTIVO', 'INACTIVO') DEFAULT 'ACTIVO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_branch_pos (codigo_sucursal, punto_venta),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 29. `daily_cufds`

```sql
CREATE TABLE daily_cufds (
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
```

---

# 30. `significant_events`

```sql
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
```

---

# 31. `electronic_invoices`

```sql
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
    estado ENUM(
        'ENVIADA',
        'AUTORIZADA',
        'RECHAZADA',
        'CONTINGENCIA',
        'ANULADA'
    ) DEFAULT 'ENVIADA',
    xml_enviado TEXT NULL,
    xml_respuesta TEXT NULL,
    leyenda1 VARCHAR(255) NULL,
    leyenda2 VARCHAR(255) NULL,
    leyenda3 VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_nro_factura (nro_factura),
    INDEX idx_cuf (cuf),
    INDEX idx_fecha (fecha_emision),
    INDEX idx_estado (estado),
    INDEX idx_receptor (nit_receptor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 32. `invoice_details`

```sql
CREATE TABLE invoice_details (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    electronic_invoice_id BIGINT UNSIGNED NOT NULL,
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

    FOREIGN KEY (electronic_invoice_id)
        REFERENCES electronic_invoices(id)
        ON DELETE CASCADE,

    INDEX idx_electronic_invoice (electronic_invoice_id),
    INDEX idx_codigo (codigo_servicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 33. `contingency_queues`

```sql
CREATE TABLE contingency_queues (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    electronic_invoice_id BIGINT UNSIGNED NOT NULL,
    intentos TINYINT UNSIGNED DEFAULT 0,
    max_intentos TINYINT UNSIGNED DEFAULT 5,
    estado ENUM('PENDIENTE', 'ENVIANDO', 'COMPLETADO', 'FALLIDO') DEFAULT 'PENDIENTE',
    ultimo_error TEXT NULL,
    proximo_intento TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (electronic_invoice_id)
        REFERENCES electronic_invoices(id)
        ON DELETE CASCADE,

    INDEX idx_electronic_invoice (electronic_invoice_id),
    INDEX idx_estado (estado),
    INDEX idx_proximo_intento (proximo_intento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 34. `rc_iva_withholdings`

```sql
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
```

---

# 35. `student_rude_records`

```sql
CREATE TABLE student_rude_records (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    codigo_rude VARCHAR(20) NULL,
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

    FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE,

    UNIQUE KEY uk_student (student_id),
    UNIQUE KEY uk_codigo_rude (codigo_rude),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# 36. `medical_records`

```sql
CREATE TABLE medical_records (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    grupo_sanguineo VARCHAR(5) NULL,
    alergias TEXT NULL,
    medicamentos TEXT NULL,
    condiciones_especiales TEXT NULL,
    contacto_emergencia_nombre VARCHAR(150) NULL,
    contacto_emergencia_telefono VARCHAR(20) NULL,
    contacto_emergencia_parentesco VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE CASCADE,

    UNIQUE KEY uk_student (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

# RESUMEN DE CONVENCIONES

```text
TABLAS
────────────────────────────────────────
roles
users
students
parent_guardians
student_parent
teachers
academic_years
levels
grades
sections
courses
subjects
course_subjects
enrollments
registrations
student_documents
monthly_payments
assessments
grade_scores
attendances
schedules
announcements
announcement_recipients
announcement_reads
tax_configurations
educational_services
invoice_sequences
daily_cufds
significant_events
electronic_invoices
invoice_details
contingency_queues
rc_iva_withholdings
student_rude_records
medical_records


PK
────────────────────────────────────────
id


FK
────────────────────────────────────────
role_id
user_id
student_id
parent_guardian_id
teacher_id
level_id
grade_id
section_id
course_id
course_subject_id
subject_id
academic_year_id
enrollment_id
registration_id
assessment_id
registered_by_user_id
author_id
announcement_id
electronic_invoice_id


CORREO
────────────────────────────────────────
email
email_receptor


CAMPOS NORMALES
────────────────────────────────────────
nombre
nombres
apellidos
fecha
fecha_nacimiento
fecha_inicio
fecha_fin
estado
telefono
direccion
observaciones
etc.
```

## Integridad Referencial

```sql
-- Todas las foreign keys utilizan nombres en inglés.

-- ON DELETE:
-- CASCADE:
--   Elimina registros hijos relacionados.

-- RESTRICT:
--   Impide eliminar un registro que tenga dependencias.

-- SET NULL:
--   Coloca NULL en la FK cuando se elimina el registro padre.
```

## Convención final para Laravel

```text
PK:
id

FK:
tabla_singular_id

Ejemplos:
user_id
student_id
teacher_id
course_id
subject_id
registration_id
assessment_id

Correo:
email
```

*Documento actualizado: 2026-08-29*
*Motor: MySQL 8.0+*
*Charset: utf8mb4_unicode_ci*
