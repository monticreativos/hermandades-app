<?php

namespace App\Mail;

use App\Models\ConfiguracionHermandad;
use App\Models\Hermano;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReclamacionDeudaMailable extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Hermano $hermano,
        public ConfiguracionHermandad $hermandad,
        public string $pdfNombre,
        public string $pdfBinary
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recordatorio de deuda pendiente — '.$this->hermandad->nombre_corto,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reclamacion-deuda',
            with: [
                'hermano' => $this->hermano,
                'hermandad' => $this->hermandad,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfBinary, $this->pdfNombre)
                ->withMime('application/pdf'),
        ];
    }
}
