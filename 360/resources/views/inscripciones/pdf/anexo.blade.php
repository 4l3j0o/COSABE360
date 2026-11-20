<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Anexo de Conformidad y Aceptación</title>
<style>
  @page { size: A4 portrait; margin: 18mm 15mm; }
  * { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; box-sizing: border-box; }
  body { font-size: 10.5px; color: #111; line-height: 1.55; text-align: justify; }
  h1 { font-size: 13px; text-align: center; text-transform: uppercase; margin: 0 0 2px 0; }
  h2 { font-size: 12px; text-align: center; text-transform: uppercase; margin: 0 0 12px 0; }
  .punto { font-weight: bold; margin: 10px 0 4px 0; text-transform: uppercase; }
  p { margin: 4px 0; }
  .firmas { width: 100%; margin-top: 34px; }
  .firmas td { font-size: 10px; width: 100%; vertical-align: top; }
  .linea { margin: 34px 0 0 0; padding-top: 4px; }
  .campo { margin-top: 14px; }
</style>
</head>
<body>
@php
  $tutorNombre = trim(($tutor->apellidos ?? '').' '.($tutor->nombres ?? '')) ?: '—';
  $tutorCi = trim(($tutor->numero_documento ?? '').' '.($tutor->complemento ?? ''));
  $estNombre = trim($student->apellidos.' '.$student->nombres);
@endphp
<h1>Anexo de conformidad y aceptación de acuerdos complementarios</h1>
<h2>Gestión escolar {{ $gestion }}</h2>

<p>Yo, <strong>{{ mb_strtoupper($tutorNombre, 'UTF-8') }}</strong>, mayor de edad, hábil por derecho, titular de la Cédula de Identidad N° <strong>{{ $tutorCi ?: '____________' }}</strong>, en mi calidad de padre, madre y/o apoderado legal del estudiante: <strong>{{ mb_strtoupper($estNombre, 'UTF-8') }}</strong>, declaro que:</p>
<p>Habiendo suscrito el Contrato de prestación de servicios educativos correspondiente a la gestión escolar {{ $gestion }} con la Unidad Educativa San Bernardo de Tarija, y a objeto de dejar constancia expresa de acuerdos complementarios vinculados a la convivencia institucional y al cumplimiento de obligaciones asumidas, manifiesto mi plena conformidad con los siguientes puntos:</p>

<div class="punto">I. (Material escolar y recursos de uso académico).</div>
<p>Declaro conocer y acepto que el estudiante deberá contar con el material escolar y recursos académicos requeridos para el desarrollo del currículo y de las actividades programadas durante la gestión escolar {{ $gestion }}, comprometiéndome a proveerlos de manera oportuna conforme a las características y necesidades del nivel correspondiente.</p>

<div class="punto">II. (Uso de teléfonos celulares y dispositivos personales).</div>
<p>Declaro conocer y acepto que no está permitido el uso de teléfonos celulares u otros dispositivos personales dentro de la Unidad Educativa, salvo autorización expresa otorgada por la autoridad institucional por motivos estrictamente justificados. Asimismo, acepto que la inobservancia de esta disposición dará lugar a la aplicación de las medidas disciplinarias y/o reparadoras previstas en la normativa interna.</p>

<div class="punto">III. (Gestión de recordatorios por mora y medios de comunicación).</div>
<p>Declaro conocer y acepto que, ante retrasos en el pago de cuotas por concepto de prestación de servicios educativos asumidas en el Contrato, la Unidad Educativa podrá realizar gestiones administrativas de recordatorio mediante mensajes escritos (WhatsApp u otros medios digitales), llamadas telefónicas, correos electrónicos, circulares impresas y/o correspondencia administrativa, entre otros medios institucionales habilitados, manteniéndose plenamente vigente lo establecido en el Contrato respecto a mora, efectos y acciones legales aplicables.</p>

<div class="punto">IV. (Aceptación de comunicaciones oficiales).</div>
<p>Declaro conocer y acepto que toda información institucional relevante relacionada con el servicio educativo, convivencia escolar, recordatorios administrativos y/o situaciones emergentes, podrá ser comunicada a través de los medios de contacto proporcionados (teléfono, WhatsApp, correo electrónico y/o plataforma institucional), comprometiéndome a mantenerlos vigentes y actualizados durante toda la gestión escolar.</p>

<div class="punto">V. (Conformidad y suscripción).</div>
<p>En señal de aceptación plena y sin reservas de todo lo establecido en el presente Anexo de Conformidad y Aceptación de Acuerdos Complementarios, lo suscribo de manera voluntaria, en un (1) ejemplar original, a los ___ días del mes de ____________ del año {{ $anio_palabras }}, en la ciudad de Tarija, Estado Plurinacional de Bolivia.</p>

<table class="firmas">
  <tr>
    <td>
      <div class="campo">NOMBRE: {{ mb_strtoupper($tutorNombre, 'UTF-8') }}</div>
      <div class="campo">C.I. N°: {{ $tutorCi ?: '_______________________________' }}</div>
      <div class="linea">FIRMA: ______________________________</div>
    </td>
  </tr>
</table>
</body>
</html>
