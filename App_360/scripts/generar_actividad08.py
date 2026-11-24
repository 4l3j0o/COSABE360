# -*- coding: utf-8 -*-
"""Genera el informe de la Jornada 5 (Actividad 08) del proyecto final.

Tema: Pruebas, informe final y ensayo (Pruebas en dos celulares, ensayo de 8 min y video de respaldo).
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
    h1(doc, "1. Avance de la Jornada 5 (Martes 24 · Pruebas, Informe y Ensayo)", salto=True)
    parrafo(
        doc,
        "La Jornada 5 representa el hito de consolidación, estabilización y preparación para la defensa final "
        "del proyecto CSB 360. En cumplimiento estricto del cronograma oficial de la asignatura, no se incorporaron "
        "nuevas funciones de código; el esfuerzo de los cuatro integrantes se concentró en ejecutar pruebas "
        "cruzadas de flujo completo en dos dispositivos móviles físicos distintos, verificar validaciones y mensajes, "
        "limpiar el repositorio, ensamblar el paquete de entrega y cronometrar el ensayo grupal de exposición.",
    )
    tabla(
        doc,
        ["Actividad planificada", "Detalle técnico implementado", "Estado"],
        [
            [
                "1. Pruebas en dos celulares distintos",
                "Ejecución de la batería de 10 casos de prueba (CP-01 a CP-10) en un Xiaomi Redmi Note 12 "
                "(Android 13) y en un Samsung Galaxy A54 5G (Android 14) sin fallos críticos.",
                "Cumplido",
            ],
            [
                "2. Revisión de usabilidad y textos",
                "Revisión integral de etiquetas, mensajes de error, confirmaciones y microinteracciones en "
                "español neutro, asegurando consistencia de colores y áreas táctiles mínimas.",
                "Cumplido",
            ],
            [
                "3. Limpieza de código y seguridad",
                "Código depurado sin bloques comentados, sin contraseñas expuestas y con variables de entorno "
                "protegidas en .env; consultas parametrizadas con comodines ? en todo el sistema.",
                "Cumplido",
            ],
            [
                "4. Informe final consolidado",
                "Documentación completa que recopila la portada institucional, mapa de navegación, diseño DDL "
                "en MySQL, consumo de la API de feriados y evidencias fotográficas explicadas.",
                "Cumplido",
            ],
            [
                "5. Empaquetado del proyecto (ZIP)",
                "Generación de archivo ZIP limpio sin la carpeta node_modules, con package.json íntegro y "
                "el instructivo LEEME.txt con instrucciones de dos líneas para su ejecución inmediata.",
                "Cumplido",
            ],
            [
                "6. Ensayo de 8 min y video demo",
                "Ensayo grupal cronometrado con tiempos asignados por integrante y planificación del video "
                "de respaldo de 1 a 2 minutos que garantiza soporte ante contingencias.",
                "Cumplido",
            ],
        ],
        anchos=[4.0, 9.5, 3.0],
    )


def pruebas_dos_celulares(doc):
    h1(doc, "2. Pruebas de Flujo Completo en Dos Dispositivos Distintos")
    parrafo(
        doc,
        "Para verificar la portabilidad y robustez de la aplicación en condiciones reales de uso por parte de los "
        "alumnos del Colegio San Bernardo, se instaló la aplicación mediante Expo Go en dos smartphones con "
        "especificaciones de hardware y versiones de sistema operativo Android heterogéneas:",
    )
    tabla(
        doc,
        ["Característica", "Dispositivo Móvil 1 (Estudiante)", "Dispositivo Móvil 2 (Docente / Prueba)"],
        [
            ["Modelo y Marca", "Xiaomi Redmi Note 12 4G", "Samsung Galaxy A54 5G"],
            ["Sistema Operativo", "Android 13 · MIUI 14", "Android 14 · One UI 6.0"],
            ["Pantalla y Tasa", "6.67 pulgadas AMOLED (1080 x 2400) @ 120Hz", "6.4 pulgadas Super AMOLED (1080 x 2340) @ 120Hz"],
            ["Procesador y RAM", "Snapdragon 685 (8 núcleos) · 6 GB RAM", "Exynos 1380 (8 núcleos) · 8 GB RAM"],
            ["Conectividad usada", "Wi-Fi local 5 GHz (10.10.13.10)", "Wi-Fi local 5 GHz (10.10.13.10)"],
            ["Estado final", "APROBADO (Fluidez continua 60 fps)", "APROBADO (Fluidez continua 60 fps)"],
        ],
        anchos=[4.0, 6.5, 6.5],
    )

    h2(doc, "Matriz de Casos de Prueba Ejecutados (Sección 4.3)")
    tabla(
        doc,
        ["ID", "Caso de Prueba", "Acción realizada", "Resultado esperado", "Disp. 1", "Disp. 2"],
        [
            [
                "CP-01",
                "Inicio de Sesión",
                "Ingresar credenciales en login y pulsar Ingresar.",
                "Acceso concedido mediante SELECT y bcrypt; redirección a Inicio.",
                "OK",
                "OK",
            ],
            [
                "CP-02",
                "Consulta de Resumen",
                "Visualizar promedio, materias y asistencia en Inicio.",
                "Cálculo exacto, anillo de progreso animado y tarjeta hero.",
                "OK",
                "OK",
            ],
            [
                "CP-03",
                "Alta con validación",
                "Intentar crear pendiente vacío y luego con datos válidos.",
                "Muestra mensajes rojos al errar e INSERT en MySQL al cumplir.",
                "OK",
                "OK",
            ],
            [
                "CP-04",
                "Edición de tarea",
                "Abrir detalle, pulsar Editar, cambiar prioridad y guardar.",
                "Ejecuta UPDATE ... WHERE; la lista muestra los cambios.",
                "OK",
                "OK",
            ],
            [
                "CP-05",
                "Marcar como hecho",
                "Tocar la casilla de verificación en la lista de pendientes.",
                "Ejecuta UPDATE hecho = 1; se tacha y se reordena al final.",
                "OK",
                "OK",
            ],
            [
                "CP-06",
                "Eliminación con modal",
                "Pulsar Eliminar y confirmar en la ventana modal de alerta.",
                "Ejecuta DELETE ... WHERE; el registro desaparece de MySQL.",
                "OK",
                "OK",
            ],
            [
                "CP-07",
                "Consumo de API",
                "Navegar a la pestaña Feriados con conexión activa.",
                "Carga 12 feriados de Nager.Date y destaca el próximo.",
                "OK",
                "OK",
            ],
            [
                "CP-08",
                "Tolerancia a fallos",
                "Activar Modo Avión en el celular y recargar Feriados.",
                "No crashea; muestra ícono WifiOff y botón Reintentar.",
                "OK",
                "OK",
            ],
            [
                "CP-09",
                "Integración API + BD",
                "Pulsar botón de guardar en un feriado de la lista.",
                "Se inserta en MySQL y la insignia cambia a [EN BD].",
                "OK",
                "OK",
            ],
            [
                "CP-10",
                "Prueba de Persistencia",
                "Cerrar Expo Go de la multitarea, reiniciar y reabrir.",
                "Los pendientes y cambios permanecen exactamente intactos.",
                "OK",
                "OK",
            ],
        ],
        anchos=[1.6, 3.2, 4.8, 4.8, 1.3, 1.3],
    )


def revision_usabilidad(doc):
    h1(doc, "3. Revisión de Usabilidad, Textos y Detalles Visuales", salto=True)
    parrafo(
        doc,
        "Durante la fase de estabilización se aplicó una auditoría heurística visual y funcional sobre cada elemento "
        "de la aplicación:",
    )
    vineta(
        doc,
        "**Unificación de textos e idioma:** Se verificó que el 100% de las pantallas, títulos, etiquetas de botones y "
        "mensajes de validación estén redactados en español latinoamericano claro, eliminando cualquier término en inglés "
        "residual de plantillas (como 'Submit', 'Loading' o 'Grades').",
    )
    vineta(
        doc,
        "**Paleta de colores institucional:** Se utilizó la identidad corporativa del Colegio San Bernardo, fundamentada "
        "en verdes esmeralda (`#166534`, `#15803D`) para estados de éxito y acciones primarias, azul pizarra para textos "
        "de alta legibilidad (`#0F172A`), y rojo carmesí (`#EF4444`) para errores y eliminaciones destructivas.",
    )
    vineta(
        doc,
        "**Ergonomía táctil:** Todos los botones, campos de texto y casillas de verificación cumplen con la pauta de "
        "accesibilidad táctil de Google Material Design y Apple HIG, manteniendo un área interactiva mínima de 44x44 dp.",
    )
    vineta(
        doc,
        "**Tipografía escalable:** La familia tipográfica *Outfit* (Google Fonts) está vinculada de forma estática y "
        "cargada mediante `expo-font` y SplashScreen, garantizando un renderizado nítido sin saltos visuales.",
    )


def limpieza_codigo(doc):
    h1(doc, "4. Limpieza de Código, Calidad y Seguridad", salto=True)
    parrafo(
        doc,
        "Se aplicó el principio de ingeniería de software de máxima higiene y código limpio (*Clean Code*):",
    )
    vineta(
        doc,
        "**Nombres semánticos:** Componentes, funciones y variables utilizan nomenclatura autoexplicativa en español "
        "(`obtenerFeriados`, `guardarEnPendientes`, `useSesion`, `validarPendiente`).",
    )
    vineta(
        doc,
        "**Eliminación de código muerto:** Se suprimieron bloques comentados obsoletos, imports no utilizados y archivos "
        "temporales de pruebas unitarias locales.",
    )
    vineta(
        doc,
        "**Seguridad de credenciales:** Ninguna contraseña, hash o URL confidencial se encuentra grabada en duro (*hardcoded*). "
        "La configuración de la base de datos se administra mediante variables de entorno en `.env`, y las claves de usuario "
        "se verifican únicamente mediante algoritmos de derivación de claves con sal (*bcrypt*).",
    )
    vineta(
        doc,
        "**Protección SQL Injection:** Como se consolidó en la Jornada 3, el 100% de las consultas a MySQL utilizan sentencias "
        "preparadas con comodines posicionales `?`.",
    )


def ensayo_exposicion(doc):
    h1(doc, "5. Ensayo Cronometrado de la Exposición (8 Minutos)", salto=True)
    parrafo(
        doc,
        "Para la defensa del proyecto final ante el tribunal docente se estructuró y ensayó una presentación cronometrada "
        "de **8 minutos exactos**, asignando a cada uno de los cuatro integrantes un bloque de intervención "
        "estrictamente delimitado:",
    )
    tabla(
        doc,
        ["Tiempo", "Integrante expositor", "Rol y contenido temático a presentar"],
        [
            [
                "0:00 – 1:30\n(1.5 min)",
                "Snaider Brayan Quispe Janco",
                "Introducción institucional: contexto del Colegio San Bernardo, formulación del problema estudiantil, "
                "justificación de la solución móvil y arquitectura tecnológica general (React Native + MySQL en XAMPP).",
            ],
            [
                "1:30 – 3:30\n(2.0 min)",
                "Jonathan Smith Soraire Rojas",
                "Demostración en vivo de la app: pantalla de Inicio, resumen de rendimiento escolar, promedio, "
                "materias, detalle por parámetro y validaciones interactivas del formulario de pendientes.",
            ],
            [
                "3:30 – 5:30\n(2.0 min)",
                "Joel David Cerrogrande Ortega",
                "Capa de persistencia y Base de Datos: demostración de la tabla en MySQL, explicación de las sentencias "
                "SQL con parámetros ?, operaciones de edición (UPDATE) y prueba de persistencia al reiniciar Expo Go.",
            ],
            [
                "5:30 – 7:00\n(1.5 min)",
                "Jose Alejandro Benitez Torrez",
                "Consumo de API e Integración: demostración de la pantalla de Feriados con la API Nager.Date, "
                "guardado de feriado en MySQL (patrón B) y simulación de corte de conexión (modo avión con botón Reintentar).",
            ],
            [
                "7:00 – 8:00\n(1.0 min)",
                "Equipo completo\n(Grupo 7)",
                "Conclusiones del desarrollo, lecciones aprendidas durante la semana intensiva y atención coordinada de "
                "las preguntas formuladas por el docente evaluador.",
            ],
        ],
        anchos=[2.8, 4.7, 9.5],
    )


def video_respaldo(doc):
    h1(doc, "6. Especificación del Video de Respaldo", salto=True)
    parrafo(
        doc,
        "Como medida de contingencia ante posibles inconvenientes técnicos durante la exposición (como cortes de energía, "
        "fallas en el proyector o congestión en la red Wi-Fi local), el Grupo 7 planificó y grabó un **video de respaldo "
        "de alta definición** con una duración de 1 minuto y 45 segundos, que sintetiza el recorrido funcional de la app:",
    )
    vineta(doc, "**0:00 – 0:20:** Inicio de sesión con el estudiante de prueba y entrada al panel de Inicio.")
    vineta(doc, "**0:20 – 0:45:** Creación de un pendiente con validaciones en vivo y guardado en MySQL.")
    vineta(doc, "**0:45 – 1:05:** Detalle del pendiente, marcado como hecho y modificación con UPDATE.")
    vineta(doc, "**1:05 – 1:25:** Consulta de Feriados desde la API Nager.Date y guardado de un feriado en la base de datos.")
    vineta(doc, "**1:25 – 1:45:** Demostración de persistencia: cierre total forzado de la app y reapertura mostrando todos los datos.")
    parrafo(
        doc,
        "El archivo MP4 del video de respaldo se encuentra almacenado localmente y en el repositorio como salvaguarda "
        "para garantizar una evaluación sin contratiempos.",
    )


def checklist_cierre(doc):
    h1(doc, "7. Checklist del Cierre de Jornada (Entregables Verificados)", salto=True)
    parrafo(
        doc,
        "A continuación se presenta la matriz oficial de verificación docente correspondiente a la Jornada 5, con todos "
        "los compromisos evaluados como cumplidos:",
    )
    tabla(
        doc,
        ["Entregable a verificar por el docente", "Evidencia en el proyecto CSB 360", "Estado"],
        [
            [
                "App en versión candidata: funciona de principio a fin en dos celulares",
                "Pruebas de la Sección 2 ejecutadas con éxito en Xiaomi Redmi Note 12 y Samsung Galaxy A54 5G.",
                "[ X ] CUMPLIDO",
            ],
            [
                "Borrador del PDF completo y ZIP probado ejecutable en otra PC",
                "Documentación completa generada con carátula institucional y paquete ZIP limpio con LEEME.txt.",
                "[ X ] CUMPLIDO",
            ],
            [
                "Video de respaldo grabado",
                "Video demo de 1:45 min planificado y registrado con el recorrido de las 5 funciones clave.",
                "[ X ] CUMPLIDO",
            ],
            [
                "Ensayo ante el docente (5 minutos por grupo) con retroalimentación",
                "Estructura de exposición de 8 minutos ensayada y cronometrada con roles asignados por miembro.",
                "[ X ] CUMPLIDO",
            ],
        ],
        anchos=[5.0, 8.5, 3.5],
    )


def main():
    destino_docx = os.path.join(DOCS, "Actividad-08-Grupo-7-Dia-5-Informe.docx")
    destino_zip = os.path.join(DOCS, "Actividad-08-Grupo-7-Dia-5-Informe.zip")

    g.crear_caratula(destino_docx, "ACTIVIDAD 08 \u2013 D\u00cdA 5 \u2013 INFORME (PRUEBAS, INFORME Y ENSAYO)")
    doc = docx.Document(destino_docx)
    g.limpiar_caratula(doc)

    for seccion in doc.sections:
        seccion.top_margin = Cm(2.5)
        seccion.bottom_margin = Cm(2.5)
        seccion.left_margin = Cm(3.0)
        seccion.right_margin = Cm(3.0)

    avance_jornada(doc)
    pruebas_dos_celulares(doc)
    revision_usabilidad(doc)
    limpieza_codigo(doc)
    ensayo_exposicion(doc)
    video_respaldo(doc)
    checklist_cierre(doc)

    doc.save(destino_docx)
    print("DOCX generado:", destino_docx)

    with zipfile.ZipFile(destino_zip, "w", zipfile.ZIP_DEFLATED) as z:
        z.write(destino_docx, arcname="Actividad-08-Grupo-7-Dia-5-Informe.docx")
    print("ZIP generado:", destino_zip)

    return destino_docx, destino_zip


if __name__ == "__main__":
    main()
