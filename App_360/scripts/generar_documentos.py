# -*- coding: utf-8 -*-
"""
Genera las Actividades 02 y 03 de Aplicaciones Moviles I (Grupo 7)
usando la caratula institucional de la UPDS y las capturas de la app CSB 360.

Uso:
    python generar_documentos.py
"""

import os
import re
import zipfile

import docx
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from docx.shared import Cm, Pt, RGBColor

DOCS_DEFAULT = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", "Docs"))
DOCS = r"C:\Users\IT\Music\Aplicaciones_Moviles_I\App_360\Docs" if os.path.exists(r"C:\Users\IT\Music\Aplicaciones_Moviles_I\App_360\Docs") else DOCS_DEFAULT
PLANTILLA = os.path.join(DOCS, "Caratula_Institucional.docx")
CAPTURAS_DEFAULT = os.path.abspath(os.path.join(os.path.dirname(__file__), "capturas"))
CAPTURAS = r"C:\Users\IT\Music\Aplicaciones_Moviles_I\App_360\scripts\capturas" if os.path.exists(r"C:\Users\IT\Music\Aplicaciones_Moviles_I\App_360\scripts\capturas") else CAPTURAS_DEFAULT

INTEGRANTES = [
    "Snaider Brayan Quispe Janco",
    "Jonathan Smith Soraire Rojas",
    "Joel David Cerrogrande Ortega",
    "Jose Alejandro Benitez Torrez",
]

GUION = "\u2013"
AZUL = RGBColor(0x1F, 0x38, 0x64)
GRIS = RGBColor(0x44, 0x44, 0x44)


# --------------------------------------------------------------------------- #
# Caratula
# --------------------------------------------------------------------------- #
def crear_caratula(destino: str, trabajo: str):
    with zipfile.ZipFile(PLANTILLA) as origen, zipfile.ZipFile(destino, "w", zipfile.ZIP_DEFLATED) as salida:
        for item in origen.infolist():
            data = origen.read(item.filename)
            if item.filename == "word/document.xml":
                xml = data.decode("utf-8")
                xml = xml.replace("PRACTICO ADMINISTRACION DE BASE DE DATOS I", trabajo)
                xml = xml.replace("Snaider Brayan Quispe Janco", ", ".join(INTEGRANTES))
                xml = xml.replace(">AGOSTO<", ">NOVIEMBRE<")
                xml = xml.replace(" 2025</w:t>", " 2026</w:t>")
                data = xml.encode("utf-8")
            elif item.filename == "word/header1.xml":
                # La plantilla trae un "1" fijo: lo cambiamos por un campo PAGE.
                xml = data.decode("utf-8")
                original = (
                    '<w:r><w:rPr><w:color w:val="FFFFFF"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr>'
                    "<w:t>1</w:t></w:r>"
                )
                campo = (
                    '<w:r><w:rPr><w:color w:val="FFFFFF"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr>'
                    '<w:fldChar w:fldCharType="begin"/></w:r>'
                    '<w:r><w:rPr><w:color w:val="FFFFFF"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr>'
                    '<w:instrText xml:space="preserve"> PAGE </w:instrText></w:r>'
                    '<w:r><w:rPr><w:color w:val="FFFFFF"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr>'
                    '<w:fldChar w:fldCharType="separate"/></w:r>'
                    '<w:r><w:rPr><w:color w:val="FFFFFF"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr>'
                    "<w:t>1</w:t></w:r>"
                    '<w:r><w:rPr><w:color w:val="FFFFFF"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr>'
                    '<w:fldChar w:fldCharType="end"/></w:r>'
                )
                if original in xml:
                    xml = xml.replace(original, campo)
                data = xml.encode("utf-8")
            salida.writestr(item, data)


