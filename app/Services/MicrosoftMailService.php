<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
//use App\Http\Controllers\ocr\AnalyzePdfController;
use Illuminate\Support\Facades\Log;

use Str;
use setasign\Fpdi\Fpdi;

use App\Services\AzureStorageService;
use App\Services\OcrAnalyzeService;

class MicrosoftMailService
{
    protected string $accessToken;
    protected string $mailbox;

    public function __construct()
    {
        $this->mailbox = config('services.ms.mailbox');
        $this->accessToken = $this->getAccessToken();
    }

    // Get application access token
    protected function getAccessToken(): string
    {
        $response = Http::asForm()->post(
            'https://login.microsoftonline.com/'.config('services.ms.tenant_id').'/oauth2/v2.0/token',
            [
                'client_id' => config('services.ms.client_id'),
                'scope' => 'https://graph.microsoft.com/.default',
                'client_secret' => config('services.ms.client_secret'),
                'grant_type' => 'client_credentials',
            ]
        );

        return $response->json()['access_token'];
    }

    // Fetch all emails from inbox with pagination
    public function getAllInboxEmails(): array
    {
        $emails = [];
        //$url = "https://graph.microsoft.com/v1.0/users/{$this->mailbox}/mailFolders/Inbox/messages?\$orderby=receivedDateTime desc&\$top=50";
        $url = "https://graph.microsoft.com/v1.0/users/{$this->mailbox}/mailFolders/Inbox/messages?\$filter=isRead eq false&\$orderby=receivedDateTime desc&\$top=50";

        do {
            $response = Http::withToken($this->accessToken)->get($url);

            if (!$response->successful()) {
                logger()->error('Graph API error', $response->json());
                break;
            }

            $data = $response->json();

            $emails = array_merge($emails, $data['value'] ?? []);

            $url = $data['@odata.nextLink'] ?? null;

        } while ($url);

        //return $emails;

        // Filter only emails that actually have attachments
        //$emailsWithAttachments = array_filter($emails, fn($email) => !empty($email['hasAttachments']));

        // Filter only emails that actually have attachments
        $emailsWithAttachments = array_filter($emails, function ($email) {
            if (empty($email['hasAttachments'])) {
                // Move emails without attachments
                $this->moveEmailToFolder($email['id'], 'No Attachment');
                return false;
            }
            return true;
        });

        return array_values($emailsWithAttachments); // reset keys        
    }

