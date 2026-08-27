<?php

namespace Tests\Unit;

use App\Models\TaxConfiguration;
use Database\Seeders\TaxConfigurationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_carga_configuracion_san_bernardo(): void
    {
        $this->seed(TaxConfigurationSeeder::class);

        $config = TaxConfiguration::query()->first();

        $this->assertNotNull($config);
        $this->assertSame('408460020', $config->nit_colegio);
        $this->assertSame('SOCIEDAD EDUCATIVA SAN BERNARDO DE TARIJA S.A.', $config->razon_social);
        $this->assertTrue($config->exento_iva_educacion);
        $this->assertTrue($config->exento_it);
        $this->assertFalse($config->iue_exento);
        $this->assertSame('COMPUTARIZADA_EN_LINEA', $config->modalidad_sfe);
        $this->assertTrue($config->estaActiva());
    }

    public function test_el_seeder_es_idempotente(): void
    {
        $this->seed(TaxConfigurationSeeder::class);
        $this->seed(TaxConfigurationSeeder::class);

        $this->assertSame(1, TaxConfiguration::count());
    }
}
