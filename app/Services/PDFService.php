<?php

namespace App\Services;

use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

class PDFService
{
    public static function getMpdfFontsDirs(): array
    {
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
        return array_merge($fontDirs, [base_path('resources/fonts')]);
    }

    public static function getMpdfFontData(): array
    {
        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];
        return array_merge($fontData, [
            'nikosh' => [
                'R' => 'Nikosh.ttf',
                'B' => 'Nikosh.ttf',
                'useOTL' => 0xFF,
                'useKashida' => 75,
            ],
            'siyamrupali' => [
                'R' => 'Siyamrupali.ttf',
                'B' => 'Siyamrupali.ttf',
                'useOTL' => 0xFF,
                'useKashida' => 75,
            ],
            'kalpurush' => [
                'R' => 'kalpurush.ttf',
                'B' => 'kalpurush.ttf',
                'useOTL' => 0xFF,
                'useKashida' => 75,
            ],
            'solaimanlipi' => [
                'R' => 'SolaimanLipi.ttf',
                'B' => 'SolaimanLipi.ttf',
                'useOTL' => 0xFF,
                'useKashida' => 75,
            ],
        ]);
    }
}
