<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceDetail extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'electronic_invoice_id',
        'nro_registro',
        'codigo_servicio',
        'descripcion',
        'cantidad',
        'unidad_medida',
        'precio_unitario',
        'monto_descuento',
        'sub_total',
        'iva_tipo',
        'iva_tasa',
        'iva_monto',
        'it_tipo',
        'it_tasa',
        'it_monto',
        'leyenda',
    ];

    protected function casts(): array
    {
        return [
            'nro_registro' => 'integer',
            'cantidad' => 'decimal:2',
            'precio_unitario' => 'decimal:2',
            'monto_descuento' => 'decimal:2',
            'sub_total' => 'decimal:2',
            'iva_tasa' => 'decimal:2',
            'iva_monto' => 'decimal:2',
            'it_tasa' => 'decimal:2',
            'it_monto' => 'decimal:2',
        ];
    }

    public function electronicInvoice(): BelongsTo
    {
        return $this->belongsTo(ElectronicInvoice::class, 'electronic_invoice_id');
    }

    public function servicio(): BelongsTo
    {
        // Relación lógica por codigo_servicio -> educational_services.codigo_sin (no FK física)
        return $this->belongsTo(EducationalService::class, 'codigo_servicio', 'codigo_sin');
    }
}
