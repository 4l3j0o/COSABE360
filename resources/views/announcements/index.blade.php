@extends('layouts.app')
@section('content')
<h1>Comunicados</h1>
@foreach($announcements as $a)
<div>{{ $a->titulo }} - {{ $a->estado }} - {{ $a->fecha_publicacion }}</div>
@endforeach
{{ $announcements->links() }}
@endsection
