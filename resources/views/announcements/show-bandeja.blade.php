@extends('layouts.app')
@section('content')
<h1>{{ $announcement->titulo }}</h1>
<p>{{ $announcement->contenido }}</p>
<p>Leído: {{ $leido ? 'Sí' : 'No' }}</p>
<form method='POST' action='{{ route('comunicados.bandeja.leido', $announcement) }}'>@csrf @method('PATCH')<button>Marcar leído</button></form>
@endsection
