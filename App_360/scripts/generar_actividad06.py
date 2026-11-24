# -*- coding: utf-8 -*-
"""Genera el informe de la Jornada 3 (Actividad 06) del proyecto final.

Tema: Base de datos y persistencia (MySQL local con XAMPP / MariaDB y API REST).
Formato: Times New Roman 12, interlineado 1.5, texto justificado, tablas con bordes.
"""

import os
import zipfile

import docx
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.shared import Cm, Pt, RGBColor

import generar_documentos as g
from generar_actividad04 import _font, _rich, codigo, h1, h2, parrafo, tabla, vineta

BASE_DIR = os.path.abspath(os.path.join(os.path.dirname(__file__), ".."))
DOCS = os.path.join(BASE_DIR, "Docs")
PLANTILLA = os.path.join(DOCS, "Caratula_Institucional.docx")
CAPTURAS_DIA3 = os.path.join(os.path.dirname(__file__), "capturas-dia3")
CAPTURAS_DIA2 = os.path.join(os.path.dirname(__file__), "capturas-dia2")

INTEGRANTES = [
    "Snaider Brayan Quispe Janco",
    "Jonathan Smith Soraire Rojas",
    "Joel David Cerrogrande Ortega",
    "Jose Alejandro Benitez Torrez",
]


def captura(doc, archivo, numero, nombre, ancho=5.6):
    """Inserta una captura de pantalla centrada con su pie de figura."""
    ruta = os.path.join(CAPTURAS_DIA3, archivo)
    if not os.path.exists(ruta):
        ruta = os.path.join(CAPTURAS_DIA2, archivo)
    if not os.path.exists(ruta):
        parrafo(doc, f"[Falta la captura {archivo}]")
        return
    p = doc.add_paragraph()
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p.paragraph_format.space_before = Pt(8)
    p.paragraph_format.space_after = Pt(2)
    p.add_run().add_picture(ruta, width=Cm(ancho))
    pie = doc.add_paragraph()
    pie.alignment = WD_ALIGN_PARAGRAPH.CENTER
    pie.paragraph_format.space_after = Pt(8)
    _font(pie.add_run(f"Figura {numero}. {nombre}"), size=10)


def avance_jornada(doc):
    h1(doc, "1. Avance de la Jornada 3 (Viernes 20 · Base de Datos)", salto=True)
    parrafo(
        doc,
        "El objetivo central de la Jornada 3 consistió en lograr que los datos de la aplicación se guarden "
        "y persistan de forma confiable, sustituyendo definitivamente los arreglos en memoria estáticos (mock) "
        "por operaciones de lectura y escritura directas contra una base de datos relacional. Siguiendo el "
        "requerimiento específico del proyecto final CSB 360 y la infraestructura local disponible en el equipo de "
        "desarrollo, la persistencia se implementó sobre el motor **MySQL / MariaDB** administrado a través de "
        "**XAMPP**, integrando sentencias SQL preparadas con parámetros `?` para garantizar la máxima seguridad "
        "frente a inyecciones SQL.",
    )
    tabla(
        doc,
        ["Actividad planificada", "Detalle técnico implementado", "Estado"],
        [
            [
                "1. Base de datos y tablas",
                "Creación de la base cosabe360_movil con las tablas usuarios y pendientes en MySQL (XAMPP). "
                "Se adaptó la consigna de SQLiteProvider mediante API REST Node/Express + mysql2 con sentencias preparadas.",
                "Cumplido",
            ],
            [
                "2. Módulo datos.js con parámetros ?",
                "Implementación de datos.js con una función por operación CRUD (obtener, agregar, modificar, "
                "eliminar, alternar) utilizando exclusivamente parámetros posicionales ?.",
                "Cumplido",
            ],
            [
                "3. Lectura desde la base de datos",
                "La pantalla principal de pendientes lee del backend MySQL mediante SELECT; se eliminó el arreglo "
                "en memoria de datos de ejemplo.",
                "Cumplido",
            ],
            [
                "4. Formulario con INSERT, UPDATE y DELETE",
                "Formulario conectado al INSERT para nuevas tareas, soporte de edición (UPDATE ... WHERE) y "
                "modal interactivo de confirmación antes de eliminar (DELETE ... WHERE).",
                "Cumplido",
            ],
            [
                "5. Recarga automática tras cada mutación",
                "Llamada sistemática a cargar() (SELECT) después de cada operación de escritura para refrescar la "
                "interfaz al instante sin desfases de sincronización.",
                "Cumplido",
            ],
            [
                "6. Migración de usuarios y Login seguro",
                "Tabla usuarios creada en MySQL; contraseñas protegidas con hash bcrypt y autenticación validada "
                "mediante consulta parametrizada SELECT ... WHERE email = ?.",
                "Cumplido",
            ],
            [
                "7. Prueba rigurosa de persistencia",
                "Cierre completo del proceso de Expo Go / cliente móvil y reapertura del sistema comprobando que "
                "todos los registros agregados, editados y eliminados persisten en MySQL.",
                "Cumplido",
            ],
        ],
        anchos=[4.0, 9.5, 3.0],
    )