    // Download PDF attachments and store in com/sales folders
    public function downloadPdfAttachments(string $messageId, string $subject): array
    {
        $url = "https://graph.microsoft.com/v1.0/users/{$this->mailbox}/messages/$messageId/attachments";
        $response = Http::withToken($this->accessToken)->get($url);
        $attachments = $response->json('value', []);
        //$savedPaths = [];

        /*
        // $com_keywords = ['consolidated', 'commercial', 'proformafaktura', 'samlefaktura', 'report', 'ci-', 'ch ', 
        //                     'Zollsammelrechnung', 'dsv', 'bring', '_ci_', 'samle', 'fakturanosam', 'proforma', '160326', 'uk ', 'gb '];
        $com_keywords = ['consolidated', 'commercial', 'proformafaktura', 'samlefaktura', 'report', 'ci-', 
                            'Zollsammelrechnung', 'dsv', 'bring', '_ci_', 'samle', 'fakturanosam', 'proforma', '160326', 'msj_'];
        $sales_keywords = ['kundefakturaer', 'sales', ' fakturaer ', 'intertrans_invoice', 'invoices_for_commercial_invoice'];
        $multiple_sales_keywords = ['nos-', 'salesinvoices_ic', 'posted sales invoices'];
        */

        $grouped = [
            'sales' => [],
            'com' => []
        ];

        foreach ($attachments as $i => $attachment) {
            if ($attachment['@odata.type'] !== '#microsoft.graph.fileAttachment') continue;

            $grouped = $this->groupFiles($attachment, $subject, $grouped);
/*            
            $fileName = $attachment['name'];            

            // Only PDF attachments
            if (strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) !== 'pdf') continue;

            // Skip files starting with EAD or ESCAN_
            //if (preg_match('/^(ATT|EAD|ESCAN_)/i', $fileName)) continue;
            if (
                preg_match('/^(ATT|EAD|ESCAN_)/i', $fileName)
                || stripos($fileName, 'AJONEDDCPA') !== false
                || Str::startsWith(Str::lower($fileName), ['26dk'])
            ) continue;

            if (stripos($subject, "second female") !== false) 
            {
                //store the same file in both folders
                //com
                $folder = 'com';
                $path = "ocr/$folder/$fileName"; // relative to storage/app
                Storage::disk('local')->put($path, base64_decode($attachment['contentBytes']));

                $fullPath = storage_path('app/' . $path); // this will exist                

                $pdfInfo = new Fpdi();
                $totalPages = $pdfInfo->setSourceFile($fullPath);

                //Log::info("Second Female Total pages: ". $totalPages);
                if($totalPages <= 3)
                {
                    // Delete local file
                    if (file_exists($fullPath)) {
                        unlink($fullPath);
                    }
                }
                else
                    $grouped[$folder][] = $fullPath;

                //sales
                $folder = 'sales';
                $path = "ocr/$folder/$fileName"; // relative to storage/app
                Storage::disk('local')->put($path, base64_decode($attachment['contentBytes']));

                $fullPath = storage_path('app/' . $path); // this will exist
                $grouped[$folder][] = $fullPath;
            }
            else
            {
                // Decide folder purely based on filename
                $isCom = false;
                foreach ($com_keywords as $word) {
                    if (stripos($fileName, $word) !== false) {
                        $isCom = true;
                        break;
                    }
                }
                
                $isSales = false;        
                foreach ($sales_keywords as $word) {
                    if (stripos($fileName, $word) !== false) {
                        $isSales = true;
                        break;
                    }
                }
                
                //$folder = $isCom ? 'com' : 'sales';

                $folder = ''; 
                if($isCom)
                    $folder = 'com';   

                if($isSales)
                {                
                    $isSalesMultiple = false;        
                    foreach ($multiple_sales_keywords as $word) {
                        if (stripos($fileName, $word) !== false) {
                            $isSalesMultiple = true;
                            break;
                        }
                    }

                    $folder = ($isSalesMultiple) ? 'multi-invoices' : 'sales';
                }
                
                if(!$folder)
                {                   
                    if (stripos($subject, "commercial_invoice_") !== false 
                        || stripos($subject, "commercial-invoice-") !== false
                        || stripos($subject, "ci-") !== false
                        || stripos($subject, "c-i-") !== false
                        || stripos($subject, "ci_") !== false
                        || stripos($subject, "c_i_") !== false
                        //|| stripos($subject, "no") !== false
                        || stripos($fileName, "nos-") !== false
                        || stripos($fileName, "nos ") !== false
                        || stripos($fileName, "ic") !== false
                        || Str::startsWith(Str::lower($fileName), ['no ', 'la_', 'haos_', 'nmd', 'ch ', 'uk ', 'gb ', 'invoices_for_commercial_invoice', 'ci'])
                        || Str::startsWith(Str::lower($subject), ['no00'])
                    )
                    { 
                        if(Str::startsWith(Str::lower($fileName), ['invoice'])
                            || stripos($fileName, "_invoice_") !== false
                        )
                            $folder = 'sales';
                        else
                            $folder = 'com';
                    }
                    else 
                        $folder = 'sales';
                }

                $path = "ocr/$folder/$fileName"; // relative to storage/app
                Storage::disk('local')->put($path, base64_decode($attachment['contentBytes']));

                $fullPath = storage_path('app/' . $path); // this will exist
                $grouped[$folder][] = $fullPath;            
            }
                */
            // // Save attachment with timestamp to prevent overwrite
            // //$path = "email_attachments/$folder/" . time() . '_' . $fileName;
            // $path = "public/ocr/$folder/$fileName";

            // Storage::put($path, base64_decode($attachment['contentBytes']));
            // //$savedPaths[] = $path;

            // $grouped[$folder][] = $path;               
        }

        // // ---------------------- TRIGGER ANALYZE ----------------------
        // foreach ($grouped as $folder => $paths) {
        //     if (!empty($paths)) {
        //         $this->triggerAnalyzeForStoredPdf($paths, $folder, $messageId);
        //     }
        // }
    
        //return $savedPaths;
        return $grouped;
    }      

