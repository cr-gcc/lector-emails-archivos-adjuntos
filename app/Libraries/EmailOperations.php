<?php

namespace App\Libraries;
use Normalizer;

class EmailOperations
{
    public static function normalizeText($text) {
        $text = Normalizer::normalize($text, Normalizer::FORM_D);
        $text = preg_replace('/\p{Mn}/u', '', $text);
        $text = strtolower($text);
        return trim($text);
    }

    public static function getProfessor($text) {
        preg_match('/asesor \(a\):\s*(.+)/i', $text, $asesorMatch);
        return $asesorMatch[1] ?? "NOT-MATCH";
    }

    public static function getModuleNumber($text) {
        $number = NULL;
        preg_match('/módulo:?\s+([IVXLCDM]+)/iu', $text, $match);
        if (!is_null($match[1])) {
            $number = self::romanToInteger($match[1]);
        }
        else {
            $number = "NOT-MATCH";
        }
        return $number;
    }    

    public static function getDiploma($text) {
        preg_match('/Diplomado en Línea(?: de)?\s+“([^”]+)”/i', $text, $diplomadoMatch);
        return $diplomadoMatch[1] ?? null;
    }    

    public static function getDates($text) {
        preg_match('/a cabo del\s+(\d{1,2}\s+de\s+\w+)\s+al\s+(\d{1,2}\s+de\s+\w+)/iu', $text, $rango);
        $inicio = $rango[1] ?? null;
        $fin = $rango[2] ?? null;
        return [$inicio, $fin];
    }

    public static function romanToInteger($roman) {
        $mapa = [
            'I' => 1,
            'II' => 2,
            'III' => 3,
            'IV' => 4,
            'V' => 5,
            'VI' => 6,
            'VII' => 7,
            'VIII' => 8
        ];
        $roman = strtoupper(trim($roman));
        return $mapa[$roman] ?? "NOT-MATC";
    }
}