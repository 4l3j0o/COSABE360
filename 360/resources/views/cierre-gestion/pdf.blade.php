<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Cierre de Gestión</title>
<style>
  @page { size: A4 landscape; margin: 12mm 10mm; }
  * { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; box-sizing: border-box; }
  body { font-size: 8px; color: #1a2e1a; }
  .brand { font-size: 14px; font-weight: 900; color: #003200; text-transform: uppercase; }
  .subt { font-size: 9px; color: #33412f; margin-top: 2px; }
  .doc { border: 1.5px solid #003200; border-radius: 7px; padding: 10px; margin-bottom: 10px; background: #f6faf6; }
  .resumen { display: table; width: 100%; margin-bottom: 8px; }
  .resumen-cell { display: table-cell; text-align: center; border: 1px solid #d1dfd1; border-radius: 5px; padding: 6px; }
  .resumen-cell .num { font-size: 18px; font-weight: 900; }
  table.grid { width: 100%; border-collapse: collapse; }
  table.grid th { background: #003200; color: #fff; font-size: 7px; padding: 4px; text-align: center; }
  table.grid td { border: 1px solid #e2e8f0; padding: 3px; font-size: 7px; text-align: center; }
  table.grid td.left { text-align: left; }
  .retenido { color: #7f1d1d; font-weight: 800; }
  .promovido { color: #14532d; font-weight: 800; }
  .foot { margin-top: 10px; font-size: 7px; color: #64748b; text-align: center; }
</style>
</head>
<body>
  <div class="doc">
    <div class="brand">U.E. SAN BERNARDO — TARIJA</div>
    <div class="subt">Cierre de Gestión — {{ $resultado['curso']->nombre }}</div>
  </div>

  <div class="resumen">
    <div class="resumen-cell"><div class="num promovido">{{ $resultado['resumen']['promovidos'] }}</div><div>Promovidos</div></div>
    <div class="resumen-cell"><div class="num retenido">{{ $resultado['resumen']['retenidos'] }}</div><div>Retenidos</div></div>
    <div class="resumen-cell"><div class="num">{{ $resultado['resumen']['sin_nota'] }}</div><div>Sin nota</div></div>
    <div class="resumen-cell"><div class="num">{{ $resultado['resumen']['total'] }}</div><div>Total</div></div>
  </div>

  <table class="grid">
    <tr>
      <th>#</th><th class="left">Estudiante</th>
      @foreach($resultado['filas']->first()?->materias ?? [] as $m)
        <th>{{ $m['materia'] }}</th>
      @endforeach
      <th>Promedio</th><th>Estado</th>
    </tr>
    @foreach($resultado['filas'] as $i => $fila)
      <tr>
        <td>{{ $i + 1 }}</td>
        <td class="left">{{ $fila->student->apellidos }} {{ $fila->student->nombres }}</td>
        @foreach($fila->materias as $m)
          <td>{{ $m['nota'] ?? '—' }} {{ $m['nota'] !== null ? $m['valoracion']['codigo'] : '' }}</td>
        @endforeach
        <td>{{ $fila->promedio ?? '—' }}</td>
        <td class="{{ $fila->promovido ? 'promovido' : 'retenido' }}">{{ $fila->promovido === null ? '—' : ($fila->promovido ? 'Promovido' : 'Retenido') }}</td>
      </tr>
    @endforeach
  </table>

  <div class="foot">Escala oficial (R.M. 473/2021): ED ≤50 · DA 51-68 · DO 69-84 · DP 85-100. Retenido si una o más áreas &lt; 51. Generado el {{ now()->format('d/m/Y H:i') }}.</div>
</body>
</html>