<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de Evaluación</title>
<style>
  @page { size: A4 landscape; margin: 12mm 10mm; }
  * { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; box-sizing: border-box; }
  body { font-size: 9px; color: #1a2e1a; line-height: 1.4; }
  .brand { font-size: 14px; font-weight: 900; color: #003200; text-transform: uppercase; }
  .subt { font-size: 9px; color: #33412f; margin-top: 2px; }
  .doc { border: 1.5px solid #003200; border-radius: 7px; padding: 10px; margin-bottom: 10px; background: #f6faf6; }
  .section-title { background: #003200; color: #fff; font-size: 10px; font-weight: 800; padding: 5px 8px; border-radius: 5px; margin: 8px 0 5px 0; text-transform: uppercase; }
  table.grid { width: 100%; border-collapse: collapse; }
  table.grid th { background: #003200; color: #fff; font-size: 8px; padding: 5px 4px; text-align: center; }
  table.grid td { border: 1px solid #e2e8f0; padding: 4px; font-size: 8px; }
  table.grid td.left { text-align: left; }
  .aprobado { color: #14532d; font-weight: 800; }
  .reprobado { color: #7f1d1d; font-weight: 800; }
  .resumen { display: table; width: 100%; margin-bottom: 8px; }
  .resumen-cell { display: table-cell; text-align: center; border: 1px solid #d1dfd1; border-radius: 5px; padding: 6px; }
  .resumen-cell .num { font-size: 18px; font-weight: 900; }
  .foot { margin-top: 10px; font-size: 7px; color: #64748b; text-align: center; }
</style>
</head>
<body>
  <div class="doc">
    <div class="brand">U.E. SAN BERNARDO — TARIJA</div>
    <div class="subt">Reporte de Evaluación — {{ $resultado['curso']?->course->nombre }} · {{ $resultado['curso']?->subject->nombre }} · Trimestre {{ $resultado['trimestre'] }}</div>
  </div>

  <div class="resumen">
    <div class="resumen-cell"><div class="num aprobado">{{ $resultado['resumen']['aprobados'] }}</div><div>Aprobados</div></div>
    <div class="resumen-cell"><div class="num reprobado">{{ $resultado['resumen']['reprobados'] }}</div><div>Reprobados</div></div>
    <div class="resumen-cell"><div class="num">{{ $resultado['resumen']['sin_nota'] }}</div><div>Sin nota</div></div>
    <div class="resumen-cell"><div class="num">{{ $resultado['resumen']['total'] }}</div><div>Total</div></div>
  </div>

  @if($resultado['top']->isNotEmpty())
    <div class="section-title">Mejores calificaciones del trimestre</div>
    <table class="grid">
      <tr><th>#</th><th class="left">Estudiante</th><th>Nota</th><th>Valoración</th></tr>
      @foreach($resultado['top'] as $i => $f)
        <tr>
          <td>{{ $i + 1 }}</td>
          <td class="left">{{ $f->student->apellidos }} {{ $f->student->nombres }}</td>
          <td>{{ $f->nota }}</td>
          <td>{{ $f->codigo }}</td>
        </tr>
      @endforeach
    </table>
  @endif

  <div class="section-title">Detalle de estudiantes</div>
  <table class="grid">
    <tr>
      <th>#</th><th class="left">Estudiante</th><th>SE</th><th>SA</th><th>HA</th><th>AU</th>
      <th>Nota</th><th>Valoración</th><th>Estado</th><th>% Asist</th>
    </tr>
    @foreach($resultado['filas'] as $i => $fila)
      <tr>
        <td>{{ $i + 1 }}</td>
        <td class="left">{{ $fila->student->apellidos }} {{ $fila->student->nombres }}</td>
        <td>{{ $fila->criterios['SE'] ?? '—' }}</td>
        <td>{{ $fila->criterios['SA'] ?? '—' }}</td>
        <td>{{ $fila->criterios['HA'] ?? '—' }}</td>
        <td>{{ $fila->criterios['AU'] ?? '—' }}</td>
        <td>{{ $fila->nota ?? '—' }}</td>
        <td>{{ $fila->codigo }}</td>
        <td class="{{ $fila->aprobado ? 'aprobado' : 'reprobado' }}">{{ $fila->aprobado === null ? '—' : ($fila->aprobado ? 'Aprobado' : 'Reprobado') }}</td>
        <td>{{ $fila->asistencia['porcentaje'] !== null ? $fila->asistencia['porcentaje'].'%' : '—' }}</td>
      </tr>
    @endforeach
  </table>

  <div class="foot">Escala oficial (R.M. 473/2021): ED ≤50 · DA 51-68 · DO 69-84 · DP 85-100. Mínimo aprobación: 51. Generado el {{ now()->format('d/m/Y H:i') }}.</div>
</body>
</html>