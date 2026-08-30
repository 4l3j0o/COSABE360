@extends('layouts.app')
@section('content')
<h1>Calificaciones por Curso/Materia</h1>
<a href="{{ route('reportes.export.academico.calificaciones', array_merge(request()->query(), ['format'=>'pdf'])) }}">PDF</a>
<a href="{{ route('reportes.export.academico.calificaciones', array_merge(request()->query(), ['format'=>'excel'])) }}">Excel</a>
@foreach($scores as $s)
<div>{{ $s->student->nombres ?? '' }} - {{ $s->assessment->nombre }} - {{ $s->nota }}</div>
@endforeach
{{ $scores->links() }}
@endsection
