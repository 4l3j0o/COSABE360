# Nuevas Historias de Usuario — Segunda Iteración (HU-36 a HU-57)

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
| Sprint 9 | HU-42, HU-43, HU-44 — Evaluaciones, lista y reportes | 18 | Finalizado |
| Sprint 10 | HU-45, HU-46 — RUDE oficial | 11 | Finalizado |
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
| `HU-57-Arreglar_Reportes_De_Evaluacion` | Épica 18: correcciones de lógica y reportes de evaluación |
| `HU-58-llamada_reportes` | Épica 19: Central de Reportes (agrupa todos los reportes + llamado a lista) |
| `HU-59-Flujo_Inscripcion` | Épica 20: Flujo de inscripción de estudiante nuevo (en curso) |

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

### Panel docente (Épica 14, HU-41 a HU-44)

- **HU-41** — "Mis Cursos" (`TeacherCourseController@index`) ahora carga los bloques de horario (`schedules`) ordenados por día/hora y los muestra como chips en cada tarjeta; se añadió acceso rápido de 4 acciones: Evaluaciones, Pasar lista, Historial de asistencia y Notas.
- **HU-42** — Ya existía: `docente.evaluaciones` crea evaluaciones dentro del curso heredando materia y gestión (validación de día de clase y peso). Sin cambios.
- **HU-43** — Ya existía: `AttendanceController@create/store` registra asistencia por curso con los 5 estados (RB17) y upsert sin duplicados. Sin cambios.
- **HU-44** — Nuevo `AttendanceReportController` con `index` y `export`: filtros por rango (día/semana/mes/trimestre), fecha base, curso y materia; conteos por estado y % de asistencia; exportación PDF (DomPDF) y Excel/CSV. Rutas `reportes.asistencia.index/export`, accesible a Docente/Secretaría/Director/Admin. Enlaces añadidos en los sidebars docente, admin y director.

### RUDE oficial Bolivia (Épica 15, HU-45 y HU-46)

- **HU-45** — Nuevo documento `Docs/rude-mapeo-oficial.md`: levantamiento campo por campo del formulario oficial RUDE 2025 (Resolución Ministerial Nº 0031/2025, formato Regular) con el mapeo capturar / ya existe en `Student` / nuevo en `student_rude_records`.
- **HU-46** — Ampliación completa del módulo RUDE para capturar las secciones I-V oficiales:
  - **Migración nueva** `2026_09_04_191411_expand_student_rude_records_oficial.php`: agrega Sección I (Unidad Educativa), Sección III (Dirección actual), Sección IV (socioeconómico) y campo 2.9 (Registro de discapacidad IBC). Las selecciones múltiples se guardan como JSON. `down()` elimina solo las columnas añadidas (rollback seguro, datos previos intactos).
  - **Modelo** `StudentRudeRecord` — nuevas constantes de opciones oficiales (idiomas, autoidentificación, seguro salud, baño, vivienda, internet, actividades laborales, transporte, razones de abandono, tipos/grados de discapacidad, dificultades de aprendizaje, convivencia) + casts `array`/`boolean` + helper `listar()`.
  - **Controlador** `RudeController@update` — valida y persiste todas las secciones; CI 7-8 dígitos + complemento, certificado completo (si uno, todos), SIE 8 dígitos.
  - **Vistas** `rude/form`, `rude/show`, `rude/index` — formulario y detalle con las secciones I-V y estado.
  - **Export** — alineado al formato oficial MinEdu RM 0031/2025 (secciones I-V), incluyendo datos de padre/madre/tutor desde `parent_guardians`.
  - **SIE de la UE** — constante `StudentRudeRecord::SIE_UE = '81730184'`; se usa como valor por defecto de `codigo_sie_ue` y se aplicó a los 1145 registros RUDE existentes.
  - **Código interno del estudiante** — el campo `students.codigo` pasa a almacenar el código interno de la columna `Codigo` del Excel de inscripciones (sin prefijo `EST-`); el RUDE oficial usa `codigo_rude` (columna `Rude` del Excel). Se actualizaron los 1512 estudiantes por match de CI.
  - **Docentes sin código** — migración `2026_09_04_200000_remove_codigo_from_teachers_table.php` elimina la columna `codigo` de `teachers`; se limpiaron controlador, vistas y seeders.
  - **Navegación** — el RUDE ya no está en el menú lateral; se accede desde el detalle del estudiante (botón "RUDE").

