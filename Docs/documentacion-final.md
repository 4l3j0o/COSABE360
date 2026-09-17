# SISTEMA DE GESTIÓN ESCOLAR PERSONALIZADO — UNIDAD EDUCATIVA SAN BERNARDO

| Campo | Detalle |
|---|---|
| Institución | Unidad Educativa San Bernardo |
| Entidad legal | Sociedad Educativa San Bernardo de Tarija S.A. |
| NIT | 408460020 |
| Ubicación | Tarija, Bolivia |
| Metodología | Scrum |
| Duración | 6 sprints · 1 día = 6 días |
| Fecha | 25/08/2006 |

---

# FASE I: FUNDAMENTOS Y ALCANCE

## 1. Introducción y objetivos

### 1.1. Propósito del documento

El presente documento constituye el informe del proyecto Sistema de Gestión Escolar Personalizado para la Unidad Educativa San Bernardo, ubicada en la ciudad de Tarija, Bolivia.

El propósito de este informe es presentar de manera clara, ordenada y completa el proceso de análisis, diseño, desarrollo y gestión del sistema, siguiendo la estructura de seis fases establecida para el proyecto.

El documento describe el contexto del problema, los objetivos, el alcance, la toma de requerimientos, la especificación funcional, el diseño de la solución, la gestión ágil mediante Scrum, la planificación temporal y el control de riesgos.

El sistema tiene como finalidad centralizar en una única plataforma web la información académica y administrativa de la institución, facilitando la administración de estudiantes, docentes, tutores, inscripciones, matrículas, calificaciones, asistencia, horarios, comunicados y reportes, considerando además los requerimientos relacionados con la normativa educativa y tributaria vigente en Bolivia.

Este documento sirve como referencia para el análisis, diseño, desarrollo, implementación y evaluación del sistema, y está dirigido principalmente a directivos, docentes, estudiantes, personal administrativo y padres o tutores de la institución.

### 1.2. Alcance del proyecto

**Nombre del sistema.** Sistema de Gestión Escolar Personalizado — Unidad Educativa San Bernardo.

**Descripción general.** El presente proyecto consiste en el diseño, desarrollo e implementación de un Sistema de Gestión Escolar Personalizado para la Unidad Educativa San Bernardo, ubicada en la ciudad de Tarija, Bolivia.

El sistema busca reemplazar o mejorar el uso de una plataforma genérica y procesos manuales anteriormente utilizados por la institución, centralizando en una única aplicación web la información académica, administrativa y estudiantil.

La plataforma integra la administración de usuarios y roles, estudiantes, docentes, tutores, inscripciones, matrículas, mensualidades, control de asistencia diaria, registro de evaluaciones y calificaciones, cálculo de promedios, horarios, comunicados institucionales y generación de reportes.

Asimismo, contempla funcionalidades relacionadas con la gestión del RUDE y la Facturación Electrónica SFE, de acuerdo con los requerimientos establecidos para el proyecto y la normativa boliviana considerada.

**Procesos incluidos.** El sistema permitirá:

- Administrar usuarios.
- Administrar roles y permisos.
- Registrar y administrar estudiantes.
- Registrar padres o tutores.
- Relacionar estudiantes con padres o tutores.
- Registrar y administrar docentes.
- Administrar niveles educativos.
- Administrar grados.
- Administrar cursos y paralelos.
- Administrar materias.
- Gestionar inscripciones.
- Gestionar matrículas.
- Gestionar mensualidades.
- Registrar documentación de estudiantes.
- Asignar docentes a materias y cursos.
- Registrar evaluaciones.
- Registrar calificaciones.
- Calcular promedios.
- Consultar historial académico.
- Registrar asistencia.
- Consultar historial de asistencia.
- Administrar horarios.
- Crear y publicar comunicados.
- Definir destinatarios de comunicados.
- Generar reportes académicos y administrativos.
- Registrar historial de operaciones importantes para auditoría.
- Activar o desactivar usuarios.
- Gestionar información relacionada con el RUDE.
- Gestionar procesos de facturación electrónica SFE contemplados en el alcance.

**Procesos no incluidos.** El sistema no será una plataforma para subir, entregar tareas o actividades en línea.

Por tanto, no tendrá como objetivo funcionar como una plataforma educativa del tipo Google Classroom o Moodle para la entrega de trabajos.

Su enfoque principal será la gestión administrativa y académica institucional.

**Beneficios para la institución.** La implementación del sistema permitirá reemplazar o reducir procesos manuales y el uso de información dispersa en documentos físicos, hojas de cálculo u otros medios independientes.

Los principales beneficios serán:

- Centralizar la información institucional.
- Reducir el uso de registros manuales.
- Reducir la duplicación de información.
- Facilitar la búsqueda de datos.
- Reducir errores en el registro de información.
- Mejorar el control de estudiantes y docentes.
- Mejorar el control de asistencia.
- Facilitar el registro y consulta de calificaciones.
- Facilitar la administración de matrículas e inscripciones.
- Mejorar la comunicación institucional.
- Generar reportes de manera más rápida.
- Contar con un sistema adaptado a los procesos propios de la institución.
- Permitir futuras ampliaciones del sistema.

**Limitaciones y restricciones.**

Restricciones técnicas:

- La aplicación será web.
- Requiere un navegador actualizado como Chrome, Firefox, Edge o Safari.
- Requiere conexión a Internet para su funcionamiento normal.
- La base de datos deberá encontrarse alojada en un servidor con acceso restringido.

Restricciones de seguridad:

- Las contraseñas deberán almacenarse mediante mecanismos de hash seguro.
- El acceso estará controlado mediante autenticación.
- Los permisos estarán determinados por roles.
- La información sensible deberá contar con mecanismos de protección adecuados.

Restricciones operativas:

- El sistema estará disponible de acuerdo con la infraestructura y disponibilidad del servidor.
- Se deberán realizar copias de seguridad periódicas.
- El funcionamiento normal dependerá de la conexión a Internet.

Restricciones de cumplimiento:

- El sistema deberá considerar la normativa educativa vigente aplicable.
- La información personal deberá ser tratada de acuerdo con las disposiciones legales correspondientes.
- Se contemplan funcionalidades relacionadas con la facturación electrónica SFE y el formulario RUDE dentro del alcance definido.

### 1.3. Objetivos

**Objetivo general.** Desarrollar un sistema de gestión escolar personalizado para la Unidad Educativa San Bernardo de Tarija que permita centralizar y administrar de manera eficiente la información académica y administrativa, facilitando la gestión de estudiantes, docentes, cursos, calificaciones, asistencia, inscripciones, horarios, comunicados y generación de reportes, considerando los requerimientos de la normativa educativa y tributaria vigente en Bolivia.

**Objetivos específicos.**

a. Analizar los procesos académicos y administrativos actuales de la unidad educativa.
b. Identificar las limitaciones de los registros manuales y sistemas genéricos utilizados previamente.
c. Diseñar e implementar una base de datos relacional centralizada y normalizada en MySQL.
d. Implementar la autenticación segura de usuarios y el control de acceso basado en roles.
e. Desarrollar la gestión integral de estudiantes, docentes y estructura académica de la unidad educativa.
f. Implementar la gestión de inscripciones, matrículas, mensualidades, evaluaciones, calificaciones y asistencia.
g. Desarrollar los módulos de horarios, comunicados institucionales y reportes académicos y administrativos.
h. Implementar las funcionalidades relacionadas con la gestión del formulario RUDE y la facturación electrónica SFE contempladas dentro del alcance del proyecto.

### 1.4. Product Goal

El Product Goal o meta a largo plazo del producto es:

Centralizar en una única aplicación web de gestión escolar la información académica y administrativa de la Unidad Educativa San Bernardo, reemplazando el sistema genérico y reduciendo los procesos manuales actuales, de modo que la institución pueda administrar de forma eficiente, segura y organizada a estudiantes, docentes, tutores, inscripciones, matrículas, pagos, calificaciones, asistencia, horarios, comunicados y reportes, brindando a directivos, docentes, estudiantes y padres o tutores el acceso a la información que les corresponde según su rol.

## 2. Toma de requerimientos

### 2.1. Técnicas utilizadas

Para la toma de requerimientos se utilizó como técnica principal la entrevista o cuestionario estructurado, complementada con el análisis de la documentación existente del proyecto, incluyendo el contexto institucional, la situación actual y la especificación de requerimientos.

**Entrevista/Cuestionario de toma de requerimientos.**

- Institución educativa: Unidad Educativa San Bernardo
- Ubicación: Tarija, Bolivia
- Cargo del entrevistado: Administrador(a)
- Número aproximado de estudiantes: Más de 1.000
- Número aproximado de docentes: Entre 51 y 100
- Niveles educativos: Inicial, Primaria y Secundaria

**Principales hallazgos.**

- El sistema actual es web, pero es antiguo.
- Presenta lentitud en determinadas operaciones.
- Su utilización resulta poco intuitiva.
- No dispone de todas las funciones requeridas.
- Puede generar errores.
- Presenta limitaciones en la generación de reportes.
- Existen preocupaciones relacionadas con la seguridad.
- Las inscripciones utilizan procesos manuales o híbridos.
- Los pagos son gestionados mediante hojas de cálculo.
- Los reportes actualmente pueden generarse en menos de 10 minutos.
- Se requiere almacenar información personal y académica de los estudiantes.
- El RUDE constituye información importante dentro del registro estudiantil.
- Se requiere almacenar documentación para los procesos de inscripción.
- El costo aproximado de matrícula indicado fue de Bs. 785.50.
- Las mensualidades se gestionan principalmente entre febrero y noviembre.
- Los pagos pendientes deben registrarse como deuda.
- Los horarios se organizan manualmente.
- Cada clase tiene una duración aproximada de 45 minutos.
- Los estudiantes tienen entre 4 y 5 horas de clases por día, dependiendo del nivel.
- La comunicación con padres se realiza mediante reuniones presenciales y WhatsApp.
- La asistencia es registrada por docentes y regente.
- Los estados inicialmente identificados fueron presente, falta y permiso.
- Se requieren reportes de docentes, calificaciones, boletines, pagos y deudas.
- El sistema debe poder utilizarse desde computadoras, portátiles, tablets y celulares.

**Información del estudiante requerida.** Entre los principales datos identificados se encuentran:

- Nombres y apellidos.
- Cédula de identidad.
- Fecha de nacimiento.
- Lugar de nacimiento.
- Dirección.
- Teléfono.
- Correo electrónico.
- Datos médicos.
- Información de padres o tutores.
- Fotografía.
- Información relacionada con RUDE.

**Documentación solicitada para inscripción.**

- Cédula de identidad.
- Certificado de nacimiento.
- Libreta o historial académico.
- Documentación del padre o tutor.
- Fotografías.
- Certificado médico.
- Información RUDE.

Esta información permitió definir los módulos del sistema, los actores, los procesos y los requisitos funcionales y no funcionales.

### 2.2. Actores del sistema