    public function groupFiles(array $attachment, string $subject = null, array $grouped = []): array
    {
        // $com_keywords = ['consolidated', 'commercial', 'proformafaktura', 'samlefaktura', 'report', 'ci-', 'ch ', 
        //                     'Zollsammelrechnung', 'dsv', 'bring', '_ci_', 'samle', 'fakturanosam', 'proforma', '160326', 'uk ', 'gb '];
        $com_keywords = ['consolidated', 'commercial', 'proformafaktura', 'samlefaktura', 'report', 'ci-', 
                            'Zollsammelrechnung', 'dsv', 'bring', '_ci_', 'samle', 'fakturanosam', 'proforma', '160326', 'msj_', 'eksportsalgsfaktura'];
        $sales_keywords = ['kundefakturaer', 'sales', ' fakturaer ', 'intertrans_invoice', 'invoices_for_commercial_invoice', 'deerhunter_invoice', 'proforma_invoice_'];
        $multiple_sales_keywords = ['nos-', 'salesinvoices_ic', 'posted sales invoices', 'invoices_for_commercial_invoice_'];

        $fileName = $attachment['name'];

        $info = pathinfo($fileName);
        // Remove existing timestamp + unique suffix if present
        $baseName = preg_replace(
            '/_\d{8}_\d{6}_[a-zA-Z0-9]{4}$/',
            '',
            $info['filename']
        );

        $timestamp = date('Ymd_His') . '_' . substr(uniqid(), -4);
        $extension = isset($info['extension']) ? '.' . $info['extension'] : '';
        
        $fileName = $baseName . '_' . $timestamp . $extension;

        // Only PDF attachments
        if (strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) !== 'pdf') return $grouped;

        // Skip files starting with EAD or ESCAN_        
        if (
            preg_match('/^(ATT|EAD|ESCAN_|Payment information|Mainifest)/i', $fileName)
            || stripos($fileName, 'AJONEDDCPA') !== false
            || stripos($fileName, 'Mva-melding') !== false
            || stripos($fileName, 'kontoudtog') !== false   
            || stripos($fileName, 'delivery_note') !== false           
            || Str::startsWith(Str::lower($fileName), ['26dk', 'gls - '])
        ) return $grouped;

