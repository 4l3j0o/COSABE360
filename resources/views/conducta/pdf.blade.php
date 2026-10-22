<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h1 { font-size: 16px; margin-bottom: 2px; }
        p.subtitulo { font-size: 10px; color: #555; margin: 0 0 2px; }
        p.fecha { font-size: 9px; color: #777; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #999; padding: 4px 6px; text-align: left; vertical-align: top; }
        th { background: #f0f0f0; font-size: 10px; }
        .vacio { text-align: center; color: #777; padding: 16px; }
    </style>
</head>
<body>
    <h1>{{ $titulo }}</h1>
    @if(!empty($subtitulo))
        <p class="subtitulo">{{ $subtitulo }}</p>
    @endif
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
                <th>Descripción</th>
                <th>Reportado por</th>
            </tr>
        </thead>
        <tbody>
            @forelse($filas as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r->fecha->format('d/m/Y') }}</td>
                    <td>{{ $r->hora ? substr((string) $r->hora, 0, 5) : '-' }}</td>
                    <td>{{ $r->student->apellidos }} {{ $r->student->nombres }}</td>
                    <td>{{ $r->section->codigo ?? $r->section->nombre ?? '-' }}</td>
                    <td>{{ $r->subject->nombre ?? 'General' }}</td>
                    <td>{{ $r->descripcion }}</td>
                    <td>{{ $r->teacher ? $r->teacher->nombres.' '.$r->teacher->apellidos : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="vacio">Sin reportes para los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
