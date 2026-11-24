# -*- coding: utf-8 -*-
"""Genera el informe de la Jornada 2 (Actividad 05) del proyecto final."""

import os

import docx
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.shared import Cm

import generar_documentos as g
from generar_actividad04 import codigo, h1, h2, parrafo, tabla, vineta, _font

DOCS = g.DOCS
CAPTURAS_DEFAULT = os.path.abspath(os.path.join(os.path.dirname(__file__), "capturas-dia2"))
CAPTURAS = r"C:\Users\IT\Music\Aplicaciones_Moviles_I\App_360\scripts\capturas-dia2" if os.path.exists(r"C:\Users\IT\Music\Aplicaciones_Moviles_I\App_360\scripts\capturas-dia2") else CAPTURAS_DEFAULT


def captura(doc, archivo, numero, nombre, ancho=5.8):
    ruta = os.path.join(CAPTURAS, archivo)
    if not os.path.exists(ruta):
        parrafo(doc, f"[Falta la captura {archivo}]")
        return
    p = doc.add_paragraph()
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p.paragraph_format.space_before = Cm(0.3)
    p.paragraph_format.space_after = Cm(0.1)
    p.add_run().add_picture(ruta, width=Cm(ancho))
    pie = doc.add_paragraph()
    pie.alignment = WD_ALIGN_PARAGRAPH.CENTER
    pie.paragraph_format.space_after = Cm(0.4)
    _font(pie.add_run(f"Figura {numero}. {nombre}"), size=10)


PANTALLAS = [
    ("02-inicio.png", "Inicio", "Resumen del estudiante: promedio, asistencia, clase en curso y accesos rápidos. "
                                 "El ícono de Pendientes muestra cuántos quedan sin completar."),
    ("03-pendientes.png", "Mis pendientes", "Lista de recordatorios con filtros (Todos, Pendientes, Hechos), "
                                             "prioridad por color y casilla para marcar como hecho."),
    ("09-formulario.png", "Formulario de pendiente", "Alta de un pendiente: título, materia, fecha y prioridad. "
                                                      "Es el formulario de la entidad principal."),
    ("10-formulario-errores.png", "Validación del formulario", "Al enviar vacío se muestran los mensajes en rojo y "
                                                                "el borde del campo cambia de color."),
    ("08-detalle-pendiente.png", "Detalle del pendiente", "Pantalla que recibe el id por parámetro y muestra el "
                                                          "registro elegido, con acciones de editar, marcar y eliminar."),
    ("04-notas.png", "Notas", "Consulta por trimestre con el promedio general y la escala ED/DA/DO/DP."),
    ("07-detalle-materia.png", "Detalle de materia", "Otra pantalla con parámetro: recibe el id de la materia y "
                                                     "muestra sus notas y criterios de evaluación."),
    ("05-feriados.png", "Feriados (API)", "Consumo de la API pública: muestra los 12 feriados de Bolivia 2026 y "
                                          "destaca el próximo. Los datos vienen de Nager.Date."),
    ("11-feriados-error.png", "Feriados sin conexión", "Estado de error de la API con botón Reintentar, probado "
                                                       "bloqueando la red."),
    ("06-perfil.png", "Perfil", "Datos del estudiante, preferencias de notificaciones y cierre de sesión."),
    ("01-login.png", "Acceso", "Pantalla de inicio de sesión con el acceso de prueba para la demostración."),
]


def avance(doc):
    h1(doc, "1. Avance de la Jornada 2", salto=True)
    parrafo(
        doc,
        "En esta jornada se completó la interfaz y la navegación de la aplicación. Todas las pantallas del mapa "
        "están maquetadas, se puede recorrer la app de principio a fin con datos de ejemplo y el formulario de "
        "la entidad principal valida antes de guardar.",
    )
    tabla(
        doc,
        ["Meta del día", "Estado"],
        [
            ["Todas las pantallas maquetadas y navegables", "Cumplido"],
            ["Al menos un detalle que recibe datos por parámetros", "Cumplido (materia y pendiente)"],
            ["Formulario que valida y muestra mensajes de error", "Cumplido"],
            ["Capturas de pantalla de cada pantalla", "Cumplido (11 capturas)"],
            ["Demostración rápida al docente", "Pendiente de la clase"],
        ],
    )
    parrafo(
        doc,
        "Además se adelantó el consumo de la API pública en la pantalla de Feriados, con sus tres estados "
        "(cargando, error con Reintentar y datos), porque la pantalla ya formaba parte del mapa.",
    )


def mapa(doc):
    h1(doc, "2. Mapa de navegación implementado")
    parrafo(
        doc,
        "Se mantiene lo aprobado en la ficha: pestañas para las secciones principales y pila para los detalles "
        "y el formulario. Los detalles reciben el identificador por parámetro.",
    )
    for linea in [
        "Stack raíz",
        " ├── Splash / Carga",
        " ├── Login",
        " ├── Formulario de pendiente      [param: id opcional para editar]",
        " ├── Detalle de pendiente         [param: id]",
        " ├── Detalle de materia           [param: id]",
        " └── Tabs",
        "      ├── Inicio",
        "      ├── Pendientes  →  formulario y detalle",
        "      ├── Notas       →  detalle de materia",
        "      ├── Feriados    →  API pública",
        "      └── Perfil",
    ]:
        p = doc.add_paragraph()
        p.paragraph_format.space_after = g.Pt(0)
        p.paragraph_format.line_spacing = 1.0
        p.paragraph_format.left_indent = Cm(0.5)
        _font(p.add_run(linea), size=10)

    tabla(
        doc,
        ["Navegador", "Se usa para", "Parámetros"],
        [
            ["Tabs (bottom)", "Inicio, Pendientes, Notas, Feriados y Perfil", "—"],
            ["Stack", "Detalle de materia, detalle de pendiente y formulario", "id de la materia o del pendiente"],
        ],
        anchos=[3.5, 9.5, 4.0],
    )


