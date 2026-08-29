@extends('layouts.app')
@section('content')
<h1>Calificaciones por Curso/Materia</h1>
@foreach($scores as $s)
<div>{{ $s->student->nombres ?? '' }} - {{ $s->assessment->nombre }} - {{ $s->nota }}</div>
@endforeach
{{ $scores->links() }}
@endsection