| Rol | Descripción | Nivel técnico | Acceso principal |
|---|---|---|---|
| Administrador | Administra y configura el sistema y sus módulos. | Medio-alto | Todos los módulos |
| Director | Supervisa la información general y toma decisiones. | Básico-medio | Supervisión, consultas y reportes |
| Secretaría | Gestiona los principales procesos administrativos. | Básico | Inscripciones, matrículas y documentación |
| Docente | Gestiona información académica de los cursos asignados. | Básico | Asistencia, calificaciones y horarios |
| Padre/Tutor | Consulta la información académica de estudiantes asociados. | Básico | Información de sus hijos |
| Estudiante | Consulta su propia información académica. | Básico | Información académica |

**Permisos por rol.**

Administrador:

- Iniciar sesión.
- Gestionar usuarios.
- Gestionar roles y permisos.
- Gestionar niveles.
- Gestionar grados.
- Gestionar paralelos.
- Gestionar materias.
- Configurar períodos académicos.
- Asignar docentes a materias y cursos.
- Consultar reportes.
- Administrar configuración del sistema.
- Revisar historial de operaciones.
- Configurar parámetros tributarios.
- Gestionar catálogo de servicios.
- Monitorear facturación electrónica.
- Exportar información RUDE.

Director:

- Iniciar sesión.
- Consultar información general.
- Supervisar calificaciones.
- Supervisar asistencia.
- Consultar reportes.
- Crear y publicar comunicados.

Secretaría:

- Iniciar sesión.
- Registrar estudiantes.
- Registrar padres o tutores.
- Gestionar inscripciones.
- Gestionar matrículas.
- Gestionar mensualidades.
- Administrar documentación.
- Generar reportes administrativos.
- Emitir facturas.
- Gestionar procesos de contingencia de facturación.
- Completar información RUDE.
- Exportar información RUDE.

Docente:

- Iniciar sesión.
- Consultar cursos asignados.
- Registrar asistencia.
- Registrar calificaciones.
- Consultar horarios.
- Recibir comunicados.

Estudiante:

- Iniciar sesión.
- Consultar calificaciones.
- Consultar asistencia.
- Consultar horario.
- Consultar comunicados.
- Consultar información académica.

Padre/Tutor:

- Iniciar sesión.
- Consultar información de estudiantes asociados.
- Consultar calificaciones.
- Consultar asistencia.
- Consultar horarios.
- Consultar comunicados.

### 2.3. Glosario de términos del negocio

| Término | Definición |
|---|---|
| Sistema | Sistema de Gestión Escolar Personalizado San Bernardo. |
| Usuario | Persona que utiliza el sistema mediante una cuenta. |
| Administrador | Usuario encargado de administrar el sistema. |
| Director | Usuario encargado de supervisar información institucional. |
| Secretaría | Usuario responsable de procesos administrativos. |
| Docente | Usuario encargado de actividades académicas. |
| Estudiante | Usuario que consulta su información académica. |
| Padre/Tutor | Usuario que consulta información de estudiantes asociados. |
| Rol | Categoría que determina las funciones disponibles. |
| Permiso | Autorización para realizar una operación. |
| Matrícula | Registro mediante el cual un estudiante queda registrado en un período académico. |
| Inscripción | Registro del proceso de incorporación o renovación de un estudiante. |
| Paralelo | División de un grado o curso. |
| Materia | Área o asignatura académica. |
| Calificación | Resultado obtenido por un estudiante en una evaluación. |
| Evaluación | Actividad mediante la cual se obtiene una calificación. |
| Asistencia | Registro de presencia, ausencia u otra situación del estudiante. |
| Comunicado | Información publicada por la institución. |
| Historial académico | Registro de información académica del estudiante. |
| Reporte | Información organizada generada por el sistema. |
| Período académico | Intervalo de tiempo correspondiente al ciclo lectivo. |
| Nivel educativo | Etapa educativa como Inicial, Primaria o Secundaria. |
| Grado | Año de escolaridad dentro de un nivel. |
| Aula | Espacio físico donde se desarrollan las clases. |
| Dashboard | Panel principal de información del usuario. |
| RUDE | Registro Único de Estudiantes. |
| Código RUDE | Identificador asociado al registro RUDE del estudiante. |
| Código SIE UE | Código asignado a la Unidad Educativa dentro del sistema educativo. |
| SIE | Sistema de Información Educativa. |
| SFE | Sistema de Facturación Electrónica. |
| CUF | Código Único de Factura. |
| CUFD | Código Único de Facturación Diaria. |
| CUIS | Código Único de Inicio de Sistema. |
| RC-IVA | Régimen Complementario al Impuesto al Valor Agregado. |
| IUE | Impuesto sobre las Utilidades de las Empresas. |
| Contingencia SFE | Mecanismo utilizado para gestionar emisiones cuando existe una interrupción de comunicación con el servicio correspondiente. |

---

# FASE II: ESPECIFICACIÓN FUNCIONAL

## 3. Requerimientos del software

### 3.1. Requerimientos funcionales

| Código | Descripción | Categoría |
|---|---|---|
| RF01 | Iniciar sesión mediante usuario y contraseña. | Autenticación |
| RF02 | Administrar usuarios. | Usuarios |
| RF03 | Asignar roles a los usuarios. | Usuarios |
| RF04 | Registrar estudiantes con datos personales. | Estudiantes |
| RF05 | Modificar y consultar información de estudiantes. | Estudiantes |
| RF06 | Registrar padres o tutores. | Padres/Tutores |
| RF07 | Relacionar estudiantes con padres/tutores. | Padres/Tutores |
| RF08 | Registrar docentes. | Docentes |
| RF09 | Administrar niveles, grados y paralelos. | Académico |
| RF10 | Administrar materias. | Académico |
| RF11 | Asignar docentes a materias y cursos. | Académico |
| RF12 | Registrar inscripciones y matrículas. | Matrículas |
| RF13 | Registrar calificaciones. | Calificaciones |
| RF14 | Consultar calificaciones. | Calificaciones |
| RF15 | Registrar asistencia. | Asistencia |
| RF16 | Consultar historial de asistencia. | Asistencia |
| RF17 | Administrar horarios. | Horarios |
| RF18 | Publicar comunicados. | Comunicados |
| RF19 | Generar reportes. | Reportes |
| RF20 | Controlar acceso según permisos del usuario. | Seguridad |
| RF21 | Almacenar historial de operaciones importantes. | Auditoría |
| RF22 | Activar y desactivar usuarios. | Usuarios |
| RF23 | Gestionar facturación electrónica SFE según el alcance definido. | Facturación |
| RF24 | Gestionar servicios educativos con tratamiento tributario correspondiente. | Facturación |
| RF25 | Gestionar contingencia de facturación. | Facturación |
| RF26 | Registrar información relacionada con retenciones RC-IVA. | Tributario |
| RF27 | Capturar información del formulario RUDE. | RUDE |
| RF28 | Almacenar información RUDE y permitir su exportación. | RUDE |
| RF29 | Administrar catálogo de productos y servicios. | Tributario |
| RF30 | Registrar fotografía del estudiante. | Estudiantes |
| RF31 | Registrar datos médicos del estudiante. | Estudiantes |
| RF32 | Controlar deudas pendientes de mensualidades. | Matrículas |

Cada requisito funcional deberá considerar validaciones de entrada, secuencia de operaciones, manejo de errores, campos obligatorios y relación entre entradas y salidas.

### 3.2. Requerimientos no funcionales

| Código | Descripción | Categoría |
|---|---|---|
| RNF01 | Interfaz sencilla y fácil de utilizar. | Usabilidad |
| RNF02 | Proteger información mediante autenticación. | Seguridad |
| RNF03 | Almacenar contraseñas mediante hash seguro. | Seguridad |
| RNF04 | Controlar permisos según el rol. | Seguridad |
| RNF05 | Realizar búsquedas rápidas. | Rendimiento |
| RNF06 | Mantener integridad de los datos. | Fiabilidad |
| RNF07 | Permitir copias de seguridad. | Fiabilidad |
| RNF08 | Funcionar en navegadores modernos. | Portabilidad |
| RNF09 | Contar con diseño responsivo. | Portabilidad |
| RNF10 | Permitir futuras ampliaciones. | Mantenibilidad |
| RNF11 | Mantener trazabilidad de operaciones de facturación. | Facturación |
| RNF12 | Validar formatos tributarios y documentos. | Facturación |
| RNF13 | Proporcionar tiempos de respuesta adecuados. | Rendimiento |
| RNF14 | Permitir actualizar configuraciones tributarias sin modificar la lógica principal. | Mantenibilidad |

**Categorías de calidad.** Los requisitos no funcionales consideran las siguientes características:

- Rendimiento.
- Seguridad.
- Usabilidad.
- Fiabilidad.
- Escalabilidad.
- Mantenibilidad.
- Portabilidad.

### 3.3. Reglas del negocio

- **RB01 — Código interno del estudiante.** El campo `codigo` del estudiante almacena el código interno proveniente de la columna `Codigo` del Excel de inscripciones (sin prefijo). El código RUDE oficial se guarda en `codigo_rude`.
- **RB02 — Documento de identidad no duplicado.** No se permitirá registrar personas con un documento de identidad duplicado cuando corresponda al mismo tipo de persona.
- **RB03 — Correo no duplicado.** El correo electrónico de un usuario deberá ser único.
- **RB04 — Un rol por usuario.** Cada usuario tendrá un único rol dentro del sistema.
- **RB05 — Autenticación obligatoria.** Ningún usuario podrá acceder a funcionalidades protegidas sin iniciar sesión.
- **RB06 — Bloqueo por intentos fallidos.** La cuenta se bloqueará después de cinco intentos fallidos consecutivos.
- **RB07 — Contraseña segura.** Las contraseñas deberán almacenarse mediante hash seguro y deberán cumplir con la longitud mínima definida por el sistema.
- **RB08 — Jerarquía académica.** Un paralelo pertenece a un grado y un grado pertenece a un nivel educativo.
- **RB09 — Eliminación protegida por uso.** Las entidades utilizadas por otros registros no deberán eliminarse físicamente cuando esto pueda afectar la integridad de los datos. En estos casos se utilizará desactivación o eliminación lógica.
- **RB10 — Período académico activo.** Solo podrá existir un período académico activo al mismo tiempo.
- **RB11 — Inscripción previa a la matrícula.** Solo se podrá matricular un estudiante cuando exista una inscripción válida y completada.
- **RB12 — Inscripción única por período.** Un estudiante no podrá tener más de una inscripción activa dentro del mismo período académico.
- **RB13 — Matrícula por estudiante.** Cada estudiante matriculado tendrá su propio registro de matrícula y sus correspondientes mensualidades.
- **RB14 — Rango de calificaciones.** Las calificaciones deberán encontrarse dentro del rango establecido por el sistema, de 0 a 100.
- **RB15 — Docente autorizado.** Solo un docente asignado a una determinada materia y curso podrá registrar las calificaciones y asistencia correspondientes.
- **RB16 — Promedio ponderado.** El sistema calculará automáticamente los promedios considerando la ponderación establecida para cada evaluación.
- **RB17 — Estados de asistencia.** Los estados de asistencia serán: Presente, Falta, Atraso, Permiso, Justificado.
- **RB18 — Sin cruces de horario.** No se permitirá asignar a un mismo docente dos materias dentro del mismo bloque horario ni utilizar una misma aula para dos clases simultáneas.
- **RB19 — Destinatarios de comunicados.** Los comunicados podrán dirigirse a: Todos, Docentes, Estudiantes, Padres/Tutores, Curso específico.
- **RB20 — Autoría de comunicados.** Solo los usuarios autorizados, principalmente Administrador y Director, podrán crear y publicar comunicados institucionales.