def arquitectura_persistencia(doc):
    h1(doc, "2. Arquitectura de Persistencia: MySQL local en XAMPP")
    parrafo(
        doc,
        "En el diseño académico general de la materia se contempla el uso de `<SQLiteProvider databaseName=\"...\" "
        "onInit={iniciarBase}>` para bases de datos SQLite embebidas en el cliente móvil. Sin embargo, en el proyecto "
        "**CSB 360** se estableció como requerimiento utilizar la base de datos relacional **MySQL local** ejecutándose "
        "en **XAMPP / MariaDB** (puerto 3306), de modo que la información del estudiante pueda centralizarse y "
        "coexistir con la plataforma institucional.",
    )
    parrafo(
        doc,
        "Debido a que React Native y Expo Go se ejecutan dentro de una máquina virtual JavaScript móvil que no dispone "
        "de sockets TCP directos para negociar el protocolo nativo de MySQL hacia el puerto 3306, se diseñó una "
        "arquitectura en capas altamente desacoplada y profesional:",
    )
    vineta(
        doc,
        "**Capa de Almacenamiento Relacional (XAMPP / MySQL):** Base de datos `cosabe360_movil` con motor InnoDB, "
        "soporte UTF-8 completo (utf8mb4) y claves foráneas con eliminación en cascada.",
    )
    vineta(
        doc,
        "**Capa de Servicio de Base de Datos (Node.js + Express en `App_360/api`):** Servidor HTTP local con pool "
        "de conexiones optimizado (`mysql2/promise`). Este servicio expone endpoints REST (`/api/pendientes`, "
        "`/api/auth/login`) y ejecuta las consultas preparadas en su archivo central `api/datos.js`.",
    )
    vineta(
        doc,
        "**Capa de Acceso a Datos Móvil (`App_360/mobile/src/data/datos.js`):** Cliente de datos que consume la API "
        "local mediante funciones tipadas y asíncronas con la misma firma conceptual (`obtener`, `agregar`, "
        "`modificar`, `eliminar`), manteniendo el código de las pantallas completamente idéntico al estándar de SQLite.",
    )
    vineta(
        doc,
        "**Capa de Estado Global (`store/pendientes.ts` con Zustand):** Administra la lista reactiva de pendientes en "
        "la aplicación móvil, garantizando que cada mutación (INSERT, UPDATE, DELETE) desencadene un SELECT de recarga.",
    )

    h2(doc, "Comparativa: SQLiteProvider vs. API MySQL local")
    tabla(
        doc,
        ["Criterio", "SQLiteProvider (Consigna base)", "MySQL en XAMPP (Implementación CSB 360)"],
        [
            ["Motor", "SQLite embebido en el almacenamiento del celular", "MySQL / MariaDB local en servidor XAMPP"],
            ["Ubicación de datos", "Archivo .db privado en el almacenamiento interno", "Base cosabe360_movil en disco (XAMPP/data)"],
            ["Sintaxis SQL", "Sentencias SQL estándar con comodines ?", "Sentencias SQL estándar con comodines ?"],
            ["Conexión móvil", "Directa mediante driver nativo de Expo SQLite", "A través de API local Node/Express (puerto 4000)"],
            ["Acceso multiusuario", "Monousuario local (por dispositivo)", "Multiusuario con clave foránea usuario_id"],
            ["Visualización externa", "Requiere exportar archivo o plugin SQLite", "Administrable directamente en phpMyAdmin"],
        ],
        anchos=[3.5, 6.5, 6.5],
    )


