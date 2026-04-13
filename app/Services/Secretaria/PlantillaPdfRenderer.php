<?php

namespace App\Services\Secretaria;

use Spatie\Browsershot\Browsershot;

class PlantillaPdfRenderer
{
    public function render(string $html): ?string
    {
        $enabled = (bool) env('BROWSERSHOT_ENABLED', false);
        if (! $enabled || ! class_exists(Browsershot::class)) {
            return null;
        }

        try {
            return Browsershot::html($html)
                ->format('A4')
                ->margins(12, 12, 14, 12)
                ->showBackground()
                ->waitUntilNetworkIdle()
                ->pdf();
        } catch (\Throwable) {
            return null;
        }
    }
}
