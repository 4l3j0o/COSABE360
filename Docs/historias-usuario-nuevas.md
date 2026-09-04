# Nuevas Historias de Usuario — Segunda Iteración (HU-36 a HU-46)

> Documento de trabajo. Rama base: `develop`. Continúa a [documentacion-final.md](./documentacion-final.md).

## 1. Alcance de esta iteración

1. **Acceso con Google (OAuth 2.0).** Cambiar el método de login a cuentas Google restringidas al dominio `cosabe.edu.bo` (API key / credenciales OAuth ya creadas en Google Cloud) y rediseñar la pantalla de login para acceso exclusivo con Google.
2. **Agrupación de módulos.** El panel del administrador está saturado: agrupar los módulos en secciones colapsables.
3. **Estructura académica jerárquica.** Navegación anidada: Gestión Escolar → Niveles → Cursos → Paralelos (hoy son 4 listados planos sin relación visual y `courses` ni siquiera tiene índice propio).
4. **Panel docente refactorizado.** Cada profesor ve los cursos y materias que dicta según su horario; dentro de cada curso: crear evaluaciones, llamar lista, y generar reportes de asistencia (día, semana, mes, trimestre) de todos los alumnos.
5. **RUDE completo.** Refactorizar el formulario RUDE con todos los campos que realmente exige el RUDE oficial de Bolivia (hoy solo captura un subconjunto, secciones I–VI parciales).

## 2. Criterio de orden (por qué en este orden)

| Orden | Fase | Justificación técnica |
|---|---|---|
| 1.º | **A — Identidad (HU-36 a HU-38)** | La autenticación es la base de todo: toca `AuthController`, rutas `guest/auth`, tabla `users` y afecta a todos los roles. Debe entrar primero para que el resto se pruebe ya con el login definitivo y no se re-testeé dos veces. Además define si se conserva o no el password local, decisión que condiciona seeders y cuentas existentes. |
| 2.º | **B — Navegación (HU-39, HU-40)** | El sidebar y la jerarquía académica son el "esqueleto" donde cuelgan todas las vistas nuevas (docente, RUDE, reportes). Hacerlo antes evita rehacer pantallas después. No depende del login salvo por testeo. |
| 3.º | **C — Panel docente (HU-41 a HU-44)** | Es el flujo de mayor valor diario (profesores). Reutiliza lo que ya existe (`docente.cursos`, `AssessmentController@porCurso`, `AttendanceController`) y cuelga de la navegación de la Fase B. Los reportes de asistencia (HU-44) van al final de la fase porque consumen el registro de la HU-43. |
| 4.º | **D — RUDE (HU-45, HU-46)** | Módulo grande pero independiente (solo Secretaría/Admin). Requiere primero un levantamiento contra el formulario oficial (HU-45, sin código) y luego la refactorización (HU-46). Al final porque no bloquea nada más. |

Regla de ejecución: **A → B → C → D**, con merge a `develop` al cerrar cada fase.

## 3. Nuevos requerimientos

| Código | Descripción | Categoría |
|---|---|---|
| RF33 | Iniciar sesión exclusivamente con cuenta Google del dominio `cosabe.edu.bo` (OAuth 2.0). | Autenticación |
| RF34 | Agrupar los módulos del panel en secciones colapsables por área. | Usabilidad |
| RF35 | Navegar la estructura académica de forma jerárquica (nivel → grado → paralelo → curso). | Académico |
| RF36 | Gestionar evaluaciones y asistencia desde la vista del curso del docente. | Académico |
| RF37 | Generar reportes de asistencia por día, semana, mes y trimestre (PDF/Excel). | Reportes |
| RF38 | Capturar el formulario RUDE completo según el formato oficial de Bolivia. | RUDE |

| Código | Descripción | Categoría |
|---|---|---|
| RNF15 | El login debe delegar la autenticación en Google (sin almacenar contraseñas locales salvo cuenta de emergencia). | Seguridad |
| RNF16 | La navegación debe mantenerse utilizable con más de 20 módulos sin scroll excesivo. | Usabilidad |

## 4. Historias de usuario

### Épica 12 — Acceso con Google Workspace (Sprint 7)