def esquema_sql(doc):
    h1(doc, "3. Diseño Relacional y Esquema DDL", salto=True)
    parrafo(
        doc,
        "El esquema relacional fue diseñado el miércoles de la semana de desarrollo e implementado en el archivo "
        "`App_360/api/esquema.sql`. Consta de dos tablas vinculadas mediante integridad referencial: la tabla "
        "`usuarios`, que sustituye los datos hardcodeados del login, y la tabla `pendientes`, que representa la "
        "entidad principal del sistema.",
    )
    codigo(
        doc,
        "-- =====================================================================\n"
        "-- Base de datos: cosabe360_movil (MySQL / MariaDB en XAMPP)\n"
        "-- =====================================================================\n"
        "CREATE DATABASE IF NOT EXISTS cosabe360_movil\n"
        "  CHARACTER SET utf8mb4\n"
        "  COLLATE utf8mb4_unicode_ci;\n\n"
        "USE cosabe360_movil;\n\n"
        "-- Tabla de usuarios (credenciales protegidas con hash bcrypt)\n"
        "CREATE TABLE IF NOT EXISTS usuarios (\n"
        "  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,\n"
        "  nombre        VARCHAR(120) NOT NULL,\n"
        "  email         VARCHAR(150) NOT NULL,\n"
        "  password_hash VARCHAR(255) NOT NULL,\n"
        "  curso         VARCHAR(80)  NOT NULL DEFAULT '',\n"
        "  codigo        VARCHAR(20)  NOT NULL DEFAULT '',\n"
        "  creado_en     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,\n"
        "  PRIMARY KEY (id),\n"
        "  UNIQUE KEY uq_usuarios_email (email)\n"
        ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n"
        "-- Entidad principal: pendientes del estudiante\n"
        "CREATE TABLE IF NOT EXISTS pendientes (\n"
        "  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,\n"
        "  usuario_id     INT UNSIGNED NOT NULL,\n"
        "  titulo         VARCHAR(150) NOT NULL,\n"
        "  materia        VARCHAR(80)  NOT NULL,\n"
        "  fecha          DATE         NOT NULL,\n"
        "  prioridad      ENUM('alta','media','baja') NOT NULL DEFAULT 'media',\n"
        "  hecho          TINYINT(1)   NOT NULL DEFAULT 0,\n"
        "  creado_en      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,\n"
        "  actualizado_en TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n"
        "  PRIMARY KEY (id),\n"
        "  KEY idx_pendientes_usuario (usuario_id, hecho, fecha),\n"
        "  CONSTRAINT fk_pendientes_usuario\n"
        "    FOREIGN KEY (usuario_id) REFERENCES usuarios (id)\n"
        "    ON DELETE CASCADE ON UPDATE CASCADE\n"
        ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
        size=8.5,
    )
    parrafo(
        doc,
        "Aspectos técnicos destacados del esquema:",
    )
    vineta(doc, "**Integridad referencial:** Clave foránea `fk_pendientes_usuario` con `ON DELETE CASCADE`. Si se borra un usuario, sus recordatorios se eliminan automáticamente para evitar registros huérfanos.")
    vineta(doc, "**Indexación optimizada:** Se configuró el índice compuesto `idx_pendientes_usuario (usuario_id, hecho, fecha)` para acelerar las consultas SELECT que filtran por estudiante y ordenan por estado.")
    vineta(doc, "**Tipo de dato DATE:** Las fechas se guardan como `DATE` de MySQL (formato estándar `aaaa-mm-dd`) y se formatean automáticamente a `dd/mm/aaaa` para la vista del estudiante.")