        if (stripos($subject, "second female") !== false) 
        {
            //store the same file in both folders
            //com
            $folder = 'com';
            $path = "ocr/$folder/$fileName"; // relative to storage/app                    
            Storage::disk('local')->put($path, base64_decode($attachment['contentBytes']));

            $fullPath = storage_path('app/' . $path); // this will exist                

            $pdfInfo = new Fpdi();
            $totalPages = $pdfInfo->setSourceFile($fullPath);

            //Log::info("Second Female Total pages: ". $totalPages);
            if($totalPages <= 3)
            {
                // Delete local file
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
            else
            {
                //$grouped[$folder][] = $fullPath;

                $grouped[$folder][] = [
                    'path' => $fullPath,
                    'prevCapture' => $attachment['prevCapture'] ?? null
                ];
            }

            //sales
            $folder = 'sales';
            $path = "ocr/$folder/$fileName"; // relative to storage/app
            Storage::disk('local')->put($path, base64_decode($attachment['contentBytes']));

            $fullPath = storage_path('app/' . $path); // this will exist
            //$grouped[$folder][] = $fullPath;

            $grouped[$folder][] = [
                'path' => $fullPath,
                'prevCapture' => $attachment['prevCapture'] ?? null
            ];
        }
        else
        {            
            if(isset($attachment['prevFolder']))
            {
                if (Str::startsWith(Str::lower($fileName), ['jessi regina'])                    
                )
                    $folder = 'com';
                else
                    $folder = $attachment['prevFolder'];
            }
            else
            {
                // Decide folder purely based on filename
                $isCom = false;
                foreach ($com_keywords as $word) {
                    if (stripos($fileName, $word) !== false) {
                        $isCom = true;
                        break;
                    }
                }
                
                $isSales = false;        
                foreach ($sales_keywords as $word) {
                    if (stripos($fileName, $word) !== false) {
                        $isSales = true;
                        break;
                    }
                }
                
                //$folder = $isCom ? 'com' : 'sales';

                $folder = ''; 
                if($isCom)
                    $folder = 'com';   

                if($isSales)
                {                
                    $isSalesMultiple = false;        
                    foreach ($multiple_sales_keywords as $word) {
                        if (stripos($fileName, $word) !== false) {
                            $isSalesMultiple = true;
                            break;
                        }
                    }

                    $folder = ($isSalesMultiple) ? 'multi-invoices' : 'sales';
                }
                
                if(!$folder)
                {                   
                    if (stripos($subject, "commercial_invoice_") !== false 
                        || stripos($subject, "commercial-invoice-") !== false
                        || stripos($subject, "ci-") !== false
                        || stripos($subject, "c-i-") !== false
                        || stripos($subject, "ci_") !== false
                        || stripos($subject, "c_i_") !== false
                        //|| stripos($subject, "no") !== false
                        || stripos($fileName, "nos-") !== false
                        || stripos($fileName, "nos ") !== false
                        || stripos($fileName, "ic") !== false
                        || Str::startsWith(Str::lower($fileName), ['no ', 'la_', 'haos_', 'nmd', 'ch ', 'uk ', 'gb ', 'invoices_for_commercial_invoice', 'ci', 'dhl', '980827682_mva_si_2610000', 'jessi regina'])
                        || Str::startsWith(Str::lower($subject), ['no00'])
                    )
                    { 
                        if(Str::startsWith(Str::lower($fileName), ['invoice'])
                            || stripos($fileName, "_invoice_") !== false
                        )
                            $folder = 'sales';
                        else
                            $folder = 'com';
                    }
                    else 
                    {
                        if(Str::startsWith(Str::lower($fileName), ['1005']))
                            $folder = 'com';
                        else    
                            $folder = 'sales';
                    }
                }
            }    
            // $azureService = new AzureStorageService();
            // $azurePath = $folder . '/' . $baseName . $extension;
            // $file_exists = $azureService->checkFile($azurePath);

            // if(!$file_exists)
            //     $fileName = $baseName . $extension;

            $path = "ocr/$folder/$fileName"; // relative to storage/app
            Storage::disk('local')->put($path, base64_decode($attachment['contentBytes']));

            $fullPath = storage_path('app/' . $path); // this will exist
            //$grouped[$folder][] = $fullPath;

            $grouped[$folder][] = [
                'path' => $fullPath,
                'prevCapture' => $attachment['prevCapture'] ?? null
            ];
        }
            
        return $grouped;    
    }

    // public function triggerAnalyzeForStoredPdf(array $savedPaths, string $folder, string $messageId): void
    // {
    //     if (empty($savedPaths)) return;

    //     // Determine invoice type from folder
    //     $invoiceType = $folder; // 'com' or 'sales'

    //     //$requestData = [];

    //     // if (count($savedPaths) === 1) {
    //     //     // Single attachment → simulate pdf_file input
    //     //     $request = Request::create('/analyzepdf', 'POST', [
    //     //         'pdf_invoice_type' => $invoiceType,
    //     //         'email_message_id' => $messageId
    //     //     ]);
    //     //     // $request->files->set('pdf_file', new \Illuminate\Http\UploadedFile(
    //     //     //     $savedPaths[0],
    //     //     //     pathinfo($savedPaths[0], PATHINFO_BASENAME)
    //     //     // ));

