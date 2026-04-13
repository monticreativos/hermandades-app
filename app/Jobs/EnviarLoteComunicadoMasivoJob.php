<?php

namespace App\Jobs;

use App\Mail\ComunicadoMasivoMailable;
use App\Models\ComunicadoMasivo;
use App\Models\ComunicadoMasivoDestinatario;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class EnviarLoteComunicadoMasivoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $comunicadoMasivoId) {}

    public function handle(): void
    {
        $comunicado = ComunicadoMasivo::query()->find($this->comunicadoMasivoId);
        if (! $comunicado) {
            return;
        }

        $pendientes = ComunicadoMasivoDestinatario::query()
            ->where('comunicado_masivo_id', $comunicado->id)
            ->whereNull('correo_enviado_en')
            ->with(['hermano', 'contactoExterno'])
            ->orderBy('id')
            ->limit(25)
            ->get();

        if ($pendientes->isEmpty()) {
            $comunicado->forceFill([
                'estado' => ComunicadoMasivo::ESTADO_COMPLETADO,
                'finalizado_en' => now(),
            ])->save();

            return;
        }

        $comunicado->forceFill(['estado' => ComunicadoMasivo::ESTADO_ENVIANDO])->save();

        foreach ($pendientes as $destinatario) {
            $email = trim((string) ($destinatario->email_destinatario ?: $destinatario->hermano?->email ?: $destinatario->contactoExterno?->email));
            $enviado = false;
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                try {
                    Mail::mailer()->to($email)->send(new ComunicadoMasivoMailable($comunicado, $destinatario));
                    $enviado = true;
                } catch (\Throwable $e) {
                    report($e);
                }
            }
            $destinatario->forceFill(['correo_enviado_en' => now()])->save();
            if ($enviado) {
                $comunicado->increment('correos_enviados');
            }
        }

        self::dispatch($this->comunicadoMasivoId)->delay(now()->addSeconds(1));
    }
}
