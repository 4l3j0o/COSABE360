@extends('layouts.app')
@section('content')
<h1>Comunicados</h1>
@foreach($announcements as $a)
<div>{{ $a->titulo }} - {{ mb_strtolower($a->estado) }} - {{ $a->fecha_publicacion }}</div>
@endforeach
{{ $announcements->links() }}
@endsection
