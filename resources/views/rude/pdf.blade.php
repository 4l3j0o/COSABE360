<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>RUDE {{ $student->codigo }} — Formulario Oficial</title>
<style>
  @page { size: A4 portrait; margin: 10mm 9mm 10mm 9mm; }
  * { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; box-sizing: border-box; }
  body { font-size: 7.5px; color: #111; line-height: 1.35; margin: 0; padding: 0; background: #fff; }
  .cab { width: 100%; border: 1.5px solid #111; margin-bottom: 6px; }
  .cab td { vertical-align: middle; padding: 5px 8px; }
  .cab .t1 { font-size: 11px; font-weight: 900; text-transform: uppercase; }
  .cab .t2 { font-size: 9px; font-weight: 800; text-transform: uppercase; }
  .cab .t3 { font-size: 6.5px; color: #333; }
  .qr { width: 70px; text-align: right; }
  .qr img { width: 64px; }
  .sec { border: 1px solid #111; margin-bottom: 5px; }
  .sec-t { background: #e5e5e5; font-size: 8px; font-weight: 900; padding: 3px 6px; text-transform: uppercase; border-bottom: 1px solid #111; }
  .sec-b { padding: 5px 6px; }
  table.g { width: 100%; border-collapse: collapse; }
  table.g td { padding: 2px 3px; vertical-align: top; }
  .lab { font-size: 6px; font-weight: 700; color: #444; text-transform: uppercase; }
  .val { font-size: 7.5px; font-weight: 600; border-bottom: 1px dotted #999; min-height: 10px; display: block; padding-bottom: 1px; }
  .num { font-weight: 900; }
  .firmas { width: 100%; margin-top: 14px; }
  .firmas td { text-align: center; font-size: 7px; width: 50%; padding: 26px 10px 2px 10px; border-top: 1px solid #111; vertical-align: bottom; }
  .pie { font-size: 6px; color: #555; text-align: center; margin-top: 6px; border-top: 1px solid #999; padding-top: 4px; }
</style>
</head>
<body>
@php
  $R = \App\Models\StudentRudeRecord::class;
  $marca = fn ($cond) => $cond ? '☒' : '☐';
  $pg = ['PADRE' => null, 'MADRE' => null, 'TUTOR' => null];
  foreach ($student->parentGuardians as $p) {
      $rel = $p->pivot?->tipo_relacion;
      if (isset($pg[$rel]) && $pg[$rel] === null) $pg[$rel] = $p;
  }
  $firma = function ($p, $campo) { return $p?->$campo ?? ''; };
@endphp

<table class="cab"><tr>
  <td>
    <div class="t1">Registro Único de Estudiantes (RUDE)</div>
    <div class="t2">Formulario de Inscripción / Actualización — RM Nº 0031/2025</div>
    <div class="t3">Ministerio de Educación del Estado Plurinacional de Bolivia &bull; Código RUDE: <strong>{{ $rude?->codigo_rude ?? 'POR GENERAR (SIE)' }}</strong> &bull; Impreso: {{ now()->format('d/m/Y H:i') }}</div>
  </td>
  @if(! empty($qrBase64))
    <td class="qr"><img src="{{ $qrBase64 }}" alt="QR"></td>
  @endif
</tr></table>

<div class="sec">
  <div class="sec-t">I. Datos de la Unidad Educativa</div>
  <div class="sec-b"><table class="g">
    <tr>
      <td style="width:50%"><span class="lab">*) Código SIE de la Unidad Educativa</span><span class="val">{{ $rude?->codigo_sie_ue ?? $R::SIE_UE }}</span></td>
      <td style="width:50%"><span class="lab">Fecha de llenado</span><span class="val">{{ $rude?->fecha_registro?->format('d/m/Y') ?? now()->format('d/m/Y') }}</span></td>
    </tr>
  </table></div>
</div>

<div class="sec">
  <div class="sec-t">II. Datos de la o el Estudiante</div>
  <div class="sec-b"><table class="g">
    <tr>
      <td style="width:45%"><span class="lab">2.1 Apellido(s) y Nombre(s)</span><span class="val">{{ $student->apellidos }} {{ $student->nombres }}</span></td>
      <td style="width:30%"><span class="lab">2.2 Lugar de nacimiento (País, Depto., Prov., Localidad)</span><span class="val">{{ $student->lugar_nacimiento ?? '' }}</span></td>
      <td style="width:25%"><span class="lab">2.4 Fecha de nacimiento</span><span class="val">{{ $student->fecha_nacimiento?->format('d/m/Y') ?? '' }}</span></td>
    </tr>
    <tr>
      <td colspan="2"><span class="lab">2.3 Certificado de nacimiento (Oficialía / Libro / Partida / Folio / Año)</span><span class="val">{{ $rude?->oficialia_nacimiento ?? '' }} / {{ $rude?->libro_nacimiento ?? '' }} / {{ $rude?->partida_nacimiento ?? '' }} / {{ $rude?->folio_nacimiento ?? '' }} / {{ $rude?->anio_nacimiento ?? '' }}</span></td>
      <td><span class="lab">2.7 Sexo</span><span class="val">{{ $marca($student->sexo === 'M') }} Masculino &nbsp; {{ $marca($student->sexo === 'F') }} Femenino</span></td>
    </tr>
    <tr>
      <td colspan="2"><span class="lab">2.5 Documento de identidad</span><span class="val">{{ $marca(in_array($student->tipo_documento, ['CI', null])) }} C.I. Nº {{ $student->numero_documento ?? '' }} @if($student->complemento) Comp. {{ $student->complemento }} @endif &nbsp; {{ $marca($student->tipo_documento === 'PASAPORTE') }} Pasaporte &nbsp; {{ $marca($student->tipo_documento === 'OTRO') }} Otro</span></td>
      <td><span class="lab">2.6 Código RUDE (automático SIE)</span><span class="val">{{ $rude?->codigo_rude ?? 'POR GENERAR (SIE)' }}</span></td>
    </tr>
    <tr>
      <td colspan="2"><span class="lab">2.5.1 Solo extranjeros: tipo de documento presentado</span><span class="val">{{ $rude?->tipo_doc_extranjero ?? '' }}</span></td>
      <td><span class="lab">2.5.2 Código del documento (extranjeros)</span><span class="val">{{ $rude?->codigo_doc_extranjero ?? '' }}</span></td>
    </tr>
    <tr>
      <td colspan="2"><span class="lab">2.8 Discapacidad (Registro de Discapacidad)</span><span class="val">{{ $student->discapacidad ?? '' }}</span></td>
      <td><span class="lab">2.9 N.º Registro Discapacidad IBC</span><span class="val">{{ $rude?->nro_registro_discapacidad ?? '' }}</span></td>
    </tr>
  </table></div>
</div>

<div class="sec">
  <div class="sec-t">III. Dirección actual de la o el Estudiante</div>
  <div class="sec-b"><table class="g">
    <tr>
      <td style="width:25%"><span class="lab">Departamento</span><span class="val">{{ $rude?->dir_departamento ?? '' }}</span></td>
      <td style="width:25%"><span class="lab">Provincia</span><span class="val">{{ $rude?->dir_provincia ?? '' }}</span></td>
      <td style="width:25%"><span class="lab">Sección/Municipio</span><span class="val">{{ $rude?->dir_municipio ?? '' }}</span></td>
      <td style="width:25%"><span class="lab">Localidad/Comunidad</span><span class="val">{{ $rude?->dir_comunidad ?? '' }}</span></td>
    </tr>
    <tr>
      <td><span class="lab">Zona/Villa</span><span class="val">{{ $rude?->dir_zona ?? '' }}</span></td>
      <td><span class="lab">Avenida/Calle</span><span class="val">{{ $rude?->dir_calle ?? '' }}</span></td>
      <td><span class="lab">N.º vivienda</span><span class="val">{{ $rude?->dir_nro_vivienda ?? '' }}</span></td>
      <td><span class="lab">Teléfono / Celular</span><span class="val">{{ $rude?->dir_telefono ?? '' }} {{ $rude?->dir_celular ?? '' }}</span></td>
    </tr>
  </table></div>
</div>

<div class="sec">
  <div class="sec-t">IV. Aspectos socioeconómicos de la o el Estudiante</div>
  <div class="sec-b"><table class="g">
    <tr><td colspan="2"><span class="lab">4.1 Idioma en el que aprendió a hablar / de uso frecuente / Autoidentificación cultural</span><span class="val">{{ $rude?->idioma_aprendio ?? '' }} / {{ $R::listar($rude?->idiomas_uso_frecuente) }} / {{ $rude?->autoidentificacion_cultural ?? '' }}</span></td></tr>
    <tr><td><span class="lab">4.2 Salud: centro cercano / atendido / veces / seguro</span><span class="val">{{ $marca($rude?->salud_centro_cercano) }} Sí {{ $marca($rude?->salud_centro_cercano === false) }} No / Atendido: {{ $marca($rude?->salud_atendido_anio) }} Sí {{ $marca($rude?->salud_atendido_anio === false) }} No ({{ $rude?->salud_veces ?? '—' }} veces) / {{ $rude?->salud_seguro ?? '' }}</span></td>
    <td><span class="lab">4.3 Servicios: agua / baño / alcantarillado / luz / basura / vivienda</span><span class="val">{{ $marca($rude?->serv_agua_red) }} Agua / {{ $rude?->serv_bano ?? '' }} / {{ $marca($rude?->serv_alcantarillado) }} Alcant. / {{ $marca($rude?->serv_electricidad) }} Luz / {{ $marca($rude?->serv_recojo_basura) }} Basura / {{ $rude?->serv_vivienda_tipo ?? '' }}</span></td></tr>
    <tr><td><span class="lab">4.4 Internet: lugares / frecuencia</span><span class="val">{{ $R::listar($rude?->internet_lugares) }} / {{ $rude?->internet_frecuencia ?? '' }}</span></td>
    <td><span class="lab">4.5 Laboral: trabajó / actividad / turnos / frecuencia / pago / meses</span><span class="val">{{ $marca($rude?->laboral_trabajo) }} Sí {{ $marca($rude?->laboral_trabajo === false) }} No / {{ $rude?->laboral_actividad ?? '' }} / {{ $R::listar($rude?->laboral_turnos) }} / {{ $rude?->laboral_frecuencia ?? '' }} / Pago: {{ $marca($rude?->laboral_recibio_pago) }} Sí {{ $marca($rude?->laboral_recibio_pago === false) }} No / {{ $R::listar($rude?->meses_trabajados) }}</span></td></tr>
    <tr><td><span class="lab">4.6 Transporte: medio / tiempo</span><span class="val">{{ $rude?->transporte_medio ?? '' }} @if($rude?->transporte_otro) ({{ $rude?->transporte_otro }}) @endif / {{ $rude?->transporte_tiempo ?? '' }}</span></td>
    <td><span class="lab">4.7 Abandono (gestión anterior): sí/no / razones</span><span class="val">{{ $marca($rude?->abandono_escolar) }} Sí {{ $marca($rude?->abandono_escolar === false) }} No / {{ $R::listar($rude?->abandono_razones) }}</span></td></tr>
    <tr><td colspan="2"><span class="lab">4.8 Discapacidad: tipos / grado — Dificultades de aprendizaje</span><span class="val">{{ $R::listar($rude?->discapacidad_tipos) }} ({{ $rude?->discapacidad_grado ?? '—' }}) / {{ $R::listar($rude?->dificultad_aprendizaje) }}</span></td></tr>
  </table></div>
</div>

<div class="sec">
  <div class="sec-t">V. Datos del Padre, Madre o Tutor(a)</div>
  <div class="sec-b"><table class="g">
    <tr><td colspan="2"><span class="lab">5.1 Vive habitualmente con</span><span class="val">@foreach($R::CONVIVENCIA as $i => $opc) {{ $marca($R::listar($rude?->convive_con) !== '' && in_array($opc, (array) ($rude?->convive_con ?? []))) }} {{ $i + 1 }}.- {{ $opc }} &nbsp; @endforeach @if($rude?->centro_acogida) / Centro: {{ $rude?->centro_acogida }} @endif</span></td></tr>
  </table>
  @foreach(['PADRE' => '5.2 Datos del Padre', 'MADRE' => '5.3 Datos de la Madre', 'TUTOR' => '5.4 Datos del Tutor(a)', 'TUTOR_EXTRAORDINARIO' => '5.5 Datos del Tutor(a) Extraordinario(a)'] as $rel => $titulo)
  @php $p = $pg[$rel] ?? $student->parentGuardians->first(fn ($x) => $x->pivot?->tipo_relacion === $rel); @endphp
  <table class="g" style="margin-top:4px;">
    <tr><td colspan="4"><span class="lab">{{ $titulo }}</span></td></tr>
    <tr>
      <td style="width:25%"><span class="lab">C.I. / DNI</span><span class="val">{{ $firma($p, 'numero_documento') }} {{ $firma($p, 'complemento') }}</span></td>
      <td style="width:25%"><span class="lab">Apellido Paterno</span><span class="val">{{ $firma($p, 'apellido_paterno') ?: $firma($p, 'apellidos') }}</span></td>
      <td style="width:25%"><span class="lab">Apellido Materno</span><span class="val">{{ $firma($p, 'apellido_materno') }}</span></td>
      <td style="width:25%"><span class="lab">Nombre(s)</span><span class="val">{{ $firma($p, 'nombres') }}</span></td>
    </tr>
    <tr>
      <td><span class="lab">Ocupación laboral actual</span><span class="val">{{ $firma($p, 'ocupacion') }}</span></td>
      <td><span class="lab">Fecha de nacimiento</span><span class="val">{{ $p?->fecha_nacimiento?->format('d/m/Y') ?? '' }}</span></td>
      <td><span class="lab">Teléfono / Celular</span><span class="val">{{ $firma($p, 'telefono') }}</span></td>
      <td><span class="lab">Idioma que habla</span><span class="val">{{ $firma($p, 'idioma') }}</span></td>
    </tr>
    <tr>
      <td colspan="2"><span class="lab">Máximo grado de instrucción alcanzado</span><span class="val">{{ $firma($p, 'grado_instruccion') }}</span></td>
      @if($rel === 'TUTOR_EXTRAORDINARIO')
      <td><span class="lab">Cargo actual</span><span class="val">{{ $firma($p, 'cargo_actual') }}</span></td>
      <td><span class="lab">Nombre de la Institución</span><span class="val">{{ $firma($p, 'nombre_institucion') }}</span></td>
      @else
      <td colspan="2"></td>
      @endif
    </tr>
  </table>
  @endforeach
  </div>
</div>

<table class="firmas"><tr>
  <td>Firma del Padre / Madre / Tutor(a)</td>
  <td>Firma del Director(a) de la Unidad Educativa</td>
</tr></table>

<div class="pie">
  Formulario RUDE — RM Nº 0031/2025 — Unidad Educativa San Bernardo, Tarija, Bolivia &bull;
  Generado el {{ now()->format('d/m/Y H:i') }} &bull; Declaración jurada — responsabilidad del Director(a) de la UE (Art. 12 RM 0001).
</div>
</body>
</html>
