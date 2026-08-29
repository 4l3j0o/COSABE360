@extends('layouts.app')
@section('content')
<h1>Historial de Operaciones</h1>
<form method='GET'>
<select name='user_id'><option value=''>-- Usuario --</option>@foreach($usuarios as $u)<option value='{{ $u->id }}' @selected(request('user_id')==$u->id)>{{ $u->nombre }} {{ $u->apellido }} ({{ $u->rol->nombre ?? '' }})</option>@endforeach</select>
<select name='modulo'><option value=''>-- Módulo --</option>@foreach($modulos as $m)<option value='{{ $m }}' @selected(request('modulo')==$m)>{{ $m }}</option>@endforeach</select>
<input type='date' name='fecha_desde' value='{{ request('fecha_desde') }}'>
<input type='date' name='fecha_hasta' value='{{ request('fecha_hasta') }}'>
<button>Filtrar</button>
</form>
<table><thead><tr><th>Quién</th><th>Acción</th><th>Módulo</th><th>Cuándo</th></tr></thead><tbody>
@foreach($historial as $h)
<tr><td>{{ $h->user->nombre ?? 'Sistema' }} {{ $h->user->apellido ?? '' }}</td><td>{{ $h->accion }}</td><td>{{ $h->modulo }}</td><td>{{ $h->created_at->format('d/m/Y H:i') }}</td></tr>
@endforeach
</tbody></table>
{{ $historial->links() }}
@endsection
