<x-mail::message>
# Factura SFE Generada - Mensualidad {{ $mesNombre }}

Estimado/a tutor/a,

Se ha registrado el pago de la mensualidad de **{{ $estudianteNombre }}** correspondiente a **{{ $mesNombre }}** y se emiti� la factura electr�nica.

**Detalles:**
- Factura N�: **{{ str_pad($invoice->nro_factura, 8, "0", STR_PAD_LEFT) }}**
- CUF: `{{ $invoice->cuf }}`
- Fecha: {{ $invoice->fecha_emision instanceof \Carbon\Carbon ? $invoice->fecha_emision->format("d/m/Y") : $invoice->fecha_emision }} {{ substr($invoice->hora_emision,0,8) }}
- Total: **Bs {{ number_format($invoice->total_factura, 2) }}** (Exento IVA/IT)
- Estado: {{ $invoice->estado }}

La factura en PDF est� adjunta a este correo, lista para imprimir y compartir. Tambi�n puede verla desde el sistema en "Pagos de mensualidades" del estudiante.

<x-mail::button :url="route('electronic-invoices.pdf', $invoice)">
Ver Factura PDF
</x-mail::button>

Gracias por su puntualidad.

Saludos cordiales,<br>
**U.E. San Bernardo - Tarija**<br>
Sistema de Facturaci�n Electr�nica - Sector Educativo Exento

<small style="color:#64748b;">Este es un correo autom�tico, no responda directamente. Si tiene dudas contacte a Secretar�a.</small>
</x-mail::message>