def datos_js_seccion(doc):
    h1(doc, "4. Implementación de datos.js con Parámetros ?", salto=True)
    parrafo(
        doc,
        "La actividad 2 exige explícitamente que todas las sentencias SQL utilicen parámetros posicionales `?` "
        "en lugar de concatenar cadenas. Esta regla constituye la mejor práctica universal de ingeniería de software "
        "en bases de datos por las siguientes razones:",
    )
    vineta(
        doc,
        "**Inmunidad contra Inyección SQL (SQL Injection):** Al utilizar `?`, el motor de base de datos trata el valor "
        "como un dato literal puro. Si un usuario escribe comillas simples, comandos `DROP TABLE` o caracteres especiales "
        "en el título o fecha, estos jamás se ejecutan como código SQL.",
    )
    vineta(
        doc,
        "**Reutilización del Plan de Ejecución (Prepared Statements):** El analizador de MySQL compila el árbol sintáctico "
        "de la consulta una sola vez y lo almacena en su caché de sentencias preparadas, ejecutándolo a gran velocidad "
        "en cada nueva llamada.",
    )
    vineta(
        doc,
        "**Manejo robusto de tipos:** Números, cadenas con acentos y fechas se escapan y convierten con tipado estricto "
        "según el protocolo binario del motor.",
    )

    h2(doc, "Código fuente de api/datos.js (Lógica SQL en servidor)")
    codigo(
        doc,
        "const pool = require('./db');\n\n"
        "// SELECT de todos los pendientes del estudiante\n"
        "async function obtener(usuarioId) {\n"
        "  const [filas] = await pool.execute(\n"
        "    `SELECT id, titulo, materia,\n"
        "            DATE_FORMAT(fecha, '%d/%m/%Y') AS fecha,\n"
        "            prioridad, hecho\n"
        "       FROM pendientes\n"
        "      WHERE usuario_id = ?\n"
        "      ORDER BY hecho ASC, fecha ASC, id DESC`,\n"
        "    [usuarioId]\n"
        "  );\n"
        "  return filas.map(mapear);\n"
        "}\n\n"
        "// SELECT de un pendiente por clave primaria\n"
        "async function obtenerPorId(usuarioId, id) {\n"
        "  const [filas] = await pool.execute(\n"
        "    `SELECT id, titulo, materia,\n"
        "            DATE_FORMAT(fecha, '%d/%m/%Y') AS fecha,\n"
        "            prioridad, hecho\n"
        "       FROM pendientes\n"
        "      WHERE id = ? AND usuario_id = ?`,\n"
        "    [id, usuarioId]\n"
        "  );\n"
        "  return filas.length ? mapear(filas[0]) : null;\n"
        "}\n\n"
        "// INSERT de un nuevo pendiente\n"
        "async function agregar(usuarioId, datos) {\n"
        "  const p = validarPendiente(datos);\n"
        "  const [resultado] = await pool.execute(\n"
        "    `INSERT INTO pendientes (usuario_id, titulo, materia, fecha, prioridad, hecho)\n"
        "     VALUES (?, ?, ?, ?, ?, 0)`,\n"
        "    [usuarioId, p.titulo, p.materia, p.fecha, p.prioridad]\n"
        "  );\n"
        "  return obtenerPorId(usuarioId, resultado.insertId);\n"
        "}\n\n"
        "// UPDATE ... WHERE de los datos del pendiente\n"
        "async function modificar(usuarioId, id, datos) {\n"
        "  const p = validarPendiente(datos);\n"
        "  await pool.execute(\n"
        "    `UPDATE pendientes\n"
        "        SET titulo = ?, materia = ?, fecha = ?, prioridad = ?\n"
        "      WHERE id = ? AND usuario_id = ?`,\n"
        "    [p.titulo, p.materia, p.fecha, p.prioridad, id, usuarioId]\n"
        "  );\n"
        "  return obtenerPorId(usuarioId, id);\n"
        "}\n\n"
        "// DELETE ... WHERE del registro\n"
        "async function eliminar(usuarioId, id) {\n"
        "  const [resultado] = await pool.execute(\n"
        "    'DELETE FROM pendientes WHERE id = ? AND usuario_id = ?',\n"
        "    [id, usuarioId]\n"
        "  );\n"
        "  return resultado.affectedRows > 0;\n"
        "}",
        size=8.5,
    )


