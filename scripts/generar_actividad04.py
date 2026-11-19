# -*- coding: utf-8 -*-
"""Genera el informe de la Jornada 1 (Actividad 04) del proyecto final.

Formato pedido: Times New Roman 12, interlineado 1.5, justificado, texto negro.
"""

import os
import re

import docx
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.shared import Cm, Pt, RGBColor

import generar_documentos as g

DOCS = g.DOCS
CAPTURAS = g.CAPTURAS

FUENTE = "Times New Roman"
NEGRO = RGBColor(0, 0, 0)


def _font(run, size=12, bold=False):
    run.font.name = FUENTE
    run.font.size = Pt(size)
    run.font.bold = bold
    run.font.color.rgb = NEGRO
    rpr = run._element.get_or_add_rPr()
    rfonts = rpr.find(qn("w:rFonts"))
    if rfonts is None:
        rfonts = OxmlElement("w:rFonts")
        rpr.append(rfonts)
    for atributo in ("w:ascii", "w:hAnsi", "w:cs", "w:eastAsia"):
        rfonts.set(qn(atributo), FUENTE)


def _rich(p, texto, size=12, bold=False):
    for trozo in re.split(r"(\*\*.*?\*\*)", texto):
        if not trozo:
            continue
        if trozo.startswith("**") and trozo.endswith("**"):
            _font(p.add_run(trozo[2:-2]), size=size, bold=True)
        else:
            _font(p.add_run(trozo), size=size, bold=bold)


def h1(doc, texto, salto=False):
    p = doc.add_paragraph()
    p.paragraph_format.space_before = Pt(12)
    p.paragraph_format.space_after = Pt(6)
    p.paragraph_format.line_spacing = 1.5
    p.paragraph_format.keep_with_next = True
    if salto:
        p.paragraph_format.page_break_before = True
    _font(p.add_run(texto.upper()), size=12, bold=True)
    return p


def h2(doc, texto):
    p = doc.add_paragraph()
    p.paragraph_format.space_before = Pt(6)
    p.paragraph_format.space_after = Pt(3)
    p.paragraph_format.line_spacing = 1.5
    p.paragraph_format.keep_with_next = True
    _font(p.add_run(texto), size=12, bold=True)
    return p


def parrafo(doc, texto, size=12, justificado=True):
    p = doc.add_paragraph()
    p.paragraph_format.space_after = Pt(6)
    p.paragraph_format.line_spacing = 1.5
    if justificado:
        p.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY
    _rich(p, texto, size=size)
    return p


def vineta(doc, texto, size=12):
    p = doc.add_paragraph()
    p.paragraph_format.left_indent = Cm(0.75)
    p.paragraph_format.first_line_indent = Cm(-0.5)
    p.paragraph_format.space_after = Pt(3)
    p.paragraph_format.line_spacing = 1.5
    _font(p.add_run("\u2022 "), size=size)
    _rich(p, texto, size=size)
    return p


def _bordes(tabla):
    tblPr = tabla._tbl.tblPr
    borders = OxmlElement("w:tblBorders")
    for borde in ("top", "left", "bottom", "right", "insideH", "insideV"):
        el = OxmlElement(f"w:{borde}")
        el.set(qn("w:val"), "single")
        el.set(qn("w:sz"), "4")
        el.set(qn("w:space"), "0")
        el.set(qn("w:color"), "000000")
        borders.append(el)
    tblPr.append(borders)


