<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Boletín {{ $student->codigo }}</title>
<style>
  @page { size: A4 portrait; margin: 14mm 12mm; }
  * { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; box-sizing: border-box; }
  body { font-size: 9px; color: #1a2e1a; line-height: 1.5; }
  .header { border: 1.8px solid #003200; border-radius: 7px; padding: 12px; margin-bottom: 12px; background: #f6faf6; display: table; width: 100%; }
  .header .left { display: table-cell; }
  .header .right { display: table-cell; text-align: right; }
  .brand { font-size: 15px; font-weight: 900; color: #003200; text-transform: uppercase; }
  .subt { font-size: 8px; color: #33412f; margin-top: 2px; }
  .datos { width: 100%; margin-bottom: 10px; }
  .datos td { padding: 2px 4px; font-size: 9px; }
  .datos .lbl { color: #64748b; font-weight: 700; text-transform: uppercase; font-size: 7px; }
  .datos .val { color: #0f172a; font-weight: 600; }
  table.grid { width: 100%; border-collapse: collapse; }
  table.grid th { background: #003200; color: #fff; font-size: 8px; padding: 5px; text-align: center; }
  table.grid td { border: 1px solid #e2e8f0; padding: 5px; font-size: 8px; }
  table.grid td.left { text-align: left; }
  .reprobado { color: #7f1d1d; font-weight: 800; }
  .aprobado { color: #14532d; font-weight: 800; }
  .tfoot td { background: #f1f5f1; font-weight: 800; }
  .foot { margin-top: 12px; font-size: 7px; color: #64748b; text-align: center; }
  .sign { margin-top: 30px; width: 100%; }
  .sign td { width: 50%; text-align: center; padding-top: 24px; border-top: 1px solid #94a3b8; font-size: 8px; }
</style>
</head>
<body>
  <div class="header">
    <div class="left">
      <div class="brand">U.E. SAN BERNARDO — TARIJA</div>
      <div class="subt">Libreta Electrónica de Calificaciones · Formato SIE · Gestión {{ $periodo }}</div>
    </div>
    <div class="right">
      <div class="subt">Código RUDE: {{ $student->rudeRecord?->codigo_rude ?? '—' }}</div>
      <div class="subt">Código: {{ $student->codigo }}</div>
    </div>
  </div>

  <table class="datos">
    <tr><td class="lbl">Estudiante</td><td class="val">{{ $student->apellidos }}, {{ $student->nombres }}</td><td class="lbl">Curso</td><td class="val">{{ $curso?->nombre ?? '—' }}</td></tr>
    <tr><td class="lbl">CI</td><td class="val">{{ $student->numero_documento }} {{ $student->complemento }}</td><td class="lbl">Promedio General</td><td class="val">{{ $promedioGeneral ?? '—' }}</td></tr>
  </table>

  <table class="grid">
    <tr>
      <th class="left">Área / Materia</th><th>Promedio Anual</th><th>Valoración</th><th>Estado</th>
    </tr>
    @foreach($filas as $fila)
      <tr>
        <td class="left">{{ $fila['materia'] }}</td>
        <td>{{ $fila['nota'] ?? '—' }}</td>
        <td>{{ $fila['valoracion']['codigo'] ?? '—' }}</td>
        <td class="{{ $fila['nota'] !== null && $fila['nota'] < 51 ? 'reprobado' : 'aprobado' }}">{{ $fila['nota'] === null ? '—' : ($fila['nota'] < 51 ? 'Retenido' : 'Aprobado') }}</td>
      </tr>
    @endforeach
    <tr class="tfoot">
      <td>PROMEDIO GENERAL</td>
      <td>{{ $promedioGeneral ?? '—' }}</td>
      <td>{{ $promedioGeneral !== null ? \App\Services\EvaluationService::valoracion($promedioGeneral)['codigo'] : '—' }}</td>
      <td>{{ $promedioGeneral !== null && $promedioGeneral < 51 ? 'Retenido' : ($promedioGeneral !== null ? 'Aprobado' : '—') }}</td>
    </tr>
  </table>

  <div class="foot">Escala oficial (R.M. 473/2021): ED ≤50 · DA 51-68 · DO 69-84 · DP 85-100. Mínimo aprobación: 51. Generado el {{ now()->format('d/m/Y H:i') }}.</div>

  <table class="sign">
    <tr>
      <td>Firma del Director(a) de la Unidad Educativa</td>
      <td>Firma del Padre / Madre / Tutor(a)</td>
    </tr>
  </table>
</body>
</html>