<?php

namespace App\Services\Secretaria;

use PhpOffice\PhpWord\IOFactory;

class DocxPlantillaImportService
{
    /**
     * @return array{html:string,sugerencias:array<int,array{token:string,sugerida:string}>}
     */
    public function importarComoHtml(string $absolutePath): array
    {
        $phpWord = IOFactory::load($absolutePath, 'Word2007');
        $writer = IOFactory::createWriter($phpWord, 'HTML');

        ob_start();
        $writer->save('php://output');
        $html = (string) ob_get_clean();

        $body = $this->extractBody($html);
        $styles = $this->extractStyles($html);
        $body = $this->normalizarHtmlImportado($body);
        $contenidoFinal = trim($styles."\n".$body);
        $sugerencias = $this->detectarSugerenciasVariables($body);

        return [
            'html' => $contenidoFinal,
            'sugerencias' => $sugerencias,
        ];
    }

    private function extractBody(string $html): string
    {
        if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $html, $m) === 1) {
            return trim((string) $m[1]);
        }

        return trim($html);
    }

    private function extractStyles(string $html): string
    {
        if (preg_match('/<style[^>]*>(.*?)<\/style>/is', $html, $m) === 1) {
            // Añadimos reglas base para tablas y párrafos al importar DOCX
            return '<style>'.$m[1].'
                table{border-collapse:collapse;width:100%;}
                td,th{border:1px solid #d1d5db;padding:6px;vertical-align:top;}
                p{margin:0 0 10px 0;}
            </style>';
        }

        return '<style>
            table{border-collapse:collapse;width:100%;}
            td,th{border:1px solid #d1d5db;padding:6px;vertical-align:top;}
            p{margin:0 0 10px 0;}
        </style>';
    }

    private function normalizarHtmlImportado(string $html): string
    {
        $html = str_replace(['<o:p>', '</o:p>'], '', $html);
        $html = preg_replace('/\s*mso-[^:]+:[^;"]+;?/i', '', $html) ?: $html;
        $html = preg_replace('/\sclass="[^"]*"/i', '', $html) ?: $html;

        return trim($html);
    }

    /**
     * @return array<int,array{token:string,sugerida:string}>
     */
    private function detectarSugerenciasVariables(string $html): array
    {
        preg_match_all('/\[[A-Z_]+\]|\{\{[a-zA-Z0-9_áéíóúñÑ]+\}\}/u', $html, $matches);
        $tokens = collect($matches[0] ?? [])->unique()->values();

        $map = [
            '[NOMBRE_HERMANO]' => '{{nombre}}',
            '[APELLIDOS_HERMANO]' => '{{apellidos}}',
            '[DNI]' => '{{dni}}',
            '[NUM_HERMANO]' => '{{num_hermano}}',
            '[FECHA_ALTA]' => '{{fecha_alta}}',
            '[NOMBRE_HERMANDAD]' => '{{nombre_hermandad}}',
            '{{dni}}' => '{{dni}}',
            '{{nombre}}' => '{{nombre}}',
            '{{apellidos}}' => '{{apellidos}}',
            '{{fecha_hoy}}' => '{{fecha_hoy}}',
        ];

        return $tokens->map(function (string $token) use ($map): array {
            $upper = mb_strtoupper($token);
            $sugerida = $map[$token] ?? ($map[$upper] ?? '');

            if ($sugerida === '' && str_contains($upper, 'NOMBRE')) {
                $sugerida = '{{nombre}}';
            } elseif ($sugerida === '' && str_contains($upper, 'DNI')) {
                $sugerida = '{{dni}}';
            } elseif ($sugerida === '' && str_contains($upper, 'APELL')) {
                $sugerida = '{{apellidos}}';
            }

            return ['token' => $token, 'sugerida' => $sugerida];
        })->filter(fn (array $i) => $i['sugerida'] !== '')->values()->all();
    }
}
