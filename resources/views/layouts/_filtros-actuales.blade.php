{{-- Conserva filtros, página y pestaña del listado al guardar/eliminar:
     el controlador los devuelve en el redirect (ver Controller::deVuelta).
     Usar dentro de formularios que viven en páginas de listado.
     `page` va plano (lo exige el paginador); el resto viaja bajo `_f`
     para no chocar con campos del modelo (ej. `estado`). --}}
@if(request()->has('page'))
    <input type="hidden" name="page" value="{{ request()->query('page') }}">
@endif
@foreach(request()->query() as $clave => $valor)
    @if($clave !== 'page' && (is_string($valor) || is_numeric($valor)))
        <input type="hidden" name="_f[{{ $clave }}]" value="{{ $valor }}">
    @endif
@endforeach
