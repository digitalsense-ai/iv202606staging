<?php

namespace App\Http\Controllers\ocr;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
// use PDF;
// use Storage;

use setasign\Fpdi\Fpdi;

use \App\Classes\CommonClass;

class SplitPdfController extends Controller
{
    public $authUser;

    public $commonClass;
   
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {                    
            $this->commonClass = new CommonClass();
            $this->authUser = $this->commonClass->getAuthUser();   
            
            return $next($request);
        });
    }   

    /* -- GET /splitpdf -- */
    public function index()
    {   
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
        /* --end PAGE CONFIG -- */
        
        /* -- RETURN VIEW -- */
        return view('content.ocr.split', [
          'pageConfigs' => $pageConfigs, 
          'authUser' => $this->authUser
        ]);
        /* --end RETURN VIEW -- */
    }
    /* --end GET /splitpdf -- */

    /* -- POST /splitpdf -- */
    public function splitPdf(Request $request)
    {
        $request->validate([
            'pdf_file' => 'required|mimes:pdf',
            'page_ranges' => 'required|string',
        ]);

        $originalName = pathinfo(
            $request->file('pdf_file')->getClientOriginalName(),
            PATHINFO_FILENAME
        );

        $filePath = $request->file('pdf_file')->store('pdfs');
        $fullPath = storage_path('app/' . $filePath);

//         $parser = new \Smalot\PdfParser\Parser();
// $pdf = $parser->parseFile($fullPath);
// $pages = $pdf->getPages();

// $ranges = [];
// $start = 1;

// foreach ($pages as $index => $page) {
//     $text = trim($page->getText());
//     if (empty($text)) {
//         echo "Page " . ($index+1) . " is empty or image\n";
//     } else {
//         echo "Page " . ($index+1) . ": " . substr($text, 0, 50) . "\n";
//     }
// }


// // foreach ($pages as $index => $page) {dd($page->getText());
// //     if (str_contains($page->getText(), 'Fakturanr.') && $index + 1 !== $start) {
// //         $ranges[] = [$start, $index];
// //         $start = $index + 1;
// //     }
// // }

// $ranges[] = [$start, count($pages)];

// dd($ranges);

        // Parse ranges: 1-2,3,4-5
        $rangesInput = explode(',', $request->page_ranges);
        $ranges = [];

        foreach ($rangesInput as $range) {
            if (str_contains($range, '-')) {
                [$start, $end] = array_map('intval', explode('-', $range));
            } else {
                $start = $end = (int)$range;
            }
            $ranges[] = [$start, $end];
        }

        $outputDir = storage_path('app/split');
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        $pdfCounter = 1;

        foreach ($ranges as [$start, $end]) {

            $pdf = new Fpdi();

            // 🔑 THIS LINE FIXES THE ERROR
            $pageCount = $pdf->setSourceFile($fullPath);

            for ($page = $start; $page <= $end; $page++) {
                if ($page > $pageCount) break; // safety check

                $pdf->AddPage();
                $tpl = $pdf->importPage($page);
                $pdf->useTemplate($tpl);
            }

            //$pdf->Output('F', $outputDir . "/pdf{$pdfCounter}.pdf");
            $pdf->Output(
                'F',
                $outputDir . '/' . $originalName . '_' . $pdfCounter . '.pdf'
            );

            $pdfCounter++;
        }

        return "PDF split successfully ✔";
    }

    /* --end POST /splitpdf -- */    
}
