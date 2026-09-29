<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Contrato de Prestación de Servicios Educativos</title>
<style>
  @page { size: A4 portrait; margin: 18mm 15mm; }
  * { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; box-sizing: border-box; }
  body { font-size: 10px; color: #111; line-height: 1.5; text-align: justify; }
  h1 { font-size: 14px; text-align: center; text-transform: uppercase; margin: 0 0 2px 0; }
  h2 { font-size: 12px; text-align: center; text-transform: uppercase; margin: 0 0 2px 0; }
  h3 { font-size: 11px; text-align: center; margin: 0 0 10px 0; }
  .intro { text-align: center; margin-bottom: 10px; }
  .clausula { font-weight: bold; margin: 10px 0 4px 0; text-transform: uppercase; }
  p { margin: 3px 0; }
  table.est { width: 100%; border-collapse: collapse; margin: 6px 0; }
  table.est th, table.est td { border: 1px solid #111; padding: 4px; font-size: 9px; text-align: center; }
  .firmas { width: 100%; margin-top: 30px; }
  .firmas td { text-align: center; font-size: 9px; width: 50%; vertical-align: top; }
  .linea { border-top: 1px solid #111; margin: 40px 20px 0 20px; padding-top: 4px; }
</style>
</head>
<body>
@php
  $tutorNombre = trim(($tutor->apellidos ?? '').' '.($tutor->nombres ?? '')) ?: '—';
  $tutorCi = trim(($tutor->numero_documento ?? '').' '.($tutor->complemento ?? ''));
  $estNombre = trim($student->apellidos.' '.$student->nombres);
  $estCi = trim(($student->numero_documento ?? '').' '.($student->complemento ?? ''));
  $rude = $student->rudeRecord?->codigo_rude ?? '—';
  $nivel = $curso?->grade?->level?->nombre ?? '—';
  $cursoNombre = $curso?->nombre ?? '—';
  $anio = $periodo?->nombre ?? $gestion;
@endphp
<div class="intro">
  <h1>Contrato de prestación de servicios educativos</h1>
  <h2>Colegio San Bernardo de Tarija</h2>
  <h3>Gestión escolar {{ $gestion }}</h3>
</div>
<p>Contrato de prestación de servicios educativos que producirá efectos de instrumento público previo reconocimiento de firmas ante autoridad notarial de fe pública o autoridad judicial competente; que se suscribe de conformidad con los siguientes términos y condiciones:</p>

<div class="clausula">Cláusula 1º (Partes).</div>
<p>I. Suscriben este contrato:<br>
1) SOCIEDAD EDUCATIVA SAN BERNARDO DE TARIJA S.A., sociedad comercial legalmente constituida en Bolivia e inscrita en el Registro de Comercio a cargo del SEPREC bajo Matrícula N° 408460020, facultada para prestar servicios educativos en mérito a la Resolución Ministerial N° 594/62 y 1018/63 emitidas por el Ministerio de Educación, Resolución Administrativa N° 028/2012 y SIE N° 81730184, con domicilio legal ubicado en la Calle Alejandro Corrado Nº339 de la ciudad de Tarija, representada en este acto por su gerente administrativa señora CARMEN PATRICIA CASSAB MICHEL, cuya personería jurídica para suscribir este contrato se encuentra contenida en el Testimonio de Poder N° 236/2024 conferido en su favor en fecha 18 de abril de 2024 ante Notaría de Fe Pública N° 9 del Distrito Judicial de Tarija; parte que en lo sucesivo se denominará simplemente UNIDAD EDUCATIVA y;<br>
2) <strong>{{ mb_strtoupper($tutorNombre, 'UTF-8') }}</strong>, titular de la C.I. N° <strong>{{ $tutorCi ?: '—' }}</strong>, de nacionalidad BOLIVIANA, mayor de edad y hábil por derecho, en su calidad de padre/madre/tutor del estudiante identificado en la cláusula 4° de este contrato; con teléfono fijo {{ $tutor->telefono ?? '—' }}, teléfono celular {{ $tutor->telefono ?? '—' }}, correo electrónico {{ $tutor->email ?? '—' }} y domicilio particular ubicado en {{ $tutor->direccion ?? '—' }} de la ciudad de Tarija, parte que en lo sucesivo se denominará simplemente EL CONTRATANTE.</p>
<p>II. Cuando LA UNIDAD EDUCATIVA y EL CONTRATANTE sean nombrados de manera conjunta en el presente contrato se denominarán LAS PARTES.</p>

<div class="clausula">Cláusula 2º (Antecedentes).</div>
<p>I. La Resolución Ministerial N° 01/{{ $anio }}, que aprueba las Normas Generales para el Subsistema de Educación Regular correspondientes a la Gestión Escolar {{ $gestion }}, ha sido publicada en el sitio web del Ministerio de Educación.</p>
<p>II. Que el artículo 74, parágrafo III, de la Resolución Ministerial N.° 01/{{ $anio }}, dispone que las Unidades Educativas Privadas deben suscribir contratos de prestación de servicios educativos con los padres, madres y/o apoderados legales de los estudiantes, en el marco de la legislación civil vigente, actuando éstos en representación de sus hijos, hijas o dependientes, estableciendo en dicho instrumento los derechos y obligaciones de las partes.</p>

<div class="clausula">Cláusula 3º (Naturaleza del contrato).</div>
<p>Este contrato es de naturaleza civil y se rige por lo dispuesto en los artículos 450°, 451°, 452°, 454°, 485°, 519°, 520°, 569° y 570° y demás disposiciones aplicables del Código Civil, Código Procesal Civil y normas conexas.</p>

<div class="clausula">Cláusula 4º (Objeto del contrato).</div>
<p>I. El objeto del presente contrato es la prestación de servicios educativos por parte de LA UNIDAD EDUCATIVA en favor de:</p>
<table class="est">
  <tr><th>Nombre y apellido completo</th><th>CI</th><th>RUDE</th><th>Año de Escolaridad / nivel</th></tr>
  <tr><td>{{ mb_strtoupper($estNombre, 'UTF-8') }}</td><td>{{ $estCi }}</td><td>{{ $rude }}</td><td>{{ $cursoNombre }} — {{ $nivel }}</td></tr>
</table>
<p>II. Para efectos del presente contrato, el estudiante identificado en el numeral 4.1. de esta cláusula se denominará en lo sucesivo simplemente EL ESTUDIANTE.</p>
<p>III. Conforme establece la oferta de servicios informada a los padres de familia y/o apoderados legales, LA UNIDAD EDUCATIVA brindará a EL ESTUDIANTE un servicio educativo que cumpla el Currículo Base y los Planes y Programas del Sistema de Educación Plurinacional del Modelo Educativo Sociocomunitario Productivo exigida por el Ministerio de Educación.</p>

<div class="clausula">Cláusula 5º (Modalidad de prestación del servicio).</div>
<p>I. El servicio educativo brindado por LA UNIDAD EDUCATIVA será presencial.</p>
<p>II. No obstante, por razones sanitarias, epidemiológicas, climatológicas, convulsión social u otras en las que las autoridades competentes dispongan que el servicio educativo sea prestado de manera semipresencial o a distancia, se hará uso de las plataformas que tenga implementadas LA UNIDAD EDUCATIVA, con los mismos costos y efectos del servicio educativo presencial previstos en este contrato.</p>

<div class="clausula">Cláusula 6º (Costo educativo).</div>
<p>I. En contraprestación por el servicio educativo brindado por LA UNIDAD EDUCATIVA, EL CONTRATANTE se obliga al pago de un costo educativo anual de <strong>Bs.{{ number_format($anual, 2) }}</strong> bolivianos ({{ mb_strtolower($anual_literal, 'UTF-8') }}), que podrá ser cancelado en un solo pago al momento de las inscripciones o, en hasta un máximo de diez (10) cuotas mensuales de <strong>Bs.{{ number_format($cuota, 2) }}</strong> ({{ mb_strtolower($cuota_literal, 'UTF-8') }}) cada una, conforme al cronograma establecido en los numerales II y III de la presente cláusula.</p>
<p>II. La primera cuota será cancelada el día de la admisión de EL ESTUDIANTE y de la firma del presente contrato, independientemente de la modalidad de inscripción que se defina para la gestión {{ $gestion }}.</p>
<p>III. Las nueve cuotas restantes serán canceladas a más tardar hasta el día cinco (5) de cada mes, a partir del mes de marzo del {{ $anio }}.</p>
<p>IV. Si el día de pago recayera en domingo o feriado, se trasladará al día hábil inmediatamente siguiente, de manera impostergable.</p>
<p>V. Si EL CONTRATANTE realizara únicamente el pago de la primera cuota y omitiera el pago oportuno y completo de las cuotas posteriores, conforme a los montos y plazos establecidos en los numerales I, II y III de la presente cláusula, incurrirá en incumplimiento contractual y en mora, situación que dará lugar a la activación de las acciones legales y contractuales correspondientes, previstas en el presente contrato.</p>
<p>VI. EL CONTRATANTE realizará cada uno de los pagos haciendo uso de cualquiera de los canales de cobro habilitados al efecto por LA UNIDAD EDUCATIVA.</p>
<p>VII. Si en el transcurso de la presente gestión escolar el Gobierno Nacional determinara un nuevo incremento salarial u otras cargas económicas de cumplimiento obligatorio dispuestas por autoridad competente, o se presentara una realidad inflacionaria elevada producto de la coyuntura social y económica, que afecten directamente las operaciones de la institución, LA UNIDAD EDUCATIVA podrá revisar y adecuar el costo educativo, comunicando al CONTRATANTE el ajuste correspondiente, el cual será aplicado a partir de la siguiente cuota exigible.</p>
<p>VIII. Si por cualquier motivo EL ESTUDIANTE no se incorporare a LA UNIDAD EDUCATIVA pese a encontrarse inscrito, el pago realizado en el periodo de inscripción se consolidará en favor de LA UNIDAD EDUCATIVA en calidad de compensación por los costos administrativos y perjuicios emergentes del desistimiento unilateral. Asimismo, si EL CONTRATANTE decidiera retirar definitivamente de LA UNIDAD EDUCATIVA a EL ESTUDIANTE por cualquier motivo o circunstancia, o si éste fuera expulsado conforme lo dispuesto en la normativa vigente, deberá cumplir todas las obligaciones económicas asumidas en este contrato hasta la fecha efectiva de retiro de EL ESTUDIANTE.</p>

<div class="clausula">Cláusula 7º (Vigencia).</div>
<p>I. El servicio educativo objeto de este contrato será prestado a partir del {{ $periodo?->fecha_inicio?->format('d/m/Y') ?? '—' }} y concluirá una vez cumplidos los DOSCIENTOS DÍAS EFECTIVOS DE CLASE, o en la fecha que el Ministerio de Educación disponga la clausura del año escolar {{ $gestion }}.</p>
<p>II. Independientemente de la fecha de clausura del año escolar y de la conclusión del servicio educativo, el presente contrato se mantendrá vigente en tanto existan obligaciones pecuniarias pendientes de pago por parte de EL CONTRATANTE.</p>

<div class="clausula">Cláusula 8º (Obligaciones).</div>
<p>I. La UNIDAD EDUCATIVA tendrá las siguientes obligaciones: 1) Brindar a EL ESTUDIANTE un ambiente de aprendizaje fundamentado en el respeto y el trato digno, sin discriminación alguna (Ley N° 045). 2) Prestar formación académica según su Plan Educativo Institucional y contenidos del Ministerio de Educación. 3) Velar por la seguridad e integridad física de EL ESTUDIANTE. 4) Respetar los periodos de descanso pedagógico, el Calendario Escolar {{ $gestion }} y la carga horaria. 5) Prestar el servicio de manera presencial y, con carácter excepcional y previa autorización, por plataforma virtual. 6) Realizar el cobro del servicio para garantizar el funcionamiento del establecimiento. 7) Dar a conocer el régimen de becas en cada nivel. 8) Brindar infraestructura, mobiliario y equipamiento adecuados. 9) Otorgar la libreta escolar al concluir la gestión. 10) Brindar información oportuna sobre el desempeño académico. 11) Consolidar la inscripción en el Sistema de Información Educativa y remitir reportes a la Dirección Distrital en los plazos de la R.M. 01/{{ $anio }}.</p>
<p>II. EL CONTRATANTE tendrá las siguientes obligaciones: 1) Pagar oportuna y completamente el costo del servicio. 2) Aceptar que la UNIDAD EDUCATIVA no sustituye la labor de orientación y crianza de los padres. 3) Conocer y acatar la normativa interna (reglamento interno, plan de convivencia, reglamento de videovigilancia y demás instructivos). 4) Involucrarse en la vida escolar, asistir a reuniones y acompañar a EL ESTUDIANTE. 5) Respetar las directrices del personal de la UNIDAD EDUCATIVA. 6) Garantizar que EL ESTUDIANTE conozca y cumpla la normativa interna. 7) Dotar a EL ESTUDIANTE del equipamiento necesario.</p>

