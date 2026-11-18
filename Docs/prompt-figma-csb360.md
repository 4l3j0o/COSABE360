# Prompt para Figma AI / Figma Make — CSB 360 Móvil (Estudiante)

> Uso: pegar el **Prompt maestro** en Figma Make (o en Figma AI → First Draft). Luego, para afinar, pegar las **addendas por pantalla** una por una.

---

## PROMPT MAESTRO (copiar y pegar)

Diseña la app móvil **"CSB 360"** para **estudiantes** de la Unidad Educativa San Bernardo (Tarija, Bolivia). Es el complemento móvil de un sistema escolar web existente; la app no reemplaza al sistema, lo acompaña.

**Entregable:** un archivo de Figma con 14 pantallas móviles de alta fidelidad, en español, para iOS y Android, más una página de Design System y una de Variables/Estilos.

**Público:** estudiantes de 11 a 18 años (1º a 6º de secundaria), usan Android de gama media, datos móviles limitados, consultan la app en el colegio y en casa. Debe sentirse **institucional, moderno y confiable**, no infantil ni "gamificado".

**Formato:** frames de 390 × 844 px (iPhone 14 / Android equivalente), márgenes laterales de 20 px, safe areas superior e inferior, grid de 8 px, esquinas suaves, Auto Layout en todo, nombre de frame `NN Nombre` (ej. `04 Inicio`).

### Identidad visual

- **Marca:** escudo/logo del colegio en verde. Nombre corto "CSB 360", tagline "Colegio San Bernardo".
- **Color primario (verde institucional):** 50 `#E6F4E6`, 100 `#C8E8C9`, 200 `#A5D7A8`, 300 `#7CC483`, 400 `#4FAD58`, 500 `#2E9E3A`, 600 `#1A8C1A` (principal), 700 `#0E710E`, 800 `#0A5A0A`, 900 `#084508`.
- **Neutros (slate):** 50 `#F8FAFC`, 100 `#F1F5F9`, 200 `#E2E8F0`, 400 `#94A3B8`, 600 `#475569`, 800 `#1E293B`, 900 `#0F172A`.
- **Semánticos:** éxito `#16A34A`, advertencia `#D97706`, peligro `#DC2626`, info `#2563EB`.
- **Superficies:** fondo `#F8FAFC`, tarjetas `#FFFFFF`, borde `#E2E8F0`, overlay `rgba(15,23,42,0.45)`.
- **Tipografía:** **Outfit** (Google Fonts). Display 28/34 Bold, Título 22/28 SemiBold, Sección 18/24 SemiBold, Cuerpo 15/22 Regular, Secundario 13/18, Micro 11/14 Medium.
- **Radios:** inputs y botones 12, tarjetas 16, bottom sheets y modales 24, chips 999.
- **Sombras:** suave `0 4 16 rgba(15,23,42,0.08)`; elevada `0 12 32 rgba(15,23,42,0.14)`.
- **Iconografía:** Lucide, trazo 1.75, 24 px. Sin emojis como iconos.
- **Espaciado:** 4 / 8 / 12 / 16 / 20 / 24 / 32.
- **Tono de imágenes:** fotos reales de estudiantes con uniforme, luz natural, diversidad boliviana; nada de stock genérico extranjero.

### Componentes (crear como componentes con variantes)

AppBar (título + back + acción), TabBar inferior de 5 ítems, Card, ListItem, Button (primary / secondary / ghost / danger, con estados default-pressed-disabled-loading), Input (default-focus-error-disabled, con label y helper), Selector de trimestre (segmented control), Chip de estado (presente / ausente / retraso / licencia / retiro justificado), Badge de no leído, Avatar, ProgressRing (promedio), StatCard, TimelineItem (horario), DocumentChecklistItem (pendiente / entregado / rechazado), Stepper horizontal, EmptyState, Skeleton, Toast, BottomSheet, Modal de confirmación.

### Navegación

TabBar inferior fija con 5 ítems: **Inicio · Notas · Asistencia · Comunicados · Perfil**. El resto son pantallas de stack con back. La tab activa usa el verde 600; las inactivas, slate 400.

### Pantallas (14)

**01 Splash / Carga.** Fondo verde 600 con degradado sutil, escudo del colegio centrado, "CSB 360" en blanco, loader discreto, texto "Verificando sesión…", versión al pie en 11 px al 60 %.

**02 Inicio de sesión.** Escudo, título "Bienvenido", subtítulo "Ingresa con tu cuenta institucional". Botón principal blanco con logo de Google: "Continuar con Google". Nota: "Solo cuentas @cosabe.edu.bo". Enlace secundario "¿Problemas para ingresar?". Sección colapsada al pie: "Acceso de prueba" con email y contraseña (para demo). Estado de error: banner rojo "Tu cuenta no está registrada. Comunícate con Secretaría." No incluir registro libre: los estudiantes los crea el colegio.

**03 Primer ingreso / Completar datos.** Saludo con nombre, avatar editable, datos de solo lectura (nombre, curso, código), campos editables: teléfono, dirección, foto. Checkbox de consentimiento de datos (ARCO). Botón "Guardar y continuar".

**04 Inicio (Home).** Header con saludo "Hola, Valeria", chip del curso "4º Sec. · Paralelo B" y campana con badge. Tarjeta hero verde: promedio general en ProgressRing (82) y texto "Buen rendimiento". Fila de 3 StatCards: Asistencia 94 %, Materias 9, Trimestre "2do". Bloque "Ahora" con la clase actual (materia, docente, aula, hora) y botón "Ver horario". Accesos rápidos en grid 4×1: Notas, Asistencia, Pagos, Inscripción. Sección "Comunicados" con 2 tarjetas. Si hay inscripción pendiente, banner ámbar "Completa tu inscripción — 2 de 5 documentos".

