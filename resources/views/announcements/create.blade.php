@extends('layouts.app')
@section('content')
<h1>Crear Comunicado</h1>
<form method='POST' action='{{ route('comunicados.store') }}'>
@csrf
<input name='titulo' placeholder='Título' required>
<textarea name='contenido' required></textarea>
<input type='datetime-local' name='fecha_publicacion'>
<select name='destinatarios[0][tipo_destinatario]'><option>TODOS</option><option>DOCENTES</option><option>ESTUDIANTES</option><option>PADRES</option><option>CURSO_ESPECIFICO</option></select>
<button>Guardar</button>
</form>
@endsection