<div class="clausula">Cláusula 9º (Mora y sus efectos).</div>
<p>I. EL CONTRATANTE cumplirá sus obligaciones con la diligencia de un buen padre de familia (arts. 302-I y 291 Código Civil). II. EL CONTRATANTE incurrirá en mora al sólo vencimiento del plazo de cualquiera de las cuotas de la cláusula 6° (art. 341-1 Código Civil), sin necesidad de intimación. III. En mora, el contrato adquirirá fuerza de título ejecutivo (plazo vencido, suma líquida y exigible) ante juzgado civil comercial de Tarija, con costos y costas; la UNIDAD EDUCATIVA podrá declarar la exigibilidad total inmediata (aceleración de cuotas), manteniendo el servicio hasta culminar el año escolar. IV. En mora, la UNIDAD EDUCATIVA podrá reportar a Infocenter o burós de información; ante dos cuotas consecutivas impagas podrá publicar la mora en prensa, radio o paneles. V. Alternativamente, EL CONTRATANTE podrá presentar reconocimiento unilateral de deuda con plan de pagos que no exceda el 31 de diciembre de {{ $anio }} (un solo plan por gestión y estudiante).</p>

<div class="clausula">Cláusula 10º (Resolución del contrato).</div>
<p>Resuelto de pleno derecho sin intervención judicial por: 1) Conclusión del año escolar y cumplimiento total. 2) Expulsión definitiva con proceso disciplinario (Art. 66 R.M. 01/{{ $anio }}). 3) Traslado por mutuo acuerdo o decisión unilateral de EL CONTRATANTE. 4) Fallecimiento o incapacidad permanente de EL ESTUDIANTE. 5) Incumplimiento de pago, pudiendo la UNIDAD EDUCATIVA exigirlo legalmente y manteniendo el servicio hasta fin de año.</p>

