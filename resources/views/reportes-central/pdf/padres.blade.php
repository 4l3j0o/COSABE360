<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Lista de Padres de Familia</title>
<style>
  @page { size: A4 landscape; margin: 12mm 10mm; }
  * { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; box-sizing: border-box; }
  body { font-size: 9px; color: #1a2e1a; line-height: 1.4; }
  .membrete { width: 100%; border: 1.5px solid #003200; border-radius: 7px; background: #f6faf6; padding: 8px 10px; margin-bottom: 10px; }
  .membrete td { vertical-align: middle; }
  .logo { width: 95px; text-align: center; }
  .logo img { width: 85px; }
  .brand { font-size: 15px; font-weight: 900; color: #003200; text-transform: uppercase; }
  .brand-sub { font-size: 9px; color: #33412f; margin-top: 2px; }
  .doc-title { font-size: 12px; font-weight: 800; color: #003200; text-transform: uppercase; margin-top: 4px; }
  .meta { font-size: 8.5px; color: #33412f; margin-top: 4px; }
  .meta b { color: #003200; }
  .section-title { background: #003200; color: #fff; font-size: 10px; font-weight: 800; padding: 5px 8px; border-radius: 5px; margin: 8px 0 5px 0; text-transform: uppercase; }
  table.grid { width: 100%; border-collapse: collapse; table-layout: fixed; }
  table.grid th { background: #003200; color: #fff; font-size: 8px; padding: 5px 4px; text-align: center; overflow: hidden; }
  table.grid td { border: 1px solid #d1dfd1; padding: 4px; font-size: 8px; text-align: center; overflow: hidden; word-wrap: break-word; }
  table.grid td.left { text-align: left; }
  table.grid tr.zebra td { background: #f2f6f2; }
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
        <div class="doc-title">Lista de Padres de Familia — Padres y apoderados</div>
        <div class="meta">
          <b>Curso:</b> {{ $resultado['curso'] ?? 'Todos los cursos' }}
          · <b>Generado:</b> {{ now()->format('d/m/Y H:i') }}
        </div>
      </td>
    </tr>
  </table>

  <div class="section-title">Detalle — {{ count($resultado['filas']) }} registro(s)</div>
  <table class="grid">
    <tr>
      <th style="width:4%">#</th><th style="width:6%">Cód.</th><th class="left">Estudiante</th><th style="width:9%">Curso</th>
      <th style="width:10%">Parentesco</th><th class="left">Padre / Tutor</th><th style="width:9%">CI</th>
      <th style="width:9%">Teléfono</th><th style="width:14%">Email</th><th style="width:6%">Princ.</th>
    </tr>
    @foreach($resultado['filas'] as $i => $fila)
      <tr>
        <td>{{ $i + 1 }}</td>
        <td>{{ $fila['codigo'] }}</td>
        <td class="left">{{ $fila['estudiante'] }}</td>
        <td>{{ $fila['curso'] }}</td>
        <td>{{ $fila['relacion'] }}</td>
        <td class="left">{{ trim($fila['apellidos'].' '.$fila['nombres']) ?: '—' }}</td>
        <td>{{ $fila['ci'] ?: '—' }}</td>
        <td>{{ $fila['telefono'] ?: '—' }}</td>
        <td>{{ $fila['email'] ?: '—' }}</td>
        <td>{{ $fila['es_principal'] ? 'Sí' : '' }}</td>
      </tr>
    @endforeach
  </table>

  <div class="foot">Generado el {{ now()->format('d/m/Y H:i') }} por CSB 360.</div>
</body>
</html>
