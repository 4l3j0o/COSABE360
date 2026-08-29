@extends('layouts.app')
@section('content')
<h1>Editar Comunicado</h1>
<form method='POST' action='{{ route('comunicados.update', $announcement) }}'>
@csrf
@method('PUT')
<input name='titulo' value='{{ $announcement->titulo }}'>
<textarea name='contenido'>{{ $announcement->contenido }}</textarea>
<button>Actualizar</button>
</form>
@endsection