### Mensualidad y descuentos (configuración)

- **Mensualidad base** — actualizada a **Bs 785.50**: `TaxConfiguration.monto_mensualidad` y las 1512 matrículas (`registrations.monto_mensual`).
- **Tipos de descuento / becas** — nuevo módulo `DiscountType`:
  - Migración `2026_09_04_210000_create_discount_types_table.php` (tabla `discount_types`).
  - Modelo `DiscountType` con `tipo_aplicacion` (`PORCENTAJE` / `IMPORTE_FIJO` / `IMPORTE_MANUAL`), `porcentaje`, `importe_fijo`, `orden` y método `montoDescuento($base)`.
  - Controlador `DiscountTypeController` con CRUD (index/store/update/destroy) y validaciones.
  - Vistas `discount-types/index` (tabla + modal crear/editar + eliminar).
  - Rutas bajo `facturacion/descuentos` (Admin/Secretaría/Director) y enlace en el sidebar de Configuración.
  - Seeder `DiscountTypeSeeder` carga los **18 descuentos** con códigos únicos por porcentaje (BECAEX100, CREDITO, BECA3HER70/100/80, BECAFUN60/30/40/55/70, BECASOCIO100/80/60, BECAESP50/70/100/20/80).

## Épica 16 — Evaluación por Trimestres y Criterios (Bolivia) + Regente y Comunicados

> Alineado a R.M. 473/2021 / R.M. 001/2022 y a la plantilla real del colegio (`S6C-CE_LELO_TRIM2.xlsx`).
> Criterios oficiales: **SE (SER 10%), SA (SABER 45%), HA (HACER 40%), AU (AUTOEVALUACIÓN 5%)**.

### Registro de implementación — HU-47 a HU-50

- **HU-47 — Modelo de evaluación por trimestres y criterios.**
  - Migración `2026_09_07_000000_create_evaluation_trimestres_tables.php`: tablas `evaluation_plans`, `evaluation_criteria` (SE/SA/HA/AU), `evaluation_activities`, `evaluation_activity_grades`.
  - `EvaluationService`: nota de criterio (promedio redondeado a entero), nota de trimestre (suma de criterios, 0-100), promedio anual (promedio de 3 trimestres) y escala ED/DA/DO/DP (mínimo 51). Validado contra la plantilla real (trimestre=68, anual=76).
  - Interfaz en "Mis Cursos": `EvaluationPlanController` (index/guardar/show/agregarActividad/guardarNotas), rutas `mis-cursos/{courseSubject}/evaluaciones-trimestre`, vistas `evaluations/*`.

- **HU-48 — Plantillas Excel de actividades (descargar y subir).**
  - Dependencia `phpoffice/phpspreadsheet`.
  - `EvaluationTemplateService`: exporta plantilla (.xlsx) con hojas Criterios y Notas (estudiantes × actividades por criterio); importa y actualiza las **notas** de las actividades existentes (las actividades se administran desde la web).
  - Rutas `evaluations.template-download/upload` y botones en la vista.
  - Estilo institucional (HU-57): logo centrado, membrete con membrete/gestión y tarjeta de datos (DOCENTE/CURSO/MATERIA/TRIMESTRE), bloques por criterio con color y columna PROMEDIO.
  - Al importar se rechazan notas fuera de rango (0..porcentaje del criterio) y se reportan por fila/actividad.

- **HU-49 — Reportes del docente.**
  - `EvaluationReportController`: **primero se elige curso/materia** (con una sola asignación se preselecciona), luego trimestre; notas por criterio, nota de trimestre, valoración, estado aprobado/reprobado, % asistencia (del trimestre) y top calificaciones.
  - Export PDF (DomPDF) y Excel con membrete institucional, logo y estructura por colores.

- **HU-50 — Rol Regente + comunicados automáticos de licencia.**
  - Rol **Regente** (RoleSeeder) asignado a `ecamacho@cosabe.edu.bo`.
  - Estado **LICENCIA** en asistencia (migración reconstruye `attendances` con CHECK ampliado).
  - `RegenteController`: lista cursos, modifica listas a su criterio, registra licencias.
  - Al registrar una licencia se crea un comunicado **PUBLICADO** dirigido (`CURSO_ESPECIFICO` por user_id) **solo a los docentes del curso del estudiante**.
  - `DocenteUsuariosSeeder`: crea cuentas `@cosabe.edu.bo` (rol Docente) y las vincula a los 55 docentes reales.

