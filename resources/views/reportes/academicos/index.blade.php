@extends('layouts.app')
@section('content')
<h1>Reportes Académicos</h1>
<p>Filtros: Período académico, Curso, Materia, Bimestre</p>
<a href='{{ route('reportes.academicos.calificaciones') }}'>Calificaciones por curso/materia</a>
<a href='{{ route('reportes.academicos.promedios') }}'>Promedios por estudiante</a>
@endsection
