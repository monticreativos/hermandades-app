<?php

namespace App\Services\Secretaria;

class ProtocoloRegistroService
{
    public function siguienteNumero(string $tipo): string
    {
        $prefijo = strtoupper($tipo === 'salida' ? 'SAL' : 'ENT');
        $anio = now()->format('Y');
        $random = str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);

        return $prefijo.'-'.$anio.'-'.$random;
    }

    public function contenidoSello(string $numeroProtocolo, string $fecha): string
    {
        return "HERMANDAD - SELLO REGISTRO\nNº PROTOCOLO: {$numeroProtocolo}\nFECHA: {$fecha}\nVALIDADO DIGITALMENTE";
    }
}
