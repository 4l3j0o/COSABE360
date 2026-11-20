<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    use AuthorizesRequests;
    /**
     * Vuelve al listado conservando filtros/página/pestaña que vengan en el
     * formulario bajo `_f` (+ `page` plano, que exige el paginador).
     * Si viene `volver` interno (otra página que usa la acción), manda ahí.
     * Sin ellos, redirect plano al index (comportamiento previo).
     */
    protected function deVuelta(string $ruta, array $filtros, string $mensaje, string $tipo = 'success')
    {
        $volver = request()->input('volver');
        if (is_string($volver) && $volver !== '' && str_starts_with($volver, url('/'))) {
            return redirect($volver)->with($tipo, $mensaje);
        }

        $query = [];
        $f = request()->input('_f', []);
        if (! is_array($f)) {
            $f = [];
        }
        foreach ($filtros as $clave) {
            $valor = $clave === 'page' ? request()->input('page') : ($f[$clave] ?? null);
            if ((is_string($valor) || is_numeric($valor)) && $valor !== null && $valor !== '') {
                $query[$clave] = $valor;
            }
        }

        return redirect()->route($ruta, $query)->with($tipo, $mensaje);
    }
}