# --------------------------------------------------------------------------- #
# Utilidades de formato
# --------------------------------------------------------------------------- #
def limpiar_caratula(doc):
    """Quita saltos de pagina y los parrafos vacios del final de la plantilla."""
    for par in list(doc.paragraphs):
        for br in par._p.findall(".//" + qn("w:br")):
            if br.get(qn("w:type")) == "page":
                br.getparent().remove(br)
    for par in reversed(list(doc.paragraphs)):
        if par.text.strip() == "" and "<w:drawing" not in par._p.xml:
            par._p.getparent().remove(par._p)
        else:
            break


def _font(run, size=10, bold=False, color=None):
    run.font.name = "Arial"
    run.font.size = Pt(size)
    run.font.bold = bold
    run.font.color.rgb = color or RGBColor(0, 0, 0)


def h1(doc, texto, size=13, salto=False):
    p = doc.add_paragraph()
    p.paragraph_format.space_before = Pt(8)
    p.paragraph_format.space_after = Pt(3)
    if salto:
        p.paragraph_format.page_break_before = True
    _font(p.add_run(texto), size=size, bold=True, color=AZUL)
    return p


def h2(doc, texto, size=10.5):
    p = doc.add_paragraph()
    p.paragraph_format.space_before = Pt(5)
    p.paragraph_format.space_after = Pt(2)
    _font(p.add_run(texto), size=size, bold=True, color=AZUL)
    return p


def _rich(p, texto, size, bold=False):
    for trozo in re.split(r"(\*\*.*?\*\*)", texto):
        if not trozo:
            continue
        if trozo.startswith("**") and trozo.endswith("**"):
            _font(p.add_run(trozo[2:-2]), size=size, bold=True)
        else:
            _font(p.add_run(trozo), size=size, bold=bold)


def parrafo(doc, texto, size=10, justificado=True, space_after=3, line=1.05):
    p = doc.add_paragraph()
    p.paragraph_format.space_after = Pt(space_after)
    p.paragraph_format.line_spacing = line
    if justificado:
        p.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY
    _rich(p, texto, size)
    return p


def vineta(doc, texto, size=9.5):
    p = doc.add_paragraph()
    p.paragraph_format.left_indent = Cm(0.5)
    p.paragraph_format.first_line_indent = Cm(-0.5)
    p.paragraph_format.space_after = Pt(1.5)
    p.paragraph_format.line_spacing = 1.03
    _font(p.add_run("\u2022 "), size=size)
    _rich(p, texto, size)
    return p


def numerada(doc, numero, texto, size=9.5):
    p = doc.add_paragraph()
    p.paragraph_format.left_indent = Cm(0.55)
    p.paragraph_format.first_line_indent = Cm(-0.55)
    p.paragraph_format.space_after = Pt(1.5)
    p.paragraph_format.line_spacing = 1.03
    _font(p.add_run(f"{numero}. "), size=size, bold=True)
    _rich(p, texto, size)
    return p


def _bordes(tabla):
    tblPr = tabla._tbl.tblPr
    borders = OxmlElement("w:tblBorders")
    for borde in ("top", "left", "bottom", "right", "insideH", "insideV"):
        el = OxmlElement(f"w:{borde}")
        el.set(qn("w:val"), "single")
        el.set(qn("w:sz"), "4")
        el.set(qn("w:space"), "0")
        el.set(qn("w:color"), "9CA3AF")
        borders.append(el)
    tblPr.append(borders)


def tabla(doc, encabezados, filas, anchos=None, size=8.5):
    t = doc.add_table(rows=1, cols=len(encabezados))
    _bordes(t)
    t.autofit = True
    hdr = t.rows[0].cells
    for i, texto in enumerate(encabezados):
        hdr[i].text = ""
        p = hdr[i].paragraphs[0]
        p.paragraph_format.space_after = Pt(0)
        _font(p.add_run(texto), size=size, bold=True, color=RGBColor(0xFF, 0xFF, 0xFF))
        shd = OxmlElement("w:shd")
        shd.set(qn("w:fill"), "1F3864")
        hdr[i]._tc.get_or_add_tcPr().append(shd)
    for fila in filas:
        celdas = t.add_row().cells
        for i, texto in enumerate(fila):
            celdas[i].text = ""
            p = celdas[i].paragraphs[0]
            p.paragraph_format.space_after = Pt(0)
            p.paragraph_format.line_spacing = 1.0
            _font(p.add_run(str(texto)), size=size)
    if anchos:
        for i, ancho in enumerate(anchos):
            for row in t.rows:
                row.cells[i].width = Cm(ancho)
    doc.add_paragraph().paragraph_format.space_after = Pt(0)
    return t


