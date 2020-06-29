<?php 
namespace App\Services;

use Mpdf\Mpdf;

 class Pdf 
 {
 	private $config;

 	function __construct($custom_config = NULL)
 	{
		$defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $this->config = [
            'fontDir' => array_merge($fontDirs, [
               public_path() . '/font/Open_Sans',
            ]),
            'opensans' => $fontData + [
                'frutiger' => [
                    'R' => 'OpenSans-Regular.ttf',
                    'I' => 'OpenSans-Regular.ttf',
                ]
            ],
            'default_font' => 'opensans',
            
            'setAutoTopMargin' => 'stretch', // Need this to get rid of content overlapping with header
            // 'margin_top' => 0,
            
        ];
        if(is_array($custom_config))
        {
            $this->config = array_merge($this->config, $custom_config);
        }


        // $mpdf->SetProtection(array('print'));
        // $mpdf->SetTitle("Acme Trading Co. - Invoice");
        // $mpdf->SetAuthor("Acme Trading Co.");
        // $mpdf->SetWatermarkText("Paid");
        // $mpdf->showWatermarkText = true;
        // $mpdf->watermark_font = 'DejaVuSansCondensed';
        // $mpdf->watermarkTextAlpha = 0.1;
        // $mpdf->SetDisplayMode('fullpage');
        // $mpdf->WriteHTML($html);
        // $mpdf->Output();
 	}

 	function download($html, $filename)
 	{
 		$mpdf = new Mpdf($this->config);
        $mpdf->WriteHTML($html);
        $mpdf->Output($filename. ".pdf", 'I');
 	}

    function get_pdf_file_path($html)
    {
        $mpdf = new Mpdf($this->config);
        $mpdf->WriteHTML($html);

        $path_to_directory = storage_path(). '/app/public/tmp';

        if (!file_exists($path_to_directory)) {
            mkdir($path_to_directory, 0777, true);
        }
        
        $file_path  = $path_to_directory . '/'.uniqid().'.pdf';
        $content    = $mpdf->Output($file_path, 'F'); // Store PDF
        return $file_path;
        //return chunk_split(base64_encode($content));
    }
 }