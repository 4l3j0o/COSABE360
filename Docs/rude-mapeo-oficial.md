# RUDE Oficial 2025 — Mapeo campo por campo (HU-45)

> **Fuente oficial:** Formulario de Registro Único de Estudiantes (RUDE) — Resolución Ministerial
> Nº 0031/2025, de 17 de diciembre de 2025, del Ministerio de Educación de Bolivia.
> Formato "Regular".

## Notas generales del formulario oficial
- **Importante:** el formulario debe ser llenado por el padre, madre o tutor(a).
- Algunos campos **serán llenados por la Unidad Educativa** (marcados con `[*]`).
- Otros campos **requieren la presentación del documento** al que se hace referencia
  (marcados con `[**]`).
- El código RUDE es **autogenerado por el Sistema** (SIE). Es solo lectura.
- El número de registro de discapacidad IBC proviene del SIPRUNPCD (Min. de Salud) o del
  Instituto Boliviano de la Ceguera (IBC).

## Convención de columnas
| Columna | Significado |
|---|---|
| **Sección** | Sección del formulario oficial |
| **N.º** | Subnúmero oficial (2.1, 4.2.4, …) |
| **Campo oficial** | Texto exacto en el formulario |
| **Tipo** | texto / número / fecha / selección única / selección múltiple |
| **Origen** | `capturar` (nuevo en RUDE), `Student` (ya existe en `students`), `StudentRudeRecord` (ya existe) |
| **Destino** | columna de `student_rude_records` (o tabla referenciada) |

---

## I. DATOS DE LA UNIDAD EDUCATIVA
> **Corrección (revisión contra formulario real): la Sección I oficial trae
> ÚNICAMENTE el Código SIE.** El formulario y el PDF solo piden SIE + fecha.
> Las columnas `ue_*` se conservan en BD por compatibilidad pero ya no se capturan.

La Unidad Educativa es un dato institucional. En CSB 360 se configura a nivel de
institución/empresa (tabla `empresas`) y se replica en el RUDE al momento de la carga.
Se incluyen columnas de respaldo en `student_rude_records` para exportación fiel del formato.

| Sección | N.º | Campo oficial | Tipo | Origen | Destino |
|---|---|---|---|---|---|
| I | — | Código SIE de la Unidad Educativa | número 8 díg. | `capturar` | `codigo_sie_ue` (por defecto `81730184`) |
| I | — | Día / Mes / Año (fecha de llenado) | fecha | `capturar` | `fecha_registro` |
| I | — | Localidad | texto | `capturar` | `ue_localidad` |
| I | — | Nombre(s) | texto | `capturar` | `ue_nombres` |
| I | — | Apellido Paterno | texto | `capturar` | `ue_apellido_paterno` |
| I | — | Apellido Materno | texto | `capturar` | `ue_apellido_materno` |
| I | — | País | texto | `capturar` | `ue_pais` |
| I | — | Departamento | texto | `capturar` | `ue_departamento` |
| I | — | Provincia | texto | `capturar` | `ue_provincia` |
| I | — | Sección/Municipio | texto | `capturar` | `ue_municipio` |
| I | — | Localidad/Comunidad | texto | `capturar` | `ue_comunidad` |
| I | — | Zona/Villa | texto | `capturar` | `ue_zona` |
| I | — | Avenida/Calle | texto | `capturar` | `ue_calle` |
| I | — | N.º vivienda | texto | `capturar` | `ue_nro_vivienda` |
| I | — | Teléfono fijo | texto | `capturar` | `ue_telefono` |
| I | — | Celular de contacto | texto | `capturar` | `ue_celular` |

---

## II. DATOS DE LA O EL ESTUDIANTE

| Sección | N.º | Campo oficial | Tipo | Origen | Destino |
|---|---|---|---|---|---|
| II | 2.1 | Apellido(s) y Nombre(s) | texto | `Student` | `students.apellidos`, `students.nombres` |
| II | 2.2 | Lugar de nacimiento (País, Departamento, Provincia, Localidad) | texto | `Student` | `students.lugar_nacimiento` |
| II | 2.3 | Certificado de nacimiento (Oficialía, Libro, Partida, Folio, Año) | texto | `StudentRudeRecord` | `oficialia_nacimiento`, `libro_nacimiento`, `partida_nacimiento`, `folio_nacimiento`, `anio_nacimiento` |
| II | 2.4 | Fecha de nacimiento (Día, Mes, Año) | fecha | `Student` | `students.fecha_nacimiento` |
| II | 2.5 | Documento de identidad: Cédula de Identidad N.º + Complemento / Certificado de Nacimiento / DNI / Pasaporte / Declaración Jurada | texto | `Student` | `students.numero_documento`, `students.complemento`, `students.tipo_documento` |
| II | 2.6 | Código RUDE (código automático generado por el Sistema) | texto | `capturar` (solo lectura) | `codigo_rude` (se asigna desde la columna `Rude` del Excel de inscripciones) |
| II | 2.7 | Sexo (Masculino / Femenino) | selección | `Student` | `students.sexo` |
| II | 2.8 | Discapacidad (colocar la información del Registro de Discapacidad) | texto | `Student` | `students.discapacidad` |
| II | 2.9 | N.º de Registro de Discapacidad IBC | texto | `capturar` | `nro_registro_discapacidad` |