## Épica 17 — Cumplimiento normativo educativo boliviano

### Registro de implementación — HU-51 a HU-56

- ~~**HU-51 — Segunda instancia / examen de recuperación.**~~ **Retirado.** La tabla `evaluation_second_attempts`, el `SegundaInstanciaController`, su vista y rutas fueron eliminados.

- **HU-52 — Escala de valoración y promoción/retención.**
  - `promedioAnual` usa `notaTrimestre` (sin segunda instancia).
  - `CierreGestionController`: promedio anual por materia, valoración ED/DA/DO/DP, promovido/retenido (retiene si alguna área < 51, Art. 51). Vista + export PDF/Excel.

- **HU-53 — Libreta electrónica / boletín oficial SIE.**
  - `BoletinController`: boletín por estudiante con notas por área, valoración y promedio. Acceso a Secretaría/Director/Admin y Padre/Tutor de su hijo. Vista + PDF.

- **HU-54 — Educación inclusiva (Ley 070).**
  - Migración `2026_09_07_160000_create_student_curricular_adaptations_table.php`.
  - `InclusionController`: CRUD de adaptaciones curriculares por estudiante (condición, tipo, grado, plan, modalidad, evaluación diferenciada). Vista `inclusion/show`.

- **HU-55 — Protección de datos personales y derechos ARCO.**
  - Migración `2026_09_07_170000_create_data_consents_arco_requests_tables.php` (`data_consents` + `arco_requests`).
  - `DataProtectionController`: consentimientos y solicitudes ARCO (Acceso/Rectificación/Cancelación/Oposición). Vista `data-protection`.

- **HU-56 — Requisitos de inscripción y expediente documental.**
  - Requisitos por nivel (R.M. 001/2022 Art. 12): Inicial (certificado nacimiento + vacunas); Primaria/Secundaria (libreta + documento).
  - `ExpedienteController`: checklist por estudiante según nivel. Migración `student_documents.archivo` nullable. Vista `expediente/show`.

## Épica 18 — Correcciones de lógica y reportes de evaluación (HU-57)

### Registro de implementación — HU-57

- **HU-57 — Arreglo de Reportes de Evaluación.**
  - **Flujo curso-primero:** `EvaluationReportController` exige elegir curso/materia antes de generar; con una sola asignación se preselecciona. Se **retiró el filtro "Asistencia (período)"** (la asistencia es siempre del trimestre).
  - **Lógica de evaluación corregida (`EvaluationService::notaCriterio`):** el promedio del criterio ahora considera **solo las actividades con nota registrada** (ignora las no calificadas), igual que `AVERAGE` de la planilla oficial. Devuelve `null` si no hay ninguna nota (antes devolvía `0`, mostrando "Reprobado" en vez de "Sin nota"). Los criterios `VALOR_UNICO` (SE/AU) promedian todas sus actividades si hay más de una.
  - **Validación de tope:** al guardar notas (web) y al importar Excel se rechazan valores fuera de `0..porcentaje` del criterio (evita notas de trimestre > 100). `EvaluationPlanController::guardarNotas` reporta las omisiones.
  - **Exports mejorados:** PDF y Excel del reporte con membrete institucional, **logo centrado y agrandado**, colores y estructura (resumen, zebra, mejores calificaciones, firmas en PDF).
  - **Plantilla Excel (HU-48):** membrete institucional, tarjeta de información con etiquetas alineadas, logo 110px centrado y **sin el punto inicial** en los nombres de estudiantes.
  - **Tests:** `EvaluationLogicTest` (8) y `EvaluationReportTest` (6). Suite completa: 274 passed / 2 skipped.

## Resumen de estado

**Épicas completadas e integradas en `develop`:**
- **Épica 16** (HU-47 a HU-50) ✅
- **Épica 17** (HU-51 a HU-56) ✅
- **Épica 18** (HU-57) ✅

## Épica 19 — Central de Reportes (HU-58, `llamada_reportes`)

> Agrupa todos los reportes del sistema en un único módulo (`/reportes`) y añade los
> reportes del "llamado a lista". Sin push/merge hasta cerrar la HU.

### Registro de implementación — HU-58

- **Landing `/reportes` (`ReporteCentralController@index`)** — tabla Nombre/Descripción/Parámetros/Ejecutar.
  Nuevos reportes con formulario GET inline (selectores curso/materia/trimestre); reportes
  existentes como enlace "Abrir" (asistencia, evaluación, cierre, académicos, administrativos).
