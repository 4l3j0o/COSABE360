<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Lista de RUDEs</title>
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
        <div class="doc-title">Lista de RUDEs del Paralelo</div>
        <div class="meta">
          <b>Curso:</b> {{ $resultado['curso'] ?? '—' }}
          · <b>Generado:</b> {{ now()->format('d/m/Y H:i') }}
        </div>
      </td>
    </tr>
  </table>

  <div class="section-title">Detalle — {{ count($resultado['filas']) }} estudiante(s)</div>
  <table class="grid">
    <tr>
      <th style="width:4%">#</th><th style="width:8%">Código</th><th class="left">Apellidos</th><th class="left">Nombres</th>
      <th style="width:10%">CI</th><th style="width:14%">Código RUDE</th><th style="width:10%">SIE UE</th>
      <th style="width:10%">Estado</th><th style="width:9%">Turno</th>
    </tr>
    @foreach($resultado['filas'] as $i => $fila)
      <tr class="{{ $i % 2 === 1 ? 'zebra' : '' }}">
        <td>{{ $i + 1 }}</td>
        <td>{{ $fila['codigo'] }}</td>
        <td class="left">{{ $fila['apellidos'] }}</td>
        <td class="left">{{ $fila['nombres'] }}</td>
        <td>{{ $fila['ci'] ?: '—' }}</td>
        <td>{{ $fila['rude'] }}</td>
        <td>{{ $fila['sie'] }}</td>
        <td>{{ $fila['estado'] }}</td>
        <td>{{ $fila['turno'] ?: '—' }}</td>
      </tr>
    @endforeach
  </table>

  <table class="firmas">
    <tr>
      <td><div class="linea">Tutor / Encargado<br><strong>Firma y sello</strong></div></td>
      <td><div class="linea">Secretaría<br><strong>V° B°</strong></div></td>
      <td><div class="linea">Dirección<br><strong>V° B°</strong></div></td>
    </tr>
  </table>

  <div class="foot">Formulario RUDE RM 0031/2025. Generado el {{ now()->format('d/m/Y H:i') }} por CSB 360.</div>
</body>
</html>
