# Instructivo Rama `HU12-administrar-grados` (Joel/Jhoel)

## Qué contiene esta rama

Implementación completa de **HU-12 – Administrar grados** (Sprint 1):

| Archivo | Contenido |
|---|---|
| `database/migrations/2026_08_26_003709_create_levels_table.php` | Tabla `levels` completa según `diagrama_fisico(2).md` + datos iniciales (Inicial, Primaria, Secundaria) |
| `database/migrations/2026_08_26_003710_create_grades_table.php` | Tabla `grades` con FK a niveles, UNIQUE(nivel,nombre), índices |
| `app/Models/Level.php`, `app/Models/Grade.php` | Modelos con relaciones y `$fillable` |
| `app/Http/controllers/GradeController.php` | CRUD completo con reglas de negocio de la HU |
| `routes/api.php` + `bootstrap/app.php` | Registro de rutas API |
| `tests/Feature/GradeTest.php` | 13 tests cubriendo los 3 criterios de aceptación |

## Cómo revisarla/probarla

```bash
git fetch origin
git checkout HU12-administrar-grados
composer install          # si es entorno nuevo
php artisan migrate:fresh # crea todas las tablas + seed de niveles
php artisan test --filter=GradeTest   # debe dar: 13 passed
php artisan serve         # y probar endpoints abajo
```

## Endpoints disponibles

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/grades?nivel_id={id}` | Lista grados (filtro opcional por nivel) |
| POST | `/api/grades` | Crear grado `{ "nivel_id": 2, "nombre": "1ro Primaria", "orden": 1 }` |
| GET | `/api/grades/{id}` | Ver un grado |
| PUT | `/api/grades/{id}` | Editar nombre/orden/estado |
| DELETE | `/api/grades/{id}` | Eliminar (409 si tiene paralelos o matriculados) |

## ⚠️ Avisos importantes para el equipo (leer antes de fusionar)

1. **`routes/api.php` se creó aquí y se registró en `bootstrap/app.php`.**
   Si tu rama también creó ese archivo, al hacer merge dejar **una sola versión** del registro y sumar tus rutas dentro del mismo archivo. Ese es el patrón oficial para las demás HUs.

2. **La migración de `levels` se completó en esta rama** como prerrequisito del FK de `grades`.
   Puede entrar en conflicto con la rama `HU11-administrar-niveles-educativos` (mismo archivo). Al resolver, conservar esta versión (ya tiene columnas + seed) y que HU-11 agregue encima solo su lógica extra.

3. **Las migraciones de `sections`, `courses` y `registrations` siguen siendo stubs vacíos en develop.**
   Por eso `GradeController::destroy()` usa guardas `Schema::hasColumn(...)` antes de validar paralelos/matriculados. Cuando esas tablas se completen (HU-13 y rama de cursos), **simplificar esas guardas**.

4. **Los endpoints aún no tienen autenticación.** Es intencional: login/roles son HU-01/HU-05 (otras ramas). Cuando se fusionen, agregar middleware de auth y permisos a estas rutas.

5. **Convención de respuestas:** éxito = `{ "data": ... }`; validación = 422 estándar de Laravel; conflicto de negocio = 409 con `"message"`.

## Reglas de negocio implementadas (HU-12)

- El nombre del grado es único **dentro del mismo nivel** (puede repetirse entre niveles).
- No se puede eliminar un grado con paralelos asociados.
- No se puede eliminar un grado con estudiantes matriculados (matrículas activas/no canceladas vía `registrations → courses.grado_id`).
- Matrículas en estado `CANCELADA` no bloquean la eliminación.
