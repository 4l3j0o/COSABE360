<?php

namespace App\Mail;

use App\Models\ElectronicInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FacturaEmitidaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ElectronicInvoice $invoice,
        public string $pdfBinary,
        public string $estudianteNombre,
        public string $mesNombre
    ) {}

    public function envelope(): Envelope
    {
        $nro = str_pad((string)$this->invoice->nro_factura, 8, "0", STR_PAD_LEFT);
        return new Envelope(
            subject: "Factura SFE {$nro} - {$this->estudianteNombre} - {$this->mesNombre} | U.E. San Bernardo",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: "emails.factura",
            with: [
                "invoice" => $this->invoice,
                "estudianteNombre" => $this->estudianteNombre,
                "mesNombre" => $this->mesNombre,
            ],
        );
    }

    public function attachments(): array
    {
        $nro = str_pad((string)$this->invoice->nro_factura, 8, "0", STR_PAD_LEFT);
        return [
            Attachment::fromData(fn() => $this->pdfBinary, "Factura-{$nro}-{$this->invoice->cuf}.pdf")
                ->withMime("application/pdf"),
        ];
    }
}
