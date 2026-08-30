@extends('layouts.app')
@section('content')
<h1>Reportes Administrativos</h1>
<a href='{{ route('reportes.administrativos.inscripciones') }}'>Inscripciones</a>
<a href='{{ route('reportes.administrativos.matriculas') }}'>Matrículas</a>
<a href='{{ route('reportes.administrativos.pagos') }}'>Pagos y Deudas</a>
<a href='{{ route('reportes.administrativos.documentacion') }}'>Documentación pendiente</a>
@endsection
