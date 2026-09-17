<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de Evaluación</title>
<style>
  @page { size: A4 landscape; margin: 12mm 10mm; }
  * { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; box-sizing: border-box; }
  body { font-size: 9px; color: #1a2e1a; line-height: 1.4; }
  .membrete { width: 100%; border: 1.5px solid #003200; border-radius: 7px; background: #f6faf6; padding: 8px 10px; margin-bottom: 10px; }
  .membrete td { vertical-align: middle; }
  .logo { width: 70px; text-align: center; }
  .logo img { width: 60px; }
  .brand { font-size: 15px; font-weight: 900; color: #003200; text-transform: uppercase; }
  .brand-sub { font-size: 9px; color: #33412f; margin-top: 2px; }
  .doc-title { font-size: 12px; font-weight: 800; color: #003200; text-transform: uppercase; margin-top: 4px; }
  .meta { font-size: 8.5px; color: #33412f; margin-top: 4px; }
  .meta b { color: #003200; }
  .section-title { background: #003200; color: #fff; font-size: 10px; font-weight: 800; padding: 5px 8px; border-radius: 5px; margin: 8px 0 5px 0; text-transform: uppercase; }
  table.grid { width: 100%; border-collapse: collapse; }
  table.grid th { background: #003200; color: #fff; font-size: 8px; padding: 5px 4px; text-align: center; }
  table.grid td { border: 1px solid #d1dfd1; padding: 4px; font-size: 8px; text-align: center; }
  table.grid td.left { text-align: left; }
  table.grid tr.zebra td { background: #f2f6f2; }
  .aprobado { color: #14532d; font-weight: 800; }
  .reprobado { color: #7f1d1d; font-weight: 800; }
  .resumen { display: table; width: 100%; margin-bottom: 8px; }
  .resumen-cell { display: table-cell; text-align: center; border: 1px solid #d1dfd1; border-radius: 5px; padding: 6px; background: #f6faf6; }
  .resumen-cell .num { font-size: 18px; font-weight: 900; }
  .firmas { width: 100%; margin-top: 26px; }
  .firmas td { text-align: center; font-size: 8px; color: #33412f; width: 33%; }
  .linea { border-top: 1px solid #003200; margin: 0 24px; padding-top: 3px; }
  .foot { margin-top: 10px; font-size: 7px; color: #64748b; text-align: center; }
</style>
</head>
<body>
  <table class="membrete">
    <tr>
      @if(! empty($logo))
        <td class="logo"><img src="{{ $logo }}" alt="Logo"></td>
      @endif
      <td>
        <div class="brand">Unidad Educativa San Bernardo — Tarija</div>
        <div class="brand-sub">Sistema de Gestión Escolar CSB 360</div>
        <div class="doc-title">Reporte de Evaluación — Aprobados, reprobados, mejores calificaciones y asistencia</div>
        <div class="meta">
          <b>Curso:</b> {{ $resultado['curso']?->course->nombre ?? '—' }}
          · <b>Materia:</b> {{ $resultado['curso']?->subject->nombre ?? '—' }}
          · <b>Trimestre:</b> {{ $resultado['trimestre'] }}°
          · <b>Gestión:</b> {{ $resultado['curso']?->course->gestion->nombre ?? now()->year }}
          @if($resultado['curso']?->teacher)
            · <b>Docente:</b> {{ trim(($resultado['curso']->teacher->nombres ?? '').' '.($resultado['curso']->teacher->apellidos ?? '')) }}
          @endif
          · <b>Generado:</b> {{ now()->format('d/m/Y H:i') }}
        </div>
      </td>
    </tr>
  </table>

  <div class="resumen">
    <div class="resumen-cell"><div class="num aprobado">{{ $resultado['resumen']['aprobados'] }}</div><div>Aprobados</div></div>
    <div class="resumen-cell"><div class="num reprobado">{{ $resultado['resumen']['reprobados'] }}</div><div>Reprobados</div></div>
    <div class="resumen-cell"><div class="num">{{ $resultado['resumen']['sin_nota'] }}</div><div>Sin nota</div></div>
    <div class="resumen-cell"><div class="num">{{ $resultado['resumen']['total'] }}</div><div>Total estudiantes</div></div>
  </div>

  @if($resultado['top']->isNotEmpty())
    <div class="section-title">Mejores calificaciones del trimestre</div>
    <table class="grid">
      <tr><th style="width:6%">#</th><th class="left">Estudiante</th><th style="width:12%">Nota</th><th style="width:16%">Valoración</th></tr>
      @foreach($resultado['top'] as $i => $f)
        <tr class="{{ $i % 2 === 1 ? 'zebra' : '' }}">
          <td>{{ $i + 1 }}</td>
          <td class="left">{{ $f->student->apellidos }} {{ $f->student->nombres }}</td>
          <td><strong>{{ $f->nota }}</strong></td>
          <td>{{ $f->codigo }}</td>
        </tr>
      @endforeach
    </table>
  @endif

  <div class="section-title">Detalle de estudiantes — Trimestre {{ $resultado['trimestre'] }}°</div>
  <table class="grid">
    <tr>
      <th style="width:4%">#</th><th class="left">Estudiante</th>
      <th style="width:6%">SE<br>(10%)</th><th style="width:6%">SA<br>(45%)</th><th style="width:6%">HA<br>(40%)</th><th style="width:6%">AU<br>(5%)</th>
      <th style="width:7%">Nota</th><th style="width:10%">Valoración</th><th style="width:10%">Estado</th><th style="width:8%">% Asist.</th>
    </tr>
    @foreach($resultado['filas'] as $i => $fila)
      <tr class="{{ $i % 2 === 1 ? 'zebra' : '' }}">
        <td>{{ $i + 1 }}</td>
        <td class="left">{{ $fila->student->apellidos }} {{ $fila->student->nombres }}</td>
        <td>{{ $fila->criterios['SE'] ?? '—' }}</td>
        <td>{{ $fila->criterios['SA'] ?? '—' }}</td>
        <td>{{ $fila->criterios['HA'] ?? '—' }}</td>
        <td>{{ $fila->criterios['AU'] ?? '—' }}</td>
        <td><strong>{{ $fila->nota ?? '—' }}</strong></td>
        <td>{{ $fila->codigo }}</td>
        <td class="{{ $fila->aprobado ? 'aprobado' : 'reprobado' }}">{{ $fila->aprobado === null ? '—' : ($fila->aprobado ? 'Aprobado' : 'Reprobado') }}</td>
        <td>{{ $fila->asistencia['porcentaje'] !== null ? $fila->asistencia['porcentaje'].'%' : '—' }}</td>
      </tr>
    @endforeach
  </table>

  <table class="firmas">
    <tr>
      <td><div class="linea">Docente de materia<br><strong>Firma y sello</strong></div></td>
      <td><div class="linea">Dirección<br><strong>V° B°</strong></div></td>
      <td><div class="linea">Padre / Tutor<br><strong>Enterado</strong></div></td>
    </tr>
  </table>

  <div class="foot">Escala oficial (R.M. 473/2021): ED ≤50 · DA 51-68 · DO 69-84 · DP 85-100. Mínimo de aprobación: 51. Asistencia del trimestre. Generado el {{ now()->format('d/m/Y H:i') }} por CSB 360.</div>
</body>
</html>