def explicacion_integrantes(doc):
    h1(doc, "5. Explicación de Sentencias SQL por Integrante del Grupo", salto=True)
    parrafo(
        doc,
        "En cumplimiento de la actividad de cierre («Cada integrante del grupo explica una de las sentencias SQL»), "
        "a continuación se documenta en detalle el análisis técnico de cada operación asignada a los miembros del Grupo 7:",
    )

    h2(doc, "5.1 Integrante 1: Snaider Brayan Quispe Janco – Sentencia SELECT")
    parrafo(
        doc,
        "**Sentencia asignada:** Consulta y lectura de registros (`obtener` y `obtenerPorId`).",
    )
    codigo(
        doc,
        "SELECT id, titulo, materia, DATE_FORMAT(fecha, '%d/%m/%Y') AS fecha, prioridad, hecho\n"
        "  FROM pendientes\n"
        " WHERE usuario_id = ?\n"
        " ORDER BY hecho ASC, fecha ASC, id DESC;",
        size=9.0,
    )
    parrafo(
        doc,
        "**Explicación técnica:**\n"
        "1. **Cláusula WHERE parametrizada:** Se filtra por `usuario_id = ?`. El parámetro posicional asegura que el "
        "estudiante solo tenga visibilidad de sus propios pendientes y no de los de otros alumnos, garantizando la privacidad.\n"
        "2. **Formateo de fecha con DATE_FORMAT:** En lugar de manipular cadenas en la aplicación móvil, se aprovecha la "
        "función nativa `DATE_FORMAT(fecha, '%d/%m/%Y')` de MySQL para transformar el formato interno `aaaa-mm-dd` al formato "
        "amigable `dd/mm/aaaa` requerido por la interfaz.\n"
        "3. **Ordenamiento multinivel:** `ORDER BY hecho ASC, fecha ASC, id DESC` posiciona primero los pendientes incompletos "
        "(`hecho = 0`) ordenados cronológicamente por fecha de entrega más cercana, enviando al fondo los ya terminados (`hecho = 1`).",
    )

    h2(doc, "5.2 Integrante 2: Jonathan Smith Soraire Rojas – Sentencia INSERT")
    parrafo(
        doc,
        "**Sentencia asignada:** Inserción y creación de registros (`agregar`).",
    )
    codigo(
        doc,
        "INSERT INTO pendientes (usuario_id, titulo, materia, fecha, prioridad, hecho)\n"
        "VALUES (?, ?, ?, ?, ?, 0);",
        size=9.0,
    )
    parrafo(
        doc,
        "**Explicación técnica:**\n"
        "1. **Parámetros preparados:** Posee 5 comodines `?` correspondientes a `usuario_id`, `titulo`, `materia`, `fecha` "
        "y `prioridad`. El motor de base de datos valida automáticamente el tipo de cada valor antes de persistirlo.\n"
        "2. **Estado por defecto:** El campo `hecho` se inserta con el valor literal `0` (falso), ya que toda tarea recién "
        "creada nace en estado pendiente.\n"
        "3. **Retorno del identificador generado:** MySQL devuelve la propiedad `resultado.insertId`, que contiene la clave "
        "primaria generada por `AUTO_INCREMENT`. Inmediatamente la función invoca `obtenerPorId(usuarioId, insertId)` para "
        "devolver el objeto completo a la interfaz móvil.",
    )

    h2(doc, "5.3 Integrante 3: Joel David Cerrogrande Ortega – Sentencia UPDATE")
    parrafo(
        doc,
        "**Sentencia asignada:** Modificación de datos y actualización de estado (`modificar` y `alternar`).",
    )
    codigo(
        doc,
        "-- Modificación completa desde formulario\n"
        "UPDATE pendientes\n"
        "   SET titulo = ?, materia = ?, fecha = ?, prioridad = ?\n"
        " WHERE id = ? AND usuario_id = ?;\n\n"
        "-- Alternar casilla de hecho / pendiente\n"
        "UPDATE pendientes\n"
        "   SET hecho = ?\n"
        " WHERE id = ? AND usuario_id = ?;",
        size=9.0,
    )
    parrafo(
        doc,
        "**Explicación técnica:**\n"
        "1. **Seguridad estricta en el WHERE:** La cláusula de condición combina `id = ? AND usuario_id = ?`. Esto previene "
        "ataques de manipulación de identificadores (IDOR): un estudiante no puede alterar un pendiente ajeno aunque intente "
        "enviar un `id` que no le pertenece.\n"
        "2. **Actualización parcial:** En la lista de pendientes, al pulsar sobre la casilla de verificación sólo se ejecuta el "
        "segundo `UPDATE`, cambiando el bit de `hecho` (1 o 0) sin necesidad de reenviar el título o la materia.\n"
        "3. **Disparador de auditoría:** El campo `actualizado_en TIMESTAMP` de la tabla se actualiza de manera automática "
        "mediante la cláusula de definición `ON UPDATE CURRENT_TIMESTAMP`.",
    )

    h2(doc, "5.4 Integrante 4: Jose Alejandro Benitez Torrez – Sentencia DELETE y Login")
    parrafo(
        doc,
        "**Sentencia asignada:** Eliminación con confirmación (`eliminar`) y consulta de autenticación (`validarUsuario`).",
    )
    codigo(
        doc,
        "-- Eliminación física con doble filtro de seguridad\n"
        "DELETE FROM pendientes WHERE id = ? AND usuario_id = ?;\n\n"
        "-- Consulta de credenciales para el inicio de sesión\n"
        "SELECT id, nombre, email, curso, codigo, password_hash\n"
        "  FROM usuarios\n"
        " WHERE email = ?\n"
        " LIMIT 1;",
        size=9.0,
    )
    parrafo(
        doc,
        "**Explicación técnica:**\n"
        "1. **Confirmación obligatoria previa:** Antes de ejecutar el `DELETE`, la aplicación móvil despliega un modal nativo "
        "de confirmación («¿Seguro que querés borrar este pendiente? Esta acción no se puede deshacer»). Sólo si el usuario "
        "presiona el botón rojo 'Eliminar' se dispara la petición al servidor.\n"
        "2. **Comprobación de filas afectadas:** Se verifica que `resultado.affectedRows > 0`. Si el registro existía y "
        "pertenecía al usuario, la operación retorna éxito.\n"
        "3. **Autenticación con hash bcrypt:** En el login, la consulta busca el usuario por su correo con `email = ?`. La "
        "contraseña jamás se compara en SQL ni se guarda en texto plano; el servidor utiliza `bcrypt.compare(password, usuario.password_hash)` "
        "para autenticar de forma criptográfica al estudiante.",
    )


