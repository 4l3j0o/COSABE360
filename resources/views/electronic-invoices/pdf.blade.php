<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Factura {{ $invoice->nro_factura }} - {{ $invoice->cuf }}</title>
<style>
  @page { size: A4 portrait; margin: 12mm 10mm 12mm 10mm; }
  @media print { body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
  * { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; box-sizing: border-box; }
  body { font-size: 8.2px; color: #1a2e1a; line-height: 1.35; margin:0; padding:0; background:#fff; }
  /* Header verde institucional San Bernardo */
  .doc-header { border: 1.8px solid #003200; border-radius: 7px; overflow:hidden; margin-bottom: 9px; }
  .doc-header-top { background: #003200; background: linear-gradient(135deg, #003200 0%, #005100 55%, #006100 100%); color:#fff; padding: 7px 12px; display: table; width:100%; }
  .doc-header-top .left, .doc-header-top .right { display: table-cell; vertical-align: middle; }
  .doc-header-top .left { width: 72%; }
  .doc-header-top .right { width: 28%; text-align:right; }
  .brand { font-size: 14px; font-weight: 900; letter-spacing: 0.4px; margin:0; text-transform: uppercase; }
  .subbrand { font-size: 6.8px; opacity: 0.92; margin:3px 0 0 0; text-transform: uppercase; letter-spacing: 0.45px; font-weight:600; }
  .badge-factura { display:inline-block; background:#fff; color:#003200; padding:5px 11px; border-radius: 5px; font-weight: 900; font-size: 11px; text-align:center; line-height:1.05; border: 1px solid #cbd5c1; min-width: 92px; }
  .badge-factura small { display:block; font-size:5.5px; font-weight:800; color:#334155; margin-top:2px; letter-spacing:0.7px; text-transform:uppercase; }
  .badge-factura .nro { font-size:10px; color:#003200; display:block; margin-top:2px; letter-spacing:0.5px; }
  .header-body { padding: 10px 11px; background: #f6faf6; }
  .header-body table { width:100%; border-collapse: collapse; }
  .header-body td { vertical-align: top; padding: 0; }
  .logo-cell { width: 78px; text-align: center; }
  .logo-box { width:68px; height:68px; border:1.2px solid #b7c9b7; border-radius:7px; background:#fff; display:flex; align-items:center; justify-content:center; overflow:hidden; padding:4px; }
  .logo-box img { max-width: 60px; max-height: 60px; }
  .emisor { padding-left: 11px; }
  .emisor h3 { margin:0; font-size:9.5px; color:#003200; font-weight:900; text-transform:uppercase; line-height:1.2; }
  .emisor p { margin:2px 0; font-size:7.2px; color:#33412f; }
  .emisor .nit { font-size:8.5px; font-weight:800; color:#003200; letter-spacing:0.2px; }
  .emisor .tags { margin-top:5px; }
  .tag { display:inline-block; padding:1.5px 6px; border-radius:3px; font-size:5.5px; font-weight:800; letter-spacing:0.3px; text-transform:uppercase; }
  .tag-sector { background:#dcfce7; color:#14532d; border:1px solid #86efac; }
  .tag-exento { background:#fef9c3; color:#713f12; border:1px solid #fde68a; margin-left:4px; }
  .meta-box { width: 252px; background:#fff; border:1.2px solid #d1dfd1; border-radius:7px; padding:7px 8px; }
  .meta-row { display: table; width:100%; margin-bottom:3.5px; }
  .meta-row:last-child { margin-bottom:0; }
  .meta-label { display:table-cell; width:70px; font-size:6.2px; font-weight:800; color:#475569; text-transform:uppercase; letter-spacing:0.35px; vertical-align: middle; }
  .meta-value { display:table-cell; font-size:7.2px; font-weight:700; color:#0f172a; word-break: break-all; vertical-align: middle; }
  .meta-value.cuf { font-size:5.7px; font-weight:600; font-family: monospace; letter-spacing:0.15px; }
  .meta-value.nro { font-size:11px; color:#003200; font-weight:900; letter-spacing:0.4px; font-family: monospace; }
  .estado-badge { display:inline-block; padding:2px 7px; border-radius:10px; font-size:6px; font-weight:900; letter-spacing:0.4px; text-transform:uppercase; }
  .estado-ENVIADA { background:#fef3c7; color:#92400e; border:1px solid #fcd34d; }
  .estado-AUTORIZADA { background:#dcfce7; color:#14532d; border:1px solid #86efac; }
  .estado-CONTINGENCIA { background:#fee2e2; color:#7f1d1d; border:1px solid #fca5a5; }
  .estado-ANULADA { background:#f1f5f9; color:#64748b; border:1px solid #cbd5e1; }
  .section { border:1.2px solid #d1dfd1; border-radius:7px; margin-bottom: 7px; overflow:hidden; }
  .section-title { background:#003200; color:#fff; font-size:6.8px; font-weight:900; padding:5px 10px; text-transform:uppercase; letter-spacing:0.5px; }
  .section-title .sub { font-weight:600; font-size:6px; opacity:0.9; float:right; text-transform:none; letter-spacing:0.2px; }
  .section-body { padding:8px 10px; background:#fff; }
  .receptor-grid { width:100%; border-collapse:collapse; }
  .receptor-grid td { padding:2px 0; vertical-align:top; }
  .field-label { font-size:6.2px; font-weight:800; color:#64748b; text-transform:uppercase; width:84px; letter-spacing:0.25px; }
  .field-value { font-size:7.4px; color:#0f172a; font-weight:600; }
  .field-value.strong { font-weight:900; color:#003200; font-size:7.6px; }
  .items-table { width:100%; border-collapse:collapse; margin:0; }
  .items-table thead th { background:#003200; color:#fff; font-size:6.2px; font-weight:800; text-transform:uppercase; letter-spacing:0.4px; padding:6px 5px; text-align:center; border: 1px solid #003200; }
  .items-table thead th:first-child { text-align:center; width:26px; }
  .items-table thead th:nth-child(2) { width:48px; }
  .items-table thead th:nth-child(3) { text-align:left; }
  .items-table tbody td { padding:5px 5px; font-size:7.3px; border:1px solid #e2e8f0; vertical-align:top; }
  .items-table tbody tr:nth-child(even) { background:#f6faf6; }
  .items-table .num { text-align:center; font-weight:800; color:#003200; }
  .items-table .right { text-align:right; font-family: monospace; font-weight:600; }
  .items-table .center { text-align:center; }
  .items-table tfoot td { padding:5px 6px; font-size:7.3px; border:1px solid #cbd5c1; background:#f1f5f1; font-weight:700; }
  .total-row td { background:#003200 !important; color:#fff !important; font-size:7.8px !important; border-color:#003200 !important; letter-spacing:0.2px; }
  .total-row td.right { font-size:9px !important; font-weight:900 !important; }
  .totales-leyendas { width:100%; border-collapse:collapse; margin-top:1px; }
  .leyendas { width:58%; vertical-align:top; padding-right:7px; }
  .totales { width:42%; vertical-align:top; }
  .leyendas-box { background:#f6faf6; border:1px solid #d1dfd1; border-radius:7px; padding:7px 8px; }
  .leyenda-item { font-size:6.3px; color:#33412f; margin-bottom:3.5px; padding-left:9px; position:relative; line-height:1.45; }
  .leyenda-item:before { content:"•"; position:absolute; left:0; color:#005100; font-weight:900; font-size:7px; top:0; }
  .leyenda-title { font-size:5.8px; font-weight:900; color:#003200; text-transform:uppercase; margin-bottom:5px; letter-spacing:0.4px; border-bottom:1px solid #e2e8f0; padding-bottom:3px; }
  .totales-table { width:100%; border-collapse:collapse; border:1.2px solid #d1dfd1; border-radius:7px; overflow:hidden; }
  .totales-table td { padding:5px 7px; font-size:7.2px; border-bottom:1px solid #e2e8f0; }
  .totales-table tr:last-child td { border-bottom:none; }
  .totales-table .label { color:#475569; font-weight:700; text-transform:uppercase; font-size:5.8px; letter-spacing:0.25px; }
  .totales-table .value { text-align:right; font-weight:800; font-family:monospace; color:#0f172a; }
  .totales-table .highlight { background:#dcfce7; }
  .totales-table .highlight .label, .totales-table .highlight .value { color:#003200; font-weight:900; }
  .literal { background:#003200; color:#fff; padding:6px 8px; border-radius:5px; margin-top:6px; font-size:6.8px; font-weight:700; text-align:center; letter-spacing:0.25px; line-height:1.3; text-transform:uppercase; }
  .condicion-box { margin-top:6px; background:#fff; border:1px solid #d1dfd1; border-radius:5px; padding:5px 7px; font-size:6px; color:#475569; line-height:1.4; }
  .condicion-box strong { color:#003200; }
  .footer { margin-top:7px; border:1.2px solid #d1dfd1; border-radius:7px; overflow:hidden; }
  .footer-qr { display:table; width:100%; }
  .qr-cell { display:table-cell; width:92px; vertical-align:middle; text-align:center; padding:7px; background:#f6faf6; border-right:1px solid #d1dfd1; }
  .qr-box { width:74px; height:74px; border:1px solid #b7c9b7; border-radius:6px; margin:0 auto; display:flex; align-items:center; justify-content:center; background:#fff; overflow:hidden; padding:2px; }
  .qr-box img { width:70px; height:70px; display:block; }
  .qr-box .ph { font-size:5.5px; color:#64748b; text-align:center; line-height:1.2; padding:4px; }
  .qr-caption { font-size:5.2px; color:#64748b; margin-top:4px; font-family:monospace; word-break:break-all; line-height:1.2; }
  .qr-verify { font-size:5px; color:#94a3b8; margin-top:2px; font-weight:700; text-transform:uppercase; letter-spacing:0.25px; }
  .footer-info { display:table-cell; vertical-align:top; padding:7px 9px; }
  .footer-info h4 { margin:0 0 4px 0; font-size:6.8px; font-weight:900; color:#003200; text-transform:uppercase; letter-spacing:0.4px; }
  .footer-info p { margin:0 0 2.5px 0; font-size:6.3px; color:#475569; line-height:1.4; }
  .footer-info .cuf-line { font-family: monospace; font-size:6px; word-break:break-all; background:#f8fafc; border:1px solid #e2e8f0; border-radius:3px; padding:2px 4px; display:inline-block; margin-top:2px; max-width:100%; }
  .sfc-note { background:#fef9c3; border:1px solid #fde68a; border-radius:4px; padding:5px 7px; margin-top:5px; font-size:5.8px; color:#713f12; text-align:center; font-weight:700; letter-spacing:0.15px; line-height:1.3; }
  .copy-mark { text-align:center; margin-top:7px; font-size:5.3px; color:#94a3b8; letter-spacing:0.3px; border-top:1px solid #e2e8f0; padding-top:5px; }
  .copy-mark strong { color:#64748b; }
  .watermark { position: fixed; top: 44%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 72px; font-weight: 900; color: rgba(0,50,0,0.055); letter-spacing: 5px; text-transform: uppercase; pointer-events: none; border: 4px solid rgba(0,50,0,0.055); padding: 8px 22px; border-radius: 12px; }
  .legal-small { font-size:5.5px; color:#64748b; text-align:center; margin-top:4px; line-height:1.3; }
</style>
</head>
<body>
  @if($invoice->estado === 'ANULADA')
    <div class="watermark">ANULADA</div>
  @elseif($invoice->estado === 'CONTINGENCIA')
    <div class="watermark">CONTINGENCIA</div>
  @endif

  <div class="doc-header">
    <div class="doc-header-top">
      <div class="left">
        <p class="brand">U.E. SAN BERNARDO — TARIJA</p>
        <p class="subbrand">Unidad Educativa San Bernardo &bull; Sistema de Facturación Electrónica &bull; Sector Educativo Exento</p>
      </div>
      <div class="right">
        <div class="badge-factura">
          FACTURA
          <small>Computarizada — SFE</small>
          <span class="nro">N° {{ str_pad($invoice->nro_factura, 8, '0', STR_PAD_LEFT) }}</span>
        </div>
      </div>
    </div>
    <div class="header-body">
      <table>
        <tr>
          <td class="logo-cell">
            <div class="logo-box">
              @if(!empty($logoBase64))
                <img src="{{ $logoBase64 }}" alt="Logo San Bernardo">
              @else
                <span style="font-size:7px; color:#003200; font-weight:900; line-height:1.1; text-align:center;">SAN<br>BERNARDO<br><span style="font-size:5px; color:#64748b; font-weight:700;">TARIJA</span></span>
              @endif
            </div>
            <div style="margin-top:4px; font-size:5px; color:#64748b; font-weight:800; letter-spacing:0.4px; text-transform:uppercase;">Tarija — Bolivia</div>
          </td>
          <td class="emisor">
            <h3>{{ $tax->razon_social ?? $invoice->razon_social_emisor }}</h3>
            <p>Casa Central &bull; Sucursal {{ $invoice->codigo_sucursal }} &bull; Punto de Venta 0 &bull; {{ str_replace('_',' ', $invoice->modalidad) }}</p>
            <p class="nit">NIT: {{ $invoice->nit_emisor }}</p>
            <p>Tarija, Calle Ingavi esq. La Paz &bull; Tel: 4-6643232 &bull; info@sanber.edu.bo</p>
            <p class="tags"><span class="tag tag-sector">Sector Educativo</span><span class="tag tag-exento">Exento IVA / IT · Ley 843 Art. 13</span></p>
          </td>
          <td style="width: 254px;">
            <div class="meta-box">
              <div class="meta-row"><span class="meta-label">N° Factura:</span><span class="meta-value nro">{{ str_pad($invoice->nro_factura, 8, '0', STR_PAD_LEFT) }}</span></div>
              <div class="meta-row"><span class="meta-label">CUF:</span><span class="meta-value cuf">{{ $invoice->cuf }}</span></div>
              @if($invoice->cufd)<div class="meta-row"><span class="meta-label">CUFD:</span><span class="meta-value cuf">{{ $invoice->cufd }}</span></div>@endif
              @if($invoice->cuis)<div class="meta-row"><span class="meta-label">CUIS:</span><span class="meta-value cuf">{{ $invoice->cuis }}</span></div>@endif
              <div class="meta-row"><span class="meta-label">Fecha:</span><span class="meta-value">{{ \Carbon\Carbon::parse($invoice->fecha_emision)->format('d/m/Y') }} &bull; {{ substr($invoice->hora_emision,0,8) }}</span></div>
              <div class="meta-row"><span class="meta-label">Estado:</span><span class="meta-value"><span class="estado-badge estado-{{ $invoice->estado }}">{{ $invoice->estado }}</span> <span style="font-size:5.5px; color:#64748b; margin-left:4px; font-weight:700; text-transform:uppercase;">{{ $invoice->tipo_emision }}</span></span></div>
            </div>
          </td>
        </tr>
      </table>
    </div>
  </div>

  <div class="section">
    <div class="section-title">Datos del Cliente / Receptor <span class="sub">Comprobante válido ante el SIN</span></div>
    <div class="section-body">
      <table class="receptor-grid">
        <tr>
          <td style="width:50%; padding-right:10px;">
            <table style="width:100%; border-collapse:collapse;">
              <tr><td class="field-label">Razón Social:</td><td class="field-value strong">{{ $invoice->razon_social_receptor ?: 'S/N — Consumidor Final' }}</td></tr>
              <tr><td class="field-label">NIT / CI / Doc:</td><td class="field-value">{{ $invoice->nit_receptor ?: ($invoice->nro_doc_receptor ?: '0') }} @if($invoice->complemento_receptor) - {{ $invoice->complemento_receptor }} @endif @if($invoice->tipo_doc_receptor) <span style="color:#64748b; font-weight:400;">({{ $invoice->tipo_doc_receptor }})</span> @endif</td></tr>
              <tr><td class="field-label">Dirección:</td><td class="field-value">{{ $invoice->direccion_receptor ?: 'Tarija - Bolivia' }}</td></tr>
            </table>
          </td>
          <td style="width:50%;">
            <table style="width:100%; border-collapse:collapse;">
              <tr><td class="field-label">Email:</td><td class="field-value">{{ $invoice->email_receptor ?: '—' }}</td></tr>
              <tr><td class="field-label">Sucursal:</td><td class="field-value">{{ $invoice->codigo_sucursal }} &bull; Punto Venta 0 &bull; Modalidad {{ $invoice->modalidad }}</td></tr>
              <tr><td class="field-label">Moneda:</td><td class="field-value">Bolivianos (BOB) &bull; Tipo de cambio 1.00</td></tr>
            </table>
          </td>
        </tr>
      </table>
    </div>
  </div>

  <div class="section" style="padding:0;">
    <div class="section-title" style="display: table; width:100%;"><span style="display:table-cell;">Detalle de Servicios Educativos — Exentos</span><span style="display:table-cell; text-align:right; font-weight:600; font-size:5.5px; opacity:0.88; vertical-align:middle;">Montos expresados en Bolivianos (Bs) &bull; Actividad: Educación</span></div>
    <table class="items-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Cód. SIN</th>
          <th>Descripción del Servicio</th>
          <th style="width:40px;">Cant.</th>
          <th style="width:50px;">U. Medida</th>
          <th style="width:64px;">P. Unitario</th>
          <th style="width:50px;">Desc.</th>
          <th style="width:66px;">SubTotal</th>
        </tr>
      </thead>
      <tbody>
        @foreach($invoice->detalles as $d)
        <tr>
          <td class="num">{{ $d->nro_registro }}</td>
          <td class="center" style="font-family:monospace; font-weight:800; font-size:7px; color:#003200;">{{ $d->codigo_servicio }}</td>
          <td style="line-height:1.35;">{{ $d->descripcion }} @if($d->leyenda)<br><span style="font-size:5.8px; color:#64748b; font-style:italic;">{{ $d->leyenda }}</span>@endif</td>
          <td class="right">{{ number_format($d->cantidad,2) }}</td>
          <td class="center" style="font-size:6px; font-weight:700; color:#33412f;">{{ $d->unidad_medida }}</td>
          <td class="right">{{ number_format($d->precio_unitario,2) }}</td>
          <td class="right">{{ number_format($d->monto_descuento,2) }}</td>
          <td class="right" style="font-weight:800; color:#003200;">{{ number_format($d->sub_total,2) }}</td>
        </tr>
        @endforeach
        @for($i = $invoice->detalles->count(); $i < 3; $i++)
        <tr><td class="num">&nbsp;</td><td></td><td style="color:#94a3b8; font-style:italic; font-size:6.5px; text-align:center; padding:7px;">— línea en blanco —</td><td></td><td></td><td></td><td></td><td></td></tr>
        @endfor
      </tbody>
      <tfoot>
        <tr>
          <td colspan="6" style="text-align:right; font-weight:800; color:#334155; background:#f1f5f1; border-top:1.2px solid #003200;">SUBTOTAL EXENTO (Bs)</td>
          <td colspan="2" class="right" style="font-weight:800; background:#f1f5f1; border-top:1.2px solid #003200;">{{ number_format($invoice->total_exento,2) }}</td>
        </tr>
        <tr>
          <td colspan="6" style="text-align:right; color:#64748b; font-weight:600;">Descuentos Adicionales (Bs)</td>
          <td colspan="2" class="right" style="color:#64748b; font-weight:700;">{{ number_format($invoice->total_descuentos,2) }}</td>
        </tr>
        <tr class="total-row">
          <td colspan="6" style="text-align:right;">IMPORTE TOTAL A PAGAR (Bs)</td>
          <td colspan="2" class="right">{{ number_format($invoice->total_factura,2) }}</td>
        </tr>
      </tfoot>
    </table>
  </div>

  <table class="totales-leyendas">
    <tr>
      <td class="leyendas">
        <div class="leyendas-box">
          <div class="leyenda-title">Leyendas obligatorias — Servicio educativo exento (Art. 13 Ley 843)</div>
          <div class="leyenda-item">{{ $invoice->leyenda1 ?: ($tax->leyenda_iva_exento ?? 'EXENTO - NO GENERA DERECHO A CRÉDITO FISCAL') }}</div>
          <div class="leyenda-item">{{ $invoice->leyenda2 ?: ($tax->leyenda_it_exento ?? 'EXENTO IT - ART. 13 INC. F LEY 843') }}</div>
          <div class="leyenda-item">{{ $invoice->leyenda3 ?: ($tax->leyenda_ley070 ?? 'LEY 070 - EDUCACIÓN EXENTA IVA ART. 13 LEY 843') }}</div>
          <div class="leyenda-item">Esta factura contribuye al desarrollo del país. El uso ilícito de ésta será sancionado de acuerdo a ley.</div>
        </div>
        <div class="literal">
          SON: {{ $invoice->total_factura_texto ?: strtoupper($literal ?? number_format($invoice->total_factura,2).' BOLIVIANOS') }}
        </div>
        <div class="legal-small">Importe literal expresado en Bolivianos — Tipo de cambio oficial BCB 1.00 &bull; Código de control: CUF válido</div>
      </td>
      <td class="totales">
        <table class="totales-table">
          <tr><td class="label">Base Exenta</td><td class="value">{{ number_format($invoice->total_exento,2) }} Bs</td></tr>
          <tr><td class="label">Descuento Total</td><td class="value">{{ number_format($invoice->total_descuentos,2) }} Bs</td></tr>
          <tr><td class="label">IVA 13% (Exento)</td><td class="value">{{ number_format($invoice->total_iva,2) }} Bs</td></tr>
          <tr><td class="label">IT 3% (Exento)</td><td class="value">{{ number_format($invoice->total_it,2) }} Bs</td></tr>
          <tr class="highlight"><td class="label">TOTAL FACTURA</td><td class="value">{{ number_format($invoice->total_factura,2) }} Bs</td></tr>
        </table>
        <div class="condicion-box">
          <strong>Condición de pago:</strong> Contado<br>
          <strong>Método:</strong> Efectivo / Transferencia bancaria<br>
          <strong>Validez:</strong> Documento tributario computable ante SIN<br>
          <strong>NIT Emisor:</strong> {{ $invoice->nit_emisor }} &bull; <strong>Sucursal:</strong> {{ $invoice->codigo_sucursal }}
        </div>
      </td>
    </tr>
  </table>

  <div class="footer">
    <div class="footer-qr">
      <div class="qr-cell">
        <div class="qr-box">
          @if(!empty($qrBase64))
            <img src="{{ $qrBase64 }}" alt="QR CUF">
          @else
            <div class="ph">QR<br><strong style="font-size:5.5px; color:#003200;">{{ substr($invoice->cuf,0,8) }}</strong><br><span style="font-size:4.5px;">CUF</span></div>
          @endif
        </div>
        <div class="qr-caption">{{ chunk_split(substr($invoice->cuf,0,32),8,' ') }}</div>
        <div class="qr-verify">Verificar en: sin.gob.bo</div>
      </div>
      <div class="footer-info">
        <h4>Información Tributaria SFE — Validación</h4>
        <p><strong>CUF:</strong> <span class="cuf-line">{{ $invoice->cuf }}</span></p>
        @if($invoice->cufd)<p><strong>CUFD:</strong> <span style="font-family:monospace; font-size:6px;">{{ $invoice->cufd }}</span> &bull; <strong>CUIS:</strong> {{ $invoice->cuis ?: '—' }}</p>@endif
        <p><strong>Modalidad:</strong> {{ str_replace('_',' ', $invoice->modalidad) }} &bull; <strong>Tipo emisión:</strong> {{ $invoice->tipo_emision }} @if($invoice->tipo_emision==='CONTINGENCIA') <span style="background:#fee2e2; color:#7f1d1d; padding:1px 5px; border-radius:3px; font-weight:900; font-size:5.5px; border:1px solid #fca5a5;">EN CONTINGENCIA — REENVÍO PENDIENTE</span> @endif &bull; <strong>Sucursal:</strong> {{ $invoice->codigo_sucursal }} &bull; <strong>Punto venta:</strong> 0</p>
        <p><strong>Fecha / Hora emisión:</strong> {{ \Carbon\Carbon::parse($invoice->fecha_emision)->format('d/m/Y') }} {{ substr($invoice->hora_emision,0,8) }} &bull; <strong>Sistema:</strong> SFE UE San Bernardo v1.0</p>
        <div class="sfc-note">
          “ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAÍS, EL USO ILÍCITO SERÁ SANCIONADO PENALMENTE DE ACUERDO A LEY” — D.S. 27874
        </div>
      </div>
    </div>
  </div>

  <div class="copy-mark">
    <strong>Original:</strong> Cliente &bull; <strong>Copia:</strong> Emisor &bull; Representación gráfica del Documento Tributario Electrónico &bull; Generado por SFE UE San Bernardo &bull; {{ now()->format('d/m/Y H:i:s') }} &bull; Estado: {{ $invoice->estado }} &bull; Impresión válida sin firma manuscrita (DS 1793)
  </div>
</body>
</html>