- **Lista de Padres de Familia** — padres y apoderados por estudiante (parentesco, CI,
  teléfono, email, principal). Parámetros: curso (opcional) / gestión. PDF/Excel.
- **Lista de RUDEs** — RUDEs del paralelo seleccionado. El curso completo solo es visible
  para el tutor/encargado (`sections.teacher_id`) o supervisión; otros docentes reciben 403.
  Parámetro: paralelo (obligatorio). PDF/Excel.
- **Centralizador por evaluación** — matriz evaluaciones (sistema `assessments` con peso)
  × estudiantes + nota final ponderada. Parámetro: curso / materia. PDF/Excel.
- **Centralizador por trimestre** — matriz actividades por criterio (SE/SA/HA/AU)
  + prom criterios + nota trimestre, valoración y estado (sistema `evaluation_plans`).
  Parámetros: curso / materia / trimestre. PDF/Excel.
- **Permisos** — Admin/Director/Secretaría: total. Docente: solo sus cursos/materias.
  Enlace "Central de Reportes" en sidebars admin, director, secretaría y docente.
- **Rutas** — `reportes.central.*` bajo prefijo `/reportes`.

## Épica 20 — Flujo de Inscripción (HU-59, `Flujo_Inscripcion`, en curso)

> Inscripción de estudiante nuevo por directores de nivel. Sin push/merge hasta cerrar.

### Registro de implementación — HU-59

- **Directores por nivel** — tabla `director_level` + `User::nivelesACargo()` /
  `puedeInscribirNivel()` + `DirectorNivelSeeder` (msuarez→Inicial/Primaria,
  mrodriguez→Secundaria). El formulario y el guardado filtran por nivel (403 si no).
- **Código correlativo** — numérico, máx + 1 sin saltos (`siguienteCodigo()`, recalculado
  en servidor dentro de la transacción).
- **Validación por CI** — `GET inscripciones/buscar-por-ci` devuelve código anterior y
  datos para autocompletar; avisa si ya está inscrito en el período.
- **Store** — crea estudiante + padre/madre/tutor (principal elegible) + cuenta User
  Estudiante (email Google) y del tutor principal + Enrollment PENDIENTE
  (preinscripción) + Registration INACTIVA con curso preasignado + 5 documentos
  pendientes de Inscripción en Línea.
- **Inscripción en Línea** — `/mi-inscripcion` (rol Estudiante, solo propio): sube CI
  anverso/reverso, certificado de nacimiento, CI de papás y comprobante de pago
  (PDF/JPG/PNG 5MB → ENTREGADO); descarga contrato y anexo.
- **Contrato + anexo PDF** — generados con los datos del sistema (contratante, estudiante,
  RUDE, curso/nivel, gestión y montos de `TaxConfiguration`); firma en papel.
- **Verificación en contabilidad** — `PATCH verificar-pago` (Admin/Secretaría/Director):
  PENDIENTE→COMPLETADA (inscrito). Botón en la ficha del estudiante.
- **Matrícula** — reutiliza la INACTIVA preasignada (activa + genera cuotas) sin duplicar.
- **PREINSCRITO** — badge y filtro en `students.index` (SIN_INSCRIBIR intacto).
- **Tests** — `InscripcionFlujoTest` (8).
- **Materias oficiales por nivel y curso** — migración `codigo/sigla/area/grupo` en
  `subjects` + `MateriasOficialesSeeder` (listas NIFC 8, PVC 11, SCP 17 siglas;
  enriquece existentes por nombre normalizado y crea faltantes, idempotente, sin
  tocar asignaciones).   `Docentes2026Seeder` con búsqueda tolerante a renombres.
- **Normalización del catálogo** — `NormalizarMateriasSeeder`: fusiona duplicadas en
  la canónica oficial remapeando course_subjects/assessments/schedules/attendances
  (ej. Inglés→LE con sus 630 asistencias, Educación Física→EF) y elimina 13 antiguas
  sin referencias. 51→36 materias, cero huérfanos. `subjects.index` ordenado por
  nivel+código con columnas Cód/Sigla.
