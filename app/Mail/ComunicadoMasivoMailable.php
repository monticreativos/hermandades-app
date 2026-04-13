<?php

namespace App\Mail;

use App\Models\ComunicadoMasivo;
use App\Models\ComunicadoMasivoDestinatario;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ComunicadoMasivoMailable extends Mailable
{
    use SerializesModels;

    public function __construct(
        public ComunicadoMasivo $comunicado,
        public ComunicadoMasivoDestinatario $destinatario
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->comunicado->asunto,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.comunicado-masivo',
            with: [
                'comunicado' => $this->comunicado,
                'destinatario' => $this->destinatario,
                'trackingUrl' => route('comunicados.track', ['token' => $this->destinatario->tracking_token]),
            ],
        );
    }
}
