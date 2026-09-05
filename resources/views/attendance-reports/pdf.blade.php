<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Asistencia</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 18px; margin: 0 0 2px; color: #0E710E; }
        .sub { color: #555; margin-bottom: 14px; }
        .meta { margin-bottom: 14px; font-size: 11px; }
        .meta b { color: #333; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
        th { background: #0E710E; color: #fff; font-size: 11px; text-transform: uppercase; }
        tr:nth-child(even) { background: #f5f5f5; }
        .pct { font-weight: bold; }
        .pct-bajo { color: #b91c1c; }
        .footer { margin-top: 16px; font-size: 10px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <h1>U.E. San Bernardo · CSB 360</h1>
    <div class="sub">Reporte de Asistencia</div>

    <div class="meta">
        <b>Curso:</b> {{ $resultado['curso'] }}
        @if($resultado['materia']) · <b>Materia:</b> {{ $resultado['materia'] }} @endif
        <br>
        <b>Rango:</b> {{ $resultado['rango'] }} · <b>Desde:</b> {{ $resultado['desde'] }} → <b>Hasta:</b> {{ $resultado['hasta'] }} ·
        <b>Generado:</b> {{ now()->format('d/m/Y H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Estudiante</th>
                @foreach($resultado['estados'] as $estado)
                    <th>{{ ucfirst(strtolower($estado)) }}</th>
                @endforeach
                <th>Total</th>
                <th>Asistencia</th>
            </tr>
        </thead>
        <tbody>
            @foreach($resultado['filas'] as $fila)
                <tr>
                    <td>{{ $fila['codigo'] }}</td>
                    <td>{{ $fila['nombre'] }}</td>
                    @foreach($resultado['estados'] as $estado)
                        <td>{{ $fila['conteo'][$estado] }}</td>
                    @endforeach
                    <td>{{ $fila['total'] }}</td>
                    <td class="pct {{ $fila['porcentaje'] < 70 ? 'pct-bajo' : '' }}">{{ $fila['porcentaje'] }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">Documento generado por el Sistema de Gestión Escolar CSB 360 · Unidad Educativa San Bernardo de Tarija</div>
</body>
</html>