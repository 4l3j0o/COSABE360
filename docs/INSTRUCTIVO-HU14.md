# INSTRUCTIVO HU-14 — Administrar Materias

> **Rama:** `HU14-administrar-materias` — basada en `develop` (53cab42).  
> **Sprint 2 — 3 pts — RF10.** Tabla `subjects` (Anexo A `SRS_Sistema_Gestion_Escolar_v2.md:452-464`, `diagrama_fisico (2).md:542-568`).

## 1. Qué se hizo — cerrado al documento

**Migración** `2026_08_26_003711_create_subjects_table.php`
- Columnas exactas del SQL del Anexo A: `id`, `nombre VARCHAR(100) NOT NULL`, `descripcion VARCHAR(255) NULL`, `nivel_id FK→levels RESTRICT`, `horas_semanales TINYINT UNSIGNED NULL`, `estado ENUM ACTIVO/INACTIVO default ACTIVO`, `timestamps`, `idx_nivel`, `idx_estado`.
- **Sin** `UNIQUE(nivel_id,nombre)` — el doc no lo tiene (a diferencia de `grades`/`sections`). No inventado.
- **Sin** `deleted_at` — catálogo no usa soft delete en el doc.
- Fix defensivo: si `levels` aún es stub (HU-11 no mergeado), la migración lo completa con `nombre,descripcion,orden,estado` y seed Inicial/Primaria/Secundaria — evita FK fail en SQLite.

**Modelo** `Subject.php`
- `fillable: nombre, descripcion, nivel_id, horas_semanales, estado`, `casts horas_semanales=>integer`, `level(): BelongsTo`.

**Fix colateral** `Level.php` — en `develop` seguía stub; se completó con `fillable` y `subjects(): HasMany` para que `Subject->level` funcione si HU-11 aún no mergeó.

**Requests**
- `StoreSubjectRequest`: `nivel_id required|exists:levels`, `nombre required|max:100`, `descripcion nullable|max:255`, `horas_semanales nullable|integer|min:1|max:255`.
- `UpdateSubjectRequest`: `sometimes` + `estado in:ACTIVO,INACTIVO`.

**Resource** `SubjectResource` — `level` con `whenLoaded`.

**Controller** `SubjectController`
- `index`: `with('level')`, filtros `?nivel_id` / `?estado`, orden `nivel_id, nombre`.
- `store`: `estado=ACTIVO`, `201`.
- `destroy`: `409` si `course_subject(s).materia_id` o `schedules.materia_id` existe (FK RESTRICT del Anexo A). Usa `Schema::hasColumn` para no romper en SQLite stub + maneja ambos nombres `course_subject` (SRS) / `course_subjects` (migración develop).

**Rutas** `routes/api.php` `apiResource('subjects')` + `bootstrap/app.php` `api: __DIR__/../routes/api.php`.

## 2. Tests — `tests/Feature/SubjectTest.php` 11/12 (1 skip)

- Crea con nivel OK, fail sin datos, fail nivel inexistente, crea sin horas/descripcion, valida `horas_semanales 1-255`, edita, cambia estado INACTIVO, elimina sin asignaciones, **no elimina con curso asignado** (skip si `course_subjects` stub), lista por `nivel_id` / `estado`, muestra `level`.
- El skip se vuelve `PASS` cuando `HU-16`/`Sprint 2` cree `course_subjects` real.

## 3. Para Keila — integración (lo que pediste: `actualicen siempre develop`)

- **HU-11** `HU11-administrar-niveles-educativos` → ya pusheado `0b96e2e` mergeado con `develop` (`e6b3bb8`→`53cab42`). Si lo mergeas primero, `HU-14` no necesita el fix defensivo de `levels`.
- **HU-12** `HU12-administrar-grados` → merge `develop` → `3c6098c` pusheado.
- **HU-13** `HU13-administrar-paralelos` → merge `develop` → `99815a3` (fix `SectionResource` `nombres/apellidos`) pusheado.
- **HU-14** está al día con `develop` (`53cab42`). Cuando merges `HU-11/12/13`, avisá y hago `git merge develop` en `HU-14` antes de tu revisión — como pediste, no al final.
- **Conflicto `routes/api.php`**: no existe en `develop`. `HU-11` crea `levels`, `HU-14` crea `subjects`; al mergear habrá conflicto de archivo que no está en `develop` — se resuelve combinando ambos `apiResource`. `bootstrap/app.php` igual (agregar `api:`).
- **Nombre tabla `course_subject`**: SRS dice singular, `develop` tiene `course_subjects` plural — `HU-14` maneja ambos.

## 4. Validar

```bash
php artisan test --filter=SubjectTest   # 11 passed, 1 skipped
php artisan test                        # 34 passed, 1 skipped, 6 failed (Vite preexistente, no HU-14)
```
