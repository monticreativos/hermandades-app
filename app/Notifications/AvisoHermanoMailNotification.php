<?php

namespace App\Notifications;

use App\Models\Aviso;
use App\Models\Hermano;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AvisoHermanoMailNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Aviso $aviso,
        private readonly Hermano $hermano
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url('/portal/login');

        return (new MailMessage)
            ->subject('Aviso de la hermandad: '.$this->aviso->titulo)
            ->greeting('Estimado cofrade,')
            ->line('Tiene un nuevo comunicado en el portal del hermano.')
            ->line($this->aviso->titulo)
            ->lines($this->lineasCuerpo())
            ->action('Abrir portal del hermano', $url)
            ->line('Si no usa el portal, puede contactar con secretaría.')
            ->salutation('Reciba un cordial saludo.');
    }

    /**
     * @return array<int, string>
     */
    private function lineasCuerpo(): array
    {
        $texto = trim(str_replace(["\r\n", "\r"], "\n", $this->aviso->cuerpo));
        $partes = array_filter(array_map('trim', explode("\n", $texto)));

        return $partes === [] ? ['(Sin texto adicional.)'] : $partes;
    }
}
