<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

class HtmlToPdfConverter
{
    private $dompdf;

    public function __construct(Dompdf $dompdf)
    {
        $this->dompdf = $dompdf;
    }

    public function convertHtmlToPdf(string $htmlContent, string $outputFilePath): void
    {
        $this->dompdf->loadHtml($htmlContent);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $this->dompdf->setOptions($options);

        $this->dompdf->render();

        file_put_contents($outputFilePath, $this->dompdf->output());
    }
}