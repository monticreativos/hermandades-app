<?php

namespace App\Support;

class SanitizeComunicadoHtml
{
    /**
     * Limpia HTML del redactor conservando etiquetas seguras para el cuerpo del email.
     */
    public static function clean(string $html): string
    {
        $allowed = '<p><br><br/><strong><b><em><i><u><ul><ol><li><a><h1><h2><h3><blockquote><span><div>';
        $html = strip_tags($html, $allowed);

        return $html;
    }
}