def tabla(doc, encabezados, filas, anchos=None, size=10):
    t = doc.add_table(rows=1, cols=len(encabezados))
    _bordes(t)
    t.autofit = True
    for i, texto in enumerate(encabezados):
        celda = t.rows[0].cells[i]
        celda.text = ""
        p = celda.paragraphs[0]
        p.paragraph_format.space_after = Pt(0)
        p.paragraph_format.line_spacing = 1.0
        _font(p.add_run(texto), size=size, bold=True)
    for fila in filas:
        celdas = t.add_row().cells
        for i, texto in enumerate(fila):
            celdas[i].text = ""
            p = celdas[i].paragraphs[0]
            p.paragraph_format.space_after = Pt(0)
            p.paragraph_format.line_spacing = 1.0
            _font(p.add_run(str(texto)), size=size)
    # Repite el encabezado si la tabla se parte entre páginas
    primera = t.rows[0]._tr.get_or_add_trPr()
    primera.append(OxmlElement("w:tblHeader"))
    # Evita que una fila se parta entre dos páginas
    for row in t.rows:
        trPr = row._tr.get_or_add_trPr()
        cant = OxmlElement("w:cantSplit")
        trPr.append(cant)
    if anchos:
        for i, ancho in enumerate(anchos):
            for row in t.rows:
                row.cells[i].width = Cm(ancho)
    doc.add_paragraph().paragraph_format.space_after = Pt(0)
    return t


def codigo(doc, texto, size=9):
    for linea in texto.split("\n"):
        p = doc.add_paragraph()
        p.paragraph_format.space_after = Pt(0)
        p.paragraph_format.line_spacing = 1.0
        p.paragraph_format.left_indent = Cm(0.5)
        _font(p.add_run(linea), size=size)


def captura(doc, archivo, numero, nombre, ancho=6.0):
    ruta = os.path.join(CAPTURAS, archivo)
    if not os.path.exists(ruta):
        return
    p = doc.add_paragraph()
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p.paragraph_format.space_before = Pt(6)
    p.paragraph_format.space_after = Pt(2)
    p.add_run().add_picture(ruta, width=Cm(ancho))
    pie = doc.add_paragraph()
    pie.alignment = WD_ALIGN_PARAGRAPH.CENTER
    pie.paragraph_format.space_after = Pt(6)
    _font(pie.add_run(f"Figura {numero}. {nombre}"), size=10)


# --------------------------------------------------------------------------- #
def ficha(doc):
    h1(doc, "1. Ficha del proyecto", salto=True)
    tabla(
        doc,
        ["Campo", "Detalle"],
        [
            ["Nombre de la app", "CSB 360 Estudiante"],
            ["Grupo", "Grupo 7"],
            ["Materia", "Aplicaciones Móviles I (SIS-0180)"],
            ["Integrantes", "Snaider Brayan Quispe Janco · Jonathan Smith Soraire Rojas · "
                            "Joel David Cerrogrande Ortega · Jose Alejandro Benitez Torrez"],
            ["Usuarios", "Todos los estudiantes del Colegio San Bernardo (Tarija), de los niveles "
                         "Inicial, Primaria y Secundaria, que cuenten con celular Android y "
                         "conexión intermitente a internet."],
        ],
        anchos=[4.0, 12.5],
    )

    h2(doc, "Problema que resuelve")
    parrafo(
        doc,
        "El colegio ya tiene el sistema web 360, pero solo se usa desde una computadora. Cualquier "
        "estudiante del Colegio San Bernardo necesita una forma ágil de saber qué pendientes tiene, "
        "cuándo es el próximo feriado y cómo va en cada materia mientras está en el colegio o en su "
        "casa. Hoy anota sus tareas en papeles que se pierden y se entera de los feriados cuando ya "
        "organizó su semana. La aplicación concentra esa información en el celular, funciona sin "
        "internet para los pendientes y se actualiza con los feriados oficiales de Bolivia.",
    )

    h2(doc, "Funciones (4 a 6)")
    tabla(
        doc,
        ["N°", "Función", "Tipo"],
        [
            ["1", "Inicio: resumen del estudiante con promedio, asistencia y clase actual.", "Esencial"],
            ["2", "Mis pendientes: crear, ver, editar y borrar recordatorios por materia y fecha (SQLite).", "Esencial"],
            ["3", "Feriados: consulta de feriados nacionales de Bolivia desde una API pública, con aviso "
                  "si un pendiente cae en feriado.", "Esencial"],
            ["4", "Notas: lista de materias y detalle de cada una, recibiendo el id por parámetro.", "Esencial"],
            ["5", "Perfil: datos del estudiante y preferencia de notificaciones locales.", "Opcional"],
            ["6", "Comunicados: lista y detalle de avisos del colegio.", "Opcional (recortable)"],
        ],
        anchos=[1.2, 12.3, 3.0],
    )

    h2(doc, "Tecnologías y arquitectura en una frase")
    parrafo(
        doc,
        "React Native + Expo (SDK 58) con Expo Router, SQLite (expo-sqlite) para los datos propios y "
        "fetch contra la API pública de feriados; patrón B: la base guarda mis pendientes y la API los "
        "complementa con los feriados oficiales.",
    )