| ID | Historia | Puntos | Sprint |
|---|---|---|---|
| HU-36 | Como usuario, quiero iniciar sesión con mi cuenta Google de `cosabe.edu.bo` para acceder sin contraseña local. | 8 | 7 |
| HU-37 | Como usuario, quiero una pantalla de login rediseñada con acceso único "Continuar con Google". | 5 | 7 |
| HU-38 | Como administrador, quiero desactivar el login por contraseña y conservar una cuenta local de emergencia documentada. | 3 | 7 |

**HU-36 — Criterios de aceptación.**

- Se instala y configura el cliente OAuth (`laravel/socialite` + driver Google, `config/services.php`, credenciales en `.env`: client ID/secret/redirect del proyecto Google Cloud).
- El callback valida `hd=cosabe.edu.bo`; cualquier otro dominio es rechazado con mensaje claro.
- Si el email ya existe en `users`, se vincula (`google_id`) conservando rol y estado; si no existe, se deniega (o se crea según se defina) — sin duplicar correos (RB03).
- Nueva migración: `users.google_id` (unique, nullable), `users.avatar` (nullable).
- El bloqueo por intentos (RB06) deja de aplicar al flujo Google; sigue vigente solo para la cuenta de emergencia.

**HU-37 — Criterios de aceptación.**

- `auth/login.blade.php` rediseñada (institucional San Bernardo + botón Google según lineamientos de marca), sin campos email/password visibles.
- Responsiva (RNF09) y funcional en navegadores modernos (RNF08).
- Mantiene mensajes de error (dominio no permitido, cuenta inactiva) y redirección al dashboard por rol.

**HU-38 — Criterios de aceptación.**

- `POST /login` por contraseña queda deshabilitado (ruta retirada o bloqueada) salvo cuenta de emergencia.
- Seeder + procedimiento documentado para la cuenta local de emergencia (password segura RB07, uso solo con `APP_DEBUG`/consola).
- Cuentas existentes migradas sin pérdida de rol, estado ni historial.

**Archivos impactados (Épica 12):** `composer.json`, `config/services.php`, `.env.example`, `app/Http/Controllers/AuthController.php`, `routes/web.php` (43–57), `resources/views/auth/login.blade.php`, `app/Models/User.php`, nueva migración `users`, `database/seeders/AdminUserSeeder.php`, `Docs/`.

### Épica 13 — Navegación y estructura académica (Sprint 8)

| ID | Historia | Puntos | Sprint |
|---|---|---|---|
| HU-39 | Como administrador, quiero ver los módulos agrupados en secciones colapsables para no saturar el panel. | 5 | 8 |
| HU-40 | Como usuario autorizado, quiero navegar Gestión Escolar → Nivel → Grado → Paralelo → Curso de forma anidada. | 8 | 8 |

**HU-39 — Criterios de aceptación.**

- `_sidebar-admin.blade.php` (y `_sidebar-*.blade.php` equivalentes) reorganizado en grupos colapsables, p. ej.: Gestión Escolar, Personas, Académico, Comunicación, Reportes, Auditoría, Facturación SFE, Configuración.
- Acordeón funcional en desktop y móvil (`layouts/app.blade.php`), recordando sección abierta; sin scroll excesivo (RNF16).
- Ninguna ruta ni permiso cambia: solo presentación (mismo `role:` middleware).

**HU-40 — Criterios de aceptación.**

- Nuevo `courses.index` jerárquico con breadcrumbs: Nivel → Grados → Paralelos (sections) → Cursos del período activo.
- Las vistas `levels/index`, `grades/index`, `sections/index` enlazan al siguiente nivel en lugar de ser listados aislados.
- Se corrige el enlace actual de "Cursos" del sidebar (hoy apunta a `asistencias.index`).
- Filtros por gestión académica y conteos (materias, estudiantes) visibles en cada nivel.

**Archivos impactados (Épica 13):** `resources/views/dashboard/partials/_sidebar-*.blade.php`, `resources/views/layouts/app.blade.php`, `app/Http/Controllers/CourseController.php` (nuevo `index`), `routes/web.php` (180–190, 213–228, 267–273), `resources/views/{levels,grades,sections}/index.blade.php`, nuevo `resources/views/courses/index.blade.php`.

