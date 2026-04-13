<?php

namespace App\Notifications;

use App\Models\Hermano;
use App\Models\RemesaRecibo;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReciboDevueltoDomiciliacionNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Hermano $hermano,
        private readonly RemesaRecibo $recibo,
        private readonly float $importeRecibo,
        private readonly float $gastosDevolucion,
        private readonly float $totalRegularizar,
        private readonly string $urlPortalPagos
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Recibo de cuota devuelto — regularización (portal del hermano)')
            ->greeting('Estimado cofrade,')
            ->line('Le informamos de que su recibo domiciliado ha sido devuelto por la entidad bancaria.')
            ->line('Periodo: '.$this->recibo->periodo_clave)
            ->line('Importe del recibo: '.number_format($this->importeRecibo, 2, ',', '.').' €')
            ->line('Gastos de devolución estimados: '.number_format($this->gastosDevolucion, 2, ',', '.').' €')
            ->line('Total a regularizar (orientativo): '.number_format($this->totalRegularizar, 2, ',', '.').' €')
            ->when(filled($this->recibo->motivo_devolucion), fn (MailMessage $m) => $m->line('Motivo indicado: '.$this->recibo->motivo_devolucion))
            ->line('En el portal tiene un aviso en «Notificaciones» y puede abonar por Bizum desde «Pagos», o contacte con secretaría.')
            ->action('Ir a Pagos del portal', $this->urlPortalPagos)
            ->line('Si el importe exacto difiere, tesorería lo ajustará al conciliar el movimiento.')
            ->salutation('Tesorería / Secretaría de la Hermandad');
    }
}
