# HU-22 — Registrar calificaciones

**Historia:** *Como docente, quiero registrar calificaciones de mis estudiantes, para evaluar su desempeño.*

**Rama:** `HU22-registrar-calificaciones` (base: `HU21-registrar-evaluaciones`)
**Prioridad:** Alta · **Puntos:** 5 · **Sprint:** 4

> **Nota de base:** esta rama se creó sobre `HU21-registrar-evaluaciones` porque `grade_scores` depende de la entidad `assessments`. Al integrar, primero mergear HU-21 a `develop` y luego HU-22.

---

## Alcance

Permite al docente seleccionar una evaluación y registrar la calificación (0-100) de cada estudiante de forma masiva, con opción de observaciones. Usa `updateOrCreate`, por lo que vuelve a guardar notas existentes sin duplicar.

## Criterios de aceptación (SRS)

- ✅ El sistema debe permitir ingresar notas del 0 al 100 para cada estudiante en cada evaluación.
- ⚠️ *Solo el docente asignado a la materia puede registrar calificaciones.* — Implementado por rol `Docente`; la asignación fina docente→curso/materia (HU-16) aún no está disponible, se documenta como limpieza pendiente.
- ✅ El sistema debe validar que la nota esté en el rango permitido (0-100).

## Cambios realizados

### Migración
- `create_grade_scores_table` — se llenó el esquema: `evaluacion_id` (FK assessments), `estudiante_id` (FK students), `nota` DECIMAL(5,2), `observaciones`, única `(evaluacion_id, estudiante_id)`.

### Modelo
- `GradeScore` — fillable + casts (`nota`) + relaciones `assessment()`, `student()`.
- `Assessment` ya tenía `gradeScores()`; `Student` ya tenía `gradeScores()`.

### Controller
- `GradeScoreController` — `index` (lista evaluaciones con conteo de notas), `show` (formulario con estudiantes activos y notas existentes), `update` (guardado masivo vía `updateOrCreate`, valida nota 0-100).
- `AssessmentController::destroy` — ahora bloquea eliminar una evaluación que tenga calificaciones.

### Rutas (`routes/web.php`)
Protegidas con `auth` + `role:Docente` (prefix `calificaciones`):
`index`, `show`, `update`.

### Vistas
- `grade-scores/index.blade.php` — listado de evaluaciones con botón "Registrar Calificaciones".
- `grade-scores/show.blade.php` — tabla de estudiantes activos con campo de nota y observaciones.
- `dashboard/partials/_sidebar-docente.blade.php` — se añadió enlace "Calificaciones".

### Tests
- `tests/Feature/GradeScoreTest.php` — auth, vista, registrar, actualizar, validación fuera de rango, notas vacías ignoradas, bloqueo de borrado con notas, acceso denegado.

---

## Decisiones y notas

- **Estudiantes listados:** se listan todos los estudiantes `ACTIVO` (la asignación estudiante→curso/paralelo aún no está operativa; se completará con HU-17/HU-18).
- **Nota vacía:** se ignora (no se guarda ni borra).
- **Duplicados:** la clave única `(evaluacion_id, estudiante_id)` evita registros dobles y permite `updateOrCreate`.

---

*Documento HU-22 · Sistema Gestión Escolar San Bernardo*