- **Asignaciones desde Excel oficial** — `database/data/docentes_20261.xlsx` (468 filas)
  + `AsignacionImportService` + `AsignacionesExcelSeeder` (idempotente, el Excel manda;
  carga horaria a `course_subjects.horas_semanales`). Huecos reales del Excel: DSAE
  (Inicial), CFIL (S6A/S6C) y EMP (S6) sin docente.
- **Lista oficial de libretas** — `Subject::resolver()` + mapa legacy; catálogo con
  nombres de libreta (Primaria 9 áreas con comunicación única, TEC solo S1-2 como
  TÉCNICA TECNOLÓGICA GENERAL, CN: BIO-GEO/FÍSICA/QUÍMICA, COSMOVISIONES con SICOLOGÍA);
  `MateriasLibretaSeeder` fusiona LELO/LE→CL y PS→CFIL con remapeo total.
- **Tutores de curso** — `TutoresCursoSeeder` (40 paralelos con docente responsable;
  distinto de padres/tutores `parent_guardians`).
- **Inscripción en Línea 5 pasos (HU-59)** — reserva de código correlativo (30 min,
  sin choques, se libera si no se concreta); paso 1 RUDE propio; paso 2 domicilio
  con mapa + foto fachada; paso 3 documentos por vínculo/nivel + anexos Docs;
  paso 4 contrato en línea (NIT/razón/ACEPTO); paso 5 revisión + consentimiento y
  bloqueo de cambios.
- **Nómina docente oficial** — `DocentesDatosOficialesSeeder` (CI/tel/email por nombre,
  sync cuenta Google; 56/77, resto ausente pendiente). `Docentes2026Seeder`
  idempotente por nombre. Importador de asignaciones determinista.
- **Limpieza de nivel cruzado** — `LimpiarCruceNivelSeeder`: el seeder viejo asignaba
  por nombre sin filtrar nivel (190 casos en 40 cursos + 630 asistencias); fusiona cada
  fila cruzada en la correcta del mismo docente/curso y mueve asistencias.
- **Limpieza HU-61** — retirado el módulo viejo de calificaciones (Assessment/
  GradeScore/FinalGrades controllers, observer, vistas assessments/grade-scores/
  calificaciones/notas-finales/reportes, seeders y tests asociados; modelos y tablas
  se conservan por historial). Rutas redirigen al sistema vigente (boletín, trimestre,
  reportes); enlaces actualizados en sidebars/dashboards.
- **Consolidación HU-58b** — la Central es el módulo único: sección Administrativos con
  descarga directa (inscripciones/matrículas/pagos/documentación); landings viejas
  redirigen a central; sidebars solo con Central; retirados exports legacy vacíos
  (assessments sin datos); fix export Pagos (usa `monto` real).
- **Restricción Director** — Matrículas y Pagos solo Admin/Secretaría (rutas + central).
- **Dashboards unificados** — una sola tarjeta Central de Reportes (admin/director/secretaría).
- **Excel real en todos los reportes** — los 6 exports viejos pasaron de CSV con comas
  a XLSX con columnas y línea visual institucional (RUDE mantiene CSV oficial MinEdu).
- **Centralizador por evaluación en sistema vigente** — usaba `assessments` (módulo
  viejo sin datos); ahora muestra T1/T2/T3 + promedio anual por materia (evaluation_plans).
- **Filtros en cascada** — centralizadores filtran primero por paralelo y luego se
  habilita la materia del paralelo (parcial compartido; compat con course_subject_id).
- **Panel estudiante** — email + paralelo, tabla INFORMACIÓN ECONÓMICA (cuotas con
  vencimiento, importes, pagos y factura); asistencia con curso bloqueado; boletín
  exige estar al día (si no, redirige a pagos recordando meses pendientes).
- **RUDE oficial completo (HU-45/46)** — municipios por departamento + cascadas I/III
  (con Otro), precarga UE San Bernardo, bloques padre/madre/tutor (CI, apellidos,
  ocupación, fecha nac, idioma, grado instrucción) con validación total, núcleo
  obligatorio, PDF formato oficial RM 0031/2025 (checks, firmas, QR).
- **RUDE fiel al formulario real** — Sección I solo SIE; 2.5.1/2.5.2 extranjeros,
  meses trabajados, transporte-otro, centro de acogida y tutor extraordinario
  (cargo/institución) con validación y PDF.
- **Export trimestre formato planilla** — el centralizador por trimestre vuelve al
  formato de ejemplo (logo 110, tarjeta DOCENTE/CURSO/MATERIA/TRIMESTRE, bloques por
  criterio con colores, PROMEDIO por criterio y total); reutiliza la hoja Notas del
  servicio de plantillas con valores calculados en vez de fórmulas.