### Épica 14 — Panel docente: evaluaciones y asistencia (Sprints 8–9)

| ID | Historia | Puntos | Sprint |
|---|---|---|---|
| HU-41 | Como docente, quiero ver mis cursos y materias organizados según mi horario. | 5 | 8 |
| HU-42 | Como docente, quiero crear evaluaciones dentro de cada curso que dicto. | 5 | 9 |
| HU-43 | Como docente, quiero llamar lista y registrar la asistencia de mis alumnos por curso. | 5 | 9 |
| HU-44 | Como docente, quiero generar reportes de asistencia del día, semana, mes y trimestre en PDF/Excel. | 8 | 9 |

**HU-41 — Criterios de aceptación.**

- `docente.cursos` muestra cada asignación (`CourseSubject`) con su materia, curso, aula y bloques de horario (`schedules`), ordenado por día/hora.
- Accesos directos por tarjeta: Evaluaciones, Pasar lista, Asistencia, Notas.

**HU-42 — Criterios de aceptación.**

- La creación de evaluaciones ocurre dentro del curso (`docente.evaluaciones`), heredando materia y gestión sin pedirlas de nuevo.
- Se mantiene la validación día-de-clase y la autorización de titular (`AssessmentController@autorizarEvaluacion`).

**HU-43 — Criterios de aceptación.**

- Vista "llamar lista": nómina del curso con los 5 estados (RB17) en un solo envío, fecha por defecto hoy.
- Solo el docente titular (o responsable del paralelo) puede registrar; el upsert por (estudiante, curso, fecha, materia) evita duplicados.

**HU-44 — Criterios de aceptación.**

- Reportes de asistencia con filtros día / semana / mes / trimestre (+ materia y curso), de todos los alumnos del curso.
- Exportación PDF (DomPDF, membrete San Bernardo) y Excel/CSV, mismo patrón de `ReporteExportController` (límite 500 filas).
- Accesible también para Director/Admin con alcance institucional.

**Archivos impactados (Épica 14):** `app/Http/Controllers/TeacherCourseController.php`, `resources/views/docente/cursos.blade.php`, `app/Http/Controllers/{AssessmentController,GradeScoreController,AttendanceController}.php`, `resources/views/{assessments,attendances,grade-scores}/*.blade.php`, `app/Http/Controllers/ReporteExportController.php` (o nuevo `AttendanceReportController`), rutas `web.php` (231–264, 381–388).

### Épica 15 — RUDE oficial Bolivia (Sprint 10)

| ID | Historia | Puntos | Sprint |
|---|---|---|---|
| HU-45 | Como secretaría, quiero que el sistema pida exactamente los campos del RUDE oficial vigente para no reprocesar. | 3 | 10 |
| HU-46 | Como secretaría, quiero registrar, validar y exportar el RUDE completo con formato oficial MinEdu. | 8 | 10 |

**HU-45 — Criterios de aceptación (sin código).**

- Levantamiento campo por campo contra el formulario RUDE oficial vigente (RM correspondiente), mapeado a: capturar / ya existe en `Student` / nuevo en `student_rude_records`.
- Documento de mapeo en `Docs/` aprobado por el PO antes de programar.

**HU-46 — Criterios de aceptación.**

- `rude/form` con todas las secciones oficiales, validaciones (CI boliviano, certificado, SIE 8 dígitos) y guardado parcial por sección.
- `rude/show` y exports (`export`, `exportList`) alineados al formato oficial MinEdu (PDF/Excel además del CSV actual).
- Migración de ampliación de `student_rude_records` con rollback probado; datos existentes intactos.

**Archivos impactados (Épica 15):** `app/Http/Controllers/RudeController.php`, `app/Models/StudentRudeRecord.php`, migración `student_rude_records`, `resources/views/rude/{index,show,form}.blade.php`, nuevo `Docs/rude-mapeo-oficial.md` (HU-45).

## 5. Plan de sprints

