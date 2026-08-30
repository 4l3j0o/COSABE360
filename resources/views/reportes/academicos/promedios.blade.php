@extends('layouts.app')
@section('content')
<h1>Promedios por Estudiante</h1>
<a href="{{ route('reportes.export.academico.promedios', array_merge(request()->query(), ['format'=>'pdf'])) }}">PDF</a>
<a href="{{ route('reportes.export.academico.promedios', array_merge(request()->query(), ['format'=>'excel'])) }}">Excel</a>
@foreach($promedios as $p)
<div>{{ $p['estudiante'] }} ({{ $p['codigo'] }}) - Promedio: {{ $p['promedio_general'] }}</div>
@endforeach
@endsection