def flujo_interfaz(doc):
    h1(doc, "6. Integración en la Interfaz Móvil y Flujo CRUD", salto=True)
    parrafo(
        doc,
        "Todas las pantallas de la aplicación móvil se integraron con la capa de datos de MySQL. A continuación "
        "se presentan las capturas reales obtenidas durante la ejecución de las pruebas:",
    )

    pantallas_dia3 = [
        ("01-login-mysql.png", "Acceso con usuario en MySQL",
         "Inicio de sesión validado contra la tabla usuarios en MySQL mediante SELECT ... WHERE email = ? y hash bcrypt."),
        ("02-lista-pendientes.png", "Mis pendientes (SELECT en MySQL)",
         "La lista lee los datos directamente de MySQL; muestra el indicador '· MySQL', filtros por estado y contador en vivo."),
        ("03-formulario-insert.png", "Nuevo pendiente (INSERT)",
         "Formulario de alta conectado a la sentencia INSERT INTO pendientes (...) VALUES (?, ?, ?, ?, ?, 0)."),
        ("04-detalle-pendiente.png", "Detalle del pendiente (SELECT por ID)",
         "Detalle recibido por parámetro mediante SELECT ... WHERE id = ? AND usuario_id = ? con botones para editar y borrar."),
        ("05-formulario-update.png", "Edición de pendiente (UPDATE)",
         "Formulario pre-cargado con los datos actuales que dispara UPDATE pendientes SET ... WHERE id = ? AND usuario_id = ?."),
        ("06-confirmacion-delete.png", "Modal de confirmación (DELETE)",
         "Alerta modal que solicita confirmación explícita del estudiante antes de ejecutar DELETE FROM pendientes WHERE id = ?."),
    ]

    for i, (archivo, nombre, desc) in enumerate(pantallas_dia3, 1):
        h2(doc, f"6.{i} {nombre}")
        parrafo(doc, desc)
        captura(doc, archivo, i, nombre)

    h2(doc, "Estrategia de Recarga Inmediata (Refresco de Pantalla)")
    parrafo(
        doc,
        "Siguiendo la actividad 5 («Después de cada cambio, volver a leer la tabla (recargar) para refrescar la pantalla»), "
        "en el archivo `store/pendientes.ts` se implementó una recarga inmediata tras cada mutación:",
    )
    codigo(
        doc,
        "// store/pendientes.ts\n"
        "agregar: async (p) => {\n"
        "  await datos.agregar(usuarioId, p); // 1. Ejecuta INSERT\n"
        "  await get().cargar();              // 2. Ejecuta SELECT y refresca la lista al instante\n"
        "},\n\n"
        "actualizar: async (id, cambios) => {\n"
        "  await datos.modificar(usuarioId, id, cambios); // 1. Ejecuta UPDATE\n"
        "  await get().cargar();                          // 2. Re-consulta la base\n"
        "},\n\n"
        "eliminar: async (id) => {\n"
        "  await datos.eliminar(usuarioId, id); // 1. Ejecuta DELETE\n"
        "  await get().cargar();                // 2. Re-consulta la base\n"
        "};",
        size=9.0,
    )


