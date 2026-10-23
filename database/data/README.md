# Datos locales no versionados

HU-84 sacó del repositorio los archivos con datos personales. Deben existir
solo en el servidor o equipo autorizado que ejecuta la importación:

- `estudiantes_2026.sql`: dump de estudiantes, tutores y matrículas.
- `docentes_20261.xlsx`: planilla original de docentes (referencia).
- `docentes_2026.json`: datos normalizados que usa `Docentes2026Seeder`.

Los tres están cubiertos por `.gitignore`. Nunca los adjuntes a un commit,
issue o respaldo público.

Para importar:

```powershell
php artisan db:seed --class=Docentes2026Seeder --force
php artisan db:seed --class=EstudiantesRealesSeeder --force
```

Si falta `docentes_2026.json`, el seeder muestra un error y no importa docentes;
el resto del sistema sigue funcionando.