def pantallas(doc):
    h1(doc, "3. Pantallas y explicación", salto=False)
    parrafo(
        doc,
        "Cada captura corresponde a una pantalla del mapa y fue tomada de la aplicación en ejecución, con datos "
        "de ejemplo del estudiante Valeria Quispe Mamani (4º de Secundaria, paralelo B).",
    )
    for i, (archivo, nombre, texto) in enumerate(PANTALLAS, 1):
        h2(doc, f"3.{i} {nombre}")
        parrafo(doc, texto)
        captura(doc, archivo, i, nombre)


def validaciones(doc):
    h1(doc, "4. Formulario y reglas de validación", salto=True)
    tabla(
        doc,
        ["Campo", "Regla", "Mensaje al usuario"],
        [
            ["Título", "Obligatorio, mínimo 3 caracteres", "«Escribí al menos 3 caracteres.»"],
            ["Materia", "Debe elegirse una de la lista", "«Elegí una materia.»"],
            ["Fecha", "Obligatoria, formato dd/mm/aaaa y fecha real", "«La fecha es obligatoria.» / "
                                                                     "«Usá el formato dd/mm/aaaa con una fecha real.»"],
            ["Prioridad", "Alta, media o baja (media por defecto)", "No requiere mensaje: siempre hay una opción."],
        ],
        anchos=[3.0, 6.5, 7.5],
    )
    parrafo(
        doc,
        "Los errores se muestran debajo de cada campo, en rojo, y el borde del campo cambia para que se identifique "
        "a simple vista. El formulario no guarda nada hasta que todas las reglas se cumplen.",
    )

    h2(doc, "Parámetros entre pantallas")
    tabla(
        doc,
        ["Pantalla destino", "Parámetro", "Uso"],
        [
            ["Detalle de materia", "id de la materia", "Busca la materia y muestra sus notas."],
            ["Detalle de pendiente", "id del pendiente", "Muestra el recordatorio elegido y permite editarlo o borrarlo."],
            ["Formulario de pendiente", "id opcional", "Sin id crea uno nuevo; con id carga los datos para editar."],
        ],
        anchos=[5.0, 4.0, 8.0],
    )


def cierre(doc):
    h1(doc, "5. Checklist del cierre de jornada", salto=True)
    for item in [
        "Todas las pantallas del mapa maquetadas y navegables sin errores",
        "Al menos un detalle que recibe datos por parámetros (materia y pendiente)",
        "Formulario que valida y muestra mensajes de error",
        "Capturas de pantalla de cada pantalla (11 capturas)",
        "Demostración rápida de 2 minutos (se realiza en clase)",
    ]:
        vineta(doc, "[X] " + item)

    h2(doc, "Cómo probar la aplicación")
    parrafo(
        doc,
        "Abrir Expo Go en el celular, conectado a la misma red Wi-Fi que la computadora, e ingresar a "
        "exp://172.30.19.174:8081. En el login usar el bloque Acceso de prueba y el botón Ingresar a la demo. "
        "Luego recorrer las pestañas y crear un pendiente para ver la validación y el guardado.",
    )

    h2(doc, "Qué falta para las siguientes jornadas")
    tabla(
        doc,
        ["Jornada", "Pendiente"],
        [
            ["Día 3 (vie 20)", "Reemplazar los arreglos por SQLite: tabla pendientes con INSERT, SELECT, UPDATE y "
                               "DELETE, y probar la persistencia cerrando la app."],
            ["Día 4 (lun 23)", "Unir los feriados de la API con los pendientes guardados (patrón B) y congelar "
                               "funcionalidades."],
            ["Día 5 (mar 24)", "Pruebas en dos celulares, PDF final, ZIP y ensayo."],
        ],
        anchos=[3.5, 13.0],
    )


def main():
    destino = os.path.join(DOCS, "Actividad-05-Grupo-7-Dia-2-Informe.docx")
    g.crear_caratula(destino, "ACTIVIDAD 05 \u2013 DÍA 2 \u2013 INFORME")
    doc = docx.Document(destino)
    g.limpiar_caratula(doc)

    for seccion in doc.sections:
        seccion.top_margin = Cm(2.5)
        seccion.bottom_margin = Cm(2.5)
        seccion.left_margin = Cm(3.0)
        seccion.right_margin = Cm(3.0)

    avance(doc)
    mapa(doc)
    pantallas(doc)
    validaciones(doc)
    cierre(doc)

    doc.save(destino)
    print(destino)
    return destino


if __name__ == "__main__":
    main()
