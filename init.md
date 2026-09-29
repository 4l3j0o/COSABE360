# Sesión de trabajo — CSB 360

> Archivo de contexto para retomar la sesión. Última actualización: 2026-09-18.

## Estado actual

- **Rama activa:** `HU-59-Flujo_Inscripcion` (recién creada desde `develop`, sin commits aún)
- **Servidor dev:** `php artisan serve` en `http://127.0.0.1:8000` (levantado durante la sesión)
- **DB:** sqlite (`DB_CONNECTION=sqlite`), migraciones al día
- **Tests:** suite verde — 281 passed, 2 skipped (al cerrar HU-58)

## HU-58 `llamada_reportes` — COMPLETADA y MERGEADA a `develop`

- Commit `40d1c53` + merge `e611e82` en `develop`. **No se hizo push** (ni de la rama ni de develop).
- Central de Reportes (`/reportes`, `ReporteCentralController`, rutas `reportes.central.*`):
  landing Nombre/Descripción/Parámetros/Ejecutar + 4 reportes nuevos con PDF/Excel:
  Lista de Padres, Lista de RUDEs (paralelo, solo tutor/encargado ve curso completo),
  Centralizador por Evaluación (`assessments`), Centralizador por Trimestre (`evaluation_plans`).
- Se quitaron del sidebar los duplicados: "Reportes Asistencia"/"Reportes Evaluación"
  (docente), "Reportes de Asistencia" (director y admin). Se dejaron "Mi Asistencia"
  (estudiante) y "Reportes Asistencia" (tutor) porque esos roles no usan la Central.
- Test nuevo: `tests/Feature/ReporteCentralTest.php` (7 tests).
- Archivos fuera del commit (no tocar): `public/build/manifest.json` (modificado previo),
  `Docs/priv_google.json` (untracked, parece secreto — NO commitear).

## HU-59 `Flujo_Inscripcion` — IMPLEMENTADA (2026-09-18, sin push/merge)

Rama `HU-59-Flujo_Inscripcion`. Test: `tests/Feature/InscripcionFlujoTest.php` (8 tests).

- **Directores por nivel:** tabla `director_level` (migración `2026_09_18_100000`),
  `User::nivelesACargo()` + `puedeInscribirNivel()`, `DirectorNivelSeeder`
  (msuarez→Inicial/Primaria, mrodriguez→Secundaria; defensivo si no existen).
  Director sin niveles = sin restricción.
- **Código correlativo:** `InscripcionController::siguienteCodigo()` (máx numérico + 1,
  ignora prefijos EST-). Se recalcula en servidor dentro de la transacción.
- **Búsqueda por CI:** `GET inscripciones/buscar-por-ci` (JSON: encontrado/código/
  datos/ya_inscrito/ver_url). El formulario autocompleta y avisa.
- **Store:** crea Student + padres (padre/madre/tutor, principal a elegir) + cuentas
  User (Estudiante con su email Google + tutor principal si tiene email) +
  Enrollment PENDIENTE + Registration INACTIVA preasignada + 5 StudentDocument
  PENDIENTE. Email estudiante `unique:users`. Curso validado a período activo +
  nivel permitido (403 si no).
- **Inscripción en Línea:** `GET /mi-inscripcion` (rol Estudiante, solo propio) +
  `POST /mi-inscripcion/{student}/documento` (pdf/jpg/png 5MB → ENTREGADO).
  Tipos: CI_ANVERSO, CI_REVERSO, CERT_NACIMIENTO, CI_PADRES, COMPROBANTE_PAGO.
- **Contrato + anexo PDF:** `inscripciones/{student}/contrato|anexo` (DomPDF, variables
  desde tutor principal/estudiante/curso/gestión/TaxConfiguration; gestión y año
  parametrizados, no hardcodeados).
- **Verificación contabilidad:** `PATCH verificar-pago/{enrollment}` (Admin/Secretaría/
  Director): PENDIENTE→COMPLETADA. Botón en `students.show` + descargas contrato/anexo.
- **Matrícula:** `StudentController@matricular` reutiliza la INACTIVA (activa + genera
  cuotas) en vez de duplicar.
- **PREINSCRITO:** badge + filtro `estado_academico=PREINSCRITO` en `students.index`;
  filtro SIN_INSCRIBIR intacto (test previo en verde).
- **Sidebars:** "Nueva Inscripción" (admin/director/secretaría), "Mi Inscripción" (estudiante).
- Rutas `inscripciones.*` (store en POST `/inscripciones/nueva` por colisión con
  `enrollments.store` de HU-17).
- Supuesto documentado: la "cuenta Google automática" crea el `User` rol Estudiante;
  el login real es vía OAuth con su email @cosabe.edu.bo (sin cambios a AuthController).
- **Ajustes formulario (2026-09-18):** apellidos paterno/materno separados (se combinan
  en `apellidos`); complemento con lista `config/bolivia.php` (deptos + SEGIP);
  lugar de nacimiento en cascada depto→provincia→ciudad (+Otra libre), se guarda
  "Ciudad, Provincia, Departamento"; email auto `inicial+paterno.codigo@cosabe.edu.bo`
  (ej. `squispe.8640@`, ASCII, con resguardo de unicidad); validación total
  (CI 7-8 dígitos, `required_with` padre/madre, provincia del depto, todo con mensajes).
