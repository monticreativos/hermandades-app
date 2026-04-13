<?php

namespace App\Services\IA;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;

class OpenRouterResumenDocumentoService
{
    public function resumirDesdeArchivo(string $diskPath, ?string $mime = null): string
    {
        $contenido = $this->extraerTexto($diskPath, $mime);
        if ($contenido === '') {
            throw new \RuntimeException('No se pudo extraer texto del documento. Intenta con un PDF con texto seleccionable.');
        }

        $apiKey = (string) config('services.openrouter.api_key');
        if ($apiKey === '') {
            throw new \RuntimeException('OPENROUTER_API_KEY no configurada.');
        }

        $prompt = "Resume este documento en 3 puntos clave, en español, orientado al Secretario de una Hermandad.\n\nDOCUMENTO:\n".$contenido;
        $response = Http::withToken($apiKey)
            ->timeout(45)
            ->acceptJson()
            ->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => config('services.openrouter.model', 'openrouter/auto'),
                'messages' => [
                    ['role' => 'system', 'content' => 'Eres un asistente administrativo preciso. Responde solo con 3 viñetas claras.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('OpenRouter devolvió error al resumir documento.');
        }

        $text = trim((string) data_get($response->json(), 'choices.0.message.content', ''));
        if ($text === '') {
            throw new \RuntimeException('La IA no devolvió un resumen válido.');
        }

        return $text;
    }

    private function extraerTexto(string $diskPath, ?string $mime = null): string
    {
        if (! Storage::disk('local')->exists($diskPath)) {
            return '';
        }

        $absolute = Storage::disk('local')->path($diskPath);
        $resolvedMime = $mime ?: (string) mime_content_type($absolute);

        if (str_contains($resolvedMime, 'pdf')) {
            $pdf = (new Parser())->parseFile($absolute);
            return mb_substr(trim($pdf->getText()), 0, 12000);
        }

        if (str_contains($resolvedMime, 'text') || str_contains($resolvedMime, 'json')) {
            return mb_substr(trim((string) file_get_contents($absolute)), 0, 12000);
        }

        return '';
    }
}