<div class="clausula">Cláusula 11º (Intransferibilidad).</div>
<p>EL CONTRATANTE no podrá ceder, transferir o subrogar derechos u obligaciones sin autorización escrita de LA UNIDAD EDUCATIVA.</p>

<div class="clausula">Cláusula 12º (Notificaciones y comunicaciones ordinarias y de emergencia).</div>
<p>I. Las comunicaciones al CONTRATANTE se cursarán a los correos/teléfonos del Formulario RUDE y/o de la cláusula 1°. II. Dichos medios valen para comunicados, circulares, recordatorios de pago, emergencias y toda información institucional. III. El domicilio y correo de la cláusula 1° valen para notificaciones procesales. IV. Serán válidos hasta que el CONTRATANTE comunique su cambio por nota escrita entregada en Dirección General.</p>

<div class="clausula">Cláusula 13º (Tratamiento de datos personales y videovigilancia).</div>
<p>I. EL CONTRATANTE acepta el registro y tratamiento de datos personales de EL ESTUDIANTE y suyos para fines institucionales y del Sistema de Información Educativa. II. Autoriza su uso estrictamente institucional, administrativo, académico, disciplinario, de seguridad, emergencia y comunicación. III. Acepta los sistemas de seguridad, control de acceso y videovigilancia con finalidad preventiva. IV. Las imágenes solo se usarán para verificación interna, investigación de incidencias, resguardo y, en su caso, como prueba ante autoridad competente. V. Se obliga a respetar el reglamento de videovigilancia; dicha información es resguardo institucional y no podrá exigirse ni difundirse sin autorización escrita, salvo requerimiento de autoridad competente.</p>