- **Materias oficiales (2026-09-18):** migración `codigo/sigla/area/grupo` en subjects +
  `MateriasOficialesSeeder` (NIFC 8, PVC 11, SCP 17 siglas; enriquece por nombre
  normalizado, crea faltantes, idempotente; asignaciones intactas).
  `Docentes2026Seeder` tolera renombres. Local: 51 materias (36 oficiales).
  Test `MateriasOficialesTest` (2).

Flujo de inscripción de estudiante nuevo, dictado por el usuario:

1. **Quién inscribe:** directores de nivel, cada uno solo en su nivel:
   - `msuarez@cosabe.edu.bo` (Marco Suarez, rol Director) → **Inicial y Primaria**
   - `mrodriguez@cosabe.edu.bo` (Mario Rodriguez, rol Director) → solo **Secundaria**
   - (Hoy ambos son rol `Director` genérico en `UsuariosRealesSeeder.php:30,34` → habrá que acotar por nivel)
2. **Código correlativo** de estudiante: sin repetidos ni saltos (ej. 8531 → 8532).
3. **Validar por CI** (identificador único en Bolivia): si el estudiante ya tenía código,
   autocompletar con su código anterior.
4. Se crea el estudiante y **automáticamente su cuenta Google** de acceso al sistema.
5. Se **asigna curso** + datos principales: nombre, código RUDE, datos padre/madre/tutor.
6. **Inscripción en Línea** (desde la cuenta del estudiante): sube foto/PDF de
   CI anverso+reverso, certificado de nacimiento, foto CI de los papás,
   comprobante de pago de la inscripción.
7. El sistema genera **contrato + anexo en PDF** (formatos PENDIENTES de que el usuario los pase).
   Se hace virtual pero **se firma en papel** y se entrega a dirección firmado por el padre.
8. **Estados:** pago NO verificado → `preinscripción`; pago verificado con el nuevo código
   desde el **módulo de contabilidad** → `inscrito`.
9. Con los datos cargados se empieza a llenar el **RUDE en línea**.

### Pendiente de definir / recibir

- [x] Formatos del contrato y del anexo (recibidos 2026-09-18, ver abajo)
- [ ] Detalle de la verificación de pago en contabilidad (qué pantalla/acción cambia el estado)
- [ ] Si "preinscripción/inscrito" son estados nuevos o mapean a los existentes
  (`enrollments`: PENDIENTE/COMPLETADA/ANULADA)
- [ ] Cómo se crea la cuenta Google "automáticamente" (¿al crear estudiante sin OAuth previo?)
- [ ] Alcance exacto por nivel para cada director (¿nuevo permiso `nivel_id`?)

### Formatos recibidos (2026-09-18, PDFs fuera del repo)

- Contrato: `C:\Users\Snaider.Ssj\Documents\Contrato de Servicios.pdf`
  (7 págs, "CONTRATO DE PRESTACIÓN DE SERVICIOS EDUCATIVOS — GESTIÓN 2026").
  Campos variables a rellenar por el sistema:
  - Contratante (cláusula 1): nombre completo, CI N°, nacionalidad, teléfono fijo,
    celular, email, domicilio. Calidad: padre/madre/tutor.
  - Estudiante (cláusula 4, tabla): nombre completo, CI, RUDE, año de escolaridad/nivel.
  - Fecha suscripción (cláusula 17 + pie): día, mes, año + lugar.
  - Firma contratante: nombre + CI. Firma UE fija: Carmen Patricia Cassab Michel.
  - Resto (costo Bs.7.855 / 10 cuotas Bs.785,50, vigencia, obligaciones, mora, etc.): texto legal fijo.
- Anexo: `C:\Users\Snaider.Ssj\Documents\Anexo_de_conformidad_contrato.pdf`
  ("ANEXO DE CONFORMIDAD Y ACEPTACIÓN DE ACUERDOS COMPLEMENTARIOS — GESTIÓN 2026",
  puntos I–V fijos). Campos variables:
  - Nombre + CI del padre/madre/apoderado, nombre del estudiante.
  - Fecha: día, mes ("...del año dos mil veintiséis").
  - Bloque firma: NOMBRE, C.I. N°, FIRMA.
- "GESTIÓN ESCOLAR 2026" debe parametrizarse por gestión activa (no hardcodear 2026).

## Cómo retomar

```powershell
git branch --show-current   # debe ser HU-59-Flujo_Inscripcion
php artisan serve            # levantar el sistema
php artisan test             # verificar suite verde
```

Seguir preguntando al usuario por los puntos pendientes antes de picar código.

## Sesion 2026-09-18/19 (continuacion)

- Ramas: HU-58 mergeada, HU-59 mergeada (flujo inscripcion), HU-60 mergeada (central unica + libreta), HU-61 mergeada (limpieza modulo viejo), HU-62 creada (tutores curso + nomina docente). Todo en develop y pusheado.
- Suite: 292 passed, 2 skipped.
- Pendiente usuario: crear o no los 21 docentes ausentes de la nomina; resto P-Z ya cargado (56/77).
- Servidor: http://127.0.0.1:8000 - usuario squispe@cosabe.edu.bo rol Director.

