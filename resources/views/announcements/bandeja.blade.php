@extends('layouts.app')
@section('content')
<h1>Mis Comunicados</h1>
@foreach($announcements as $a)
<div>{{ $a->titulo }} - {{ $a->fecha_publicacion }} - @if($a->es_nuevo)<span>NUEVO</span>@else<span>Leído</span>@endif</div>
@endforeach
{{ $announcements->links() }}
@endsection
