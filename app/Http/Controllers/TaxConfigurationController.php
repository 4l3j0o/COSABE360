<?php

namespace App\Http\Controllers;

use App\Models\TaxConfiguration;
use Illuminate\Http\Request;

class TaxConfigurationController extends Controller
{
    public function index()
    {
        $config = TaxConfiguration::firstOrCreate(
            [],
            [
                'nit_colegio' => '',
                'razon_social' => '',
                'tipo_entidad' => 'CON_FINES_LUCRO',
                'exento_iva_educacion' => true,
                'exento_it' => true,
                'iue_exento' => false,
                'modalidad_sfe' => 'COMPUTARIZADA_EN_LINEA',
                'cuis' => null,
                'codigo_sucursal' => '0',
                'agente_retencion_rc_iva' => true,
                'leyenda_iva_exento' => '',
                'leyenda_it_exento' => '',
                'leyenda_ley070' => '',
                'monto_mensualidad' => 0,
                'estado' => 'ACTIVO',
            ]
        );

        return view('config.tax-configuration', compact('config'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'nit_colegio' => ['required', 'string', 'max:20'],
            'razon_social' => ['required', 'string', 'max:200'],
            'tipo_entidad' => ['required', 'in:CON_FINES_LUCRO,SIN_FINES_LUCRO'],
            'exento_iva_educacion' => ['boolean'],
            'exento_it' => ['boolean'],
            'iue_exento' => ['boolean'],
            'modalidad_sfe' => ['required', 'in:COMPUTARIZADA_EN_LINEA,ELECTRONICA_EN_LINEA,PORTAL_WEB'],
            'cuis' => ['nullable', 'string', 'max:20'],
            'codigo_sucursal' => ['required', 'string', 'max:10'],
            'agente_retencion_rc_iva' => ['boolean'],
            'leyenda_iva_exento' => ['required', 'string', 'max:255'],
            'leyenda_it_exento' => ['required', 'string', 'max:255'],
            'leyenda_ley070' => ['required', 'string', 'max:255'],
            'monto_mensualidad' => ['required', 'numeric', 'min:0'],
            'estado' => ['required', 'in:ACTIVO,INACTIVO'],
        ]);

        $validated['exento_iva_educacion'] = $request->boolean('exento_iva_educacion');
        $validated['exento_it'] = $request->boolean('exento_it');
        $validated['iue_exento'] = $request->boolean('iue_exento');
        $validated['agente_retencion_rc_iva'] = $request->boolean('agente_retencion_rc_iva');

        $config = TaxConfiguration::first();
        $config->update($validated);

        return back()->with('mensaje', 'Configuración tributaria actualizada correctamente.');
    }
}
