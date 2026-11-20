<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ranking de mejores promedios</title>
    <style>
        @page { size: A4 landscape; margin: 12mm 10mm; }
        * { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; box-sizing: border-box; }
        body { font-size: 9px; color: #1a2e1a; line-height: 1.35; }
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
        .rank { font-weight: 800; color: #003200; }
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
                <div class="doc-title">Ranking de mejores promedios</div>
                <div class="meta">
                    <b>Ámbito:</b> {{ $resultado['scope_label'] }} ·
                    <b>Materia:</b> {{ $resultado['materia']?->nombre ?? 'Promedio general' }} ·
                    <b>Período:</b> {{ $resultado['periodo_label'] }} ·
                    <b>Generado:</b> {{ now()->format('d/m/Y H:i') }}
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">
        Ranking — {{ $resultado['total_filas'] }} estudiante(s) con nota ·
        {{ $resultado['sin_nota'] }} sin nota
    </div>

    <table class="grid">
        <thead>
            <tr>
                <th style="width:5%">#</th>
                <th style="width:8%">Código</th>
                <th class="left">Estudiante</th>
                <th style="width:18%">Curso</th>
                <th style="width:15%">Materia</th>
                <th style="width:8%">Promedio</th>
                <th style="width:14%">Valoración</th>
            </tr>
        </thead>
        <tbody>
            @foreach($resultado['filas'] as $fila)
                <tr class="{{ $loop->even ? 'zebra' : '' }}">
                    <td class="rank">{{ $fila->posicion }}</td>
                    <td>{{ $fila->student->codigo }}</td>
                    <td class="left">{{ $fila->student->apellidos }} {{ $fila->student->nombres }}</td>
                    <td>{{ $fila->section->codigoCompleto() }} — {{ $fila->section->grade?->nombre }}</td>
                    <td>{{ $resultado['materia']?->nombre ?? 'General' }}</td>
                    <td class="rank">{{ $fila->promedio }}</td>
                    <td>{{ $fila->valoracion['codigo'] }} — {{ $fila->valoracion['nombre'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="foot">Generado el {{ now()->format('d/m/Y H:i') }} por CSB 360.</div>
</body>
</html>
