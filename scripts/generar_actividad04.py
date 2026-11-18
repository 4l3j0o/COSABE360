# -*- coding: utf-8 -*-
"""Genera el informe de la Jornada 1 (Actividad 04) del proyecto final."""

import os
import subprocess
import zipfile

import docx

import generar_documentos as g

DOCS = g.DOCS
CAPTURAS = g.CAPTURAS


def ficha(doc):
    g.h1(doc, "1. FICHA DEL PROYECTO", salto=True)
    g.tabla(
        doc,
        ["Campo", "Detalle"],
        [
            ["Nombre de la app", "CSB 360 Estudiante"],
            ["Grupo", "Grupo 7"],
            ["Materia", "Aplicaciones Móviles I (SIS-0180)"],
            ["Integrantes", "Snaider Brayan Quispe Janco · Jonathan Smith Soraire Rojas · "
                            "Joel David Cerrogrande Ortega · Jose Alejandro Benitez Torrez"],
            ["Usuarios", "Estudiantes de 1º a 6º de secundaria de la Unidad Educativa San Bernardo (Tarija), "
                         "de 11 a 18 años, con celular Android y conexión intermitente."],
        ],
        anchos=[4.0, 12.5],
    )

    g.h2(doc, "Problema que resuelve")
    g.parrafo(
        doc,
        "El colegio ya tiene el sistema web 360, pero **solo se usa desde una computadora**. El estudiante "
        "no tiene forma ágil de saber qué tareas o pendientes tiene, cuándo es el próximo feriado o cómo va "
        "en cada materia mientras está en el colegio o en su casa. Anota sus pendientes en papeles que se "
        "pierden y se entera de los feriados cuando ya organizó su semana. La app concentra esa información "
        "en el celular, funciona sin internet para los pendientes y se actualiza con los feriados oficiales.",
    )

    g.h2(doc, "Funciones (4 a 6)")
    g.tabla(
        doc,
        ["#", "Función", "Tipo"],
        [
            ["1", "Inicio: resumen del estudiante con promedio, asistencia y clase actual.", "Esencial"],
            ["2", "Mis pendientes: crear, ver, editar y borrar recordatorios por materia y fecha (SQLite).", "Esencial"],
            ["3", "Feriados: consulta de feriados nacionales de Bolivia desde una API pública, con aviso si un "
                  "pendiente cae en feriado.", "Esencial"],
            ["4", "Notas: lista de materias y detalle de cada una, recibiendo el id por parámetro.", "Esencial"],
            ["5", "Perfil: datos del estudiante y preferencia de notificaciones locales.", "Opcional"],
            ["6", "Comunicados: lista y detalle de avisos del colegio.", "Opcional (recortable)"],
        ],
        anchos=[1.0, 12.5, 3.0],
    )

    g.h2(doc, "Tecnologías y arquitectura en una frase")
    g.parrafo(
        doc,
        "**React Native + Expo (SDK 58) con Expo Router**, **SQLite (expo-sqlite)** para los datos propios y "
        "**fetch** contra la API pública de feriados; patrón **B**: la base guarda mis pendientes y la API "
        "los complementa con los feriados oficiales.",
        size=9.5,
    )


def api(doc):
    g.h1(doc, "2. API ELEGIDA Y PRUEBA EN EL NAVEGADOR")
    g.parrafo(
        doc,
        "**Nombre:** Nager.Date — feriados públicos por país.  \n"
        "**Dirección:** `https://date.nager.at/api/v3/PublicHolidays/2026/BO`  \n"
        "**Método:** GET · **Clave:** no requiere · **Formato:** JSON  \n"
        "**Prueba:** abierta en el navegador el 18/11/2026, respuesta **HTTP 200** con 12 feriados de Bolivia "
        "en español.",
        size=9.5,
        justificado=False,
    )

    g.h2(doc, "Fragmento de la respuesta (recortado)")
    codigo = (
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
        "]"
    )
    for linea in codigo.split("\n"):
        p = doc.add_paragraph()
        p.paragraph_format.space_after = g.Pt(0)
        g._font(p.add_run(linea), size=8.5)

    g.h2(doc, "Qué datos usa la app")
    g.parrafo(
        doc,
        "Solo tres campos: **date** (fecha), **localName** (nombre en español) y **global**. Se descartan "
        "los campos en inglés y los países, para no cargar datos que la app no muestra.",
        size=9.5,
    )

    g.h2(doc, "Patrón de integración elegido: B — mis datos + datos externos")
    g.parrafo(
        doc,
        "La **base SQLite** guarda los pendientes que crea el estudiante (título, materia, fecha, prioridad). "
        "La **API** aporta los feriados nacionales. La integración ocurre en la pantalla de Feriados y en la "
        "lista de pendientes: si la fecha de un pendiente coincide con un feriado, la app muestra el aviso "
        "«Este día es feriado: …». Así los datos propios y los externos se complementan.",
        size=9.5,
    )


