<?php

namespace App\Helpers;

class BarcodeHelper
{
    public static function generateBarcodeSVG($code, $width = 200, $height = 60)
    {
        $code = strtoupper($code);
        if (!preg_match('/^[A-Z0-9\-\.\s]+$/', $code)) {
            $code = preg_replace('/[^A-Z0-9\-\.\s]/', '', $code);
        }
        if (strlen($code) === 0) {
            $code = 'EMPTY';
        }

        $chars = str_split($code);
        $charWidth = 11;
        $quietZone = 10;
        $totalWidth = $quietZone * 2 + count($chars) * $charWidth;
        $barHeight = $height - 20;

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $totalWidth . ' ' . $height . '" width="' . $width . '" height="' . $height . '">';
        $svg .= '<rect width="' . $totalWidth . '" height="' . $height . '" fill="white"/>';

        $x = $quietZone;
        foreach ($chars as $char) {
            $patterns = [
                '0' => '11011001100', '1' => '11001101100', '2' => '11001100110',
                '3' => '10011001100', '4' => '11001001100', '5' => '11001001100',
                '6' => '10011001100', '7' => '10010011000', '8' => '11001001100',
                '9' => '11001001100', 'A' => '11011001100', 'B' => '11001101100',
                'C' => '11001100110', 'D' => '10011001100', 'E' => '11001001100',
                'F' => '11001001100', 'G' => '10011001100', 'H' => '10010011000',
                'I' => '11001001100', 'J' => '11001001100', 'K' => '11011001100',
                'L' => '11001101100', 'M' => '11001100110', 'N' => '10011001100',
                'O' => '11001001100', 'P' => '11001001100', 'Q' => '10011001100',
                'R' => '10010011000', 'S' => '11001001100', 'T' => '11001001100',
                'U' => '11011001100', 'V' => '11001101100', 'W' => '11001100110',
                'X' => '10011001100', 'Y' => '11001001100', 'Z' => '11001001100',
                '-' => '10010011000', '.' => '11001001100', ' ' => '11001001100',
            ];

            $pattern = $patterns[$char] ?? '11011001100';
            foreach (str_split($pattern) as $j => $bit) {
                if ($bit === '1') {
                    $svg .= '<rect x="' . ($x + $j) . '" y="0" width="1" height="' . $barHeight . '" fill="black"/>';
                }
            }
            $x += $charWidth;
        }

        $svg .= '<text x="' . ($totalWidth / 2) . '" y="' . ($height - 4) . '" text-anchor="middle" font-family="monospace" font-size="10">' . htmlspecialchars($code) . '</text>';
        $svg .= '</svg>';

        return $svg;
    }
}
