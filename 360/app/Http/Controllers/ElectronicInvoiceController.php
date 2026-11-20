<?php

namespace App\Http\Controllers;

use App\Models\ElectronicInvoice;
use App\Models\InvoiceDetail;
use App\Models\InvoiceSequence;
use App\Models\TaxConfiguration;
use App\Support\AccessScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ElectronicInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', ElectronicInvoice::class);

        if ($request->expectsJson()) {
            return $this->apiIndex($request);
        }

        $invoices = AccessScope::aplicarFacturas(ElectronicInvoice::with('detalles'), $request->user())
            ->when($request->query('estado'), fn ($q, $v) => $q->where('estado', $v))
            ->when($request->query('nit_receptor'), fn ($q, $v) => $q->where('nit_receptor', 'like', "%{$v}%"))
            ->orderByDesc('fecha_emision')->orderByDesc('nro_factura')
            ->paginate(15)->withQueryString();

        return view('electronic-invoices.index', compact('invoices'));
    }

    private function apiIndex(Request $request): JsonResponse
    {
        $invoices = AccessScope::aplicarFacturas(ElectronicInvoice::with('detalles'), $request->user())
            ->when($request->query('estado'), fn ($q, $v) => $q->where('estado', $v))
            ->orderByDesc('fecha_emision')->get();
        return response()->json(['data' => $invoices]);
    }

    public function show(ElectronicInvoice $electronicInvoice): JsonResponse
    {
        $this->authorize('view', $electronicInvoice);

        return response()->json(['data' => $electronicInvoice->load('detalles')]);
    }

    /**
     * Emite factura EXENTA para servicio educativo (HU-TRIB-02).
     * Espera: nit_receptor, razon_social_receptor, total_factura, detalles[] (codigo_servicio, descripcion, cantidad, precio_unitario)
     * Genera: nro_factura secuencial, CUF simulado, totales IVA/IT 0, leyendas desde TaxConfiguration.
     */
    public function store(Request $request)
    {
        $this->authorize('create', ElectronicInvoice::class);

        $validated = $request->validate([
            'codigo_sucursal' => ['sometimes', 'string', 'max:10'],
            'nit_receptor' => ['nullable', 'string', 'max:20'],
            'tipo_doc_receptor' => ['nullable', 'string', 'max:20'],
            'nro_doc_receptor' => ['nullable', 'string', 'max:20'],
            'razon_social_receptor' => ['nullable', 'string', 'max:200'],
            'direccion_receptor' => ['nullable', 'string', 'max:255'],
            'email_receptor' => ['nullable', 'email', 'max:150'],
            'total_factura' => ['required', 'numeric', 'min:0'],
            'tipo_emision' => ['sometimes', 'in:ONLINE,CONTINGENCIA'],
            'detalles' => ['sometimes', 'array', 'min:1'],
            'detalles.*.codigo_servicio' => ['required_with:detalles', 'string', 'max:20'],
            'detalles.*.descripcion' => ['required_with:detalles', 'string', 'max:200'],
            'detalles.*.cantidad' => ['sometimes', 'numeric', 'min:0'],
            'detalles.*.precio_unitario' => ['required_with:detalles', 'numeric', 'min:0'],
            'detalles.*.unidad_medida' => ['sometimes', 'string', 'max:20'],
        ]);

        $tax = TaxConfiguration::first();
        $sucursal = $validated['codigo_sucursal'] ?? $tax?->codigo_sucursal ?? '0';
        $nro = InvoiceSequence::siguienteNumero($sucursal, '0');

        // CUF simulado: SHA256-like base + nro + timestamp (en prod usar algoritmo SIN)
        $cuf = strtoupper(Str::random(10) . str_pad((string) $nro, 8, '0', STR_PAD_LEFT) . now()->format('YmdHis') . Str::random(12));
        $cuf = substr(hash('sha256', $cuf), 0, 64); // 64 hex simula longitud CUF SIN
        // Simplificado: en integración real se calcula con NIT, fecha, sucursal, modalidad, etc.

        $estado = ($validated['tipo_emision'] ?? 'ONLINE') === 'CONTINGENCIA' ? 'CONTINGENCIA' : 'ENVIADA';

        $invoice = null;
        DB::transaction(function () use ($validated, $tax, $sucursal, $nro, $cuf, $estado, &$invoice) {
            $invoice = ElectronicInvoice::create([
                'nro_factura' => $nro,
                'cuf' => $cuf,
                'cufd' => null,
                'cuis' => $tax?->cuis,
                'fecha_emision' => now()->toDateString(),
                'hora_emision' => now()->format('H:i:s'),
                'tipo_factura' => 'VENTA',
                'tipo_emision' => $validated['tipo_emision'] ?? 'ONLINE',
                'modalidad' => $tax?->modalidad_sfe ?? 'COMPUTARIZADA_EN_LINEA',
                'codigo_sucursal' => $sucursal,
                'nit_emisor' => $tax?->nit_colegio ?? '000000000',
                'razon_social_emisor' => $tax?->razon_social ?? 'UNIDAD EDUCATIVA SAN BERNARDO',
                'nit_receptor' => $validated['nit_receptor'] ?? null,
                'tipo_doc_receptor' => $validated['tipo_doc_receptor'] ?? null,
                'nro_doc_receptor' => $validated['nro_doc_receptor'] ?? null,
                'razon_social_receptor' => $validated['razon_social_receptor'] ?? null,
                'direccion_receptor' => $validated['direccion_receptor'] ?? null,
                'email_receptor' => $validated['email_receptor'] ?? null,
                'total_exento' => $validated['total_factura'],
                'total_descuentos' => 0,
                'total_iva' => 0, // EXENTO
                'total_it' => 0,  // EXENTO
                'total_factura' => $validated['total_factura'],
                'total_factura_texto' => $this->numeroALiteral((float) $validated['total_factura']),
                'estado' => $estado,
                'leyenda1' => $tax?->leyenda_iva_exento,
                'leyenda2' => $tax?->leyenda_it_exento,
                'leyenda3' => $tax?->leyenda_ley070,
            ]);

            $detalles = $validated['detalles'] ?? [
                [
                    'codigo_servicio' => '001',
                    'descripcion' => 'SERVICIO EDUCATIVO - MENSUALIDAD',
                    'cantidad' => 1,
                    'precio_unitario' => $validated['total_factura'],
                    'unidad_medida' => 'Unidad (B1)',
                ]
            ];

            foreach ($detalles as $idx => $d) {
                $sub = (float) $d['cantidad'] * (float) $d['precio_unitario'];
                InvoiceDetail::create([
                    'electronic_invoice_id' => $invoice->id,
                    'nro_registro' => $idx + 1,
                    'codigo_servicio' => $d['codigo_servicio'],
                    'descripcion' => $d['descripcion'],
                    'cantidad' => $d['cantidad'] ?? 1,
                    'unidad_medida' => $d['unidad_medida'] ?? 'Unidad (B1)',
                    'precio_unitario' => $d['precio_unitario'],
                    'monto_descuento' => 0,
                    'sub_total' => $sub,
                    'iva_tipo' => 'EXENTO',
                    'iva_tasa' => 0,
                    'iva_monto' => 0,
                    'it_tipo' => 'EXENTO',
                    'it_tasa' => 0,
                    'it_monto' => 0,
                    'leyenda' => $tax?->leyenda_iva_exento,
                ]);
            }

            // Si es contingencia, encola reintento automático HU-TRIB-03
            if ($estado === 'CONTINGENCIA') {
                \App\Models\ContingencyQueue::create([
                    'electronic_invoice_id' => $invoice->id,
                    'intentos' => 0,
                    'max_intentos' => 5,
                    'estado' => 'PENDIENTE',
                    'proximo_intento' => now()->addMinutes(5),
                ]);
            }
        });

        $invoice->load('detalles');

        if ($request->expectsJson()) {
            return response()->json(['data' => $invoice], 201);
        }

        return redirect()->route('electronic-invoices.index')->with('success', "Factura Nro {$nro} emitida (CUF {$invoice->cuf}).");
    }

    public function update(Request $request, ElectronicInvoice $electronicInvoice)
    {
        $this->authorize('update', $electronicInvoice);

        // Solo permite anular o actualizar estado/leyendas - no recalcula totales
        $validated = $request->validate([
            'estado' => ['sometimes', 'in:ENVIADA,AUTORIZADA,RECHAZADA,CONTINGENCIA,ANULADA'],
            'xml_respuesta' => ['sometimes', 'string', 'nullable'],
            'leyenda1' => ['sometimes', 'string', 'max:255', 'nullable'],
        ]);

        $electronicInvoice->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['data' => $electronicInvoice->fresh('detalles')]);
        }
        return redirect()->route('electronic-invoices.index')->with('success', 'Factura actualizada.');
    }

    public function destroy(ElectronicInvoice $electronicInvoice)
    {
        $this->authorize('delete', $electronicInvoice);

        if ($electronicInvoice->estado === 'AUTORIZADA') {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'No se puede eliminar factura autorizada. Anule en su lugar.'], 409);
            }
            return back()->with('error', 'No se puede eliminar factura autorizada.');
        }

        $electronicInvoice->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Factura eliminada.']);
        }
        return redirect()->route('electronic-invoices.index')->with('success', 'Factura eliminada.');
    }

    /**
     * Genera PDF factura electrónica para imprimir/compartir (formato SFE profesional no genérico).
     * Usa plantilla SIN-compliant con branding verde institucional, QR real y literal en palabras.
     */
    public function pdf(ElectronicInvoice $electronicInvoice)
    {
        $this->authorize('view', $electronicInvoice);

        $invoice = $electronicInvoice->load('detalles');
        $tax = TaxConfiguration::first();
        $logoBase64 = $this->resolveLogoBase64();
        $qrBase64 = $this->generateQrBase64($invoice);
        $literal = $invoice->total_factura_texto ?: $this->numeroALiteral((float) $invoice->total_factura);
        $html = view('electronic-invoices.pdf', [
            'invoice' => $invoice,
            'tax' => $tax,
            'logoBase64' => $logoBase64,
            'qrBase64' => $qrBase64,
            'literal' => $literal,
        ])->render();
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isPhpEnabled', false);
        $options->set('chroot', public_path());
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $filename = 'Factura-'.str_pad((string)$invoice->nro_factura,8,'0',STR_PAD_LEFT).'-'.$invoice->cuf.'.pdf';
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'Cache-Control' => 'private, max-age=0, must-revalidate',
        ]);
    }

    public function downloadPdf(ElectronicInvoice $electronicInvoice)
    {
        $this->authorize('view', $electronicInvoice);

        $invoice = $electronicInvoice->load('detalles');
        $tax = TaxConfiguration::first();
        $logoBase64 = $this->resolveLogoBase64();
        $qrBase64 = $this->generateQrBase64($invoice);
        $literal = $invoice->total_factura_texto ?: $this->numeroALiteral((float) $invoice->total_factura);
        $html = view('electronic-invoices.pdf', ['invoice' => $invoice, 'tax' => $tax, 'logoBase64' => $logoBase64, 'qrBase64' => $qrBase64, 'literal' => $literal])->render();
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('chroot', public_path());
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Factura-'.str_pad((string)$invoice->nro_factura,8,'0',STR_PAD_LEFT).'.pdf"',
        ]);
    }

    private function resolveLogoBase64(): ?string
    {
        // Prioriza thumb optimizado (320px) para evitar OOM con PNG gigante 4609x2336
        $candidates = [
            public_path('images/logo-colegio-thumb.png'),
            public_path('images/logo-colegio.png'),
        ];
        foreach ($candidates as $path) {
            if (!file_exists($path)) continue;
            if (filesize($path) > 90000) continue; // evita dompdf memory 43MB
            $mime = @mime_content_type($path) ?: 'image/png';
            if (!str_starts_with($mime, 'image/')) continue;
            return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path));
        }
        return null;
    }

    private function generateQrBase64(ElectronicInvoice $invoice): ?string
    {
        try {
            // Contenido QR estilo SIN: URL verificación + NIT + CUF + Nro + fecha + total
            $payload = sprintf(
                'https://sin.gob.bo/verificar?cuf=%s&nit=%s&nro=%s&fecha=%s&total=%s',
                $invoice->cuf,
                $invoice->nit_emisor,
                $invoice->nro_factura,
                $invoice->fecha_emision instanceof \Carbon\Carbon ? $invoice->fecha_emision->format('Y-m-d') : $invoice->fecha_emision,
                number_format((float)$invoice->total_factura, 2, '.', '')
            );
            // Si es muy largo, usa solo CUF
            if (strlen($payload) > 180) $payload = $invoice->cuf;

            $result = \Endroid\QrCode\Builder\Builder::create()
                ->writer(new \Endroid\QrCode\Writer\PngWriter())
                ->writerOptions([])
                ->data($payload)
                ->encoding(new \Endroid\QrCode\Encoding\Encoding('UTF-8'))
                ->errorCorrectionLevel(\Endroid\QrCode\ErrorCorrectionLevel::Medium)
                ->size(300)
                ->margin(8)
                ->roundBlockSizeMode(\Endroid\QrCode\RoundBlockSizeMode::Margin)
                ->validateResult(false)
                ->build();
            return 'data:image/png;base64,'.base64_encode($result->getString());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('QR gen fail: '.$e->getMessage());
            return null;
        }
    }

    /**
     * Convierte número a literal boliviano en palabras (ej: 700 => SETECIENTOS 00/100 BOLIVIANOS)
     * Soporta hasta 9999999.99
     */
    private function numeroALiteral(float $n): string
    {
        $n = round($n, 2);
        $entero = (int) floor($n);
        $centavos = (int) round(($n - $entero) * 100);
        $palabras = $this->numeroEnPalabras($entero);
        if ($palabras === '') $palabras = 'CERO';
        return $palabras.' '.str_pad((string)$centavos, 2, '0', STR_PAD_LEFT).'/100 BOLIVIANOS';
    }

    private function numeroEnPalabras(int $n): string
    {
        if ($n === 0) return 'CERO';
        $unidades = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE', 'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
        $veintenas = ['', 'VEINTI', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

        $toWords = function(int $num) use (&$toWords, $unidades, $veintenas, $centenas): string {
            if ($num < 20) return $unidades[$num];
            if ($num < 100) {
                $d = intdiv($num, 10);
                $r = $num % 10;
                if ($d === 2 && $r !== 0) return 'VEINTI'.$unidades[$r];
                if ($d === 2) return 'VEINTE';
                $base = $veintenas[$d - 1];
                return $r === 0 ? $base : $base.' Y '.$unidades[$r];
            }
            if ($num < 1000) {
                if ($num === 100) return 'CIEN';
                $c = intdiv($num, 100);
                $r = $num % 100;
                $pref = $centenas[$c];
                return $r === 0 ? $pref : $pref.' '.$toWords($r);
            }
            if ($num < 1000000) {
                $miles = intdiv($num, 1000);
                $r = $num % 1000;
                $pref = $miles === 1 ? 'MIL' : $toWords($miles).' MIL';
                return $r === 0 ? $pref : $pref.' '.$toWords($r);
            }
            // hasta 9 millones
            $millones = intdiv($num, 1000000);
            $r = $num % 1000000;
            $pref = $millones === 1 ? 'UN MILLON' : $toWords($millones).' MILLONES';
            return $r === 0 ? $pref : $pref.' '.$toWords($r);
        };

        return $toWords($n);
    }
}