def captura(doc, archivo, numero, nombre):
    ruta = os.path.join(CAPTURAS, archivo)
    if not os.path.exists(ruta):
        parrafo(doc, f"[ Captura pendiente: {nombre} ]", size=9, justificado=False)
        return
    p = doc.add_paragraph()
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p.paragraph_format.space_before = Pt(6)
    p.paragraph_format.space_after = Pt(2)
    p.add_run().add_picture(ruta, width=Cm(6.1))

    pie = doc.add_paragraph()
    pie.alignment = WD_ALIGN_PARAGRAPH.CENTER
    pie.paragraph_format.space_after = Pt(0)
    _font(pie.add_run(f"Figura {numero}. {nombre}"), size=8.5, color=GRIS)


# --------------------------------------------------------------------------- #
# ACTIVIDAD 02 (una pagina de contenido)
# --------------------------------------------------------------------------- #
def actividad_02():
    destino = os.path.join(DOCS, "Actividad-02-Grupo-7-Idea de Proyecto.docx")
    crear_caratula(destino, f"ACTIVIDAD 02 {GUION} IDEA DE PROYECTO")
    doc = docx.Document(destino)
    limpiar_caratula(doc)

    h1(doc, "1. NOMBRE DE LA APLICACIÓN", size=12, salto=True)
    parrafo(
        doc,
        "**CSB 360 Estudiante** \u2014 aplicación móvil del Sistema de Gestión Escolar 360 de la Unidad "
        "Educativa San Bernardo (Tarija, Bolivia). Es el complemento móvil del sistema web institucional, "
        "orientado al rol **Estudiante**.",
        size=9.5,
    )

    h1(doc, "2. PROBLEMA ESPECÍFICO QUE RESUELVE Y BENEFICIARIOS DIRECTOS", size=12)
    parrafo(
        doc,
        "El colegio ya cuenta con el sistema web 360, pero **solo se accede desde una computadora**. Por eso "
        "el estudiante y su familia dependen de ir al colegio, preguntar en Secretaría o esperar la libreta "
        "impresa para conocer notas, faltas, horarios y comunicados. La inscripción se realiza con documentos "
        "en papel que se extravían o se entregan fuera de plazo, y los pagos quedan en duda hasta que "
        "contabilidad los verifica. El resultado son filas y trámites presenciales, información dispersa, "
        "errores de registro y estudiantes que se enteran tarde de su situación académica, especialmente "
        "quienes viven en zonas alejadas o no tienen computadora en casa.",
        size=9.5,
    )
    parrafo(
        doc,
        "**Beneficiarios directos:** estudiantes de 1º a 6º de secundaria y sus familias. "
        "**Indirectos:** docentes, Secretaría, Dirección y Regente, que reducen consultas repetitivas y "
        "reciben documentación digitalizada y ordenada.",
        size=9.5,
    )

    h1(doc, "3. FUNCIONALIDADES PRINCIPALES (CORE FEATURES)", size=12)
    features = [
        "**Autenticación institucional por roles:** ingreso con la cuenta de Google Workspace "
        "(@cosabe.edu.bo) y token seguro (Sanctum).",
        "**Panel del estudiante:** promedio general, porcentaje de asistencia, curso y clase en curso.",
        "**Notas y boletín:** consulta por trimestre y criterio (SE, SA, HA, AU), escala ED/DA/DO/DP y "
        "boletín en PDF.",
        "**Asistencia y horario:** historial con los cinco estados y grilla semanal por materia.",
        "**Comunicados:** bandeja con destacados, marcado de leído y notificaciones push.",
        "**Inscripción en línea (funcionalidad estrella):** cinco documentos con cámara o galería, "
        "aceptación del contrato y seguimiento del estado.",
        "**Mensualidades:** estado de cuenta y envío del comprobante de pago desde la cámara.",
        "**Perfil y seguridad:** datos de contacto, preferencias de notificaciones y cierre de sesión.",
    ]
    for i, f in enumerate(features, 1):
        numerada(doc, i, f)

    h1(doc, "4. MAPA PRELIMINAR DE TECNOLOGÍAS", size=12)
    tabla(
        doc,
        ["Capa", "Tecnología", "Uso en el proyecto"],
        [
            ["Frontend móvil", "React Native + Expo (SDK 58), TypeScript, Expo Router",
             "App multiplataforma Android/iOS con tabs y stack."],
            ["Sesión", "Zustand + Expo SecureStore",
             "Token cifrado en el dispositivo y estado del usuario."],
            ["API REST", "Laravel 12 + Sanctum (/api/v1)",
             "Endpoints de notas, asistencia, horarios, comunicados, pagos e inscripción."],
            ["Base de datos", "MySQL (base csb360)",
             "Misma base del sistema 360: no se duplican datos."],
            ["Archivos y PDF", "Storage privado + FormData · DomPDF",
             "Documentos, comprobantes y boletín/contrato en PDF."],
            ["Servicios nativos", "Expo Camera · Expo Notifications · Google OAuth 2.0",
             "Cámara para documentos, push y login @cosabe.edu.bo."],
        ],
        anchos=[3.0, 6.3, 7.2],
    )
    parrafo(
        doc,
        "**Arquitectura:** App Expo \u2192 HTTPS/JSON \u2192 API REST v1 (Laravel + Sanctum) \u2192 MySQL. "
        "La API se agrega al sistema 360 reutilizando modelos, roles y permisos; la app **no reemplaza** "
        "al sistema web, lo acompaña.",
        size=9,
    )

    doc.save(destino)
    return destino


