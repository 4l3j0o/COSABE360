<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>RUDE {{ $student->apellidos }} {{ $student->nombres }}</title>
<style>
  @page { size: A4 portrait; margin: 12mm 10mm 12mm 10mm; }
  @media print { body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
  * { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; box-sizing: border-box; }
  body { font-size: 8px; color: #1a2e1a; line-height: 1.35; margin:0; padding:0; background:#fff; }
  .doc-header { border: 1.8px solid #003200; border-radius: 7px; overflow:hidden; margin-bottom: 9px; }
  .doc-header-top { background: #003200; background: linear-gradient(135deg,#003200 0%,#005100 55%,#006100 100%); color:#fff; padding: 7px 12px; display: table; width:100%; }
  .doc-header-top .left { display: table-cell; width:72%; vertical-align:middle; }
  .brand { font-size: 14px; font-weight: 900; letter-spacing: 0.4px; margin:0; text-transform: uppercase; }
  .subbrand { font-size: 6.8px; opacity: 0.92; margin:3px 0 0 0; text-transform: uppercase; letter-spacing: 0.45px; font-weight:600; }
  .header-body { padding: 9px 11px; background: #f6faf6; }
  .header-body table { width:100%; border-collapse:collapse; }
  .header-body td { vertical-align: top; }
  .emisor h3 { margin:0; font-size:9.5px; color:#003200; font-weight:900; text-transform:uppercase; line-height:1.2; }
  .emisor p { margin:2px 0; font-size:7.2px; color:#33412f; }
  .emisor .sie { font-size:8.5px; font-weight:800; color:#003200; letter-spacing:0.2px; }
  .section { border:1.2px solid #d1dfd1; border-radius:7px; margin-bottom:7px; overflow:hidden; }
  .section-title { background:#003200; color:#fff; font-size:7px; font-weight:900; padding:5px 10px; text-transform:uppercase; letter-spacing:0.5px; }
  .section-body { padding:8px 10px; background:#fff; }
  table.grid { width:100%; border-collapse:collapse; }
  table.grid td { padding:3px 4px; vertical-align:top; }
  .field-label { font-size:6.2px; font-weight:800; color:#64748b; text-transform:uppercase; letter-spacing:0.25px; }
  .field-value { font-size:7.6px; color:#0f172a; font-weight:600; }
  .field-value.strong { font-weight:900; color:#003200; }
  .divider { border-bottom:1px solid #e2e8f0; margin:6px 0; }
  .check-list { width:100%; }
  .check-item { display:inline-block; padding:1.5px 5px; border:1px solid #d1dfd1; border-radius:3px; font-size:6.2px; color:#33412f; margin:1px; }
  .check-item.on { background:#dcfce7; color:#14532d; border-color:#86efac; font-weight:800; }
  .sign-row { width:100%; margin-top:12px; }
  .sign-row td { width:33%; text-align:center; padding:22px 6px 2px 6px; font-size:7px; color:#33412f; border-top:1px solid #94a3b8; vertical-align:bottom; }
  .estado-badge { display:inline-block; padding:2px 8px; border-radius:10px; font-size:6px; font-weight:900; text-transform:uppercase; background:#fef3c7; color:#92400e; border:1px solid #fcd34d; }
  .estado-APROBADO { background:#dcfce7; color:#14532d; border-color:#86efac; }
  .foot-note { font-size:5.8px; color:#64748b; text-align:center; margin-top:6px; line-height:1.35; border-top:1px solid #e2e8f0; padding-top:5px; }
</style>
</head>
<body>
  <div class="doc-header">
    <div class="doc-header-top">
      <div class="left">
        <p class="brand">U.E. SAN BERNARDO — TARIJA</p>
        <p class="subbrand">Formulario de Registro Único de Estudiantes (RUDE) &bull; RM Nº 0031/2025 &bull; Ley 070 Art. 68</p>
      </div>
    </div>
    <div class="header-body">
      <table>
        <tr>
          <td class="emisor">
            <h3>SOCIEDAD EDUCATIVA SAN BERNARDO DE TARIJA S.A.</h3>
            <p class="sie">Código SIE de la Unidad Educativa: {{ $rude?->codigo_sie_ue ?? \App\Models\StudentRudeRecord::SIE_UE }}</p>
            <p>Tarija — Bolivia &bull; Código RUDE: {{ $rude?->codigo_rude ?? 'POR GENERAR (SIE)' }}</p>
          </td>
        </tr>
      </table>
    </div>
  </div>

  <div class="section">
    <div class="section-title">Sección II — Datos de la o el Estudiante</div>
    <div class="section-body">
      <table class="grid">
        <tr>
          <td style="width:50%;"><span class="field-label">Apellido(s)</span><br><span class="field-value strong">{{ $student->apellidos }}</span></td>
          <td><span class="field-label">Nombre(s)</span><br><span class="field-value strong">{{ $student->nombres }}</span></td>
        </tr>
        <tr>
          <td><span class="field-label">Fecha de Nacimiento</span><br><span class="field-value">{{ $student->fecha_nacimiento?->format('d/m/Y') }}</span></td>
          <td><span class="field-label">Lugar de Nacimiento</span><br><span class="field-value">{{ $student->lugar_nacimiento ?? '—' }}</span></td>
        </tr>
        <tr>
          <td><span class="field-label">Sexo</span><br><span class="field-value">{{ $student->sexo === 'M' ? 'Masculino' : 'Femenino' }}</span></td>
          <td><span class="field-label">Documento</span><br><span class="field-value">{{ $student->tipo_documento ?? 'CI' }} {{ $student->numero_documento }} @if($student->complemento) - {{ $student->complemento }} @endif</span></td>
        </tr>
        <tr>
          <td><span class="field-label">Código RUDE</span><br><span class="field-value strong">{{ $rude?->codigo_rude ?? 'POR GENERAR (SIE)' }}</span></td>
          <td><span class="field-label">N.º Registro Discapacidad IBC</span><br><span class="field-value">{{ $rude?->nro_registro_discapacidad ?? '—' }}</span></td>
        </tr>
        <tr>
          <td colspan="2"><span class="field-label">Certificado de Nacimiento (Oficialía / Libro / Partida / Folio / Año)</span><br>
            <span class="field-value">{{ $rude?->oficialia_nacimiento ?? '—' }} / {{ $rude?->libro_nacimiento ?? '—' }} / {{ $rude?->partida_nacimiento ?? '—' }} / {{ $rude?->folio_nacimiento ?? '—' }} / {{ $rude?->anio_nacimiento ?? '—' }}</span></td>
        </tr>
      </table>
    </div>
  </div>

  <div class="section">
    <div class="section-title">Sección III — Dirección Actual del Estudiante</div>
    <div class="section-body">
      <table class="grid">
        <tr>
          <td style="width:33%;"><span class="field-label">Departamento</span><br><span class="field-value">{{ $rude?->dir_departamento ?? '—' }}</span></td>
          <td style="width:33%;"><span class="field-label">Provincia</span><br><span class="field-value">{{ $rude?->dir_provincia ?? '—' }}</span></td>
          <td><span class="field-label">Municipio</span><br><span class="field-value">{{ $rude?->dir_municipio ?? '—' }}</span></td>
        </tr>
        <tr>
          <td><span class="field-label">Comunidad</span><br><span class="field-value">{{ $rude?->dir_comunidad ?? '—' }}</span></td>
          <td><span class="field-label">Zona/Villa</span><br><span class="field-value">{{ $rude?->dir_zona ?? '—' }}</span></td>
          <td><span class="field-label">Calle / N.º</span><br><span class="field-value">{{ $rude?->dir_calle ?? '—' }} {{ $rude?->dir_nro_vivienda ? 'Nº '.$rude?->dir_nro_vivienda : '' }}</span></td>
        </tr>
        <tr>
          <td><span class="field-label">Teléfono fijo</span><br><span class="field-value">{{ $rude?->dir_telefono ?? '—' }}</span></td>
          <td><span class="field-label">Celular de contacto</span><br><span class="field-value">{{ $rude?->dir_celular ?? '—' }}</span></td>
          <td></td>
        </tr>
      </table>
    </div>
  </div>

  <div class="section">
    <div class="section-title">Sección IV — Aspectos Socioeconómicos</div>
    <div class="section-body">
      <table class="grid">
        <tr>
          <td style="width:33%;"><span class="field-label">4.1 Idioma en que aprendió</span><br><span class="field-value">{{ $rude?->idioma_aprendio ?? '—' }}</span></td>
          <td style="width:33%;"><span class="field-label">4.1 Idioma de uso frecuente</span><br><span class="field-value">{{ \App\Models\StudentRudeRecord::listar($rude?->idiomas_uso_frecuente) ?: '—' }}</span></td>
          <td><span class="field-label">4.1 Autoidentificación cultural</span><br><span class="field-value">{{ $rude?->autoidentificacion_cultural ?? '—' }}</span></td>
        </tr>
        <tr>
          <td><span class="field-label">4.2 Seguro de salud</span><br><span class="field-value">{{ $rude?->salud_seguro ?? '—' }}</span></td>
          <td><span class="field-label">4.2 Centro de Salud cercano</span><br><span class="field-value">{{ $rude?->salud_centro_cercano ? 'Sí' : 'No' }}</span></td>
          <td><span class="field-label">4.2 Veces al Centro</span><br><span class="field-value">{{ $rude?->salud_veces ?? '—' }}</span></td>
        </tr>
        <tr>
          <td><span class="field-label">4.3 Baño / Vivienda</span><br><span class="field-value">{{ $rude?->serv_bano ?? '—' }} / {{ $rude?->serv_vivienda_tipo ?? '—' }}</span></td>
          <td><span class="field-label">4.4 Acceso a internet</span><br><span class="field-value">{{ \App\Models\StudentRudeRecord::listar($rude?->internet_lugares) ?: '—' }} @if($rude?->internet_frecuencia) ({{ $rude?->internet_frecuencia }}) @endif</span></td>
          <td><span class="field-label">4.5 Actividad laboral</span><br><span class="field-value">{{ $rude?->laboral_trabajo ? 'Sí' : 'No' }} @if($rude?->laboral_actividad) — {{ $rude?->laboral_actividad }} @endif</span></td>
        </tr>
        <tr>
          <td><span class="field-label">4.6 Transporte</span><br><span class="field-value">{{ $rude?->transporte_medio ?? '—' }} @if($rude?->transporte_tiempo) ({{ $rude?->transporte_tiempo }}) @endif</span></td>
          <td><span class="field-label">4.7 Abandono escolar</span><br><span class="field-value">{{ $rude?->abandono_escolar ? 'Sí' : 'No' }} @if($rude?->abandono_razones) — {{ \App\Models\StudentRudeRecord::listar($rude?->abandono_razones) }} @endif</span></td>
          <td><span class="field-label">4.8 Discapacidad</span><br><span class="field-value">{{ \App\Models\StudentRudeRecord::listar($rude?->discapacidad_tipos) ?: '—' }} @if($rude?->discapacidad_grado) ({{ $rude?->discapacidad_grado }}) @endif</span></td>
        </tr>
      </table>
    </div>
  </div>

  <div class="section">
    <div class="section-title">Sección V — Padres, Madres o Tutores</div>
    <div class="section-body">
      <table class="grid">
        <tr>
          <td style="width:50%;"><span class="field-label">La o el estudiante vive con</span><br><span class="field-value">{{ \App\Models\StudentRudeRecord::listar($rude?->convive_con) ?: '—' }}</span></td>
          <td><span class="field-label">Estado del trámite</span><br><span class="field-value"><span class="estado-badge estado-{{ $rude?->estado ?? 'PENDIENTE' }}">{{ mb_strtolower($rude?->estado ?? 'PENDIENTE') }}</span></span></td>
        </tr>
      </table>
      <div class="divider"></div>
      <table class="grid">
        <tr>
          <td style="width:25%;"><span class="field-label">Relación</span></td>
          <td style="width:35%;"><span class="field-label">Nombre(s)</span></td>
          <td style="width:40%;"><span class="field-label">Documento / Teléfono / Ocupación</span></td>
        </tr>
        @forelse($student->parentGuardians as $pg)
        <tr>
          <td><span class="field-value strong">{{ $pg->pivot?->tipo_relacion ?? 'Tutor' }}</span></td>
          <td><span class="field-value">{{ $pg->nombres }} {{ $pg->apellidos }}</span></td>
          <td><span class="field-value">{{ $pg->numero_documento ?? '—' }} @if($pg->telefono) &bull; {{ $pg->telefono }} @endif @if($pg->ocupacion) &bull; {{ $pg->ocupacion }} @endif</span></td>
        </tr>
        @empty
        <tr><td colspan="3"><span class="field-value">Sin tutores registrados.</span></td></tr>
        @endforelse
      </table>
    </div>
  </div>

  <table class="sign-row">
    <tr>
      <td>Firma del Padre / Madre / Tutor(a)</td>
      <td>Firma del Director(a) de la Unidad Educativa</td>
      <td>Complemento / Observaciones<br><span style="font-size:6px; font-weight:400;">{{ $rude?->observaciones ?? '—' }}</span></td>
    </tr>
  </table>

  <div class="foot-note">
    Formulario de Registro Único de Estudiantes (RUDE) — RM Nº 0031/2025 — Unidad Educativa San Bernardo, Tarija, Bolivia &bull;
    Generado el {{ now()->format('d/m/Y H:i') }} &bull; El código RUDE es generado por el SIE.
  </div>
</body>
</html>