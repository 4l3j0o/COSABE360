<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h1 { font-size: 16px; margin-bottom: 2px; }
        p.fecha { font-size: 10px; color: #666; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #999; padding: 4px 6px; text-align: left; vertical-align: top; }
        th { background: #f0f0f0; font-size: 10px; }
        .tipo { font-weight: bold; }
    </style>
</head>
<body>
    <h1>{{ $titulo }}</h1>
    <p class="fecha">Generado el {{ now()->format('d/m/Y H:i') }} · CSB 360</p>
    <table>
        <thead>
            <tr>
                <th style="width:26px">N.º</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Estudiante</th>
                <th>Curso</th>
                <th>Materia</th>
                <th>Falta</th>
                <th>Descripción</th>
                <th>Reportado por</th>
            </tr>
        </thead>
        <tbody>
            @foreach($filas as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r->fecha->format('d/m/Y') }}</td>
                    <td>{{ $r->hora ? substr($r->hora, 0, 5) : '-' }}</td>
                    <td>{{ $r->student->apellidos }} {{ $r->student->nombres }}</td>
                    <td>{{ $r->section->codigo ?? $r->section->nombre ?? '-' }}</td>
                    <td>{{ $r->subject->nombre ?? 'General' }}</td>
                    <td class="tipo">{{ $r->tipoLabel() }}</td>
                    <td>{{ $r->descripcion }}</td>
                    <td>{{ $r->teacher ? $r->teacher->nombres.' '.$r->teacher->apellidos : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