## 4. Modelado de negocio y casos de uso

### 4.1. Diagrama general de casos de uso

El diagrama general de casos de uso representa las principales interacciones entre los actores y el sistema.

Los actores principales son:

- Administrador.
- Director.
- Secretaría.
- Docente.
- Padre/Tutor.
- Estudiante.

> Figura 1. Diagrama general de casos de uso dividido por roles.

### 4.2. Especificaciones de casos de uso

**Administrador.** Entre los principales casos de uso se encuentran:

- Iniciar sesión.
- Gestionar usuarios.
- Gestionar roles y permisos.
- Gestionar niveles, grados y paralelos.
- Gestionar materias.
- Asignar docentes a materias y cursos.
- Configurar períodos académicos.
- Consultar reportes.
- Administrar configuración del sistema.
- Revisar historial de operaciones.
- Gestionar parámetros tributarios.
- Gestionar catálogo de servicios.
- Monitorear facturación.
- Exportar información RUDE.

**Director.**

- Iniciar sesión.
- Consultar información general.
- Supervisar calificaciones.
- Supervisar asistencia.
- Consultar reportes.
- Publicar comunicados.

**Secretaría.**

- Iniciar sesión.
- Registrar estudiantes.
- Registrar padres/tutores.
- Gestionar inscripciones.
- Gestionar matrículas.
- Gestionar mensualidades.
- Administrar documentación.
- Generar reportes.
- Gestionar facturación.
- Completar información RUDE.

**Docente.**

- Iniciar sesión.
- Consultar cursos asignados.
- Registrar asistencia.
- Registrar calificaciones.
- Consultar horarios.
- Recibir comunicados.

**Padre/Tutor.**

- Iniciar sesión.
- Consultar estudiantes asociados.
- Consultar calificaciones.
- Consultar asistencia.
- Consultar horarios.
- Consultar comunicados.

**Estudiante.**

- Iniciar sesión.
- Consultar calificaciones.
- Consultar asistencia.
- Consultar horarios.
- Consultar comunicados.
- Consultar información académica.

### 4.3. CU-01: Iniciar sesión

- **Actor:** Usuario.
- **Actores involucrados:** Administrador, Director, Secretaría, Docente, Estudiante y Padre/Tutor.
- **Precondición:** El usuario debe contar con una cuenta activa.

**Flujo básico.**

1. El usuario accede a la pantalla de inicio de sesión.
2. El usuario introduce su correo o usuario.
3. El usuario introduce su contraseña.
4. El sistema valida las credenciales.
5. El sistema verifica que la cuenta esté activa.
6. El sistema identifica el rol del usuario.
7. El sistema permite el acceso.
8. El sistema redirige al Dashboard correspondiente.

**Flujo alternativo.** Si las credenciales son incorrectas:

1. El sistema informa que las credenciales no son válidas.
2. El sistema registra el intento fallido.
3. El usuario puede volver a intentar iniciar sesión.
4. Después de cinco intentos fallidos consecutivos, la cuenta se bloquea.

**Postcondición.** El usuario accede únicamente a los módulos y funcionalidades permitidos por su rol.

### 4.4. Matriz de trazabilidad

| Requerimiento | Historia de Usuario | Épica |
|---|---|---|
| RF01 | HU-01 | Autenticación y Seguridad |
| RF02, RF22 | HU-02 | Autenticación y Seguridad |
| RF03 | HU-03 | Autenticación y Seguridad |
| RNF03, RF20 | HU-04, HU-05 | Autenticación y Seguridad |
| RF04 | HU-06 | Gestión de Información Personal |
| RF05 | HU-07 | Gestión de Información Personal |
| RF06 | HU-08 | Gestión de Información Personal |
| RF07 | HU-09 | Gestión de Información Personal |
| RF08 | HU-10 | Gestión de Información Personal |
| RF09 | HU-11, HU-12, HU-13 | Configuración Académica |
| RF10 | HU-14 | Configuración Académica |
| RF11 | HU-15, HU-16 | Configuración Académica |
| RF12 | HU-17, HU-18, HU-19, HU-20 | Inscripciones y Matrículas |
| RF13 | HU-21, HU-22, HU-24 | Evaluaciones y Calificaciones |
| RF14 | HU-23, HU-25 | Evaluaciones y Calificaciones |
| RF15 | HU-26 | Asistencia |
| RF16 | HU-27 | Asistencia |
| RF17 | HU-28, HU-29 | Horarios |
| RF18 | HU-30, HU-31 | Comunicados |
| RF19 | HU-32, HU-33, HU-34 | Reportes |
| RF21 | HU-35 | Auditoría |
| RF23-RF26, RF29 | HU-TRIB-01, HU-TRIB-02, HU-TRIB-03 | Cumplimiento Tributario |
| RF27-RF28 | HU-TRIB-04 | RUDE |

---

# FASE III: DISEÑO DE LA SOLUCIÓN

## 5. Modelado de objetos y clases

### 5.1. Diagrama de clases

El diagrama de clases representa la estructura estática del sistema, mostrando las principales entidades y sus relaciones.

> Figura 2. Diagrama de clases del sistema.

**Principales entidades.**

- Usuario.
- Estudiante.
- Padre/Tutor.
- Docente.
- Nivel.
- Grado.
- Paralelo.
- Curso.
- Materia.
- Inscripción.
- Matrícula.
- Pago.
- Evaluación.
- Calificación.
- Asistencia.
- Horario.
- Comunicado.
- Historial de operaciones.
- Configuración tributaria.
- Factura electrónica.
- Registro RUDE.
- Registro médico.

### 5.2. Diagrama de objetos

El diagrama de objetos representa instancias concretas de las clases en un momento determinado de la ejecución del sistema.

> Figura 3. Diagrama de objetos.

**Ejemplo de instancias.**

- `usuario: Usuario` — codigo = "ADM001", nombre = "María", apellido = "García", rol = "Administrador", estado = "ACTIVO".
- `estudiante: Estudiante` — codigo = "EST2026-0001", nombres = "Juan Carlos", apellidos = "Pérez Choque", fechaNacimiento = "12/05/2014", sexo = "M", documentoIdentidad = "1234567", estado = "ACTIVO".
- `padre: PadreTutor` — nombres = "Roberto", apellidos = "Pérez", documentoIdentidad = "2345678", tipoRelacion = "PADRE".
- `vinculo: Relación` — estudiante = "EST2026-0001", padreTutor = "padre", esPrincipal = 1.
- `inscripcion: Inscripcion` — estudiante = "EST2026-0001", fecha = "01/02/2026", estado = "COMPLETADA".
- `matricula: Matricula` — estudiante = "EST2026-0001", curso = "4to Sec. A", montoMensual = 785.50, estado = "ACTIVA".
- `pago: PagoMensualidad` — matricula = "matricula", mes = 2, anio = 2026, monto = 785.50, estado = "PAGADO".

## 6. Diseño de base de datos

### 6.1. Modelo conceptual

El modelo conceptual representa las principales entidades del sistema y sus relaciones.

> Figura 4. Modelo conceptual de la base de datos.

**Relaciones principales.**

- Un rol puede estar asociado a muchos usuarios.
- Un usuario posee un único rol.
- Un estudiante puede relacionarse con uno o más padres/tutores.
- Un docente puede estar asignado a varias materias y cursos.
- Un nivel contiene varios grados.
- Un grado contiene varios paralelos.
- Un curso corresponde a una estructura académica dentro de un período.
- Un estudiante puede tener una inscripción por período académico.
- Una inscripción completada puede originar una matrícula.
- Una matrícula puede generar varias mensualidades.
- Una evaluación puede tener varias calificaciones.
- Un estudiante puede tener múltiples registros de asistencia.
- Un comunicado puede tener múltiples destinatarios.
- Un comunicado puede tener múltiples registros de lectura.
- Un estudiante puede tener un registro RUDE.
- Un estudiante puede tener un registro médico.

### 6.2. Modelo lógico

| Tabla | Clave Primaria | Claves Foráneas | Atributos principales |
|---|---|---|---|
| roles | id | — | nombre, descripcion, estado |
| users | id | role_id | nombre, apellido, email, password, estado |
| operation_histories | id | user_id | accion, modulo, detalle |
| students | id | user_id | codigo, nombres, apellidos, documento |
| parent_guardians | id | user_id | nombres, apellidos, documento |
| student_parent | id | student_id, parent_guardian_id | tipo_relacion, es_principal |
| teachers | id | user_id | nombres, especialidad |
| academic_years | id | — | nombre, fecha_inicio, fecha_fin |
| levels | id | — | nombre, descripcion, orden |
| grades | id | level_id | nombre, orden |
| sections | id | grade_id, teacher_id | nombre, capacidad |
| courses | id | grade_id, section_id, academic_year_id | nombre, aula, estado |
| subjects | id | level_id | nombre, descripcion |
| course_subjects | id | subject_id, course_id, teacher_id | relación docente-materia-curso |
| enrollments | id | student_id, academic_year_id | fecha, estado |
| registrations | id | enrollment_id, course_id | fecha, monto_mensual, estado |
| student_documents | id | student_id | tipo_documento, archivo, estado |
| monthly_payments | id | registration_id, electronic_invoice_id | mes, año, monto, estado |
| discount_types | id | — | código, nombre, tipo_aplicación, porcentaje, importe_fijo, orden, estado |
| evaluation_plans | id | course_subject_id | trimestre, nombre, estado |
| evaluation_criteria | id | evaluation_plan_id | código (SE/SA/HA/AU), porcentaje, tipo |
| evaluation_activities | id | evaluation_criteria_id | nombre, orden, fecha |
| evaluation_activity_grades | id | evaluation_activity_id, student_id | nota |
| evaluation_second_attempts | id | evaluation_plan_id, student_id | nota_original, nota_recuperación, estado |
| student_curricular_adaptations | id | student_id | condición, tipo, grado, plan, evaluación diferenciada |
| data_consents | id | titular (morph) | finalidad, consentimiento, fecha, revocación |
| arco_requests | id | titular (morph) | tipo, estado, fecha_solicitud, resolución |
| assessments | id | course_subject_id, subject_id, academic_year_id | nombre, tipo, peso |
| grade_scores | id | assessment_id, student_id, registered_by_user_id | nota, promedio |
| attendances | id | student_id, course_id, registered_by_user_id | fecha, estado |
| schedules | id | course_id, subject_id, teacher_id | día, hora_inicio, hora_fin |
| announcements | id | author_id | titulo, contenido, estado |
| announcement_reads | id | announcement_id, user_id | leido, fecha_lectura |
| announcement_recipients | id | announcement_id | tipo_destinatario |
| tax_configurations | id | — | nit, razon_social, modalidad_sfe |
| educational_services | id | — | codigo_sin, descripcion, iva_tipo |
| invoice_sequences | id | — | codigo_sucursal, punto_venta |
| daily_cufds | id | — | cufd, codigo_control, vigencia |
| significant_events | id | — | tipo_evento, fecha_inicio, motivo |
| electronic_invoices | id | — | nro_factura, cuf, cufd, total |
| invoice_details | id | electronic_invoice_id | codigo_servicio, descripcion |
| contingency_queues | id | electronic_invoice_id | intentos, estado |
| rc_iva_withholdings | id | — | fecha, sujeto_id, base_imponible |
| student_rude_records | id | student_id | codigo_rude, codigo_sie_ue |
| medical_records | id | student_id | grupo_sanguineo, alergias, condiciones |

