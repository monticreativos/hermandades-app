<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PortalInvitacionAccesoNotification extends Notification
{
    public function __construct(
        private readonly string $tokenPlano,
        private readonly string $codigoSeisDigitos,
        private readonly int $validezHoras = 72
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('portal.activar.show', ['token' => $this->tokenPlano], absolute: true);
        $urlCodigo = route('portal.activacion.codigo', absolute: true);

        return (new MailMessage)
            ->subject('Invitación al Portal del Hermano')
            ->line('La secretaría de su hermandad ha habilitado el acceso al portal cofrade.')
            ->line('Su código de activación (6 dígitos): '.$this->codigoSeisDigitos)
            ->line('Introduzca este código junto con su correo en la página de activación, o bien use el botón siguiente para abrir el enlace directo.')
            ->action('Activar con enlace seguro', $url)
            ->line('Activación manual: '.$urlCodigo)
            ->line('Caducidad del código y del enlace: '.$this->validezHoras.' horas. No comparta el código ni el enlace con terceros.');
    }
}
