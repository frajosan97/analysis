<?php

namespace App\Services;

use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

class PdfService
{
    private $mpdf;

    public function __construct()
    {
        $this->initializeMpdf();
    }

    private function initializeMpdf($orientation = 'P') // 'P' for portrait, 'L' for landscape
    {
        // Default mPDF config
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        // Default font config
        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        // Custom configuration
        $this->mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => $orientation, // Add orientation parameter
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'default_font' => 'dejavusans', // Supports UTF-8
            'fontDir' => array_merge($fontDirs, [
                storage_path('fonts/'), // Custom fonts directory
            ]),
            'fontdata' => $fontData + [
                'dejavusans' => [
                    'R' => 'DejaVuSans.ttf',
                    'B' => 'DejaVuSans-Bold.ttf',
                ],
            ],
        ]);

        // Set footer with page numbers
        // $this->mpdf->SetFooter('Page {PAGENO} of {nbpg}');
    }

    /**
     * Generate a PDF from HTML
     *
     * @param string $html
     * @param string $filename
     * @param string $outputMode (I = inline, D = download, F = save to file)
     * @param string $orientation (P = portrait, L = landscape)
     * @return mixed
     */
    public function generatePdfFromHtml(string $html, string $filename = 'document.pdf', string $outputMode = 'D', string $orientation = 'P')
    {
        if ($orientation === 'L') {
            $this->initializeMpdf('L');
        }
        $this->mpdf->WriteHTML($html);
        return $this->mpdf->Output($filename, $outputMode);
    }

    /**
     * Generate a PDF from a Blade view
     *
     * @param string $view
     * @param array $data
     * @param string $filename
     * @param string $outputMode
     * @param string $orientation (P = portrait, L = landscape)
     * @return mixed
     */
    public function generatePdfFromView(string $view, array $data = [], string $filename = 'document.pdf', string $outputMode = 'D', string $orientation = 'P')
    {
        $html = view($view, $data)->render();
        return $this->generatePdfFromHtml($html, $filename, $outputMode, $orientation);
    }
}