    //     //     $fullPath = storage_path('app/' . $savedPaths[0]);
    //     //     $request->files->set('pdf_file', new \Illuminate\Http\UploadedFile(
    //     //         $fullPath,
    //     //         pathinfo($savedPaths[0], PATHINFO_BASENAME),
    //     //         'application/pdf',
    //     //         filesize($fullPath),
    //     //         true
    //     //     ));

    //     // } else {
    //         // Multiple attachments → simulate pdfs array
    //         $request = Request::create('/analyzepdf', 'POST', [
    //             'pdf_invoice_type' => $invoiceType,
    //             'email_message_id' => $messageId,
    //             'pdf_paths' => $savedPaths
    //         ]);
    //         // $files = [];
    //         // foreach ($savedPaths as $path) {
    //         //     // $files[] = new \Illuminate\Http\UploadedFile(
    //         //     //     $path,
    //         //     //     pathinfo($path, PATHINFO_BASENAME)
    //         //     // );

    //         //     $fullPath = storage_path('app/' . $path);
    //         //     $files[] = new \Illuminate\Http\UploadedFile(
    //         //         $fullPath,
    //         //         pathinfo($path, PATHINFO_BASENAME),
    //         //         'application/pdf',
    //         //         filesize($fullPath),
    //         //         true
    //         //     );
    //         // }

    //         // $request->files->set('pdfs', $files);
    //     //}

    //     // Call analyze() method directly
    //     $controller = app(AnalyzePdfController::class);
    //     $controller->analyze($request);

    //     Log::info("Queued PDF processing for folder {$folder}", $savedPaths);
    // }

    public function markEmailAsRead(string $messageId)
    {
        $url = "https://graph.microsoft.com/v1.0/users/{$this->mailbox}/messages/$messageId";

        Http::withToken($this->accessToken)->patch($url, [
            'isRead' => true
        ]);
    }

    public function addCategory(string $messageId, string $categoryName = 'Green Category')
    {
        $url = "https://graph.microsoft.com/v1.0/users/{$this->mailbox}/messages/$messageId";

        Http::withToken($this->accessToken)->patch($url, [
            'categories' => [$categoryName]
        ]);
    }

    public function moveEmailToFolder(string $messageId, string $subFolder = 'Done')
    {
        $url = "https://graph.microsoft.com/v1.0/users/{$this->mailbox}/messages/$messageId/move";

        $folderId = $this->getSubFolderId('OCR-Test', $subFolder);

        Http::withToken($this->accessToken)->post($url, [
            'destinationId' => $folderId
        ]);
    }

    public function getFolderIdByName($name)
    {
        $response = Http::withToken($this->accessToken)
            ->get("https://graph.microsoft.com/v1.0/users/{$this->mailbox}/mailFolders");

        foreach ($response->json()['value'] as $folder) {
            if ($folder['displayName'] === $name) {
                return $folder['id'];
            }
        }

        return null;
    }

    public function getSubFolderId($parentFolderName, $subFolderName)
    {
        // Step 1: Get all top folders
        $response = Http::withToken($this->accessToken)
            ->get("https://graph.microsoft.com/v1.0/users/{$this->mailbox}/mailFolders");

        $folders = $response->json()['value'];

        $parentId = null;

        foreach ($folders as $folder) {
            if ($folder['displayName'] === $parentFolderName) {
                $parentId = $folder['id'];
                break;
            }
        }

        if (!$parentId) {
            return null;
        }

        // Step 2: Get subfolders
        $response = Http::withToken($this->accessToken)
            ->get("https://graph.microsoft.com/v1.0/users/{$this->mailbox}/mailFolders/$parentId/childFolders");

        foreach ($response->json()['value'] as $folder) {
            if ($folder['displayName'] === $subFolderName) {
                return $folder['id'];
            }
        }

        return null;
    }
}