def api(doc):
    h1(doc, "2. API elegida y prueba en el navegador", salto=True)
    tabla(
        doc,
        ["Dato", "Detalle"],
        [
            ["Nombre", "Nager.Date (feriados públicos por país)"],
            ["Dirección", "https://date.nager.at/api/v3/PublicHolidays/2026/BO"],
            ["Método", "GET"],
            ["Clave", "No requiere"],
            ["Formato", "JSON"],
            ["Prueba", "18/11/2026 en el navegador: HTTP 200, 12 feriados de Bolivia en español"],
        ],
        anchos=[3.5, 13.0],
    )

    h2(doc, "Fragmento de la respuesta (recortado)")
    codigo(
        doc,
        "[\n"
        "  {\n"
        '    "date": "2026-01-01",\n'
        '    "localName": "Año Nuevo",\n'
        '    "name": "New Year\'s Day",\n'
        '    "global": true\n'
        "  },\n"
        "  {\n"
        '    "date": "2026-02-16",\n'
        '    "localName": "Feriado por Carnaval",\n'
        '    "name": "Carnival",\n'
        '    "global": true\n'
        "  }\n"
        "]",
    )

    h2(doc, "Qué datos usa la app")
    parrafo(
        doc,
        "Solo tres campos: date (fecha), localName (nombre en español) y global. Se descartan los "
        "campos en inglés y los países, para no cargar datos que la aplicación no muestra.",
    )

    h2(doc, "Patrón de integración elegido: B, mis datos más datos externos")
    parrafo(
        doc,
        "La base SQLite guarda los pendientes que crea el estudiante (título, materia, fecha y "
        "prioridad). La API aporta los feriados nacionales. La integración ocurre en la pantalla de "
        "Feriados y en la lista de pendientes: si la fecha de un pendiente coincide con un feriado, la "
        "aplicación muestra el aviso «Este día es feriado: …». Así los datos propios y los externos se "
        "complementan.",
    )


def tablas(doc):
    h1(doc, "3. Diseño de la base de datos", salto=True)
    parrafo(
        doc,
        "Una tabla principal con siete columnas y clave primaria autoincremental. Todos los tipos son "
        "los que acepta SQLite y las consultas usarán parámetros ?.",
    )
    tabla(
        doc,
        ["Columna", "Tipo", "Restricción", "Descripción"],
        [
            ["id", "INTEGER", "PRIMARY KEY AUTOINCREMENT", "Identificador del pendiente."],
            ["titulo", "TEXT", "NOT NULL", "Qué debe hacer el estudiante."],
            ["materia", "TEXT", "NOT NULL", "Materia a la que pertenece."],
            ["fecha", "TEXT", "NOT NULL", "Fecha límite en formato AAAA-MM-DD."],
            ["prioridad", "TEXT", "NOT NULL CHECK (alta, media, baja)", "Urgencia del pendiente."],
            ["hecho", "INTEGER", "NOT NULL DEFAULT 0", "0 = pendiente, 1 = completado."],
            ["creado_en", "TEXT", "NOT NULL DEFAULT fecha local", "Fecha de creación del registro."],
        ],
        anchos=[2.7, 2.5, 6.5, 4.8],
    )

    h2(doc, "Sentencias previstas (irán en datos.js)")
    for s in [
        "obtenerPendientes: SELECT * FROM pendientes ORDER BY fecha ASC",
        "insertarPendiente: INSERT INTO pendientes (titulo, materia, fecha, prioridad) VALUES (?, ?, ?, ?)",
        "actualizarPendiente: UPDATE pendientes SET titulo = ?, materia = ?, fecha = ?, prioridad = ?, hecho = ? WHERE id = ?",
        "eliminarPendiente: DELETE FROM pendientes WHERE id = ?",
        "contarPendientes: SELECT COUNT(*) AS total FROM pendientes WHERE hecho = 0",
    ]:
        vineta(doc, s, size=10)