---

## III. DIRECCIÓN ACTUAL DE LA O EL ESTUDIANTE

| Sección | N.º | Campo oficial | Tipo | Origen | Destino |
|---|---|---|---|---|---|
| III | — | Departamento | texto | `capturar` | `dir_departamento` |
| III | — | Provincia | texto | `capturar` | `dir_provincia` |
| III | — | Sección/Municipio | texto | `capturar` | `dir_municipio` |
| III | — | Localidad/Comunidad | texto | `capturar` | `dir_comunidad` |
| III | — | Zona/Villa | texto | `capturar` | `dir_zona` |
| III | — | Avenida/Calle | texto | `capturar` | `dir_calle` |
| III | — | N.º vivienda | texto | `capturar` | `dir_nro_vivienda` |
| III | — | Teléfono fijo | texto | `capturar` | `dir_telefono` |
| III | — | Celular de contacto | texto | `capturar` | `dir_celular` |

---

## IV. ASPECTOS SOCIOECONÓMICOS DE LA O EL ESTUDIANTE

### 4.1 Idioma y autoidentificación cultural
| N.º | Campo oficial | Tipo | Origen | Destino |
|---|---|---|---|---|
| 4.1.1 | Idioma en el que aprendió a hablar la o el estudiante | selección | `capturar` | `idioma_aprendio` |
| 4.1.2 | Idioma(s) de uso frecuente | selección múltiple | `capturar` | `idiomas_uso_frecuente` (JSON) |
| 4.1.3 | Autoidentificación cultural (Nación / Pueblo Indígena Originario / Afrob, etc.) | selección | `capturar` | `autoidentificacion_cultural` |

### 4.2 Salud
| N.º | Campo oficial | Tipo | Origen | Destino |
|---|---|---|---|---|
| 4.2.1 | ¿Existe algún Centro de Salud/Posta/Hospital en su comunidad/barrio/zona? | sí/no | `capturar` | `salud_centro_cercano` |
| 4.2.2 | El año pasado, por problemas de salud, ¿acudió o se… | sí/no | `capturar` | `salud_atendido_anio` |
| 4.2.3 | El año pasado, ¿cuántas veces fue al Centro de Salud? | número | `capturar` | `salud_veces` |
| 4.2.4 | ¿Tiene seguro de salud? (SUS / Otra caja o seguro / Est. público / Privado / No) | selección | `capturar` | `salud_seguro` |

### 4.3 Acceso a servicios básicos
| N.º | Campo oficial | Tipo | Origen | Destino |
|---|---|---|---|---|
| 4.3.1 | ¿Tiene acceso a agua potable por cañería de red? | sí/no | `capturar` | `serv_agua_red` |
| 4.3.2 | ¿Tiene baño en su vivienda? (Propio / Cedido por servicios / Letrina / Alcantarillado / No tiene) | selección | `capturar` | `serv_bano` |
| 4.3.3 | ¿Tiene red de alcantarillado? | sí/no | `capturar` | `serv_alcantarillado` |
| 4.3.4 | ¿Usa energía eléctrica para alumbrar su vivienda? | sí/no | `capturar` | `serv_electricidad` |
| 4.3.5 | ¿Cuenta con servicio de recojo de basura? | sí/no | `capturar` | `serv_recojo_basura` |
| 4.3.6 | La vivienda que ocupa el hogar es (Propia / Alquilada / Anticrético / Contrato Mixto / Cedida / Otra) | selección | `capturar` | `serv_vivienda_tipo` |

### 4.4 Acceso a internet
| N.º | Campo oficial | Tipo | Origen | Destino |
|---|---|---|---|---|
| 4.4.1 | El estudiante accede a internet en (Su vivienda / Lugares públicos / UE / Teléfono celular / No accede) | selección múltiple | `capturar` | `internet_lugares` (JSON) |
| 4.4.2 | ¿Con qué frecuencia usa internet? (Diariamente / Más de una vez por semana / Una vez por semana / Una vez al mes) | selección | `capturar` | `internet_frecuencia` |

