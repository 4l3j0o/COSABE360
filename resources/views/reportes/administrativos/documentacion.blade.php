@extends('layouts.app')
@section('content')
<h1>Documentación Pendiente</h1>
@foreach($documentos as $d)
<div>{{ $d->student->nombres ?? '' }} - {{ $d->tipo_documento }} - {{ $d->estado }}</div>
@endforeach
{{ $documentos->links() }}
@endsection