# --------------------------------------------------------------------------- #
# ACTIVIDAD 03
# --------------------------------------------------------------------------- #
PANTALLAS = [
    {
        "n": 1,
        "nombre": "Splash / Carga",
        "archivo": "01-splash.png",
        "objetivo": "Mostrar la identidad institucional mientras se valida la sesión guardada.",
        "elementos": [
            "Escudo del colegio y nombre CSB 360 sobre fondo verde institucional.",
            "Indicador de carga y texto \u201cVerificando sesión\u2026\u201d.",
            "Versión de la aplicación al pie.",
        ],
        "acciones": "Lee el token de SecureStore: si es válido va al Inicio; si no, al Login. "
                    "Valida además el estado activo de la cuenta.",
        "api": "GET /api/v1/me",
    },
    {
        "n": 2,
        "nombre": "Inicio de Sesión (Login)",
        "archivo": "02-login.png",
        "objetivo": "Autenticar al estudiante con su cuenta institucional y emitir un token de acceso.",
        "elementos": [
            "Botón principal \u201cContinuar con Google\u201d y aviso del dominio @cosabe.edu.bo.",
            "Acceso de prueba con correo y contraseña para la demostración.",
            "Mensajes de error para cuenta no registrada, inactiva o bloqueada.",
        ],
        "acciones": "Valida credenciales, registra el intento en el historial de seguridad y guarda el token "
                    "en SecureStore. Bloqueo temporal tras cinco intentos fallidos.",
        "api": "POST /api/v1/auth/login \u00b7 POST /api/v1/auth/google",
    },
    {
        "n": 3,
        "nombre": "Registro de Usuario / Primer ingreso",
        "archivo": "03-datos.png",
        "objetivo": "Completar el perfil del estudiante en su primer acceso y registrar el consentimiento de datos.",
        "elementos": [
            "Datos de solo lectura: nombre, curso, paralelo y código de estudiante.",
            "Campos editables: teléfono, dirección y fotografía.",
            "Rol visible (Estudiante) y aceptación del consentimiento ARCO.",
        ],
        "acciones": "Actualiza el perfil y deja constancia del consentimiento. Las cuentas las crea el colegio: "
                    "no existe auto-registro de estudiantes.",
        "api": "PATCH /api/v1/me \u00b7 POST /api/v1/consentimientos",
    },
    {
        "n": 4,
        "nombre": "Inicio (Home / Panel principal)",
        "archivo": "04-inicio.png",
        "objetivo": "Resumir en una sola vista la situación académica y económica del estudiante.",
        "elementos": [
            "Saludo personalizado, curso y campana de notificaciones.",
            "Tarjeta con promedio general y anillo de progreso.",
            "Indicadores: asistencia, materias y trimestre vigente.",
            "Bloque \u201cAhora\u201d con la clase en curso y accesos rápidos.",
        ],
        "acciones": "Navega a Notas, Asistencia, Pagos e Inscripción; abre el detalle de un comunicado.",
        "api": "GET /api/v1/me/resumen",
    },
    {
        "n": 5,
        "nombre": "Catálogo / Lista de materias",
        "archivo": "05-materias.png",
        "objetivo": "Listar las materias del curso con su promedio y docente.",
        "elementos": [
            "Buscador y selector de trimestre (1T / 2T / 3T).",
            "Tarjetas por materia con inicial, docente y promedio coloreado según la escala.",
            "Estado vacío cuando no hay materias asignadas.",
        ],
        "acciones": "Filtra por trimestre y abre el detalle de la materia.",
        "api": "GET /api/v1/materias?trimestre=2",
    },
    {
        "n": 6,
        "nombre": "Detalle de materia",
        "archivo": "06-detalle-materia.png",
        "objetivo": "Explicar de dónde sale la nota de una materia.",
        "elementos": [
            "Encabezado con materia, docente y promedio.",
            "Pestañas Resumen, Notas y Asistencia.",
            "Desglose por criterios SE (10), SA (45), HA (40) y AU (5).",
        ],
        "acciones": "Consulta notas por actividad y su fecha; muestra aviso si el trimestre no está publicado.",
        "api": "GET /api/v1/materias/{id}",
    },
    {
        "n": 7,
        "nombre": "Notas / Boletín",
        "archivo": "07-notas.png",
        "objetivo": "Consultar el rendimiento del trimestre y descargar el boletín oficial.",
        "elementos": [
            "Selector de trimestre y promedio general destacado.",
            "Lista de materias con su nota y sigla de la escala ED/DA/DO/DP.",
            "Leyenda de la escala oficial y botón \u201cDescargar boletín PDF\u201d.",
        ],
        "acciones": "Cambia de trimestre, consulta cada materia y descarga el documento oficial SIE.",
        "api": "GET /api/v1/notas?trimestre=2 \u00b7 GET /api/v1/boletin/pdf",
    },
    {
        "n": 8,
        "nombre": "Asistencia",
        "archivo": "08-asistencia.png",
        "objetivo": "Mostrar el historial de asistencia con sus cinco estados.",
        "elementos": [
            "Resumen porcentual y contadores por estado.",
            "Calendario mensual con puntos de color.",
            "Lista de registros con fecha, materia y justificación.",
        ],
        "acciones": "Filtra por mes y abre el detalle de una falta para ver o adjuntar la justificación.",
        "api": "GET /api/v1/asistencia \u00b7 POST /api/v1/asistencia/{id}/justificacion",
    },
    {
        "n": 9,
        "nombre": "Horario de clases",
        "archivo": "09-horario.png",
        "objetivo": "Ubicar al estudiante en su semana y en la clase que está cursando.",
        "elementos": [
            "Selector de lunes a viernes.",
            "Bloques con hora, materia, docente y aula.",
            "Bloque actual resaltado con la etiqueta \u201cAhora\u201d.",
        ],
        "acciones": "Cambia de día y abre el detalle de la materia desde el bloque.",
        "api": "GET /api/v1/horario",
    },
    {
        "n": 10,
        "nombre": "Comunicados",
        "archivo": "10-comunicados.png",
        "objetivo": "Centralizar los avisos de Dirección, docentes y administración.",
        "elementos": [
            "Pestañas Todos, Sin leer y Destacados.",
            "Tarjetas con imagen, título, fecha y chip NUEVO.",
            "Estado vacío cuando no hay comunicados.",
        ],
        "acciones": "Filtra por categoría y abre el comunicado; el contador de no leídos se actualiza.",
        "api": "GET /api/v1/comunicados",
    },
    {
        "n": 11,
        "nombre": "Detalle de comunicado",
        "archivo": "11-comunicado.png",
        "objetivo": "Leer el comunicado completo y descargar sus adjuntos.",
        "elementos": [
            "Imagen de portada, título, autor y fecha.",
            "Cuerpo del comunicado y adjuntos descargables.",
            "Estado leído / no leído.",
        ],
        "acciones": "Marca el comunicado como leído y descarga los adjuntos.",
        "api": "GET /api/v1/comunicados/{id} \u00b7 PATCH /api/v1/comunicados/{id}/leido",
    },
    {
        "n": 12,
        "nombre": "Formulario de solicitud / Inscripción en línea",
        "archivo": "12-inscripcion.png",
        "objetivo": "Completar la inscripción en cinco pasos con validación en cada uno.",
        "elementos": [
            "Indicador de pasos: Datos, Documentos, Domicilio, Contrato y Confirmar.",
            "Lista de los cinco documentos con estado pendiente, entregado o rechazado.",
            "Vista previa del contrato y casilla de aceptación.",
        ],
        "acciones": "Valida campos obligatorios, sube documentos desde la cámara, registra la aceptación "
                    "del contrato y muestra el estado final con número de trámite.",
        "api": "POST /api/v1/inscripcion \u00b7 POST /api/v1/inscripcion/{id}/documentos \u00b7 "
               "POST /api/v1/inscripcion/{id}/contrato",
    },
    {
        "n": 13,
        "nombre": "Captura de evidencia / Cámara",
        "archivo": "13-camara.png",
        "objetivo": "Digitalizar documentos y comprobantes sin escáner.",
        "elementos": [
            "Vista de cámara con marco guía y texto de encuadre.",
            "Controles de flash, cambio de cámara y disparo.",
            "Previsualización con Repetir / Usar foto y barra de subida.",
        ],
        "acciones": "Solicita permiso de cámara, captura la imagen, la comprime y la envía por FormData. "
                    "Contempla permiso denegado y error de red.",
        "api": "POST /api/v1/inscripcion/documentos (multipart/form-data)",
    },
    {
        "n": 14,
        "nombre": "Pagos / Mensualidades",
        "archivo": "14-pagos.png",
        "objetivo": "Consultar el estado de cuenta y enviar el comprobante de pago.",
        "elementos": [
            "Resumen de estado de cuenta y deuda pendiente.",
            "Tarjetas por mes con monto en Bs y estado pagado, pendiente o vencido.",
            "Botón \u201cSubir comprobante\u201d que abre la cámara.",
        ],
        "acciones": "Consulta el detalle de cada cuota y adjunta el comprobante; el pago queda en "
                    "verificación hasta que Secretaría lo aprueba.",
        "api": "GET /api/v1/pagos \u00b7 POST /api/v1/pagos/{id}/comprobante",
    },
    {
        "n": 15,
        "nombre": "Perfil de Usuario",
        "archivo": "15-perfil.png",
        "objetivo": "Gestionar los datos personales, las notificaciones y la sesión.",
        "elementos": [
            "Avatar, nombre, curso y código de estudiante.",
            "Secciones de datos personales, notificaciones y seguridad.",
            "Botón de cierre de sesión y versión de la app.",
        ],
        "acciones": "Edita datos de contacto, activa o desactiva notificaciones y revoca el token al salir.",
        "api": "GET /api/v1/me \u00b7 PATCH /api/v1/me \u00b7 POST /api/v1/auth/logout",
    },
]