### 4.5 Actividad laboral
| N.º | Campo oficial | Tipo | Origen | Destino |
|---|---|---|---|---|
| 4.5.1 | En la pasada gestión, ¿el estudiante trabajó? | sí/no | `capturar` | `laboral_trabajo` |
| 4.5.2 | ¿En qué actividad trabajó? | selección | `capturar` | `laboral_actividad` |
| 4.5.3 | ¿En qué turno(s) trabajó? | selección múltiple | `capturar` | `laboral_turnos` (JSON) |
| 4.5.4 | ¿Con qué frecuencia trabajó? (Todos los días / Días hábiles / Fines de semana / Días festivos / Eventual) | selección | `capturar` | `laboral_frecuencia` |
| 4.5.5 | ¿Recibió algún pago? | sí/no | `capturar` | `laboral_recibio_pago` |

### 4.6 Medio de transporte para llegar a la Unidad Educativa
| N.º | Campo oficial | Tipo | Origen | Destino |
|---|---|---|---|---|
| 4.6.1 | Generalmente, ¿cómo llega el estudiante a la UE? (A pie / Transporte público / Bicicleta / Vehículo propio / Otro) | selección | `capturar` | `transporte_medio` |
| 4.6.2 | Según el medio de transporte, ¿cuál es el tiempo máximo que demora desde su vivienda hasta la UE? (Menos de 1/2 h / Entre 1/2 y 1 h / Entre 1 y 2 h / Más de 2 h) | selección | `capturar` | `transporte_tiempo` |

### 4.7 Abandono escolar
| N.º | Campo oficial | Tipo | Origen | Destino |
|---|---|---|---|---|
| 4.7.1 | ¿El estudiante abandonó la UE el año pasado? | sí/no | `capturar` | `abandono_escolar` |
| 4.7.2 | ¿Cuáles fueron las razones del abandono escolar? | selección múltiple | `capturar` | `abandono_razones` (JSON) |

### 4.8 Características particulares de la o el estudiante
| N.º | Campo oficial | Tipo | Origen | Destino |
|---|---|---|---|---|
| 4.8.1 | Tipos y grados de discapacidad (Auditiva, Visual, Intelectual, Física/Motora, Psíquica-Mental, Múltiple) | selección múltiple | `capturar` | `discapacidad_tipos` (JSON) |
| 4.8.1 | Grado de discapacidad (Leve / Moderado / Grave / Muy grave) | selección | `capturar` | `discapacidad_grado` |
| 4.8.2 | Estudiantes con dificultades en el aprendizaje (Lectura y Escritura / Cálculo Matemático) | selección múltiple | `capturar` | `dificultad_aprendizaje` (JSON) |

---

## V. DATOS DEL PADRE, MADRE O TUTOR(A)

### 5.1 La o el estudiante vive habitualmente con:
| Opciones oficiales |
|---|
| 1. Padre y Madre |
| 2. Solo Padre |
| 3. Solo Madre |
| 4. Tutor(a) |
| 5. Solo(a) |
| 6. En tutela extraordinaria |

Destino: `convive_con` (JSON de opciones) en `student_rude_records`.

### 5.2 / 5.3 / 5.4 / 5.5 Datos del Padre, Madre, Tutor(a) y Tutor(a) Extraordinario(a)
Cada bloque repite el mismo set de campos. Se almacenan en la tabla pivote
`student_parent` (`parent_guardians`) con `tipo_relacion` = `PADRE`, `MADRE`, `TUTOR`,
`TUTOR_EXTRAORDINARIO`. El extraordinario suma `cargo_actual` y `nombre_institucion`.

| Campo oficial | Tipo | Destino (tabla `parent_guardians`) |
|---|---|---|
| Cédula de Identidad / DNI | texto | `numero_documento` |
| Complemento | texto | `complemento` |
| Apellido Paterno | texto | `apellido_paterno` |
| Apellido Materno | texto | `apellido_materno` |
| Nombre(s) | texto | `nombres` |
| Ocupación laboral actual | texto | `ocupacion` |
| Fecha de nacimiento | fecha | `fecha_nacimiento` |
| Teléfono / Celular de contacto | texto | `telefono` |
| Idioma que habla (frecuentemente) | texto | `idioma` |
| Máximo grado de instrucción alcanzado | texto | `grado_instruccion` |

> Nota: `parent_guardians` ya es utilizado por el módulo de estudiantes; el RUDE reutiliza esa
> relación para no duplicar datos de adultos responsables.

---

## FÍN DEL FORMULARIO
- Firma del padre/madre/tutor(a).
- Firma del director(a) de la Unidad Educativa.
- Complemento / observaciones.

---

## Resumen de impacto en esquema
- **Nueva migración de ampliación** sobre `student_rude_records` (todas las columnas nuevas de
  las secciones I, III, IV y los campos 2.9, 4.x). Secciones II y V mayormente reutilizan
  `students` y `parent_guardians`.
- Las selecciones múltiples se guardan como **JSON** en columnas `text`/`json`.
- **Rollback probado:** la migración `down()` elimina solo las columnas añadidas; los datos
  preexistentes (código RUDE, certificado, estado) permanecen intactos.