### 6.3. Modelo físico

**Características.**

- Motor: MySQL 8.0+.
- Codificación: utf8mb4.
- Collation: utf8mb4_unicode_ci.
- Motor de almacenamiento: InnoDB.
- Claves primarias mediante id.
- Claves foráneas en inglés.
- Índices para campos de búsqueda.
- Restricciones de integridad referencial.
- Eliminación lógica mediante estados cuando corresponde.
- Campos created_at y updated_at para las tablas administradas por Laravel.

> Figura 5. Diagrama físico de la base de datos. Ver detalle completo en [diagrama-fisico.md](./diagrama-fisico.md).

**Ejemplo de tabla roles.**

```sql
CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NULL,
    estado ENUM('ACTIVO', 'INACTIVO', 'ELIMINADO')
        DEFAULT 'ACTIVO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
```

**Ejemplo de tabla users.**

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    telefono VARCHAR(20) NULL,
    estado ENUM('ACTIVO', 'INACTIVO', 'ELIMINADO')
        DEFAULT 'ACTIVO',
    intentos_fallidos TINYINT UNSIGNED DEFAULT 0,
    bloqueado_hasta TIMESTAMP NULL,
    ultimo_acceso TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    FOREIGN KEY (role_id)
        REFERENCES roles(id)
        ON DELETE RESTRICT,
    INDEX idx_email (email),
    INDEX idx_estado (estado),
    INDEX idx_role (role_id)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
