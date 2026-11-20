<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Enfermería</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        .muted { color: #666; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 4px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>
    <h1>Reporte de Enfermería</h1>
    <div class="muted">Generado el {{ now()->format('d/m/Y H:i') }} · {{ $atenciones->count() }} atención(es)</div>
    <table>
        <thead>
            <tr><th>Fecha</th><th>Estudiante</th><th>Curso</th><th>Motivo</th><th>Estado</th><th>Derivación</th></tr>
        </thead>
        <tbody>
            @foreach($atenciones as $atencion)
                <tr>
                    <td>{{ $atencion->fecha_hora?->format('d/m/Y H:i') }}</td>
                    <td>{{ trim(($atencion->student?->apellidos ?? '').' '.($atencion->student?->nombres ?? '')) }}</td>
                    <td>{{ $atencion->section?->codigoCorto() ?? '' }}</td>
                    <td>{{ $atencion->motivo?->nombre ?? '' }}</td>
                    <td>{{ str_replace('_', ' ', $atencion->estado) }}</td>
                    <td>{{ $atencion->tipo_derivacion ? str_replace('_', ' ', $atencion->tipo_derivacion) : '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