## HU-65: Diario de aula + Reportes de conducta (2026-10-05)
- **Diario de aula** � el docente anota por clase: avance curricular, ejercicios propuestos y observaciones (diario_aulas); visible para estudiantes de su curso, padres, directores y admin. Rutas /diario-aula.
- **Reportes de conducta** � faltas LEVE/GRAVE/MUY_GRAVE con fecha, hora, materia (o General) y descripci�n (conducta_reportes); el estudiante solo ve las propias. Rutas /conducta.
- **Reportes**: general por curso (filtros curso/materia/tipo/rango + conteos) y espec�fico por estudiante (historial + conteo por tipo); ambos exportan a PDF (dompdf) y Excel (PhpSpreadsheet).
- **Permisos** � registra: Docente asignado al curso (o tutor del paralelo) + Admin/Director/Secretar�a; consulta: + Estudiante, Padre/Tutor, Regente (solo conducta). Sin Policies: ownership en controlador, como asistencias.

## HU-65b/65c: accesos por curso + acciones rapidas + demo (2026-10-05)
- **Tarjetas Mis Cursos** � cada tarjeta curso+materia trae botones Diario y Conducta prefiltrados a ese curso/materia.
- **Acciones rapidas del panel docente** � Diario de Aula y Conducta abren la clase actual segun horario (hora Bolivia); fuera de horario muestran aviso y llevan a Mis Cursos (rutas diario.ahora, conducta.ahora).
- **Sin selectores en dashboard** � el selector curso/materia en cascada se retiro; direccion/admin/secretaria/regente mantienen acceso global en sidebar; estudiante/padre entran desde sus paneles.
- **DocenteDemoSeeder** � horario semanal sin choques para los 10 cursos del docente (+ bloque que cubre la hora actual), 30 bitacoras, 14 reportes de conducta, asistencias de 5 dias y 2 comunicados a docentes. Corre con php artisan db:seed --class=DocenteDemoSeeder.

## HU-82: Comunicados multi-paralelo + licencias con expediente (2026-10-19)
- **Comunicados a varios paralelos** � un mismo comunicado puede dirigirse a N paralelos; el request usa `section_ids[]` (se admite `section_id` unico por compatibilidad). Se crea una fila en `announcement_recipients` por paralelo y `ComunicadoAlcanceService::visiblePara()` evalua TODOS los destinatarios (antes solo el primero, bug de visibilidad).
- **Licencias con expediente (HU-82)** � nueva tabla `licencias` (student, section, fecha_inicio/fin, motivo, evidencia_path, announcement_id, estado, created_by_user_id). El Regente la registra directo; se aplica `LICENCIA` a TODAS las materias del curso en los dias con clase (segun horario; lun-sab si no hay horario) y se genera un comunicado PUBLICADO a los docentes del curso con la evidencia adjunta.
- **Bloqueo al docente** � un dia con licencia activa es solo lectura en la lista del docente (radios deshabilitados); el servidor ignora cualquier estado enviado y conserva `LICENCIA`. El Regente anula la licencia (revierte filas y archiva el comunicado).
- **Listas de asistencia (Opcion B - consolidar)** � las listas por materia son la unica fuente de verdad; se eliminan las filas generales (`subject_id IS NULL`) via migracion. La vista del Regente es un consolidado de solo lectura (estudiantes x materias) mas un corrector por materia. `AttendanceReportController` y `EvaluationReportController` ignoran/filtran por materia (se corrige el doble conteo de porcentajes).
- **Boton "Limpiar"** � en la lista del docente, vuelve todo a AUSENTE manteniendo intactas las licencias (como "Presentes a todos").
- **Modulo de licencias separado** � el registro y la anulacion de licencias viven en su propio modulo (`regente.licencias`; `LicenciaController` + `LicenciaService`), con enlace en el sidebar del Regente, independiente de "Listas de Asistencia". La vista del curso (`regente.curso`) solo conserva el consolidado y el corrector por materia.
- **Encabezados del consolidado** � las columnas usan `subjects.sigla` (MAT, CL, LEO...) en lugar de truncar el nombre a 8 caracteres (que cortaba tildes y repetia columnas).
- **Pendiente** � `Conducta` y `Diario de Aula` quedan para una iteracion posterior.
