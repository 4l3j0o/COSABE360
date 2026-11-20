<?php

namespace Database\Seeders;

use App\Models\TaxConfiguration;
use Illuminate\Database\Seeder;

class TaxConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        TaxConfiguration::updateOrCreate(
            ['nit_colegio' => '408460020'],
            [
                'razon_social' => 'SOCIEDAD EDUCATIVA SAN BERNARDO DE TARIJA S.A.',
                'tipo_entidad' => 'CON_FINES_LUCRO',
                'exento_iva_educacion' => true,
                'exento_it' => true,
                'iue_exento' => false,
                'modalidad_sfe' => 'COMPUTARIZADA_EN_LINEA',
                'cuis' => null,
                'codigo_sucursal' => '0',
                'agente_retencion_rc_iva' => true,
                'leyenda_iva_exento' => 'EXENTO - NO GENERA DERECHO A CRÉDITO FISCAL',
                'leyenda_it_exento' => 'EXENTO IT - ART. 13 INC. F LEY 843',
                'leyenda_ley070' => 'LEY 070 - EDUCACION EXENTA IVA ART. 13 LEY 843',
                'monto_mensualidad' => 785.50,
                'estado' => 'ACTIVO',
            ]
        );
    }
}
