# AGENTS.md — CSB 360

Sistema de gestión escolar (Laravel) del Colegio San Bernardo. Trabajo activo sobre `develop`; los módulos se cierran por "HU" (historia de usuario) numeradas.

## Stack

- Laravel 12, PHP 8.2, **MySQL** (base `csb360`; tests en `csb360_test`). Ya NO se usa SQLite.
- Front: **Blade + Tailwind 4 + JS vanilla**. NO hay Vue/React/Livewire/Inertia — no busques componentes JS ni stores.
- PDF: `barryvdh/laravel-dompdf` (`Pdf::loadView(...)->download()`).
- Excel: `phpoffice/phpspreadsheet` (spreadsheets creados a mano; **no** `maatwebsite/excel`).
- QR (RUDE): `endroid/qr-code`. Google OAuth: `laravel/socialite`.

## Comandos

```powershell
php artisan serve                          # http://127.0.0.1:8000
php artisan test                           # suite completa
php artisan test --filter=NombreTest       # un test
php artisan migrate --force
php artisan db:seed --class=NombreSeeder --force
npm run build                              # Vite (tras tocar resources/css|js o Blade con @vite)
```

No hay CI ni linters configurados. `vendor/bin/pint` existe pero no es parte del flujo.

## Tests

- `tests/Feature/*` con PHPUnit (`RefreshDatabase`, DB MySQL `csb360_test`). Base: `tests/TestCase.php`.
- Solo existe `UserFactory`. El resto de modelos se crea **a mano** con `Model::create([...])` + `fake()`. No esperes factories para Section/Student/Teacher/etc.
- Patrón de setUp: `$this->seed(['RoleSeeder', 'AdminUserSeeder'])` y luego armar el grafo Level→Grade→Section(=paralelo/clase)→AcademicYear→Subject→Teacher→SectionSubject→Student→Enrollment→Registration. La sección lleva `academic_year_id` y `aula`.
- Para montar el usuario docente: crear `User` (rol Docente) **y** `Teacher` con `user_id`. Para estudiante: crear `Student` y luego `$student->update(['user_id' => ...])`.
- Fechas dependientes del día actual: los tests de "clase actual" se saltan si es domingo (`markTestSkipped`). Mantén ese patrón.
- Referencia canónica de roles/ownership: `tests/Feature/AttendanceTest.php`.

## Autorización (no hay Policies)

- 7 roles base: Administrador, Director, Secretaría, Docente, Estudiante, Regente y Enfermera (`roles` en BD); se pueden crear roles adicionales desde `/roles-permisos` y un usuario puede tener varios. Los tutores/padres NO son rol ni tienen cuenta: son datos en `parent_guardians` ligados al estudiante vía `student_parent`. **No existe `courses`**: el paralelo (`sections`) es la unidad-clase (grado + gestión + aula) y todo apunta a `section_id`; la asignación materia↔paralelo↔docente es `section_subjects` (`SectionSubject`).
- Middleware alias en `bootstrap/app.php`: `role:Admin,Director` (acepta lista separada por comas), `permission:permiso.a` (usa Spatie + catálogo) y `admin` (solo Administrador). Acceso no autorizado → redirect a `/acceso-denegado`.
- Permisos con `spatie/laravel-permission`: catálogo y asignaciones en `config/permisos360.php`; `User::puede()` / `canAccess()` resuelven permisos. Un usuario puede tener varios roles (`model_has_roles`); `users.primary_role_id` define el rol principal para listados/reportes, no para permisos. Middleware `permission:` y `role_or_permission:`. Gate `access-module`.
- Blade: `@role('Docente')`, `@hasanyrole('A,B')` (definidas en `AppServiceProvider`).
- **Ownership se valida inline en el controlador** (ej. `authorizeDocenteDelCurso`), no con Policies. Al añadir un módulo nuevo, replica ese patrón y agrega el permiso/módulo a `config/permisos360.php`.

## Gotchas

- **Zona horaria: America/La_Paz en todo** (sistema de un solo país, UTC-4): `config/app.php`, `date.timezone` del php.ini y MySQL (SYSTEM = hora Windows local). No uses `now()` UTC ni asumas UTC en ningún lado.
- **No commitear:** `public/build/manifest.json` (artefacto de build, aunque está trackeado — se modifica solo) y `Docs/priv_google.json` (secreto, ignorado). Aparecen como `M`/`??` constantemente; déjalos fuera de los commits de HU.
- Cada llamada a `shell` es una sesión nueva: procesos en background (`php artisan serve`) no sobreviven entre llamadas. Para levantarlo, usa `Start-Process`.
- PowerShell se come `$` en `php artisan tinker --execute="..."`. Para scripts con variables, escribe un `.php` temporal y córrelo con `php archivo.php`.
- **MySQL local:** usa el MySQL de XAMPP (arrancar `C:\xampp\mysql\bin\mysqld.exe --defaults-file=C:\xampp\mysql\bin\my.ini`; existe un `.bat`/servicio con `mysql_installservice`). El PHP del `php.ini` de WinGet ya tiene `pdo_mysql` habilitado. Bases: `csb360` y `csb360_test` (root, sin contraseña).
- **`max_allowed_packet=64M`** en `my.ini`: el dump de datos reales se importa por sentencias; si baja de ~2 MB vuelve a fallar la importación.
- **Datos reales (PII):** `database/data/estudiantes_2026.sql` está en **sintaxis MySQL** (generado desde el dump SQLite original en HU-68). `EstudiantesRealesSeeder` lo ejecuta sentencia por sentencia con las FK desactivadas. No lo regeneres a la ligera (contiene PII).
- **`parent_guardians.numero_documento` usa collation `utf8mb4_bin`** (los datos reales traían documentos/direcciones con diferencias solo de mayúsculas; con la collation CI de MySQL chocaban en el índice único).
- **Horarios:** periodos oficiales en `Schedule::PERIODOS_NIVEL` (fuente: `Docs/Horarios/HORARIO_PERIODOS.html`). `CargaHorariaSeeder` carga ocupación desde el xlsx (section/subject NULL); `HorariosDetalladosSeeder` fija el paralelo a bloques existentes (va después). La vista docente agrupa en tablas por nivel (Inicial/Primaria/Secundaria) con grilla completa. `schedules.tipo` (CLASE/VISITA): la visita la gestiona el docente en `visitas.mis` y se consulta en `visitas.index`; las acciones "clase actual" solo usan bloques CLASE con paralelo.

## Flujo Git

- Ramas por historia: `HU-NN-descripcion`. Se mergean a `develop` con `--no-ff` y se hace push.
- El remoto es `github-360:SnaiderMdb/360.git`; desarrollo local sobre `develop`.
- Verifica con `php artisan test` antes de commitear una HU.

## Documentación

- `Docs/historias-usuario-nuevas.md`: notas de cada HU cerrada (útil como contexto de decisiones).
- `Docs/rude-mapeo-oficial.md`, `Docs/documentacion-final.md`: especificación funcional/RUDE.
- `Docs/proforma-sistema-360.md`: inventario de módulos.
- `init.md`: log de sesión **antiguo/stale** (2026-09-18) — úsalo como pista histórica, no como estado actual.
- `config/bolivia.php`: departamentos/provincias/municipios/complementos CI (cascadas).
