# -*- coding: utf-8 -*-
"""Genera el informe de la Jornada 4 (Actividad 07) del proyecto final.

Tema: API e integración (Consumo de API pública Nager.Date, patrón de integración con MySQL y congelamiento).
Formato: Times New Roman 12, interlineado 1.5, texto justificado, tablas con bordes, carátula institucional UPDS.
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
CAPTURAS_DIA2 = os.path.join(os.path.dirname(__file__), "capturas-dia2")
CAPTURAS_DIA3 = os.path.join(os.path.dirname(__file__), "capturas-dia3")

INTEGRANTES = [
    "Snaider Brayan Quispe Janco",
    "Jonathan Smith Soraire Rojas",
    "Joel David Cerrogrande Ortega",
    "Jose Alejandro Benitez Torrez",
]


def captura(doc, archivo, numero, nombre, ancho=5.6):
    """Inserta una captura de pantalla centrada con su pie de figura."""
    ruta = os.path.join(CAPTURAS_DIA2, archivo)
    if not os.path.exists(ruta):
        ruta = os.path.join(CAPTURAS_DIA3, archivo)
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
    h1(doc, "1. Avance de la Jornada 4 (Lunes 23 · API e Integración)", salto=True)
    parrafo(
        doc,
        "El objetivo principal de la Jornada 4 consistió en dotar a la aplicación CSB 360 de la capacidad de "
        "consumir datos reales en vivo desde internet y unir esa información externa con la base de datos "
        "relacional implementada en la jornada anterior. Al cierre de esta jornada se efectuó el congelamiento "
        "de funcionalidades acordado con el docente, consolidando el alcance final del producto sin incorporar "
        "nuevas funciones que puedan comprometer su estabilidad.",
    )
    tabla(
        doc,
        ["Actividad planificada", "Detalle técnico implementado", "Estado"],
        [
            [
                "1. Creación de api.js con fetch",
                "Módulo src/data/api.js implementado con fetch nativo, async/await, bloque try/catch/finally "
                "y verificación explícita de respuesta.ok.",
                "Cumplido",
            ],
            [
                "2. Tres estados de interfaz",
                "Pantalla de Feriados con spinner de carga, estado de error sin red con botón Reintentar "
                "y visualización completa de datos.",
                "Cumplido",
            ],
            [
                "3. Transformación de la respuesta",
                "Filtrado mediante map y slice para extraer únicamente los primeros 12 feriados de Bolivia 2026 "
                "con date, localName y formateo a dd/mm/aaaa.",
                "Cumplido",
            ],
            [
                "4. Integración con la base de datos",
                "Patrón B implementado: el usuario puede guardar cualquier feriado de la API directamente como "
                "un pendiente en la tabla MySQL con un toque, detectando si ya fue registrado.",
                "Cumplido",
            ],
            [
                "5. Prueba de modo avión",
                "Simulación de pérdida de red y modo avión: la aplicación no se cierra, atrapa la excepción y "
                "ofrece la opción de reintento.",
                "Cumplido",
            ],
            [
                "6. Congelamiento de funcionalidades",
                "Matriz de alcance congelada y acordada: 5 módulos principales activos al 100% y módulos secundarios "
                "recortados para la defensa final.",
                "Cumplido",
            ],
        ],
        anchos=[4.0, 9.5, 3.0],
    )


def especificacion_api(doc):
    h1(doc, "2. Especificación y Consumo de la API Pública")
    parrafo(
        doc,
        "Para responder a la necesidad de los estudiantes del Colegio San Bernardo de planificar sus semanas "
        "académicas, se integró la API pública **Nager.Date**, un servicio web global de feriados públicos "
        "estandarizado y gratuito sin requerimiento de API Key.",
    )
    tabla(
        doc,
        ["Parámetro", "Valor"],
        [
            ["Servicio", "Nager.Date Public Holidays API v3"],
            ["Endpoint consultado", "https://date.nager.at/api/v3/PublicHolidays/2026/BO"],
            ["Método HTTP", "GET"],
            ["Formato de respuesta", "JSON (application/json)"],
            ["País y año", "Bolivia (código ISO: BO) · Gestión 2026"],
            ["Autenticación", "Pública y abierta (sin token ni clave expuesta)"],
            ["Campos aprovechados", "date (fecha ISO), localName (nombre oficial), name (nombre internacional)"],
        ],
        anchos=[4.5, 12.0],
    )

    h2(doc, "Código de src/data/api.js (Consumo Robusto)")
    codigo(
        doc,
        "// src/data/api.js\n"
        "const API_FERIADOS_URL = 'https://date.nager.at/api/v3/PublicHolidays/2026/BO';\n\n"
        "export async function obtenerFeriados() {\n"
        "  let cargando = true;\n"
        "  try {\n"
        "    const respuesta = await fetch(API_FERIADOS_URL, {\n"
        "      method: 'GET',\n"
        "      headers: { Accept: 'application/json' },\n"
        "    });\n\n"
        "    // Verificación obligatoria de respuesta.ok (códigos HTTP 200-299)\n"
        "    if (!respuesta.ok) {\n"
        "      throw new Error(`Error en la API pública: estado HTTP ${respuesta.status}`);\n"
        "    }\n\n"
        "    const datosCrudos = await respuesta.json();\n"
        "    if (!Array.isArray(datosCrudos)) {\n"
        "      throw new Error('La respuesta de la API no contiene una lista válida.');\n"
        "    }\n\n"
        "    // Transformación de datos: se limpian campos y se conservan los primeros 12 feriados\n"
        "    return datosCrudos\n"
        "      .map((item) => ({\n"
        "        date: String(item.date || ''),\n"
        "        localName: String(item.localName || item.name || 'Feriado nacional'),\n"
        "        name: String(item.name || ''),\n"
        "      }))\n"
        "      .slice(0, 12);\n"
        "  } catch (error) {\n"
        "    console.error('[API Feriados] Error al consumir el servicio:', error?.message);\n"
        "    throw new Error(error?.message || 'Revisa tu conexión a internet.');\n"
        "  } finally {\n"
        "    cargando = false;\n"
        "  }\n"
        "}",
        size=8.5,
    )


def tres_estados_interfaz(doc):
    h1(doc, "3. Manejo de los Tres Estados de Interfaz", salto=True)
    parrafo(
        doc,
        "Una de las exigencias pedagógicas centrales de la Jornada 4 es el tratamiento exhaustivo del ciclo de "
        "vida de la petición de red. La pantalla `feriados.tsx` implementa de forma clara y diferenciada los tres estados:",
    )
    vineta(
        doc,
        "**Estado 1: Cargando (`isLoading = true`):** Se muestra un indicador de actividad giratorio (`ActivityIndicator`) "
        "con el mensaje informativo «Cargando feriados desde la API pública…», previniendo bloqueos de la interfaz.",
    )
    vineta(
        doc,
        "**Estado 2: Error (`isError = true`):** Si el dispositivo carece de conexión o el servidor externo falla, se atrapa "
        "la excepción y se renderiza un ícono de advertencia (`WifiOff`), el texto explicativo y el botón interactivo "
        "«Reintentar» que ejecuta `refetch()` sin reiniciar la app.",
    )
    vineta(
        doc,
        "**Estado 3: Éxito con datos (`data` disponible):** Se renderiza la tarjeta verde con el próximo feriado oficial "
        "destacado y la lista ordenada con sus etiquetas de día, mes abreviado, nombre y botones de guardado.",
    )

    h2(doc, "Capturas de la pantalla de feriados en ejecución")
    captura(doc, "05-feriados.png", 1, "Pantalla de Feriados con datos de la API Nager.Date y próximo feriado destacado")
    captura(doc, "11-feriados-error.png", 2, "Estado de error controlado con botón «Reintentar» (modo avión sin internet)")


def patron_integracion(doc):
    h1(doc, "4. Integración de la API con la Base de Datos (Patrón Elegido)", salto=True)
    parrafo(
        doc,
        "En cumplimiento de la actividad 4 («Unir con la base de datos según el patrón elegido: guardar favoritos o "
        "registros, complementar datos con la API, o guardar una copia local»), en CSB 360 se seleccionó el "
        "**Patrón B: Complementación e inserción directa de registros en la base de datos relacional**.",
    )
    parrafo(
        doc,
        "**Mecánica de la integración:**\n"
        "1. Al consultar los feriados por internet, la aplicación compara en memoria las fechas y nombres de la API "
        "con los registros existentes en la tabla `pendientes` de MySQL consultados mediante `usePendientes`.\n"
        "2. Si un feriado ya fue guardado como recordatorio escolar, la tarjeta exhibe automáticamente la insignia verde **[EN BD]**.\n"
        "3. Si el estudiante desea incorporar el feriado a su calendario de estudio, presiona el botón de guardar (`BookmarkPlus`). "
        "La aplicación ejecuta la sentencia preparada `INSERT INTO pendientes (usuario_id, titulo, materia, fecha, prioridad, hecho) VALUES (?, ?, ?, ?, ?, 0)` "
        "en MySQL a través de `agregarPendiente(...)`.\n"
        "4. Al completarse la inserción, la lista de pendientes se actualiza de inmediato mediante `cargar()` y la insignia "
        "cambia a guardado, reflejándose también en el contador de tareas del inicio.",
    )

    h2(doc, "Código de integración en feriados.tsx")
    codigo(
        doc,
        "// feriados.tsx (Integración con usePendientes / MySQL)\n"
        "const pendientes = usePendientes((s) => s.items);\n"
        "const agregarPendiente = usePendientes((s) => s.agregar);\n\n"
        "function yaGuardado(feriado) {\n"
        "  const fechaFormat = formatearFecha(feriado.date);\n"
        "  return pendientes.some((p) => p.fecha === fechaFormat || p.titulo.includes(feriado.localName));\n"
        "}\n\n"
        "async function guardarEnPendientes(feriado) {\n"
        "  await agregarPendiente({\n"
        "    titulo: `Feriado: ${feriado.localName}`,\n"
        "    materia: 'General',\n"
        "    fecha: formatearFecha(feriado.date),\n"
        "    prioridad: 'media',\n"
        "  });\n"
        "}",
        size=9.0,
    )


def congelamiento_funcionalidades(doc):
    h1(doc, "5. Matriz de Congelamiento de Funcionalidades", salto=True)
    parrafo(
        doc,
        "Al cierre de la Jornada 4 se formaliza el **congelamiento de funcionalidades**, acordando con el docente "
        "la lista definitiva de módulos que ingresan a la versión candidata y los elementos recortados para asegurar "
        "la total estabilidad del software durante la defensa final:",
    )
    tabla(
        doc,
        ["Módulo / Funcionalidad", "Tipo de elemento", "Decisión", "Justificación técnica"],
        [
            [
                "Inicio y Resumen Escolar",
                "Pantalla principal",
                "CONSERVADO (100%)",
                "Muestra el promedio, asistencia, accesos rápidos y contador dinámico de pendientes vinculados.",
            ],
            [
                "Mis Pendientes (CRUD Completo)",
                "Entidad principal",
                "CONSERVADO (100%)",
                "Crear, listar, editar y eliminar con confirmación y persistencia en MySQL.",
            ],
            [
                "Consulta de Notas y Materias",
                "Pantallas académicas",
                "CONSERVADO (100%)",
                "Visualización por trimestre y pantalla de detalle por materia recibida por parámetro.",
            ],
            [
                "Feriados Oficiales (API + BD)",
                "Consumo externo",
                "CONSERVADO (100%)",
                "Integración con Nager.Date, 3 estados de red y guardado en la base de datos.",
            ],
            [
                "Perfil y Cierre de Sesión",
                "Módulo de usuario",
                "CONSERVADO (100%)",
                "Datos del estudiante, credenciales en MySQL con bcrypt y cierre de sesión seguro.",
            ],
            [
                "Subida de fotos de justificativos",
                "Funcionalidad accesoria",
                "RECORTADO / DIFERIDO",
                "Requiere almacenamiento S3/Cloudinary en la nube; se sustituyó por flujo de comunicados.",
            ],
            [
                "Pasarela de pagos en línea",
                "Funcionalidad accesoria",
                "RECORTADO / DIFERIDO",
                "Excede el alcance de la materia; se preserva únicamente la pantalla informativa estática.",
            ],
        ],
        anchos=[4.0, 3.5, 3.5, 5.5],
    )


def checklist_cierre(doc):
    h1(doc, "6. Checklist del Cierre de Jornada (Entregables Verificados)", salto=True)
    parrafo(
        doc,
        "A continuación se presenta el cuadro oficial de verificación docente con todos los requisitos de la "
        "Jornada 4 evaluados satisfactoriamente:",
    )
    tabla(
        doc,
        ["Entregable a verificar por el docente", "Evidencia en el proyecto CSB 360", "Estado"],
        [
            [
                "Pantalla que consume la API con cargando, error y datos funcionando",
                "Pantalla Feriados con los 3 estados implementados y verificados con capturas en la Sección 3.",
                "[ X ] CUMPLIDO",
            ],
            [
                "Integración demostrada: lo que trae la API se guarda en la base o se combina",
                "Botón de guardado que inserta el feriado en MySQL (`pendientes`) y marca visual [EN BD].",
                "[ X ] CUMPLIDO",
            ],
            [
                "Prueba con modo avión: aparece el mensaje y el botón «Reintentar»",
                "Prueba documentada en Figura 2 con componente `WifiOff` y llamada interactiva a `refetch()`.",
                "[ X ] CUMPLIDO",
            ],
            [
                "Lista de pendientes y recortes acordada con el docente (congelamiento)",
                "Matriz de congelamiento aprobada y documentada en la Sección 5 con 5 módulos conservados y 2 recortados.",
                "[ X ] CUMPLIDO",
            ],
        ],
        anchos=[5.0, 8.5, 3.5],
    )


def main():
    destino_docx = os.path.join(DOCS, "Actividad-07-Grupo-7-Dia-4-Informe.docx")
    destino_zip = os.path.join(DOCS, "Actividad-07-Grupo-7-Dia-4-Informe.zip")

    g.crear_caratula(destino_docx, "ACTIVIDAD 07 \u2013 D\u00cdA 4 \u2013 INFORME (API E INTEGRACI\u00d3N)")
    doc = docx.Document(destino_docx)
    g.limpiar_caratula(doc)

    for seccion in doc.sections:
        seccion.top_margin = Cm(2.5)
        seccion.bottom_margin = Cm(2.5)
        seccion.left_margin = Cm(3.0)
        seccion.right_margin = Cm(3.0)

    avance_jornada(doc)
    especificacion_api(doc)
    tres_estados_interfaz(doc)
    patron_integracion(doc)
    congelamiento_funcionalidades(doc)
    checklist_cierre(doc)

    doc.save(destino_docx)
    print("DOCX generado:", destino_docx)

    with zipfile.ZipFile(destino_zip, "w", zipfile.ZIP_DEFLATED) as z:
        z.write(destino_docx, arcname="Actividad-07-Grupo-7-Dia-4-Informe.docx")
    print("ZIP generado:", destino_zip)

    return destino_docx, destino_zip


if __name__ == "__main__":
    main()
