@extends('layouts.app')
@section('content')
<h1>Reportes Académicos</h1>
<p>Filtros: Período académico, Curso, Materia, Bimestre</p>
<a href='{{ route('reportes.academicos.calificaciones') }}'>Calificaciones por curso/materia</a>
<a href='{{ route('reportes.academicos.promedios') }}'>Promedios por estudiante</a>
<hr>
<a href='{{ route('reportes.export.academico.calificaciones', ['format'=>'pdf']) }}'>Exportar Calificaciones PDF</a>
<a href='{{ route('reportes.export.academico.calificaciones', ['format'=>'excel']) }}'>Exportar Calificaciones Excel</a>
<a href='{{ route('reportes.export.academico.promedios', ['format'=>'pdf']) }}'>Exportar Promedios PDF</a>
<a href='{{ route('reportes.export.academico.promedios', ['format'=>'excel']) }}'>Exportar Promedios Excel</a>
@endsection