def evidencia_persistencia(doc):
    h1(doc, "7. Evidencia de Persistencia: Prueba de Cierre y Reapertura", salto=True)
    parrafo(
        doc,
        "La actividad 7 exige verificar la persistencia cerrando Expo Go por completo, abriéndolo de nuevo y comprobando "
        "que los datos ingresados se conservan intactos. Esta prueba demuestra fehacientemente que la información ya no vive "
        "en la memoria volátil de JavaScript, sino en los archivos físicos del motor MySQL (`cosabe360_movil.ibd`).",
    )
    parrafo(
        doc,
        "**Procedimiento experimental ejecutado:**\n"
        "1. **Paso 1 (Creación y registro):** Se inició la aplicación y se dio de alta un nuevo pendiente con el título "
        "«Presentar Informe Jornada 3 Base de Datos», materia «Aplicaciones Móviles I», fecha «20/11/2026» y prioridad «alta».\n"
        "2. **Paso 2 (Verificación antes del cierre):** La tarea apareció inmediatamente en la lista de pendientes (Figura 7).\n"
        "3. **Paso 3 (Cierre total de la app):** Se forzó el cierre de Expo Go / cliente móvil, eliminando por completo la instancia "
        "de memoria RAM del dispositivo.\n"
        "4. **Paso 4 (Reapertura y consulta):** Se abrió nuevamente la aplicación desde cero y se inició sesión. La lista ejecutó "
        "un nuevo `SELECT` contra MySQL y recuperó exactamente todos los registros creados (Figura 8).",
    )

    h2(doc, "7.1 Captura antes del cierre de la app")
    parrafo(
        doc,
        "La Figura 7 muestra la aplicación con los registros creados antes de finalizar el proceso del cliente móvil:",
    )
    captura(doc, "07-persistencia-antes.png", 7, "Lista de pendientes antes de cerrar la app (registro guardado en MySQL)")

    h2(doc, "7.2 Captura después de cerrar y reabrir la app")
    parrafo(
        doc,
        "La Figura 8 muestra la aplicación reabierta desde cero tras un reinicio completo; todos los datos persisten:",
    )
    captura(doc, "08-persistencia-despues.png", 8, "Lista de pendientes tras reabrir la app (persistencia comprobada)")

    h2(doc, "7.3 Verificación de persistencia en phpMyAdmin / MySQL")
    parrafo(
        doc,
        "Al consultar la base de datos `cosabe360_movil` desde phpMyAdmin o consola MySQL en XAMPP mediante "
        "`SELECT * FROM pendientes WHERE usuario_id = 1;`, se constata la presencia exacta de las filas con sus marcas "
        "temporales de auditoría (`creado_en`), confirmando la persistencia física en el disco duro.",
    )