def actividad_03():
    destino = os.path.join(DOCS, "Actividad-03-Grupo-7-Mis Pantallas.docx")
    crear_caratula(destino, f"ACTIVIDAD 03 {GUION} MIS PANTALLAS")
    doc = docx.Document(destino)
    limpiar_caratula(doc)

    h1(doc, "PROPUESTA DE ARQUITECTURA DE PANTALLAS \u2014 CSB 360 ESTUDIANTE", salto=True)
    parrafo(
        doc,
        "La aplicación se organiza en cuatro módulos que siguen la propuesta del enunciado: "
        "**autenticación y onboarding**, **flujo principal de negocio**, **integración de APIs nativas** "
        "y **módulo transaccional y de gestión**. La navegación principal usa una barra inferior de cinco "
        "pestañas \u2014Inicio, Notas, Asistencia, Comunicados y Perfil\u2014 y las pantallas de detalle se "
        "abren en pila (stack) sobre ella.",
    )

    h2(doc, "Mapa de navegación")
    parrafo(
        doc,
        "Splash \u2192 Login \u2192 Primer ingreso \u2192 **Inicio**\n"
        "Inicio \u2192 Mis materias \u2192 Detalle de materia\n"
        "Inicio \u2192 Inscripción (Datos \u2192 Documentos \u2192 Cámara \u2192 Domicilio \u2192 Contrato \u2192 Confirmar)\n"
        "Inicio \u2192 Pagos \u2192 Cámara (comprobante)\n"
        "Inicio \u2192 Comunicados \u2192 Detalle de comunicado\n"
        "Perfil \u2192 Notificaciones / Seguridad / Cerrar sesión",
        size=9.5,
        justificado=False,
    )

    h2(doc, "Cobertura de los módulos exigidos")
    tabla(
        doc,
        ["Módulo del enunciado", "Pantallas de la app"],
        [
            ["Autenticación y onboarding", "1 Splash \u00b7 2 Login \u00b7 3 Primer ingreso"],
            ["Dashboard y flujo de negocio", "4 Inicio \u00b7 5 Mis materias \u00b7 6 Detalle de materia"],
            ["Integración de APIs externas", "12 Inscripción \u00b7 13 Cámara (FormData)"],
            ["Transaccional y de gestión", "7 Notas \u00b7 8 Asistencia \u00b7 9 Horario \u00b7 10-11 Comunicados \u00b7 "
                                           "14 Pagos \u00b7 15 Perfil"],
        ],
        anchos=[6.0, 10.5],
    )
    parrafo(
        doc,
        "Las capturas corresponden al prototipo navegable desarrollado para el proyecto, con datos de "
        "ejemplo (estudiante Valeria Quispe Mamani, 4º de Secundaria \u2013 Paralelo B).",
        size=9,
    )

    for pantalla in PANTALLAS:
        h1(doc, f"{pantalla['n']}. {pantalla['nombre'].upper()}", salto=True)
        parrafo(doc, f"**Objetivo:** {pantalla['objetivo']}")
        h2(doc, "Elementos principales")
        for e in pantalla["elementos"]:
            vineta(doc, e, size=9.5)
        h2(doc, "Acciones y validaciones")
        parrafo(doc, pantalla["acciones"], size=9.5)
        h2(doc, "Servicio de la API")
        parrafo(doc, pantalla["api"], size=9.5, justificado=False)
        captura(doc, pantalla["archivo"], pantalla["n"], pantalla["nombre"])

    doc.save(destino)
    return destino


if __name__ == "__main__":
    print(actividad_02())
    print(actividad_03())
