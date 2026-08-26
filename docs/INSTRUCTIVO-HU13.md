# Instructivo Rama `HU13-administrar-paralelos` (Joel/Jhoel)

## Qué contiene esta rama

Implementación completa de **HU-13 – Administrar paralelos** (Sprint 1):

| Archivo | Contenido |
|---|---|
| `database/migrations/2026_08_26_003710_create_sections_table.php` | Tabla `sections` completa según `diagrama_fisico(2).md`: grado_id FK RESTRICT, nombre, responsable_id FK SET NULL, capacidad, estado, UNIQUE(grado_id, nombre) |
| `app/Models/Section.php` | Modelo con relaciones: belongsTo Grade, belongsTo Teacher (responsable), hasMany courses |
| `app/Http/Controllers/SectionController.php` | CRUD completo con validación y reglas de negocio |
| `routes/api.php` | Archivo de rutas API (creado aquí porque no existía en develop) |
| `bootstrap/app.php` | Registro de `api:` en `withRouting()` |
| `tests/Feature/SectionTest.php` | 12 tests (28 assertions) — todos pasando |

## Cómo probarla

```bash
git fetch origin
git checkout HU13-administrar-paralelos
composer install
php artisan migrate:fresh    # crea tablas + datos iniciales
php artisan test --filter=SectionTest   # debe dar: 12 passed
```

## Endpoints

| Método | Ruta | Descripción | Ejemplo body |
|---|---|---|---|
| GET | `/api/sections?grado_id={id}&nivel_id={id}` | Lista paralelos (filtros opcionales) | — |
| POST | `/api/sections` | Crear paralelo | `{ "grado_id": 1, "nombre": "A" }` |
| GET | `/api/sections/{id}` | Ver paralelo con grado y responsable | — |
| PUT | `/api/sections/{id}` | Editar nombre/capacidad/responsable/estado | `{ "nombre": "B", "capacidad": 35 }` |
| DELETE | `/api/sections/{id}` | Eliminar (409 si tiene matriculados activos) | — |

## Reglas de negocio (HU-13)

| Criterio | Cómo se verifica |
|---|---|
| Nombre único **por grado** (paralelos A, B, C… en el mismo grado) | `Rule::unique('sections')->where('grado_id', ...)` en store y update |
| Paralelo duplicado **en distinto grado** = permitido | Test explícito: mismo nombre "A" en grados distintos |
| Asignar docente responsable (nullable) | `responsable_id` nullable, se puede crear sin docente |
| Capacidad por defecto = 40 | DB default + test que verifica `data.capacidad === 40` |
| No eliminar con estudiantes matriculados | JOIN courses → registrations (excluye CANCELADA), trảs 409 |
| Matrícula CANCELADA **no bloquea** eliminación | Test explícito |
| Respuesta incluye grado asociado | Test: `data.grade.id` y `data.grade.nombre` |

## ⚠️ Avisos importantes para el equipo

1. **Este branch crea `routes/api.php` y registra `api:` en `bootstrap/app.php`.**
   Si HU-12 (otra rama de Joel) también crea `routes/api.php`, al fusionar ambas ramas **dejar una sola versión** y combinar las rutas. La rama HU-12 ya tiene rutas de grados dentro del mismo archivo.

2. **Depende de HU-12** para que la migración de `grades` tenga columnas reales (`nivel_id`, `nombre`, `orden`, `estado`). Sin esa rama, la FK `sections.grado_id → grades.id` funciona a nivel estructural pero no hay datos de prueba.

3. **`teachers` es un stub en develop** (solo `id` + `timestamps`). La migración de sections puede crear la FK a `teachers.id` porque el stub sí tiene esa columna. Pero para que `responsable_id` funcione a nivel de aplicación, la rama de docentes debe completar la tabla `teachers`.

4. **`courses` y `registrations` son stubs.** La lógica de `destroy()` usa guardas `Schema::hasColumn(...)` para no romperse hasta que esas tablas se completen con sus columnas reales.

5. **Sin autenticación** en estos endpoints (intencional: HU-01/HU-05 aún no fusionados). Cuando existan, agregar middleware.

## Orden de fusión recomendado

```
develop ← HU12-administrar-grados (primero: completa levels + grades)
       ← HU13-administrar-paralelos (después: completa sections con FK a grades)
```

Si se fusiona HU13 primero, las migraciones crearán `sections` con FK a `grades` que sigue siendo stub — funciona en SQLite (sin FK enforcement) pero falla en MySQL con `foreign_keys=ON`.
