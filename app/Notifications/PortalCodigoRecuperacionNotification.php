<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PortalCodigoRecuperacionNotification extends Notification
{
    public function __construct(
        private readonly string $codigoSeisDigitos
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Código para restablecer contraseña — Portal del Hermano')
            ->line('Su código de verificación es:')
            ->line($this->codigoSeisDigitos)
            ->line('Caduca en 10 minutos. Si no ha solicitado el cambio, ignore este correo.');
    }
}