def navegacion(doc):
    h1(doc, "4. Mapa de navegación")
    parrafo(
        doc,
        "Se usan dos tipos de navegador: pestañas (Tabs) para las secciones principales y pila "
        "(Stack) para los detalles y el formulario. Los detalles reciben el identificador por "
        "parámetro.",
    )
    for linea in [
        "STACK raíz",
        " ├── Splash / Carga",
        " ├── Login (acceso de prueba)",
        " └── TABS",
        "      ├── Inicio",
        "      │     └── (acceso) Detalle de materia  [param: id]",
        "      ├── Pendientes",
        "      │     ├── Formulario nuevo / editar  [param: id opcional]",
        "      │     └── Detalle del pendiente      [param: id]",
        "      ├── Notas",
        "      │     └── Detalle de materia         [param: id]",
        "      ├── Feriados  (consume la API)",
        "      └── Perfil",
    ]:
        p = doc.add_paragraph()
        p.paragraph_format.space_after = Pt(0)
        p.paragraph_format.line_spacing = 1.0
        p.paragraph_format.left_indent = Cm(0.5)
        _font(p.add_run(linea), size=10)

    h2(doc, "Tabla de navegadores")
    tabla(
        doc,
        ["Navegador", "Dónde se usa", "Parámetros"],
        [
            ["Tabs (bottom)", "Inicio · Pendientes · Notas · Feriados · Perfil", "—"],
            ["Stack", "Splash → Login → Tabs; y detalles sobre las pestañas", "id de materia y de pendiente"],
        ],
        anchos=[3.5, 9.5, 4.0],
    )


def evidencia(doc):
    h1(doc, "5. Avance de la Jornada 1")
    parrafo(
        doc,
        "El proyecto Expo ya abre en Expo Go en el celular y navega entre sus pantallas con datos de "
        "ejemplo. Se adjuntan dos capturas del estado actual como evidencia.",
    )
    captura(doc, "04-inicio.png", 1, "Inicio del estudiante (app actual en Expo Go)", ancho=5.6)
    captura(doc, "05-materias.png", 2, "Lista navegable con datos de ejemplo", ancho=5.6)

    h2(doc, "Checklist del cierre de jornada")
    for item in [
        "Ficha del proyecto con las funciones esenciales marcadas",
        "API elegida y probada en el navegador (dirección y JSON anotados)",
        "Diseño de tablas con tipos y clave primaria",
        "Mapa de navegación dibujado",
        "Proyecto Expo abriendo en Expo Go y navegando",
    ]:
        vineta(doc, "[X] " + item, size=12)

    h2(doc, "Roles del grupo")
    tabla(
        doc,
        ["Integrante", "Rol"],
        [
            ["Snaider Brayan Quispe Janco", "Coordinador e integrador"],
            ["Jonathan Smith Soraire Rojas", "Interfaz y navegación"],
            ["Joel David Cerrogrande Ortega", "Base de datos (SQLite)"],
            ["Jose Alejandro Benitez Torrez", "API e integración"],
        ],
        anchos=[8.5, 8.0],
    )


def main():
    destino = os.path.join(DOCS, "Actividad-04-Grupo-7-Dia-1-Informe.docx")
    g.crear_caratula(destino, "ACTIVIDAD 04 \u2013 DÍA 1 \u2013 INFORME")
    doc = docx.Document(destino)
    g.limpiar_caratula(doc)

    for seccion in doc.sections:
        seccion.top_margin = Cm(2.5)
        seccion.bottom_margin = Cm(2.5)
        seccion.left_margin = Cm(3.0)
        seccion.right_margin = Cm(2.5)

    ficha(doc)
    api(doc)
    tablas(doc)
    navegacion(doc)
    evidencia(doc)

    doc.save(destino)
    print(destino)
    return destino


if __name__ == "__main__":
    main()