| Sprint | Contenido | Puntos | Estado |
|---|---|---|---|
| Sprint 7 | HU-36, HU-37, HU-38 — Identidad Google | 16 | Finalizado |
| Sprint 8 | HU-39, HU-40 — Navegación (HU-41 en rama panel-docente) | 13 | HU-39/40 Finalizado |
| Sprint 9 | HU-42, HU-43, HU-44 — Evaluaciones, lista y reportes | 18 | Por iniciar |
| Sprint 10 | HU-45, HU-46 — RUDE oficial | 11 | Por iniciar |
| **Total** | **11 historias** | **63** | |

## 6. Trazabilidad

| Requerimiento | Historia | Épica |
|---|---|---|
| RF33, RNF15 | HU-36, HU-37, HU-38 | 12. Acceso Google |
| RF34, RNF16 | HU-39 | 13. Navegación |
| RF35 | HU-40 | 13. Navegación |
| RF36 | HU-41, HU-42, HU-43 | 14. Panel docente |
| RF37 | HU-44 | 14. Panel docente |
| RF38 | HU-45, HU-46 | 15. RUDE |

## 7. Ramas de trabajo (desde `develop`)

| Rama | Contenido |
|---|---|
| `HU-36-38-acceso-google` | Épica 12 completa |
| `HU-39-40-navegacion-modulos` | Épica 13 completa |
| `HU-41-44-panel-docente` | Épica 14 completa |
| `HU-45-46-rude-completo` | Épica 15 completa |

Flujo por rama: trabajar → PR contra `develop` → merge al cerrar la fase (orden A → B → C → D).

## 8. Registro de implementación — Épica 12 (HU-36 a HU-38)

### Cuenta local de emergencia (HU-38)

| Campo | Valor |
|---|---|
| Email | `admin@csb360.local` |
| Rol | Administrador |
| Uso | Solo acceso por contraseña de respaldo si Google falla |
| Habilitación | `EMERGENCY_LOGIN_ENABLED=true` en `.env` (por defecto solo en `local`) |
| Credencial | Definida localmente; no se versiona en el repo |

Reglas de acceso por contraseña tras HU-38:

- El `POST /login` queda **restringido únicamente** a la cuenta de emergencia (`config('app.emergency_admin_email')`).
- Cualquier otro correo que intente entrar por contraseña es redirigido al login de Google con mensaje de que el acceso por contraseña está deshabilitado.
- El resto de usuarios accede **exclusivamente** por Google Workspace (`@cosabe.edu.bo`).
- La contraseña de emergencia se almacena con `Hash::make` (RB07) y solo aplica si `EMERGENCY_LOGIN_ENABLED` es true.

### Seguridad SSL en Windows (nota de ambiente)

PHP en Windows requiere un bundle de certificados CA para verificar HTTPS (OAuth de Google). Se configuró:

- `curl.cainfo` y `openssl.cafile` en `php.ini` apuntando a `cacert.pem` descargado en el directorio de PHP.
- Sin esto, el callback OAuth fallaba con `cURL error 60`.

### Cambios de base de datos (Épica 12)

Tabla `users` — se agregaron columnas (no se eliminó `password`, se conserva para la cuenta de emergencia y migración):

- `google_id` (string, unique, nullable): id del usuario en Google; se autocompleta en el primer acceso.
- `avatar` (string, nullable): foto de perfil de Google.

El login real se resuelve por `google_id`/`email`; `password` queda como respaldo exclusivo de la cuenta de emergencia (`admin@csb360.local`).

### Cambios de interfaz (Épica 13, HU-39 y HU-40)

- **HU-39** — El sidebar del administrador se reorganizó en secciones colapsables (`<details>`/`<summary>`): Gestión Escolar, Personas, Comunicación, Reportes, Auditoría, Configuración y Facturación SFE. Un script guarda en `localStorage` los grupos abiertos. No cambia ninguna ruta ni permiso.
- **HU-40** — Nueva vista jerárquica `courses.index` (ruta `GET /cursos` solo admin) que muestra Nivel → Grado → Paralelo → Curso con conteos (materias y estudiantes), con enlaces a la gestión de grados (filtro `level_id`) y paralelos (filtro `grade_id`). El enlace "Cursos" del sidebar ahora apunta a `courses.index` (antes apuntaba a `asistencias.index`).
