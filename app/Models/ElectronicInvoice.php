<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ElectronicInvoice extends Model
{
    protected $fillable = [
        'nro_factura',
        'cuf',
        'cufd',
        'cuis',
        'fecha_emision',
        'hora_emision',
        'tipo_factura',
        'tipo_emision',
        'modalidad',
        'codigo_sucursal',
        'nit_emisor',
        'razon_social_emisor',
        'nit_receptor',
        'tipo_doc_receptor',
        'nro_doc_receptor',
        'complemento_receptor',
        'razon_social_receptor',
        'direccion_receptor',
        'email_receptor',
        'total_exento',
        'total_descuentos',
        'total_iva',
        'total_it',
        'total_factura',
        'total_factura_texto',
        'estado',
        'xml_enviado',
        'xml_respuesta',
        'leyenda1',
        'leyenda2',
        'leyenda3',
    ];

    protected function casts(): array
    {
        return [
            'nro_factura' => 'integer',
            'fecha_emision' => 'date',
            'hora_emision' => 'datetime:H:i:s',
            'total_exento' => 'decimal:2',
            'total_descuentos' => 'decimal:2',
            'total_iva' => 'decimal:2',
            'total_it' => 'decimal:2',
            'total_factura' => 'decimal:2',
        ];
    }

    public const ESTADOS = ['ENVIADA', 'AUTORIZADA', 'RECHAZADA', 'CONTINGENCIA', 'ANULADA'];
    public const TIPOS_EMISION = ['ONLINE', 'CONTINGENCIA'];

    public function detalles(): HasMany
    {
        return $this->hasMany(InvoiceDetail::class, 'electronic_invoice_id');
    }

    // Alias para compatibilidad diagrama
    public function invoiceDetails(): HasMany
    {
        return $this->detalles();
    }

    public function contingencyQueue(): HasOne
    {
        return $this->hasOne(ContingencyQueue::class, 'electronic_invoice_id');
    }

    public function estaAutorizada(): bool
    {
        return $this->estado === 'AUTORIZADA';
    }

    public function estaEnContingencia(): bool
    {
        return $this->estado === 'CONTINGENCIA';
    }
}