**05 Mis materias.** Búsqueda, segmented control "1T / 2T / 3T", lista de tarjetas: inicial de la materia en círculo verde, nombre, docente, promedio a la derecha con color según escala (≥85 verde, 69-84 azul, 51-68 ámbar, ≤50 rojo) y chevron.

**06 Detalle de materia.** Header con nombre de materia y docente. Tabs: Resumen · Notas · Asistencia. Resumen: promedio grande, escala ED/DA/DO/DP, asistencia de la materia, horario. Notas: lista de actividades con peso y nota sobre 100, desglose por criterios SE(10) / SA(45) / HA(40) / AU(5). Incluir un estado vacío "Aún no hay notas publicadas en este trimestre".

**07 Notas / Boletín.** Selector de trimestre, promedio general destacado, lista de todas las materias con nota y chip de escala, leyenda de escala (ED 0-50, DA 51-68, DO 69-84, DP 85-100), botón "Descargar boletín PDF" y nota "Documento oficial SIE".

**08 Asistencia.** Tarjeta resumen con 5 contadores (Presente, Ausente, Retraso, Licencia, Retiro justificado) y porcentaje. Calendario mensual con puntos de color por día. Lista de registros: fecha, materia, estado como chip, justificación si existe.

**09 Horario.** Segmented control Lunes a Viernes, timeline vertical de bloques con hora de inicio/fin, materia, docente y aula; el bloque de la hora actual resaltado en verde con etiqueta "Ahora".

**10 Comunicados.** Tabs "Todos · Sin leer · Destacados", tarjetas con imagen de portada opcional, título, fecha, chip "NUEVO", indicador de destacado. Empty state "No tienes comunicados".

**11 Detalle de comunicado.** Imagen de portada, título, autor y fecha, cuerpo con párrafos, adjuntos descargables, botón "Marcar como leído" (o estado ya leído).

**12 Inscripción en línea (funcionalidad estrella).** Stepper de 5 pasos: 1 Datos, 2 Documentos, 3 Domicilio, 4 Contrato, 5 Confirmar. Paso 2: checklist de 5 documentos (CI anverso, CI reverso, certificado de nacimiento, CI de padres, comprobante de pago) con estado pendiente/entregado, botón "Tomar foto" y "Elegir de galería", barra de progreso "3 de 5". Paso 4: vista previa del contrato con scroll y checkbox "He leído y acepto el contrato". Paso 5: resumen y estado final "Preinscrito — pago en verificación" con número de trámite.

**13 Captura de documento (cámara).** Vista de cámara a pantalla completa con marco guía y texto "Ubica el documento dentro del marco", botón de disparo grande, flash, cambiar cámara. Incluir variantes: permiso denegado (con botón "Abrir ajustes"), previsualización con "Repetir / Usar foto", y estado de subida con progreso.

**14 Pagos.** Resumen "Estado de cuenta", tarjetas de mensualidad por mes: monto en Bs, estado pagado/pendiente/vencido, fecha de pago, botón "Subir comprobante" (abre cámara). Nota "El pago se confirma en Secretaría".

**15 Perfil.** Avatar, nombre, curso, código de estudiante. Secciones: Datos personales, Notificaciones (toggles de comunicados, notas y asistencia), Seguridad, Ayuda, Cerrar sesión en rojo. Versión de la app al pie.

**Estados globales (página aparte):** skeleton de carga, sin conexión con botón "Reintentar", error de servidor, vacío, permiso de cámara denegado, permiso de notificaciones.

### Datos de ejemplo (usar exactamente estos en los mockups)

- Estudiante: **Valeria Quispe Mamani**, código **8640**, **4º de Secundaria — Paralelo B**, turno mañana.
- Materias: Matemática, Lengua Castellana, Ciencias Sociales, Ciencias Naturales, Inglés, Educación Física, Artes Plásticas, Informática, Religión.
- Docentes: Prof. Marco Suárez (Matemática), Prof. Ana Rodríguez (Lengua), Prof. Luis Fernández (Ciencias Naturales).
- Notas: 87, 76, 64, 51, 42, 92, 70, 58, 81 (sobre 100).
- Asistencia: 94 %, 2 faltas, 1 retraso, 1 licencia.
- Fechas en formato **dd/mm/aaaa**; moneda **Bs** (ej. Bs 785,50); trimestres **1er, 2do, 3er Trimestre**.

### Reglas

- Todo en español de Bolivia, sin lorem ipsum.
- Contraste AA, áreas táctiles mínimas de 44 px, textos escalables.
- Un solo acento verde por pantalla; el resto blanco y neutros.
- No inventar módulos de docentes, administración ni notas editables: el estudiante **solo consulta** (excepto inscripción, documentos y comprobantes de pago).
- Prototipo navegable: Splash → Login → Inicio → cada tab; Inicio → Materia → Detalle; Inscripción paso a paso; Cámara desde Documentos y desde Pagos.

---

## Addendas por pantalla (para iterar en Figma Make)

**Home:** agrega una variante con inscripción pendiente y otra con comunicado destacado; muestra el bloque "Ahora" con la clase en curso y un contador regresivo discreto.

**Inscripción:** agrega la variante del paso 2 con un documento rechazado (borde rojo y texto "La foto está borrosa, vuelve a intentarlo") y la variante de éxito con descarga de contrato en PDF.

**Asistencia:** agrega la vista mensual con un día seleccionado y el bottom sheet de detalle de ese día.

**Notas:** agrega la variante de trimestre sin publicar, con candado y texto "Las notas del 3er trimestre se publican al cierre".

**Comunicados:** agrega la variante de comunicado con imagen destacada y la de bandeja vacía.