def checklist_cierre(doc):
    h1(doc, "8. Avance al Cierre y Checklist de Verificación Docente", salto=True)
    parrafo(
        doc,
        "A continuación se presenta la matriz de verificación docente correspondiente a la Jornada 3, con todos los "
        "entregables validados satisfactoriamente:",
    )
    tabla(
        doc,
        ["Entregable a verificar por el docente", "Evidencia en el proyecto", "Estado"],
        [
            [
                "Crear, ver, modificar y borrar funcionando en la entidad principal",
                "Operaciones CRUD completas y operativas en la entidad 'pendientes', conectadas con INSERT, SELECT, "
                "UPDATE y DELETE a la base MySQL.",
                "[ X ] CUMPLIDO",
            ],
            [
                "Evidencia de persistencia: captura antes y después de cerrar la app",
                "Prueba documentada en la Sección 7 con capturas antes y después de finalizar el proceso de la app.",
                "[ X ] CUMPLIDO",
            ],
            [
                "datos.js con las sentencias SQL y parámetros ?",
                "Archivo datos.js (en api y en mobile) utilizando exclusivamente parámetros posicionales ? en todas las consultas.",
                "[ X ] CUMPLIDO",
            ],
            [
                "Cada integrante del grupo explica una de las sentencias SQL",
                "Explicación exhaustiva en la Sección 5 asignada individualmente a los 4 integrantes del Grupo 7.",
                "[ X ] CUMPLIDO",
            ],
        ],
        anchos=[5.0, 8.5, 3.5],
    )

    h2(doc, "Guía de Ejecución Local para Demostración")
    parrafo(
        doc,
        "Para replicar la demostración de la Jornada 3 en cualquier computadora con el repositorio:",
    )
    vineta(doc, "1. Iniciar **Apache** y **MySQL** en el Panel de Control de **XAMPP**.")
    vineta(doc, "2. En una terminal, situarse en `App_360/api` y ejecutar `npm run crear-db` para inicializar el esquema y datos.")
    vineta(doc, "3. Ejecutar `npm run dev` en `App_360/api` para encender la API local (puerto 4000).")
    vineta(doc, "4. En otra terminal, situarse en `App_360/mobile` y ejecutar `npx expo start`.")
    vineta(doc, "5. Abrir la app desde el celular mediante Expo Go o en el navegador pulsando `w`.")


def main():
    destino_docx = os.path.join(DOCS, "Actividad-06-Grupo-7-Dia-3-Informe.docx")
    destino_zip = os.path.join(DOCS, "Actividad-06-Grupo-7-Dia-3-Informe.zip")

    # 1. Crear documento a partir de la carátula institucional
    g.crear_caratula(destino_docx, "ACTIVIDAD 06 \u2013 D\u00cdA 3 \u2013 INFORME (BASE DE DATOS Y PERSISTENCIA)")
    doc = docx.Document(destino_docx)
    g.limpiar_caratula(doc)

    for seccion in doc.sections:
        seccion.top_margin = Cm(2.5)
        seccion.bottom_margin = Cm(2.5)
        seccion.left_margin = Cm(3.0)
        seccion.right_margin = Cm(3.0)

    # 2. Agregar secciones del informe
    avance_jornada(doc)
    arquitectura_persistencia(doc)
    esquema_sql(doc)
    datos_js_seccion(doc)
    explicacion_integrantes(doc)
    flujo_interfaz(doc)
    evidencia_persistencia(doc)
    checklist_cierre(doc)

    doc.save(destino_docx)
    print("DOCX generado:", destino_docx)

    # 3. Empaquetar en el formato ZIP pedido
    with zipfile.ZipFile(destino_zip, "w", zipfile.ZIP_DEFLATED) as z:
        z.write(destino_docx, arcname="Actividad-06-Grupo-7-Dia-3-Informe.docx")
    print("ZIP generado:", destino_zip)

    return destino_docx, destino_zip


if __name__ == "__main__":
    main()
