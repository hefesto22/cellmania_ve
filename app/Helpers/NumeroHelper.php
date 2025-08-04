<?php

namespace App\Helpers;

class NumeroHelper
{
    public static function numeroALetras($numero): string
    {
        $formatter = new \NumberFormatter('es', \NumberFormatter::SPELLOUT);
        $entero = floor($numero);
        $letras = strtoupper($formatter->format($entero));
        return $letras;
    }
}