<div class="clausula">Cláusula 14º (Responsabilidad por daños a bienes institucionales).</div>
<p>I. EL ESTUDIANTE debe cuidar los bienes e instalaciones. II. Por daño, deterioro, pérdida o uso indebido atribuible a EL ESTUDIANTE, EL CONTRATANTE asumirá la reparación (reposición, reparación técnica o pago a valor de mercado). III. La UNIDAD EDUCATIVA notificará el informe y la valoración; EL CONTRATANTE cumplirá en máximo cinco (5) días hábiles, salvo acuerdo distinto. IV. Ello es independiente de las medidas disciplinarias aplicables a EL ESTUDIANTE.</p>

<div class="clausula">Cláusula 15º (Modificaciones y complementaciones).</div>
<p>Toda modificación o complementación deberá instrumentarse en adendas.</p>

<div class="clausula">Cláusula 16º (Uniforme escolar).</div>
<p>Forma parte integrante del contrato la aceptación expresa del uso del uniforme escolar establecido por LA UNIDAD EDUCATIVA, conforme a la reglamentación interna vigente.</p>

<div class="clausula">Cláusula 17º (Conformidad).</div>
<p>En señal de aceptación de todas las estipulaciones contenidas en el presente contrato, LAS PARTES lo suscriben en dos ejemplares de idéntico contenido a los {{ $dia }} días del mes de {{ $mes }} del año {{ $anio_palabras }} en la ciudad de Tarija, Estado Plurinacional de Bolivia, comprometiéndose a su fiel y estricto cumplimiento.</p>
<p>Tarija, {{ $hoy->format('d/m/Y') }}</p>

<table class="firmas">
  <tr>
    <td><div class="linea">Carmen Patricia Cassab Michel<br><strong>UNIDAD EDUCATIVA</strong></div></td>
    <td><div class="linea">{{ mb_strtoupper($tutorNombre, 'UTF-8') }}<br>CI: {{ $tutorCi }}<br><strong>CONTRATANTE</strong></div></td>
  </tr>
</table>
</body>
</html>
