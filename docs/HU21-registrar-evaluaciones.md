# HU-21 — Registrar evaluaciones

**Historia:** *Como docente, quiero registrar evaluaciones por materia y período, para definir qué se calificará.*

**Rama:** `HU21-registrar-evaluaciones` (base: `develop`)
**Prioridad:** Alta · **Puntos:** 5 · **Sprint:** 4

---

## Alcance

Permite al docente crear, listar, editar y eliminar evaluaciones asociadas a una materia y a un período (gestión) académico.

## Criterios de aceptación (SRS)

- ✅ El sistema debe permitir crear evaluaciones con nombre, tipo y materia.
- ✅ El sistema debe permitir definir el peso de cada evaluación en el promedio.
- ✅ Las evaluaciones deben estar asociadas a un período académico específico.

## Cambios realizados

### Migraciones
- `create_subjects_table` — se llenó el esquema (nombre, descripcion, nivel_id, horas_semanales, estado).
- `create_academic_years_table` — se llenó el esquema (nombre, fecha_inicio, fecha_fin, estado).
- `create_assessments_table` — se llenó el esquema: `nombre`, `tipo`, `materia_id` (FK subjects), `gestion_id` (FK academic_years), `peso` DECIMAL(5,2), `fecha`.

> Las tablas `subjects` y `academic_years` eran dependencias en blanco (stub). Se completan aquí como habilitadores del módulo de evaluaciones; las HU-14 y HU-15 (materias y períodos) las extenderán.

### Modelos
- `Subject` — fillable + relaciones `level()`, `assessments()`.
- `AcademicYear` — fillable + casts de fechas + `assessments()`.
- `Assessment` — fillable + casts (`peso`, `fecha`) + relaciones `subject()`, `academicYear()`, `gradeScores()`.

### Seeders
- `SubjectSeeder` (nuevo) — materias base por nivel.
- `AcademicYearSeeder` — queda registrada la gestión "2026".
- `DatabaseSeeder` — se añade `SubjectSeeder`.

### Controller
- `AssessmentController` — CRUD web completo con validaciones:
  - `peso` validado entre 0 y 1 (decimal).
  - `materia_id` y `gestion_id` validados contra existencias.
  - Al eliminar no se verifican calificaciones (por implementar en HU-22).

### Rutas (`routes/web.php`)
Protegidas con `auth` + `role:Docente` (prefix `evaluaciones`):
`index`, `create`, `store`, `edit`, `update`, `destroy`.

### Vistas
- `dashboard/partials/_sidebar-docente.blade.php` (nuevo).
- `assessments/index.blade.php`, `assessments/create.blade.php`, `assessments/edit.blade.php`.

### Tests
- `tests/Feature/AssessmentTest.php` — auth, vista, crear, editar, eliminar, validaciones, acceso denegado.

---

## Decisiones y notas

- **Acceso:** solo rol `Docente` (según SRS "como docente"). Administrador no accede por ahora.
- **Evaluaciones sin calificaciones:** en esta HU el borrado no verifica `grade_scores`; HU-22 añadirá el guard (no eliminar si tiene notas).
- **Rama HU22** se basará en esta rama por dependencia de la tabla/modelo `assessments`.

---

*Documento HU-21 · Sistema Gestión Escolar San Bernardo*