```

**Ejemplo de tabla students.**

```sql
CREATE TABLE students (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nombres VARCHAR(150) NOT NULL,
    apellidos VARCHAR(150) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    lugar_nacimiento VARCHAR(150) NULL,
    sexo ENUM('M', 'F') NOT NULL,
    tipo_documento ENUM('CI', 'PASAPORTE', 'OTRO')
        DEFAULT 'CI',
    numero_documento VARCHAR(20) NOT NULL,
    complemento VARCHAR(5) NOT NULL DEFAULT '',
    direccion VARCHAR(255) NULL,
    telefono VARCHAR(20) NULL,
    email VARCHAR(150) NULL,
    fotografia VARCHAR(500) NULL,
    estado ENUM(
        'ACTIVO',
        'INACTIVO',
        'EGRESADO',
        'TRASLADADO',
        'ELIMINADO'
    ) DEFAULT 'ACTIVO',
    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE SET NULL,
    UNIQUE KEY uk_documento
        (numero_documento, complemento),
    UNIQUE KEY uk_email (email),
    INDEX idx_codigo (codigo),
    INDEX idx_nombre (nombres, apellidos),
    INDEX idx_estado (estado)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
```

**Integridad referencial.** Las claves foráneas utilizarán reglas como:

- CASCADE.
- RESTRICT.
- SET NULL.

Estas reglas permitirán mantener la integridad de la información almacenada y controlar qué sucede cuando se modifican o eliminan registros relacionados.

---

# FASE IV: GESTIÓN ÁGIL DEL PROYECTO

## 7. Roles y equipo del proyecto

### 7.1. Roles Scrum

| Rol Scrum | Responsable / Descripción |
|---|---|
| Product Owner (PO) | Representa a la institución, Unidad Educativa San Bernardo. Es responsable de definir y priorizar el Product Backlog de acuerdo con el valor de negocio, validar cada entregable presentado en las Demo y velar por el cumplimiento del Product Goal. |
| Scrum Master | Facilita la aplicación del marco de trabajo Scrum, ayuda a eliminar impedimentos, organiza y coordina las ceremonias Scrum, como Sprint Planning, Daily Scrum, Sprint Review y Sprint Retrospective, además de acompañar al equipo de desarrollo durante el proyecto. |
| Development Team | Equipo encargado de diseñar, desarrollar, probar e implementar el sistema. Está conformado por tres desarrolladores Full Stack: Jonathan Soraire, Joel Cerrogrande y Roberto. Cada desarrollador es responsable del desarrollo integral de los módulos asignados, incluyendo frontend y backend, de acuerdo con la planificación de los Sprints. |

### 7.2. Equipo del proyecto

El equipo del proyecto está conformado por cinco integrantes. Cada miembro tiene un rol principal dentro de Scrum; sin embargo, los cinco integrantes participaron activamente en el desarrollo del sistema.

| Integrante | Rol Scrum | Perfil / Responsabilidades principales |
|---|---|---|
| Kehila Molina | Scrum Master | Facilita el proceso Scrum, ayuda a eliminar impedimentos, organiza las ceremonias Scrum (Sprint Planning, Daily Scrum, Sprint Review y Sprint Retrospective) y acompaña al equipo de desarrollo. Además, participó activamente como desarrolladora en la implementación de funcionalidades del sistema. |
| Snaider Quispe | Product Owner | Define y prioriza el Product Backlog de acuerdo con el valor de negocio, valida los entregables presentados en las Demo y vela por el cumplimiento del Product Goal. Además, participó activamente como desarrollador en la implementación de funcionalidades del sistema. |
| Jonathan Soraire | Development Team | Desarrollador Full Stack, responsable del desarrollo integral de los módulos asignados, incluyendo frontend y backend. |
| Joel Cerrogrande | Development Team | Desarrollador Full Stack, responsable del desarrollo integral de los módulos asignados, incluyendo frontend y backend. |
| Roberto | Development Team | Desarrollador Full Stack, responsable del desarrollo integral de los módulos asignados, incluyendo frontend y backend. |

**Funciones divididas del equipo de desarrollo.** Los cinco integrantes del equipo participaron en el desarrollo del sistema, además de cumplir con sus responsabilidades principales dentro del marco de trabajo Scrum.

Los tres integrantes que conforman principalmente el Development Team: Jonathan Soraire, Joel Cerrogrande y Roberto, desempeñaron el perfil de desarrolladores Full Stack, encargándose del desarrollo de los módulos asignados de principio a fin. Esto comprende tanto las actividades de frontend como de backend.

Desarrollo Frontend. Las actividades de frontend incluyen:

- Desarrollo de interfaces de usuario.
- Diseño de interfaces responsivas.
- Implementación de formularios y componentes visuales.
- Desarrollo de consultas y visualización de información.
- Implementación de dashboards de acuerdo con los diferentes roles de usuario.
- Validación y presentación de la información.

Desarrollo Backend. Las actividades de backend incluyen:

- Implementación de la lógica de negocio.
- Desarrollo de controladores y funcionalidades del sistema.
- Gestión y conexión con la base de datos.
- Implementación de operaciones CRUD.
- Desarrollo de reglas y validaciones.
- Integración de los diferentes módulos del sistema.
- Implementación de funcionalidades relacionadas con la facturación electrónica SFE.
- Registro y gestión de auditoría de las operaciones realizadas.

**Participación del Scrum Master y Product Owner.** Además de desempeñar sus respectivos roles dentro de Scrum, Kehila Molina (Scrum Master) y Snaider Quispe (Product Owner) también participaron activamente en el desarrollo del sistema como desarrolladores.

Su participación incluyó la colaboración en la implementación de funcionalidades, corrección de errores, desarrollo de componentes y apoyo en las tareas técnicas definidas durante los Sprints.

Por lo tanto, aunque existió una distribución de responsabilidades según los roles Scrum, el desarrollo del sistema fue realizado de manera colaborativa por los cinco integrantes del equipo.

**Módulos desarrollados.** Los módulos y funcionalidades contemplados en el desarrollo incluyen:

- Gestión de usuarios.
- Gestión de estudiantes.
- Gestión de docentes.
- Gestión de cursos.
- Inscripciones.
- Matrículas.
- Pagos.
- Horarios.
- Calificaciones.
- Asistencia.
- Comunicados.
- Reportes.
- Facturación electrónica SFE.
- Auditoría.

La asignación específica de módulos y tareas entre los cinco integrantes se realizó de acuerdo con la planificación de los Sprints y la distribución de las historias de usuario del proyecto.

### 7.3. Matriz RACI (Responsable, Aprobador, Consultado e Informado de los entregables)

La matriz RACI permite establecer el nivel de participación y responsabilidad de cada integrante del equipo en los diferentes entregables del proyecto. Se consideran cuatro categorías: Responsable (R), Aprobador (A), Consultado (C) e Informado (I).

| Entregable | Kehila (Scrum Master) | Snaider (Product Owner) | Jonathan (Dev) | Joel (Dev) | Rober (Dev) | Administrador |
|---|---|---|---|---|---|---|
| Product Backlog | R | A | C | C | C | C |
| Sprint Backlog | R | C | R | R | R | I |
| Requerimientos e Historias de Usuario | A | A | R | R | R | C |
| Diagramas y Modelos | C/A | C | R | R | R | I |
| Base de datos / DDL | C/A | C | R | R | R | I |
| Autenticación y Seguridad (HU-01 a HU-05) | R/A | R/A | R | C | R | I |
| Módulo de Estudiantes y Personas (HU-06 a HU-10) | R/A | R/A | R | R | C | C |
| Configuración Académica (HU-11 a HU-16) | R/A | R/A | C | R | R | I |
| Módulo de Matrículas/Pagos (HU-17 a HU-20) | R/A | R/A | R | C | R | C |
| Módulo Académico — notas/asistencia (HU-21 a HU-29) | R/A | R/A | R | C | R | C |
| Comunicados y Auditoría (HU-30, HU-31, HU-35) | C/A | R/A | C | C | C | C |
| Reportes (HU-32, HU-33, HU-34) | C/A | R/A | C | C | C | C |
| Facturación SFE y RUDE (HU-TRIB-01 a HU-TRIB-04) | C/A | R/A | C | R | C | C |
| Acta de Aceptación del Cliente | C/A | A | C | C | C | C |

**Leyenda de la matriz RACI:**

- R — Responsable: persona encargada de ejecutar y realizar las actividades correspondientes al entregable.
- A — Aprobador: persona que tiene la responsabilidad final de aprobar el entregable.
- C — Consultado: persona que proporciona información, conocimientos u orientación durante la elaboración del entregable.
- I — Informado: persona que debe mantenerse informada sobre el avance o resultado del entregable.

Nota: Debido a que el Scrum Master y el Product Owner participaron activamente en el desarrollo del sistema, ambos pueden figurar como responsables en determinados entregables, además de cumplir con sus respectivas funciones dentro del marco de trabajo Scrum.

## 8. Artefactos de Scrum (Backlogs)

### 8.1. Product Backlog

| Épica | Historias | Puntos |
|---|---|---|
| 1. Autenticación y Seguridad | HU-01 a HU-05 | 19 |
| 2. Gestión de Información Personal | HU-06 a HU-10 | 17 |
| 3. Configuración Académica | HU-11 a HU-16 | 20 |
| 4. Inscripciones y Matrículas | HU-17 a HU-20 | 16 |
| 5. Evaluaciones y Calificaciones | HU-21 a HU-25 | 19 |
| 6. Asistencia | HU-26 a HU-27 | 8 |
| 7. Horarios | HU-28 a HU-29 | 8 |
| 8. Comunicados | HU-30 a HU-31 | 8 |
| 9. Reportes | HU-32 a HU-34 | 13 |
| 10. Auditoría | HU-35 | 3 |
| 11. Cumplimiento Tributario y RUDE | HU-TRIB-01 a HU-TRIB-04 | 21 |
| **Total** | **39 historias** | **152** |

**Historias de usuario.**

Épica 1 — Autenticación y Seguridad:

| ID | Historia | Prioridad | Puntos | Sprint |
|---|---|---|---|---|
| HU-01 | Como usuario, quiero iniciar sesión con usuario y contraseña para acceder al sistema según mi rol. | Alta | 5 | 1 |
| HU-02 | Como administrador, quiero gestionar usuarios para controlar el acceso. | Alta | 5 | 1 |
| HU-03 | Como administrador, quiero asignar roles a usuarios. | Alta | 3 | 1 |
| HU-04 | Como usuario, quiero que mi contraseña se almacene de forma segura. | Alta | 3 | 1 |
| HU-05 | Como usuario, quiero que el sistema valide mis permisos. | Alta | 3 | 1 |

Épica 2 — Gestión de Información Personal:

| ID | Historia | Prioridad | Puntos | Sprint |
|---|---|---|---|---|
| HU-06 | Como administrador, quiero registrar y gestionar estudiantes incluyendo sus datos RUDE. | Alta | 5 | 2 |
| HU-07 | Como usuario autorizado, quiero consultar y editar la información de los estudiantes. | Alta | 3 | 2 |
| HU-08 | Como administrador, quiero registrar y gestionar padres o tutores incluyendo sus datos RUDE. | Alta | 3 | 2 |
| HU-09 | Como administrador, quiero relacionar estudiantes con sus padres o tutores. | Alta | 3 | 2 |
| HU-10 | Como administrador, quiero registrar y gestionar docentes. | Alta | 3 | 2 |

Épica 3 — Configuración Académica:

| ID | Historia | Puntos | Sprint |
|---|---|---|---|
| HU-11 | Como administrador, quiero gestionar los niveles educativos para configurar la estructura académica. | 3 | 1 |
| HU-12 | Como administrador, quiero gestionar los grados para organizar los cursos. | 3 | 1 |
| HU-13 | Como administrador, quiero gestionar los paralelos para organizar los estudiantes. | 3 | 1 |
| HU-14 | Como administrador, quiero gestionar las materias para organizar la oferta académica. | 3 | 2 |
| HU-15 | Como administrador, quiero gestionar el período académico para organizar las actividades escolares. | 3 | 2 |
| HU-16 | Como administrador, quiero asignar docentes a materias y cursos para organizar la enseñanza. | 5 | 2 |

Épica 4 — Inscripciones y Matrículas:

| ID | Historia | Puntos | Sprint |
|---|---|---|---|
| HU-17 | Como personal administrativo, quiero gestionar las inscripciones de estudiantes y sus estados. | 5 | 3 |
| HU-18 | Como personal administrativo, quiero realizar matrículas asociando estudiantes con curso, paralelo y mensualidad. | 5 | 3 |
| HU-19 | Como personal administrativo, quiero registrar pagos de mensualidades y controlar la morosidad. | 5 | 3 |
| HU-20 | Como personal administrativo, quiero adjuntar y validar documentación de los estudiantes. | 3 | 3 |

Épica 5 — Evaluaciones y Calificaciones:

| ID | Historia | Puntos | Sprint |
|---|---|---|---|
| HU-21 | Como docente, quiero gestionar evaluaciones por materia y período. | 5 | 4 |
| HU-22 | Como docente asignado, quiero registrar calificaciones de mis estudiantes. | 5 | 4 |
| HU-23 | Como estudiante, docente o padre/tutor, quiero consultar mi horario para conocer mis actividades académicas. | 3 | 4 |
| HU-24 | Como sistema, quiero calcular automáticamente los promedios de los estudiantes. | 3 | 4 |
| HU-25 | Como usuario autorizado, quiero consultar el historial académico del estudiante. | 3 | 4 |

Épica 6 — Asistencia:

| ID | Historia | Puntos | Sprint |
|---|---|---|---|
| HU-26 | Como docente, quiero registrar la asistencia diaria de los estudiantes utilizando diferentes estados. | 5 | 4 |
| HU-27 | Como estudiante o padre/tutor, quiero consultar el historial de asistencia. | 3 | 4 |

Épica 7 — Horarios:

| ID | Historia | Puntos | Sprint |
|---|---|---|---|
| HU-28 | Como usuario, quiero gestionar y consultar horarios según mi rol. | 5 | 4 |
| HU-29 | Como estudiante, docente o padre/tutor, quiero consultar mi horario para conocer mis actividades académicas. | 3 | 4 |

Épica 8 — Comunicados:

| ID | Historia | Puntos | Sprint |
|---|---|---|---|
| HU-30 | Como administrador, quiero crear y gestionar comunicados dirigidos a determinados roles o grupos. | 5 | 5 |
| HU-31 | Como usuario, quiero consultar una bandeja de comunicados dirigidos a mí. | 3 | 5 |

Épica 9 — Reportes:

| ID | Historia | Puntos | Sprint |
|---|---|---|---|
| HU-32 | Como administrador, quiero generar reportes académicos con filtros y exportarlos a PDF o Excel. | 5 | 5 |
| HU-33 | Como administrador, quiero generar reportes administrativos con filtros y exportarlos a PDF o Excel. | 5 | 5 |
| HU-34 | Como usuario autorizado, quiero disponer de opciones para exportar los reportes. | 3 | 5 |

Épica 10 — Auditoría:

| ID | Historia | Puntos | Sprint |
|---|---|---|---|
| HU-35 | Como administrador, quiero consultar un registro de auditoría de las operaciones críticas del sistema. | 3 | 5 |

Épica 11 — Cumplimiento Tributario y RUDE:

| ID | Historia | Prioridad | Puntos | Sprint |
|---|---|---|---|---|
| HU-TRIB-01 | Como administrador, quiero configurar los datos y parámetros tributarios de la institución, para cumplir con los requerimientos del Sistema de Facturación Electrónica (SFE). | Alta | 5 | 1 |
| HU-TRIB-02 | Como personal administrativo, quiero generar facturas EXENTAS por matrícula, mensualidad y transporte, asociadas al padre/tutor. | Alta | 5 | 6 |
| HU-TRIB-03 | Como administrador, quiero contar con un mecanismo de contingencia para generar y reenviar facturas cuando el SFE no esté disponible. | Alta | 5 | 6 |
| HU-TRIB-04 | Como secretaría, quiero completar el formulario RUDE oficial RM 0031/2025 (secciones I-V) con validaciones CI, certificado de nacimiento y código SIE UE, y exportar el formato oficial MinEdu. | Alta | 6 | 2 |

### 8.2. Product Goal

La meta del producto es:

Centralizar en una única aplicación web la gestión académica y administrativa de la Unidad Educativa San Bernardo, permitiendo administrar estudiantes, docentes, tutores, inscripciones, matrículas, pagos, calificaciones, asistencia, horarios, comunicados y reportes, con acceso controlado según los roles de los usuarios.

### 8.3. Sprint Backlog

El Sprint Backlog detalla las historias de usuario y tareas técnicas seleccionadas para cada Sprint, junto con su estimación en puntos y el responsable de su ejecución. La distribución permite organizar el trabajo del equipo y facilitar el seguimiento del avance durante cada iteración.

**Sprint 1 — Fundación y Seguridad.**

| HU | Puntos | Responsable | Comentario |
|---|---|---|---|
| HU-01 | 5 | Snaider | Implementación del inicio de sesión y gestión de sesión. |
| HU-04 | 3 | Snaider | Implementación del almacenamiento seguro de contraseñas mediante bcrypt/Argon2. |
| HU-05 | 3 | Kehila | Implementación del middleware para control de permisos. |
| HU-02 | 5 | Jonathan | CRUD de usuarios y gestión de roles. |
| HU-03 | 3 | Roberto | Asignación de roles a usuarios. |
| HU-11 | 3 | Kehila | Gestión de niveles educativos. |
| HU-12 | 3 | Joel | Gestión de grados. |
| HU-13 | 3 | Joel | Gestión de paralelos. |
| HU-TRIB-01 | 5 | Snaider | Configuración tributaria: NIT 408460020, leyendas y modalidad SFE. |
| **Total** | **33** | | |

**Sprint 2 — Personas y Configuración Académica.**

| HU | Puntos | Responsable | Comentario |
|---|---|---|---|
| HU-06 | 5 | Kehila | CRUD de estudiantes, incluyendo campos requeridos para RUDE. |
| HU-07 | 3 | Joel | Edición y consulta de información de estudiantes. |
| HU-08 | 3 | Snaider | CRUD de padres/tutores, incluyendo campos requeridos para RUDE. |
| HU-09 | 3 | Snaider | Gestión de la relación entre estudiantes y padres/tutores. |
| HU-10 | 3 | Jonathan | CRUD de docentes. |
| HU-14 | 3 | Joel | CRUD de materias. |
| HU-15 | 3 | Snaider | Gestión del período académico. |
| HU-16 | 5 | Roberto | Asignación de docente, materia y curso. |
| HU-TRIB-04 | 6 | Snaider | Gestión de información tributaria y formulario RUDE. |
| **Total** | **34** | | |

**Sprint 3 — Inscripciones, Matrículas y Pagos.**

| HU | Puntos | Responsable | Comentario |
|---|---|---|---|
| HU-17 | 5 | Jonathan | Gestión de inscripciones mediante CRUD y control de estados. |
| HU-18 | 5 | Snaider | Gestión de matrículas con curso, paralelo y mensualidad. |
| HU-19 | 5 | Kehila | Gestión de pagos de mensualidades y control de morosidad. |
| HU-20 | 3 | Roberto | Subida y validación de documentación adjunta. |
| **Total** | **18** | | |

**Sprint 4 — Núcleo Académico.**

| HU | Puntos | Responsable | Comentario |
|---|---|---|---|
| HU-28 | 5 | Roberto | CRUD de horarios y visualización según el usuario. |
| HU-29 | 3 | Snaider | Visualización del horario para estudiantes, docentes y padres/tutores. |
| HU-21 | 5 | Snaider | Gestión de evaluaciones por materia y período. |
| HU-22 | 5 | Snaider | Registro de calificaciones por el docente asignado. |
| HU-24 | 3 | Jonathan | Cálculo automático de promedios. |
| HU-23 | 3 | Jonathan | Consulta de calificaciones. |
| HU-25 | 3 | Snaider | Consulta del historial académico. |
| HU-26 | 5 | Snaider | Registro de asistencia diaria con cinco estados. |
| HU-27 | 3 | Kehila | Consulta del historial de asistencia. |
| **Total** | **35** | | |

**Sprint 5 — Comunicación, Reportes, Auditoría y Pulido.**

| HU / Tarea | Puntos | Responsable | Comentario |
|---|---|---|---|
| HU-30 | 5 | Snaider | Gestión de comunicados, destinatarios por rol y grupos. |
| HU-31 | 3 | Snaider | Implementación de la bandeja de comunicados para cada usuario. |
| HU-32 | 5 | Snaider | Generación de reportes académicos con filtros y exportación a PDF/Excel. |
| HU-33 | 5 | Snaider | Generación de reportes administrativos con filtros y exportación a PDF/Excel. |
| HU-34 | 3 | Snaider | Implementación de botones de exportación en los reportes. |
| HU-35 | 3 | Snaider | Registro de auditoría y visor de operaciones para el administrador. |
| Tarea técnica | 5 | Kehila | Refinamiento de la experiencia de usuario del login, validaciones y diseño responsivo. |
| RNF01, RNF08, RNF09 | 5 | Kehila | Implementación de requisitos relacionados con usabilidad, compatibilidad con navegadores y diseño responsivo. |
| **Total** | **34** | | |

**Sprint 6 — Facturación Electrónica SFE y Cumplimiento Tributario.**

| HU | Puntos | Responsable | Comentario |
|---|---|---|---|
| HU-TRIB-02 | 5 | Joel | Implementación de factura exenta para matrícula, mensualidad y transporte; receptor Padre/Tutor, leyendas, generación de CUF y envío al SIN. |
| HU-TRIB-03 | 5 | Joel | Implementación de contingencia SFE mediante CUF offline, cola persistente, reenvío automático y registro de auditoría. |
| **Total** | **10** | | |

**Resumen de puntos por Sprint.**

| Sprint | Nombre | Puntos | Estado |
|---|---|---|---|
| Sprint 1 | Fundación y Seguridad | 33 | Finalizado |
| Sprint 2 | Personas y Configuración Académica | 34 | Finalizado |
| Sprint 3 | Inscripciones, Matrículas y Pagos | 18 | Finalizado |
| Sprint 4 | Núcleo Académico | 35 | Finalizado |
| Sprint 5 | Comunicación, Reportes, Auditoría y Pulido | 34 | Finalizado |
| Sprint 6 | Facturación Electrónica SFE y Cumplimiento Tributario | 10 | Finalizado |
| **Total** | **Product Backlog** | **164** | **Finalizado** |

**Criterios de aceptación.**

Épica 1: Autenticación y Seguridad.

- HU-01: Iniciar sesión.
  - El sistema debe permitir ingresar usuario y contraseña.
  - El sistema debe validar credenciales correctas y mostrar mensaje de error si son incorrectas.
  - Al iniciar sesión exitosamente, el sistema debe redirigir al Dashboard según el rol del usuario.
  - El sistema debe bloquear la cuenta después de 5 intentos fallidos.
- HU-02: Gestionar usuarios.
  - El administrador debe poder crear usuarios con nombre, apellido, correo, contraseña y rol.
  - El administrador debe poder editar información de usuarios existentes.
  - El administrador debe poder activar y desactivar usuarios.
  - El sistema debe validar que el correo no esté duplicado al crear un usuario.
- HU-03: Asignar roles.
  - El administrador debe poder asignar un rol a cada usuario (Administrador, Director, Secretaría, Docente, Padre/Tutor, Estudiante).
  - El sistema debe reflejar inmediatamente los permisos del nuevo rol asignado.
  - Un usuario solo puede tener un rol asignado a la vez.
- HU-04: Contraseña segura.
  - Las contraseñas deben almacenarse con hash.
  - Las contraseñas nunca deben mostrarse en texto plano en la base de datos.
  - El sistema debe exigir mínimo 8 caracteres para nuevas contraseñas.
- HU-05: Control de permisos.
  - El sistema debe denegar acceso a módulos no autorizados para el rol del usuario.
  - El sistema debe ocultar menúes y botones que el usuario no puede utilizar.
  - Si un usuario intenta acceder directamente a una URL no autorizada, el sistema debe redirigirlo a una página de acceso denegado.

Épica 2: Gestión de Información Personal.

- HU-06: Registrar estudiantes.
  - El sistema debe permitir registrar estudiantes con: nombres, apellidos, fecha de nacimiento, lugar de nacimiento, sexo, documento de identidad, dirección, teléfono.
  - El sistema debe almacenar el código interno del estudiante (columna `Codigo` del Excel de inscripciones).
  - Los campos obligatorios deben estar marcados y el sistema no debe permitir guardar si están vacíos.
  - El sistema debe validar que el documento de identidad no esté duplicado.
- HU-07: Modificar y consultar estudiantes.
  - El sistema debe permitir buscar estudiantes por nombre, código o documento de identidad.
  - El sistema debe permitir editar cualquier campo de la información del estudiante.
  - El sistema debe mostrar un historial de cambios realizados al estudiante.
- HU-08: Registrar padres o tutores.
  - El sistema debe permitir registrar padres/tutores con: nombres, apellidos, documento de identidad, teléfono, correo, dirección.
  - El sistema debe permitir registrar el tipo de relación (padre, madre, tutor).
  - El sistema debe validar que el correo no esté duplicado.
- HU-09: Relacionar estudiantes con padres/tutores.
  - El sistema debe permitir asociar un estudiante con uno o más padres/tutores.
  - Un padre/tutor puede estar asociado a múltiples estudiantes (hermanos).
  - El sistema debe mostrar la información del tutor asociado en el perfil del estudiante.
- HU-10: Registrar docentes.
  - El sistema debe permitir registrar docentes con: nombres, apellidos, especialidad, documento de identidad, teléfono, correo.
  - Los docentes no requieren código interno.
  - El sistema debe permitir activar o desactivar docentes.

Épica 3: Configuración Académica.

- HU-11: Administrar niveles educativos.
  - El sistema debe permitir crear, editar y eliminar niveles educativos (Inicial, Primaria, Secundaria).
  - El sistema no debe permitir eliminar un nivel que tenga grados asociados.
  - El sistema debe mostrar la cantidad de grados asociados a cada nivel.
- HU-12: Administrar grados.
  - El sistema debe permitir crear grados asociados a un nivel (ej: 1ro Primaria, 2do Primaria).
  - El sistema debe permitir editar el nombre del grado.
  - El sistema no debe permitir eliminar un grado que tenga paralelos o estudiantes matriculados.
- HU-13: Administrar paralelos.
  - El sistema debe permitir crear paralelos para cada grado (A, B, C, etc.).
  - El sistema debe permitir asignar un docente responsable a cada paralelo.
  - El sistema no debe permitir eliminar un paralelo que tenga estudiantes matriculados.
- HU-14: Administrar materias.
  - El sistema debe permitir crear materias con nombre y descripción.
  - El sistema debe permitir asignar materias a niveles específicos.
  - El sistema no debe permitir eliminar una materia que tenga evaluaciones o calificaciones registradas.
- HU-15: Configurar períodos académicos.
  - El sistema debe permitir crear períodos académicos con nombre, fecha inicio y fecha fin.
  - Solo puede haber un período académico activo a la vez.
  - El sistema debe permitir cerrar un período y abrir uno nuevo.
- HU-16: Asignar docentes a materias y cursos.
  - El sistema debe permitir asignar un docente a una materia específica dentro de un curso/paralelo.
  - Un docente puede estar asignado a múltiples materias y cursos.
  - El sistema no debe permitir asignar el mismo docente a dos materias en el mismo horario.

Épica 4: Inscripciones y Matrículas.

- HU-17: Registrar inscripciones.
  - El sistema debe permitir registrar inscripciones con estudiante, fecha y observaciones.
  - El sistema debe permitir asignar estados a la inscripción (Pendiente, Completada, Anulada).
  - El sistema debe validar que el estudiante no tenga una inscripción activa en el mismo período.
- HU-18: Registrar matrículas.
  - El sistema debe permitir matricular estudiantes en un curso y paralelo específico.
  - El sistema debe registrar el costo de la matrícula y la mensualidad.
  - Solo se puede matricular un estudiante que tenga inscripción completada.
- HU-19: Registrar pagos de mensualidades.
  - El sistema debe permitir registrar pagos con monto, fecha y método de pago.
  - El sistema debe calcular automáticamente el saldo pendiente por estudiante.
  - El sistema debe marcar como "Pendiente" las mensualidades no pagadas.
- HU-20: Administrar documentación.
  - La gestión de la documentación debe realizarse desde la ficha del estudiante.
  - El sistema debe permitir marcar documentos como "Pendiente" o "Entregado".
  - El sistema debe mostrar un listado de documentación pendiente por estudiante.

Épica 5: Evaluaciones y Calificaciones.

- HU-21: Registrar evaluaciones.
  - El sistema debe permitir crear evaluaciones con nombre, tipo y materia.
  - El sistema debe permitir definir el peso de cada evaluación en el promedio.
  - Las evaluaciones deben estar asociadas a un período académico específico.
- HU-22: Registrar calificaciones.
  - El sistema debe permitir ingresar notas del 0 al 100 para cada estudiante en cada evaluación.
  - Solo el docente asignado a la materia puede registrar calificaciones.
  - El sistema debe validar que la nota esté en el rango permitido.
- HU-23: Consultar calificaciones.
  - El estudiante debe poder ver sus calificaciones por materia y período.
  - El padre/tutor debe poder ver las calificaciones de sus hijos.
  - El sistema debe mostrar el promedio calculado por materia.
- HU-24: Cálculo automático de promedios.
  - El sistema debe calcular el promedio ponderado automáticamente al registrar calificaciones.
  - El promedio debe considerar el peso de cada evaluación.
  - El sistema debe mostrar la nota final por materia al cierre del período.
- HU-25: Consultar historial académico.
  - El sistema debe mostrar todas las materias cursadas por el estudiante.
  - El sistema debe mostrar las calificaciones y promedios de períodos anteriores.
  - El sistema debe permitir exportar el historial académico en PDF.

Épica 6: Asistencia.

- HU-26: Registrar asistencia.
  - El sistema debe permitir registrar asistencia diaria con estados: Presente, Falta, Atraso, Permiso, Justificado.
  - Solo el docente asignado al curso puede registrar asistencia.
  - El sistema debe mostrar la lista de estudiantes del curso para el registro rápido.
- HU-27: Consultar historial de asistencia.
  - El estudiante debe poder ver su historial de asistencia por fecha y curso.
  - El padre/tutor debe poder ver la asistencia de sus hijos.
  - El sistema debe mostrar estadísticas de inasistencias por estudiante.

Épica 7: Horarios.

- HU-28: Administrar horarios.
  - El sistema debe permitir crear horarios con día, hora inicio, hora fin, materia, docente y aula.
  - El sistema debe validar que no haya cruces de horario para el mismo docente.
  - El sistema debe validar que no haya cruces de horario para la misma aula.
- HU-29: Consultar horarios.
  - El estudiante debe poder ver su horario semanal completo.
  - El docente debe poder ver los horarios de sus cursos asignados.
  - El sistema debe mostrar el horario en formato de tabla semanal.

Épica 8: Comunicados.

- HU-30: Crear comunicados.
  - El sistema debe permitir crear comunicados con título, contenido y fecha de publicación.
  - El sistema debe permitir definir destinatarios: todos, docentes, estudiantes, padres, curso específico.
  - Solo el administrador y director pueden crear comunicados.
- HU-31: Consultar comunicados.
  - El sistema debe mostrar los comunicados dirigidos al rol del usuario.
  - Los comunicados deben ordenarse por fecha de publicación (más recientes primero).
  - El sistema debe indicar si un comunicado es nuevo (no leído).

Épica 9: Reportes.

- HU-32: Reportes académicos.
  - El sistema debe generar reportes de calificaciones por curso, paralelo y materia.
  - El sistema debe generar reportes de promedios por estudiante.
  - El sistema debe permitir filtrar reportes por período académico.
- HU-33: Reportes administrativos.
  - El sistema debe generar reportes de inscripciones y matrículas.
  - El sistema debe generar reportes de pagos y deudas pendientes.
  - El sistema debe generar reportes de documentación pendiente.
- HU-34: Exportar reportes.
  - El sistema debe permitir exportar reportes en formato PDF.
  - El sistema debe permitir exportar reportes en formato Excel.
  - Los reportes exportados deben incluir encabezado con logo del colegio y fecha de generación.

Épica 10: Auditoría.

- HU-35: Historial de operaciones.
  - El sistema debe registrar: quién, qué acción, en qué módulo y cuándo.
  - El administrador debe poder filtrar el historial por usuario, fecha y módulo.
  - El historial no debe ser editable ni eliminable por ningún usuario.

Épica 11: Cumplimiento Tributario y RUDE.

- HU-TRIB-01: Configuración tributaria.
  - El sistema debe permitir configurar: NIT, razón social, tipo entidad, modalidad SFE, CUIS.
  - El sistema debe almacenar las leyendas obligatorias para facturas exentas.
  - La configuración tributaria solo debe ser editable por el administrador.
- HU-TRIB-02: Emitir facturas exentas.
  - El sistema debe generar facturas con CUF válido según algoritmo SIN.
  - Las facturas deben incluir las leyendas: "EXENTO - NO GENERA DERECHO A CRÉDITO FISCAL", "EXENTO IT - ART. 13 INC. F LEY 843", "LEY 070 - EDUCACION EXENTA IVA ART. 13 LEY 843".
  - El receptor de la factura debe ser el padre/tutor (responsable financiero).
  - El sistema debe enviar la factura a los servicios web SIN y registrar la respuesta.
- HU-TRIB-03: Contingencia SFE.
  - El sistema debe permitir emitir facturas offline con CUF precalculado.
  - Las facturas en contingencia deben reenviarse automáticamente al recuperar conexión.
  - El sistema debe mantener un log de todas las transacciones en contingencia.
- HU-TRIB-04: Formulario RUDE.
  - El sistema debe capturar todos los campos del formulario RUDE oficial RM 0031/2025 (secciones I-V).
  - El sistema debe validar el CI boliviano con dígito verificador.
  - El sistema debe permitir exportar el formulario RUDE en formato oficial MinEdu (PDF/Excel).

### 8.4. Sprint Goals

- **Sprint 1 — Fundación y Seguridad.** Construir la base técnica del sistema, implementar autenticación, roles, permisos, catálogos académicos básicos y configuración inicial.
- **Sprint 2 — Personas y Configuración Académica.** Implementar la gestión de estudiantes, padres, docentes, materias, períodos, asignaciones y estructura relacionada con RUDE.
- **Sprint 3 — Inscripciones, Matrículas y Pagos.** Implementar el proceso administrativo de inscripción, matrícula, mensualidades, pagos y documentación.
- **Sprint 4 — Núcleo Académico.** Implementar evaluaciones, calificaciones, promedios, asistencia y horarios.
- **Sprint 5 — Comunicación, Reportes, Auditoría y Pulido.** Implementar comunicados, reportes, auditoría, mejoras de interfaz y diseño responsivo.
- **Sprint 6 — Facturación Electrónica.** Implementar las funcionalidades de facturación electrónica y mecanismos de contingencia contemplados en el alcance.

## 9. Ceremonias y seguimiento

### 9.1. Sprint Planning

Durante la Sprint Planning se realizan las siguientes actividades:

1. Revisar el Product Backlog.
2. Analizar prioridades.
3. Seleccionar historias.
4. Descomponer historias en tareas técnicas.
5. Definir el Sprint Goal.
6. Establecer criterios de aceptación.
7. Definir la Definición de Hecho.
8. Establecer el compromiso del equipo.

**Ejemplo de Sprint Planning.**

| Campo | Detalle |
|---|---|
| Sprint | Sprint 1 — Fundación y Seguridad |
| Fecha de inicio | 25 de agosto de 2026 |
| Fecha de entrega | Según cronograma oficial |
| Sprint Goal | Base técnica, autenticación, roles y catálogos |
| Historias | HU-01, HU-02, HU-03, HU-04, HU-05, HU-11, HU-12, HU-13, HU-TRIB-01 |
| Puntos | 33 |
| DoD | Funcionalidades terminadas, probadas y validadas |

### 9.2. Daily Scrum

La Daily Scrum es una reunión breve destinada al seguimiento del avance.

Cada miembro responde:

1. ¿Qué hice ayer?
2. ¿Qué haré hoy?
3. ¿Qué impedimentos tengo?

| Perfil | ¿Qué hice ayer? | ¿Qué haré hoy? | Impedimentos |
|---|---|---|---|
| Backend | Implementación de autenticación | Middleware de permisos | — |
| Frontend | Pantalla de login | Dashboard | Diseño responsivo |
| Full Stack | CRUD de niveles | Configuración académica | Datos pendientes |

### 9.3. Sprint Review

Durante la Sprint Review se presenta al Product Owner el trabajo terminado.

| Sprint | Entregables | Validación |
|---|---|---|
| 1 | Autenticación, usuarios, roles y catálogos | Validado |
| 2 | Personas, materias, docentes y RUDE | Validado |
| 3 | Inscripciones, matrículas y pagos | Validado |
| 4 | Evaluaciones, notas, asistencia y horarios | Validado |
| 5 | Comunicados, reportes y auditoría | Validado |
| 6 | Facturación y contingencia | Validado |
| 7 | Épica 12: Acceso Google, navegación | Validado |
| 8 | Épica 13-14: Navegación y panel docente | Validado |
| 9 | Épica 14: Evaluaciones, lista y reportes | Validado |
| 10 | Épica 15: RUDE oficial Bolivia | Validado |
| 11 | Épica 16: Evaluación por trimestres y criterios (HU-47 a HU-50) | Validado |
| 12 | Épica 17: Cumplimiento normativo (HU-51 a HU-56) | Validado |
| 13 | Épica 18: Correcciones de lógica y reportes de evaluación (HU-57) | Validado |

### 9.4. Sprint Retrospective

| Aspecto | Observación |
|---|---|
| ¿Qué funcionó bien? | Avance rápido en autenticación, catálogos y validaciones. |
| ¿Qué se debe mejorar? | Definir anticipadamente reglas complejas y dependencias externas. |
| ¿Qué acciones implementar? | Refinar criterios de aceptación y realizar pruebas técnicas antes de desarrollar funcionalidades críticas. |

---

# FASE V: PLANIFICACIÓN Y CONTROL

## 10. Planificación temporal

### 10.1. Cronograma

El proyecto se planifica mediante seis sprints de aproximadamente 1 día cada uno.

| Sprint | Periodo | Enfoque |
|---|---|---|
| Sprint 1 | 25/26 agosto | Fundación y Seguridad |
| Sprint 2 | 26/27 agosto | Personas y Configuración |
| Sprint 3 | 27/28 agosto | Inscripciones y Matrículas |
| Sprint 4 | 28/29 agosto | Núcleo Académico |
| Sprint 5 | 29/30 agosto | Comunicación y Reportes |
| Sprint 6 | 01 septiembre | Facturación |

**Dependencias principales.**

- Sprint 1 proporciona la base técnica para los demás sprints.
- Sprint 2 depende de la autenticación y estructura académica.
- Sprint 3 depende de estudiantes y estructura académica.
- Sprint 4 depende de cursos, matrículas y docentes.
- Sprint 5 depende de la existencia de datos académicos y administrativos.
- Sprint 6 depende principalmente de la información generada por matrículas y pagos.

## 11. Gestión de riesgos y contingencias

### 11.1. Matriz de riesgos

| # | Riesgo | Probabilidad | Impacto | Plan A | Plan B |
|---|---|---|---|---|---|
| 1 | Problemas con integración SFE | Alta | Crítico | Iniciar pruebas tempranas | Escalar y ampliar pruebas |
| 2 | Retraso del código SIE | Media | Alto | Gestionar anticipadamente | Utilizar datos temporales |
| 3 | Errores en generación de códigos tributarios | Media | Crítico | Realizar pruebas | Revisar implementación |
| 4 | Leyendas tributarias incorrectas | Baja | Alto | Configuración centralizada | Revisión administrativa |
| 5 | Problemas tributarios | Media | Alto | Validación con responsable contable | Ajustes administrativos |
| 6 | Retenciones no aplicadas | Media | Medio | Validaciones en sistema | Revisión manual |
| 7 | Complejidad de promedios | Media | Alto | Definir reglas anticipadamente | Refinar lógica |
| 8 | Bajo rendimiento en reportes | Media | Medio | Optimizar consultas e índices | Optimización adicional |
| 9 | Cambios en reglas de horarios | Alta | Medio | Validaciones configurables | Ajustar reglas |
| 10 | Baja adopción de usuarios | Media | Alto | Interfaz sencilla y capacitación | Soporte adicional |
| 11 | Cambios normativos | Baja | Alto | Revisar requisitos | Adaptar modelo |
| 12 | Problemas de conectividad | Media | Alto | Mecanismos de contingencia | Cola y reintentos |

La gestión de riesgos permitirá identificar anticipadamente situaciones que puedan afectar el desarrollo y establecer medidas de respuesta.

---

# FASE VI: ANEXOS Y CIERRE

## 12. Anexos

### 12.1. Diccionario de datos

**Tabla users.**

| Campo | Tipo | Descripción | Restricción |
|---|---|---|---|
| id | BIGINT UNSIGNED | Identificador | PK, AUTO_INCREMENT |
| nombre | VARCHAR(100) | Nombre | NOT NULL |
| apellido | VARCHAR(100) | Apellido | NOT NULL |
| email | VARCHAR(150) | Correo | UNIQUE |
| password | VARCHAR(255) | Contraseña cifrada mediante hash | NOT NULL |
| role_id | BIGINT UNSIGNED | Rol | FK |
| estado | ENUM | Estado del usuario | DEFAULT ACTIVO |
| intentos_fallidos | TINYINT | Intentos fallidos | DEFAULT 0 |

**Tabla students.**

| Campo | Tipo | Descripción | Restricción |
|---|---|---|---|
| id | BIGINT UNSIGNED | Identificador | PK |
| codigo | VARCHAR(20) | Código del estudiante | UNIQUE |
| nombres | VARCHAR(150) | Nombres | NOT NULL |
| apellidos | VARCHAR(150) | Apellidos | NOT NULL |
| fecha_nacimiento | DATE | Fecha de nacimiento | NOT NULL |
| sexo | ENUM | Sexo | NOT NULL |
| numero_documento | VARCHAR(20) | Documento | UNIQUE con complemento |
| complemento | VARCHAR(5) | Complemento | DEFAULT |
| email | VARCHAR(150) | Correo | UNIQUE |
| estado | ENUM | Estado | DEFAULT ACTIVO |

**Tabla monthly_payments.**

| Campo | Tipo | Descripción | Restricción |
|---|---|---|---|
| id | BIGINT UNSIGNED | Identificador | PK |
| registration_id | BIGINT UNSIGNED | Matrícula | FK |
| electronic_invoice_id | BIGINT UNSIGNED | Factura | FK, nullable |
| mes | TINYINT | Mes | NOT NULL |
| anio | SMALLINT | Año | NOT NULL |
| monto_esperado | DECIMAL(10,2) | Monto esperado | NOT NULL |
| monto_pagado | DECIMAL(10,2) | Monto pagado | DEFAULT 0 |
| estado | ENUM | Estado de pago | DEFAULT PENDIENTE |

**Tabla electronic_invoices.**

| Campo | Tipo | Descripción | Restricción |
|---|---|---|---|
| id | BIGINT UNSIGNED | Identificador | PK |
| nro_factura | INT | Número de factura | NOT NULL |
| cuf | VARCHAR(100) | Código de factura | UNIQUE |
| cufd | VARCHAR(100) | Código diario | NULL |
| tipo_emision | ENUM | Tipo de emisión | DEFAULT ONLINE |
| nit_emisor | VARCHAR(20) | NIT emisor | NOT NULL |
| nit_receptor | VARCHAR(20) | NIT receptor | NULL |
| total_factura | DECIMAL(10,2) | Total | NOT NULL |
| estado | ENUM | Estado de factura | DEFAULT ENVIADA |

**Tabla student_rude_records.**

> Formulario RUDE oficial 2025 (RM 0031/2025). Solo se listan los campos clave; el esquema
> completo (secciones I, III, IV y campo 2.9) está documentado en `Docs/diagrama-fisico.md`.
> El SIE de la Unidad Educativa es `81730184` (constante `StudentRudeRecord::SIE_UE`), usado
> como valor por defecto de `codigo_sie_ue`. El acceso al RUDE está dentro del detalle del
> estudiante (botón "RUDE"), ya no en el menú lateral.

| Campo | Tipo | Descripción | Restricción |
|---|---|---|---|
| id | BIGINT UNSIGNED | Identificador | PK |
| student_id | BIGINT UNSIGNED | Estudiante | FK, UNIQUE |
| codigo_rude | VARCHAR(20) | Código RUDE | UNIQUE |
| codigo_sie_ue | VARCHAR(20) | Código SIE UE | NULL |
| fecha_registro | DATE | Fecha de llenado (Sec. I) | NULL |
| ue_nombres | VARCHAR(150) | Nombre(s) UE (Sec. I) | NULL |
| ue_departamento | VARCHAR(100) | Departamento UE (Sec. I) | NULL |
| dir_departamento | VARCHAR(100) | Departamento dir. actual (Sec. III) | NULL |
| dir_calle | VARCHAR(150) | Calle dir. actual (Sec. III) | NULL |
| idioma_aprendio | VARCHAR(100) | Idioma inicial (Sec. IV.1) | NULL |
| idiomas_uso_frecuente | JSON | Idiomas de uso (Sec. IV.1) | NULL |
| salud_seguro | VARCHAR(100) | Seguro de salud (Sec. IV.2) | NULL |
| serv_agua_red | BOOLEAN | Agua por red (Sec. IV.3) | NULL |
| internet_lugares | JSON | Acceso internet (Sec. IV.4) | NULL |
| laboral_actividad | VARCHAR(150) | Actividad laboral (Sec. IV.5) | NULL |
| transporte_medio | VARCHAR(100) | Medio transporte (Sec. IV.6) | NULL |
| abandono_razones | JSON | Razones abandono (Sec. IV.7) | NULL |
| discapacidad_tipos | JSON | Tipos discapacidad (Sec. IV.8) | NULL |
| convive_con | JSON | Con quién vive (Sec. V) | NULL |
| turno | ENUM | Turno | NULL |
| estado | ENUM | Estado RUDE | DEFAULT PENDIENTE |

### 12.2. Mockups o prototipos

Los prototipos permiten representar visualmente las principales interfaces del sistema antes o durante el proceso de desarrollo.

- Pantalla de inicio de sesión — Figura 6. Prototipo de inicio de sesión.
- Dashboard — Figura 7. Prototipo del panel principal.
- Gestión de estudiantes — Figura 8. Prototipo de gestión de estudiantes.
- Matrículas — Figura 9. Prototipo de matrículas.
- Calificaciones — Figura 10. Prototipo de calificaciones.
- Asistencia — Figura 11. Prototipo de asistencia.
- Reportes — Figura 12. Prototipo de reportes.
- Horario docente — Figura 13. Prototipo de horario docente.
- Horario estudiante — Figura 14. Prototipo de horario estudiante.

### 12.3. Acta de aceptación del cliente

| Campo | Detalle |
|---|---|
| Proyecto | Sistema de Gestión Escolar Personalizado — Unidad Educativa San Bernardo |
| Institución | Unidad Educativa San Bernardo — Tarija, Bolivia |
| Entidad legal | Sociedad Educativa San Bernardo de Tarija S.A. |
| NIT | 408460020 |
| Fecha | 01 / 09 / 2026 |
| Alcance | Aplicación web de gestión escolar |

**Declaración de aceptación.** Los abajo firmantes, en representación de la Unidad Educativa San Bernardo, declaran que el Sistema de Gestión Escolar Personalizado ha sido revisado y validado conforme a los requerimientos establecidos para el proyecto.

Asimismo, se deja constancia de que el sistema contempla las funcionalidades definidas en el alcance del proyecto y que los entregables correspondientes han sido revisados por las partes involucradas.

## 13. Tecnologías utilizadas

Para el desarrollo del Sistema de Gestión Escolar Personalizado se utilizó Laravel como framework principal de desarrollo.

Laravel permitió estructurar el sistema aplicando el patrón arquitectónico MVC (Modelo-Vista-Controlador), separando las responsabilidades relacionadas con los datos, la lógica de negocio y la presentación.

**Laravel.** Laravel fue utilizado como framework principal para:

- Desarrollar la aplicación web.
- Gestionar rutas.
- Crear modelos.
- Implementar controladores.
- Gestionar migraciones.
- Implementar validaciones.
- Organizar la lógica de negocio.
- Gestionar autenticación y sesiones.
- Conectar la aplicación con la base de datos.

**Arquitectura MVC.** La arquitectura MVC permitió dividir el sistema en tres componentes principales:

- Modelo: encargado de representar y gestionar los datos y las operaciones relacionadas con la base de datos.
- Vista: encargada de presentar la información al usuario mediante las interfaces del sistema.
- Controlador: encargado de recibir las solicitudes del usuario, procesar la lógica correspondiente y coordinar la interacción entre modelos y vistas.

**MySQL.** Se utilizó MySQL como sistema gestor de base de datos.

La base de datos permite almacenar y gestionar información relacionada con:

- Usuarios.
- Estudiantes.
- Padres/tutores.
- Docentes.
- Cursos.
- Materias.
- Inscripciones.
- Matrículas.
- Pagos.
- Calificaciones.
- Asistencia.
- Horarios.
- Comunicados.
- Reportes.
- Auditoría.

**Migraciones de Laravel.** Las migraciones de Laravel fueron utilizadas para crear y modificar la estructura de las tablas de la base de datos de manera organizada y controlada.

Tailwind CSS fue utilizado para facilitar la creación de interfaces modernas, organizadas y responsivas mediante clases utilitarias.

Bootstrap fue utilizado como apoyo para la construcción de componentes de interfaz y adaptación responsiva.

**Git.** Se utilizó Git como sistema de control de versiones.

Git permitió:

- Registrar cambios realizados.
- Mantener un historial del proyecto.
- Crear ramas de trabajo.
- Integrar cambios.
- Recuperar versiones anteriores.
- Facilitar el trabajo colaborativo.
- Gestionar el código fuente del proyecto.

## 14. Conclusiones

El desarrollo del Sistema de Gestión Escolar Personalizado para la Unidad Educativa San Bernardo representa una solución orientada a mejorar la organización y administración de los principales procesos académicos y administrativos de la institución.

La implementación de una aplicación web centralizada y una base de datos relacional permitirá disponer de información organizada y accesible, reduciendo la dependencia de registros manuales y de información distribuida en diferentes medios.

El sistema permitirá gestionar de manera integrada información de estudiantes, docentes, padres o tutores, cursos, matrículas, pagos, evaluaciones, calificaciones, asistencia, horarios, comunicados y reportes.

Asimismo, la utilización de roles y permisos permitirá controlar el acceso a la información de acuerdo con las responsabilidades de cada usuario, contribuyendo a mejorar la seguridad y privacidad de los datos.

El uso de Laravel, MySQL, MVC, Tailwind CSS, Bootstrap y Git proporciona una base tecnológica adecuada para el desarrollo y mantenimiento del sistema, permitiendo organizar el código, gestionar la base de datos, construir interfaces responsivas y mantener un historial de cambios.

La aplicación de Scrum permite organizar el proyecto mediante iteraciones, establecer objetivos por sprint, realizar seguimiento constante y aplicar mejoras a través de las retrospectivas.

En conclusión, el proyecto constituye una alternativa tecnológica orientada a centralizar, organizar y optimizar la gestión escolar, proporcionando una plataforma que puede facilitar las actividades administrativas y académicas de la Unidad Educativa San Bernardo y servir como base para futuras ampliaciones.

## 15. Repositorios del proyecto

**GitHub.** Repositorio del proyecto: <https://github.com/saaay13/sanber>

**GitHub Projects.** Tablero de gestión del proyecto: <https://github.com/users/saaay13/projects/15>

## 16. Cierre del proyecto

El presente informe reúne los principales elementos relacionados con el análisis, especificación, diseño, planificación y desarrollo del Sistema de Gestión Escolar Personalizado — Unidad Educativa San Bernardo.

La documentación incluye los fundamentos del proyecto, alcance, objetivos, requerimientos, actores, casos de uso, modelos de datos, arquitectura de base de datos, metodología Scrum, Product Backlog, Sprint Backlog, planificación temporal, riesgos, diccionario de datos, prototipos, tecnologías utilizadas y conclusiones.

De esta manera, el documento proporciona una visión integral del proyecto y establece una base documental para respaldar el desarrollo y evaluación del sistema.

---

## Documentos relacionados

- [Diagrama físico de la base de datos](./diagrama-fisico.md)
- [Nuevas historias de usuario HU-36 a HU-57 (segunda iteración)](./historias-usuario-nuevas.md)
