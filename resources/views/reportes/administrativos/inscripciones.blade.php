@extends('layouts.app')
@section('content')
<h1>Reporte Inscripciones</h1>
@foreach($inscripciones as $i)
<div>{{ $i->student->nombres ?? '' }} - {{ $i->estado }} - {{ $i->academicYear->nombre ?? '' }}</div>
@endforeach
{{ $inscripciones->links() }}
@endsection
