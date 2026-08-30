@extends('layouts.app')
@section('content')
<h1>Reporte Matrículas</h1>
@foreach($matriculas as $m)
<div>{{ $m->enrollment->student->nombres ?? '' }} - {{ $m->course->nombre ?? '' }} - {{ mb_strtolower($m->estado) }}</div>
@endforeach
{{ $matriculas->links() }}
@endsection
