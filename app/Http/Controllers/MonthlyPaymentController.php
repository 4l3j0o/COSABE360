<?php

namespace App\Http\Controllers;

use App\Mail\FacturaEmitidaMail;
use App\Models\Enrollment;
use App\Models\MonthlyPayment;
use App\Models\ParentGuardian;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class MonthlyPaymentController extends Controller
{
    /**
     * Listado general de pagos (acceso secundario).
     */
    public function index(Request $request)
    {
        $query = MonthlyPayment::with(['registration.enrollment.estudiante'])->latest();

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->whereHas('registration.enrollment.estudiante', function ($q) use ($buscar) {
                $q->where('nombres', 'like', "%{$buscar}%")
                    ->orWhere('apellidos', 'like', "%{$buscar}%")
                    ->orWhere('codigo', 'like', "%{$buscar}%");
            });
        }

        $pagos = $query->paginate(10)->withQueryString();

        return view('monthly-payments.index', compact('pagos'));
    }

    /**
     * CA-19-02/03: Pagos y saldo de un único estudiante.
     */
    public function estudiante(Student $estudiante)
    {
        $this->autorizarAccesoEstudiante($estudiante);

        $inscripcion = $estudiante->enrollments()
            ->with('gestion')
            ->where('estado', 'COMPLETADA')
            ->whereHas('registration', function ($q) {
                $q->where('estado', 'ACTIVA');
            })
            ->latest('fecha')
            ->first();

        $pagos = collect();
        $saldoPendiente = 0.0;
        $totalPagado = 0.0;
        $mesesPendientes = 0;
        $mesesPagados = 0;

        $matricula = $inscripcion?->registration;
        if ($matricula) {
            $pagos = $matricula->monthlyPayments()
                ->with('electronicInvoice')
                ->orderBy('mes')
                ->get();

            $saldoPendiente = (float) $pagos->whereIn('estado', ['PENDIENTE', 'ESPERA'])->sum('monto');
            $totalPagado = (float) $pagos->where('estado', 'PAGADA')->sum('monto');
            $mesesPendientes = $pagos->whereIn('estado', ['PENDIENTE', 'ESPERA'])->count();
            $mesesPagados = $pagos->where('estado', 'PAGADA')->count();
        }

        // Receptor/facturación: el tutor que paga (usuario actual si es Padre/Tutor, si no su tutor principal)
        $currentUser = auth()->user();
        $esTutor = $currentUser && $currentUser->hasRole('Padre/Tutor');
        $pagador = null;
        if ($esTutor) {
            $pagador = ParentGuardian::where('user_id', $currentUser->id)->first();
        }
        if (! $pagador) {
            $pagador = $estudiante->parentGuardians()->wherePivot('es_principal', true)->first();
        }

        return view('monthly-payments.estudiante', compact(
            'estudiante',
            'inscripcion',
            'pagos',
            'saldoPendiente',
            'totalPagado',
            'mesesPendientes',
            'mesesPagados',
            'pagador',
            'esTutor'
        ));
    }

    /**
     * CA-19-01: Registrar un pago con fecha y método de pago para un estudiante.
     * El monto se toma de la cuota ya generada en la matrícula.
     * Opcionalmente genera factura electrónica SFE (sector educativo exento) lista para imprimir/compartir PDF.
     * Si es tutor/padre, se valida que sea hijo suyo y se envía factura por correo automáticamente (local log driver).
     */
    public function storeEstudiante(Request $request, Student $estudiante)
    {
        $this->autorizarAccesoEstudiante($estudiante);

        $validated = $request->validate([
            'mes'           => ['required', 'integer', 'between:2,11'],
            'fecha'         => ['required', 'date'],
            'metodo_pago'   => ['required', Rule::in(MonthlyPayment::METODOS_PAGO)],
            'generar_factura' => ['sometimes', 'boolean'],
            'nit_receptor'  => ['nullable', 'string', 'max:20'],
            'razon_social_receptor' => ['nullable', 'string', 'max:200'],
        ]);

        $inscripcion = $estudiante->enrollments()
            ->where('estado', 'COMPLETADA')
            ->whereHas('registration', function ($q) {
                $q->where('estado', 'ACTIVA');
            })
            ->latest('fecha')
            ->first();

        if (! $inscripcion) {
            return back()->withErrors([
                'mes' => 'El estudiante no tiene una matrícula activa para registrar el pago.',
            ])->withInput();
        }

        $cuota = $inscripcion->registration->monthlyPayments()
            ->where('mes', $validated['mes'])
            ->first();

        if (! $cuota) {
            return back()->withErrors([
                'mes' => 'No se encontró la mensualidad del mes seleccionado para este estudiante.',
            ])->withInput();
        }

        if (in_array($cuota->estado, ['PAGADA', 'ESPERA'])) {
            $msg = $cuota->estado === 'ESPERA'
                ? 'La mensualidad de este mes ya fue registrada y está pendiente de verificación.'
                : 'La mensualidad de este mes ya se encuentra pagada.';
            return back()->withErrors(['mes' => $msg])->withInput();
        }

        $esTutor = auth()->user()->hasRole('Padre/Tutor');

        if ($esTutor) {
            $cuota->update([
                'fecha'       => $validated['fecha'],
                'metodo_pago' => $validated['metodo_pago'],
                'estado'      => 'ESPERA',
            ]);

            return redirect()->route('monthly-payments.estudiante', $estudiante)
                ->with('mensaje', 'Pago registrado correctamente. Está pendiente de verificación por el personal de la institución.');
        }

        $generarFactura = $request->boolean('generar_factura', true);
        $facturaId = null;
        $facturaMsg = '';
        $invoice = null;

        if ($generarFactura) {
            try {
                $invoice = $this->crearFacturaExenta($estudiante, $cuota, $validated);
                $facturaId = $invoice->id;
                $facturaMsg = " Factura N° ".str_pad((string)$invoice->nro_factura,8,'0',STR_PAD_LEFT)." generada (PDF listo para imprimir/compartir).";
                $this->enviarFacturaPorCorreo($estudiante, $invoice, (int)$validated['mes']);
                $facturaMsg .= " Enviada por correo al tutor.";
                $this->crearComunicadoFactura($estudiante, $invoice, (int)$validated['mes']);
                $facturaMsg .= " Notificación en Comunicados.";
            } catch (\Throwable $e) {
                Log::error('Error factura auto: '.$e->getMessage(), ['trace'=>$e->getTraceAsString()]);
                $facturaMsg = ' (Factura no pudo generarse: '.$e->getMessage().')';
            }
        }

        $cuota->update([
            'fecha'        => $validated['fecha'],
            'metodo_pago'  => $validated['metodo_pago'],
            'estado'       => 'PAGADA',
            'electronic_invoice_id' => $facturaId ?? $cuota->electronic_invoice_id,
        ]);

        if ($facturaId) {
            $pdfUrl = route('electronic-invoices.pdf', $facturaId);
            return redirect()->route('monthly-payments.estudiante', $estudiante)
                ->with('mensaje', 'Pago registrado correctamente.'.$facturaMsg.' <a href="'.$pdfUrl.'" target="_blank" class="underline font-bold text-primary">Ver factura PDF</a>');
        }

        return redirect()->route('monthly-payments.estudiante', $estudiante)
            ->with('mensaje', 'Pago de la mensualidad registrado correctamente.'.$facturaMsg);
    }

    /**
     * Reenvía factura por correo y comunicado (visible en bandeja).
     */
    public function reenviar(Request $request, Student $estudiante, MonthlyPayment $pago)
    {
        $this->autorizarAccesoEstudiante($estudiante);
        if ($pago->registration->enrollment->student_id !== $estudiante->id) abort(404);
        if (! $pago->electronic_invoice_id || ! $pago->electronicInvoice) {
            return back()->with('error', 'Este pago no tiene factura generada.');
        }
        $invoice = $pago->electronicInvoice;
        try {
            $this->enviarFacturaPorCorreo($estudiante, $invoice, (int)$pago->mes);
            $this->crearComunicadoFactura($estudiante, $invoice, (int)$pago->mes);
            return back()->with('mensaje', 'Factura N° '.str_pad($invoice->nro_factura,8,'0',STR_PAD_LEFT).' reenviada por correo a '.($invoice->email_receptor ?? 'tutor').' y notificación en Comunicados. <a href="'.route('electronic-invoices.pdf',$invoice).'" target="_blank" class="underline font-bold text-primary">Ver PDF</a>');
        } catch (\Throwable $e) {
            Log::error('Error reenvio factura: '.$e->getMessage());
            return back()->with('error', 'No se pudo reenviar: '.$e->getMessage());
        }
    }

    /**
     * Aprueba un pago en estado ESPERA: cambia a PAGADA, genera factura, email y comunicado.
     * Solo Admin/Secretaría/Director.
     */
    public function aprobar(Request $request, Student $estudiante, MonthlyPayment $pago)
    {
        $user = auth()->user();
        if (! $user || ! $user->hasAnyRole(['Administrador', 'Secretaría', 'Director'])) {
            abort(403, 'Solo el personal de la institución puede aprobar pagos.');
        }
        if ($pago->registration->enrollment->student_id !== $estudiante->id) abort(404);
        if ($pago->estado !== 'ESPERA') {
            return back()->with('error', 'Este pago no está en estado de espera.');
        }

        $validated = $request->validate([
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        $invoice = null;
        $facturaMsg = '';

        try {
            $invoice = $this->crearFacturaExenta($estudiante, $pago, ['mes' => (int)$pago->mes]);
            $this->enviarFacturaPorCorreo($estudiante, $invoice, (int)$pago->mes);
            $this->crearComunicadoFactura($estudiante, $invoice, (int)$pago->mes);
            $facturaMsg = " Factura N° ".str_pad((string)$invoice->nro_factura,8,'0',STR_PAD_LEFT)." generada y enviada por correo. Notificación en Comunicados.";
        } catch (\Throwable $e) {
            Log::error('Error factura al aprobar: '.$e->getMessage(), ['trace'=>$e->getTraceAsString()]);
            $facturaMsg = ' (Error al generar factura: '.$e->getMessage().')';
        }

        $pago->update([
            'estado' => 'PAGADA',
            'electronic_invoice_id' => $invoice?->id ?? $pago->electronic_invoice_id,
            'observaciones' => $validated['observaciones'] ?? $pago->observaciones,
        ]);

        if ($invoice) {
            $pdfUrl = route('electronic-invoices.pdf', $invoice->id);
            return back()->with('mensaje', 'Pago aprobado correctamente.'.$facturaMsg.' <a href="'.$pdfUrl.'" target="_blank" class="underline font-bold text-primary">Ver factura PDF</a>');
        }

        return back()->with('mensaje', 'Pago aprobado correctamente.'.$facturaMsg);
    }

    /**
     * Verifica que un tutor/padre solo acceda a sus hijos.
     * Admin/Secretaría/Director pasan directo.
     */
    private function autorizarAccesoEstudiante(Student $estudiante): void
    {
        $user = auth()->user();
        if (! $user) abort(403, 'No autenticado');
        $rol = $user->rol?->nombre;
        if (in_array($rol, ['Administrador', 'Secretaría', 'Director'], true)) {
            return;
        }
        if ($rol === 'Padre/Tutor') {
            $parent = ParentGuardian::where('user_id', $user->id)->first();
            if (! $parent) abort(403, 'No tiene perfil de tutor.');
            $esHijo = $parent->students()->where('students.id', $estudiante->id)->exists();
            if (! $esHijo) abort(403, 'No tiene permiso para gestionar pagos de este estudiante.');
            return;
        }
        abort(403, 'Rol no autorizado para gestionar pagos.');
    }

    private function crearComunicadoFactura(Student $estudiante, \App\Models\ElectronicInvoice $invoice, int $mesNum): void
    {
        try {
            $meses = [2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre'];
            $mesNombre = $meses[$mesNum] ?? 'Mes '.$mesNum;
            $nro = str_pad((string)$invoice->nro_factura, 8, '0', STR_PAD_LEFT);
            $estudianteNombre = trim($estudiante->apellidos.' '.$estudiante->nombres);
            $pdfUrl = route('electronic-invoices.pdf', $invoice->id);
            $downloadUrl = route('electronic-invoices.download', $invoice->id);
            $titulo = "Factura SFE {$nro} — Mensualidad {$mesNombre} — {$estudiante->codigo}";
            $contenido = "<p>Se registró el pago de <strong>{$mesNombre} {$invoice->fecha_emision->format('Y')}</strong> de <strong>{$estudianteNombre} ({$estudiante->codigo})</strong>.</p>"
                ."<p><strong>Factura N° {$nro}</strong> — CUF <code>{$invoice->cuf}</code> — Total <strong>Bs ".number_format($invoice->total_factura,2)."</strong> (Exento IVA/IT)</p>"
                ."<p>Estado: {$invoice->estado} — Emitida: {$invoice->fecha_emision->format('d/m/Y')} ".substr($invoice->hora_emision,0,5)." — Receptor: {$invoice->razon_social_receptor} (NIT/CI {$invoice->nit_receptor}) — Email: ".($invoice->email_receptor ?? '—')."</p>"
                ."<p><a href=\"{$pdfUrl}\" target=\"_blank\" style=\"background:#003200;color:#fff;padding:8px 14px;border-radius:6px;text-decoration:none;font-weight:bold;\">📄 Ver factura PDF</a> "
                ."<a href=\"{$downloadUrl}\" style=\"border:1px solid #003200;color:#003200;padding:8px 14px;border-radius:6px;text-decoration:none;margin-left:8px;\">⬇ Descargar PDF</a></p>"
                ."<p style=\"font-size:12px;color:#64748b;\">Este comunicado confirma el envío por correo a <strong>".($invoice->email_receptor ?? 'tutor registrado')."</strong>. También puede ver la factura desde <em>Pagos de mensualidades → Mensualidad {$mesNombre}</em>. Si no recibió el correo, use <em>Reenviar</em> en la página de pagos.</p>";
            $authorId = auth()->id() ?? 1;
            $announcement = \App\Models\Announcement::create([
                'titulo' => $titulo,
                'contenido' => $contenido,
                'author_id' => $authorId,
                'fecha_publicacion' => now(),
                'estado' => 'PUBLICADO',
            ]);
            // Destinatarios: tutores principales del estudiante (user_id específico)
            $tutores = $estudiante->parentGuardians()->wherePivot('es_principal', true)->get();
            $userIds = collect();
            foreach ($tutores as $t) {
                if ($t->user_id) $userIds->push($t->user_id);
            }
            // Si el pagador es tutor y no estaba en tutores principales, inclúyelo
            $current = auth()->user();
            if ($current && $current->hasRole('Padre/Tutor') && !$userIds->contains($current->id)) {
                $userIds->push($current->id);
            }
            $userIds = $userIds->unique()->filter();
            if ($userIds->isEmpty()) {
                // Fallback: tipo PADRES (visible para todos padres, pero mejor que nada)
                $announcement->recipients()->create(['tipo_destinatario' => 'PADRES']);
            } else {
                foreach ($userIds as $uid) {
                    $announcement->recipients()->create(['tipo_destinatario' => 'PADRES', 'user_id' => $uid]);
                }
            }
            Log::info("Comunicado factura {$nro} creado id {$announcement->id} para userIds: ".implode(',',$userIds->toArray()));
        } catch (\Throwable $e) {
            Log::error('Error creando comunicado factura: '.$e->getMessage(), ['trace'=>$e->getTraceAsString()]);
        }
    }

    private function enviarFacturaPorCorreo(Student $estudiante, \App\Models\ElectronicInvoice $invoice, int $mesNum): void
    {
        try {
            $meses = [2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre'];
            $mesNombre = $meses[$mesNum] ?? 'Mes '.$mesNum;
            $estudianteNombre = trim($estudiante->apellidos.' '.$estudiante->nombres);

            // Resolver destinatarios: tutor/es principal/es + usuario actual + estudiante email si existe
            $destinatarios = collect();
            // Todos los tutores principales del estudiante
            $tutores = $estudiante->parentGuardians()->wherePivot('es_principal', true)->get();
            foreach ($tutores as $t) {
                if (!empty($t->email) && filter_var($t->email, FILTER_VALIDATE_EMAIL)) $destinatarios->push($t->email);
                if ($t->user && !empty($t->user->email) && filter_var($t->user->email, FILTER_VALIDATE_EMAIL)) $destinatarios->push($t->user->email);
            }
            // Usuario actual solo si es Padre/Tutor (evita enviar copia a admin/secretaria)
            $currentUser = auth()->user();
            if ($currentUser && $currentUser->hasRole('Padre/Tutor') && !empty($currentUser->email) && filter_var($currentUser->email, FILTER_VALIDATE_EMAIL)) {
                $destinatarios->push($currentUser->email);
            }
            // Email receptor de la factura si es distinto
            if (!empty($invoice->email_receptor) && filter_var($invoice->email_receptor, FILTER_VALIDATE_EMAIL)) {
                $destinatarios->push($invoice->email_receptor);
            }
            $destinatarios = $destinatarios->unique()->filter()->values();
            if ($destinatarios->isEmpty()) {
                Log::warning("Factura {$invoice->nro_factura}: sin destinatario de correo, se omite envío.");
                return;
            }

            // Generar PDF binario sin duplicar lógica de ElectronicInvoiceController (reusa vista)
            $tax = \App\Models\TaxConfiguration::first();
            $logoBase64 = $this->resolveLogoBase64();
            $qrBase64 = $this->generateQrBase64($invoice);
            $literal = $invoice->total_factura_texto ?: $this->numeroALiteral((float) $invoice->total_factura);
            $html = view('electronic-invoices.pdf', [
                'invoice' => $invoice->load('detalles'),
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
            $pdfBinary = $dompdf->output();

            $mailable = new FacturaEmitidaMail($invoice, $pdfBinary, $estudianteNombre, $mesNombre);
            // En local con MAIL_MAILER=log esto escribe a storage/logs/laravel.log y no falla si no hay SMTP
            Mail::to($destinatarios->toArray())->send($mailable);
            Log::info("Factura {$invoice->nro_factura} enviada por correo a: ".implode(', ', $destinatarios->toArray()));
        } catch (\Throwable $e) {
            Log::error('Error enviando factura por correo: '.$e->getMessage(), ['trace'=>$e->getTraceAsString()]);
            // No relanzar: el pago ya está registrado, el correo es secundario
        }
    }

    private function resolveLogoBase64(): ?string
    {
        $candidates = [
            public_path('images/logo-colegio-thumb.png'),
            public_path('images/logo-colegio.png'),
        ];
        foreach ($candidates as $path) {
            if (!file_exists($path)) continue;
            if (filesize($path) > 90000) continue;
            $mime = @mime_content_type($path) ?: 'image/png';
            if (!str_starts_with($mime, 'image/')) continue;
            return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path));
        }
        return null;
    }

    private function generateQrBase64(\App\Models\ElectronicInvoice $invoice): ?string
    {
        try {
            $payload = sprintf(
                'https://sin.gob.bo/verificar?cuf=%s&nit=%s&nro=%s&fecha=%s&total=%s',
                $invoice->cuf,
                $invoice->nit_emisor,
                $invoice->nro_factura,
                $invoice->fecha_emision instanceof \Carbon\Carbon ? $invoice->fecha_emision->format('Y-m-d') : $invoice->fecha_emision,
                number_format((float)$invoice->total_factura, 2, '.', '')
            );
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
            Log::warning('QR gen fail: '.$e->getMessage());
            return null;
        }
    }

    private function crearFacturaExenta(Student $estudiante, MonthlyPayment $cuota, array $validated): \App\Models\ElectronicInvoice
    {
        $tax = \App\Models\TaxConfiguration::first();
        $sucursal = $tax?->codigo_sucursal ?? '0';
        $nro = \App\Models\InvoiceSequence::siguienteNumero($sucursal, '0');
        $cuf = strtoupper(\Illuminate\Support\Str::random(10).str_pad((string)$nro,8,'0',STR_PAD_LEFT).now()->format('YmdHis').\Illuminate\Support\Str::random(12));
        $cuf = substr(hash('sha256', $cuf), 0, 64);
        $monto = (float) $cuota->monto;
        $meses = [2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre'];
        $mesNombre = $meses[(int)$validated['mes']] ?? 'Mes '.$validated['mes'];
        $descripcion = "SERVICIO EDUCATIVO - MENSUALIDAD {$mesNombre} {$cuota->anio} - {$estudiante->codigo} {$estudiante->apellidos} {$estudiante->nombres}";

        // Literal en palabras
        $literal = $this->numeroALiteral($monto);

        // Determinar el pagador/receptor: prioriza el usuario actual si es Padre/Tutor,
        // luego el tutor principal del estudiante. El receptor de la factura es quien paga.
        $currentUser = auth()->user();
        $pagador = null;
        if ($currentUser && $currentUser->hasRole('Padre/Tutor')) {
            $pagador = ParentGuardian::where('user_id', $currentUser->id)->first();
        }
        if (! $pagador) {
            $pagador = $estudiante->parentGuardians()->wherePivot('es_principal', true)->first();
        }

        $emailReceptor = null;
        if ($pagador && !empty($pagador->email) && filter_var($pagador->email, FILTER_VALIDATE_EMAIL)) {
            $emailReceptor = $pagador->email;
        } elseif ($currentUser && $currentUser->hasRole('Padre/Tutor') && !empty($currentUser->email)) {
            $emailReceptor = $currentUser->email;
        } elseif ($pagador && $pagador->user && !empty($pagador->user->email) && filter_var($pagador->user->email, FILTER_VALIDATE_EMAIL)) {
            $emailReceptor = $pagador->user->email;
        }
        if (empty($emailReceptor) && !empty($estudiante->email) && filter_var($estudiante->email, FILTER_VALIDATE_EMAIL)) {
            $emailReceptor = $estudiante->email;
        }
        // Si se pasó explícitamente en el form, respétalo
        if (!empty($validated['email_receptor'] ?? null) && filter_var($validated['email_receptor'], FILTER_VALIDATE_EMAIL)) {
            $emailReceptor = $validated['email_receptor'];
        }

        // Datos de identidad del receptor (tutor/pagador si existe, si no el estudiante)
        $receptorNombre  = $pagador
            ? (trim(($pagador->apellidos ?? '').' '.($pagador->nombres ?? '')) ?: null)
            : (trim(($estudiante->apellidos ?? '').' '.($estudiante->nombres ?? '')) ?: null);
        $receptorDoc     = $pagador?->numero_documento ?: $estudiante->numero_documento;
        $receptorTipoDoc = $pagador?->tipo_documento ?: $estudiante->tipo_documento;
        $receptorDir     = $pagador?->direccion ?: $estudiante->direccion;

        $invoice = null;
        \Illuminate\Support\Facades\DB::transaction(function() use ($validated, $tax, $sucursal, $nro, $cuf, $monto, $descripcion, $literal, $emailReceptor, $receptorNombre, $receptorDoc, $receptorTipoDoc, $receptorDir, $estudiante, &$invoice) {
            $invoice = \App\Models\ElectronicInvoice::create([
                'nro_factura' => $nro,
                'cuf' => $cuf,
                'cufd' => null,
                'cuis' => $tax?->cuis,
                'fecha_emision' => now()->toDateString(),
                'hora_emision' => now()->format('H:i:s'),
                'tipo_factura' => 'VENTA',
                'tipo_emision' => 'ONLINE',
                'modalidad' => $tax?->modalidad_sfe ?? 'COMPUTARIZADA_EN_LINEA',
                'codigo_sucursal' => $sucursal,
                'nit_emisor' => $tax?->nit_colegio ?? '000000000',
                'razon_social_emisor' => $tax?->razon_social ?? 'UNIDAD EDUCATIVA SAN BERNARDO',
                'nit_receptor' => $validated['nit_receptor'] ?? $receptorDoc ?? null,
                'tipo_doc_receptor' => $receptorTipoDoc ?? null,
                'nro_doc_receptor' => $receptorDoc ?? null,
                'razon_social_receptor' => $validated['razon_social_receptor'] ?? $receptorNombre ?: 'S/N',
                'direccion_receptor' => $receptorDir ?? null,
                'email_receptor' => $emailReceptor,
                'total_exento' => $monto,
                'total_descuentos' => 0,
                'total_iva' => 0,
                'total_it' => 0,
                'total_factura' => $monto,
                'total_factura_texto' => $literal,
                'estado' => 'ENVIADA',
                'leyenda1' => $tax?->leyenda_iva_exento,
                'leyenda2' => $tax?->leyenda_it_exento,
                'leyenda3' => $tax?->leyenda_ley070,
            ]);
            \App\Models\InvoiceDetail::create([
                'electronic_invoice_id' => $invoice->id,
                'nro_registro' => 1,
                'codigo_servicio' => '001',
                'descripcion' => $descripcion,
                'cantidad' => 1,
                'unidad_medida' => 'Unidad (B1)',
                'precio_unitario' => $monto,
                'monto_descuento' => 0,
                'sub_total' => $monto,
                'iva_tipo' => 'EXENTO',
                'iva_tasa' => 0,
                'iva_monto' => 0,
                'it_tipo' => 'EXENTO',
                'it_tasa' => 0,
                'it_monto' => 0,
                'leyenda' => $tax?->leyenda_iva_exento,
            ]);
        });
        return $invoice;
    }

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
                $d = intdiv($num, 10); $r = $num % 10;
                if ($d === 2 && $r !== 0) return 'VEINTI'.$unidades[$r];
                if ($d === 2) return 'VEINTE';
                $base = $veintenas[$d - 1];
                return $r === 0 ? $base : $base.' Y '.$unidades[$r];
            }
            if ($num < 1000) {
                if ($num === 100) return 'CIEN';
                $c = intdiv($num, 100); $r = $num % 100;
                $pref = $centenas[$c];
                return $r === 0 ? $pref : $pref.' '.$toWords($r);
            }
            if ($num < 1000000) {
                $miles = intdiv($num, 1000); $r = $num % 1000;
                $pref = $miles === 1 ? 'MIL' : $toWords($miles).' MIL';
                return $r === 0 ? $pref : $pref.' '.$toWords($r);
            }
            $millones = intdiv($num, 1000000); $r = $num % 1000000;
            $pref = $millones === 1 ? 'UN MILLON' : $toWords($millones).' MILLONES';
            return $r === 0 ? $pref : $pref.' '.$toWords($r);
        };
        return $toWords($n);
    }
}
