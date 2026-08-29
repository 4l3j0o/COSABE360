@extends('layouts.app')
@section('content')
<h1>Promedios por Estudiante</h1>
@foreach($promedios as $p)
<div>{{ $p['estudiante'] }} ({{ $p['codigo'] }}) - Promedio: {{ $p['promedio_general'] }}</div>
@endforeach
@endsection