def tablas(doc):
    g.h1(doc, "3. DISEÑO DE LA BASE DE DATOS")
    g.parrafo(
        doc,
        "Una tabla principal con siete columnas y clave primaria autoincremental. Todos los tipos son los que "
        "acepta SQLite y las consultas usarán parámetros `?`.",
        size=9.5,
    )
    g.tabla(
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
        anchos=[2.8, 2.6, 6.4, 4.8],
    )

    g.h2(doc, "Sentencias previstas (irán en datos.js)")
    sentencias = [
        "obtenerPendientes: SELECT * FROM pendientes ORDER BY fecha ASC",
        "insertarPendiente: INSERT INTO pendientes (titulo, materia, fecha, prioridad) VALUES (?, ?, ?, ?)",
        "actualizarPendiente: UPDATE pendientes SET titulo = ?, materia = ?, fecha = ?, prioridad = ?, hecho = ? WHERE id = ?",
        "eliminarPendiente: DELETE FROM pendientes WHERE id = ?",
        "contarPendientes: SELECT COUNT(*) AS total FROM pendientes WHERE hecho = 0",
    ]
    for s in sentencias:
        p = doc.add_paragraph()
        p.paragraph_format.space_after = g.Pt(1)
        g._font(p.add_run("• " + s), size=8.5)


def navegacion(doc):
    g.h1(doc, "4. MAPA DE NAVEGACIÓN")
    g.parrafo(
        doc,
        "Se usan **dos tipos de navegador**: pestañas (Tabs) para las secciones principales y pila (Stack) "
        "para los detalles y el formulario. Los detalles reciben el identificador por parámetro.",
        size=9.5,
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
        p.paragraph_format.space_after = g.Pt(0)
        g._font(p.add_run(linea), size=9)

    g.h2(doc, "Tabla de navegadores")
    g.tabla(
        doc,
        ["Navegador", "Dónde se usa", "Parámetros"],
        [
            ["Tabs (bottom)", "Inicio · Pendientes · Notas · Feriados · Perfil", "—"],
            ["Stack", "Splash → Login → Tabs; y detalles sobre las pestañas", "id de materia y de pendiente"],
        ],
        anchos=[3.5, 9.5, 4.0],
    )


def evidencia(doc):
    g.h1(doc, "5. AVANCE DE LA JORNADA 1")
    g.parrafo(
        doc,
        "El proyecto Expo ya abre en **Expo Go** en el celular y navega entre sus pantallas con datos de "
        "ejemplo. Se adjuntan dos capturas del estado actual como evidencia.",
        size=9.5,
    )
    g.captura(doc, "04-inicio.png", 1, "Inicio del estudiante (app actual en Expo Go)")
    g.captura(doc, "05-materias.png", 2, "Lista navegable con datos de ejemplo")

    g.h2(doc, "Checklist del cierre de jornada")
    for item, ok in [
        ("Ficha del proyecto con las funciones esenciales marcadas", True),
        ("API elegida y probada en el navegador (dirección y JSON anotados)", True),
        ("Diseño de tablas con tipos y clave primaria", True),
        ("Mapa de navegación dibujado", True),
        ("Proyecto Expo abriendo en Expo Go y navegando", True),
    ]:
        p = doc.add_paragraph()
        p.paragraph_format.space_after = g.Pt(1)
        g._font(p.add_run(("[X] " if ok else "[ ] ") + item), size=9)

    g.h2(doc, "Roles del grupo")
    g.tabla(
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
