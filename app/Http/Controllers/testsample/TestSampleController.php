<?php

namespace App\Http\Controllers\testsample;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

use App\Jobs\ReadFtpFiles;

use App\Models\CargoDeclarationFiles;
use App\Models\Client;
use App\Models\ClientApi;
use App\Models\SystemApis;
use App\Models\VATRegistrationMain;
use App\Models\VATRegistrationMainCasDdaMonths;
use App\Models\CashAccountStatement;
use App\Models\DutyDefermentAccount;
use App\Models\VATRegistration;
use App\Models\ImportReconciliationComInvoices;
use App\Models\ImportReconciliationSalesInvoices;
use App\Models\ImportReconciliationFiles;
use App\Models\ImportReconciliationSalesInvoicesData;

use \App\Classes\CommonClass;
use \App\Classes\ApiClass;
use \App\Classes\HMRCApiClass;
use \App\Classes\CVRApiClass;
use \App\Classes\EmailBoxApiClass;
use \App\Classes\CommercialInvoicesClass;
use \App\Classes\InvoiceClass;
use \App\Classes\FtpClass;
use \App\Classes\EFactoClass;
use \App\Classes\CargoDeclarationClass;
use \App\Classes\SwissImportReconciliationClass;

use App\Services\TableToExcelService;

use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;

use OpenAI\Laravel\Facades\OpenAI;
use HelgeSverre\ReceiptScanner\Facades\ReceiptScanner;
use HelgeSverre\ReceiptScanner\Facades\Text;
//use HelgeSverre\ReceiptScanner\ModelNames;
use HelgeSverre\ReceiptScanner\Enums\Model;

use Webklex\IMAP\Facades\Client as MailBoxClient;

use Storage;

use Spatie\PdfToText\Pdf as PdfExtract;
use Spatie\PdfToImage\Pdf as PdfImage;
use Aws\Textract\TextractClient;

class TestSampleController extends Controller
{
	public $authUser;

    public $commonClass;
    public $apiClass;
    public $hmrcApiClass;
    public $cvrApiClass;
    public $emailBoxApiClass;
    public $invoiceClass;
    public $ftpClass;
    public $eFactoClass;
    public $cargoDeclarationClass;
    public $swissImportReconciliationClass;

	public function __construct()
    {
       if (strpos(URL::full(), 'confirm-email') !== false)
          \Session::put('url.intended', URL::full());  

		$this->middleware('auth');
        $this->middleware(function ($request, $next) {        	          
            $this->commonClass = new CommonClass();
            $this->hmrcApiClass = new HMRCApiClass();
            $this->apiClass = new ApiClass();
            $this->cvrApiClass = new CVRApiClass();
            $this->emailBoxApiClass = new EmailBoxApiClass();
            $this->invoiceClass = new InvoiceClass();
            $this->ftpClass = new FtpClass();
            $this->eFactoClass = new EFactoClass();
            $this->cargoDeclarationClass = new CargoDeclarationClass();
            $this->swissImportReconciliationClass = new SwissImportReconciliationClass();

            $this->authUser = $this->commonClass->getAuthUser();          

            if($this->authUser->role == 'client-user')            
                $this->clientIds = $this->commonClass->getClientIdsForClientUser($this->authUser); 

        	if (strpos(\Session::get('url.intended'), 'confirm-email') !== false)
        		return \Redirect::to(\Session::get('url.intended'));
        	else
        		return $next($request);
        });      
	}

	public function index()
	{	
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
        /* --end PAGE CONFIG -- */

        /* -- RETURN VIEW -- */
        return view('content.table', [
          'pageConfigs' => $pageConfigs, 
          'authUser' => $this->authUser                
        ]);
        /* --end RETURN VIEW -- */        
	}
   
    public function exchangerateexcel()
    { 
        try 
        {                 
            $result = $this->commonClass->insertExchangeRates('excel');    

            if($result == 'success')
                dd('Todays exchange rate has been stored successfully');   
            else
                dd('Error in fetching the todays exchange rate.' . $result);          
        }
        catch (\Exception $e) 
        {
            return  $e->getMessage();
        }
    }

    public function coinvoices()
    {       
        $commercialInvoicesClass = new CommercialInvoicesClass();
        $flepath = '01 commercial invoices/';

        //$filename = 'ADAG ApS - CommercialInvoice_200104.pdf';//ok
        //$filename = 'AID STUDIO - commercial_invoice_129.pdf';//ok
        //$filename = 'BECKSONDERGAARD ApS - NIC00924.pdf';//ok
        //$filename = 'BERENDSOHN AG - 983799620MVA_CI_0000041790_END.pdf';//ok
        //$filename = 'BESSIE - Samlefaktura 22-03-24.pdf';//ok
        //$filename = 'BIANCO FOOTWEAR - commercial_invoice_50297.pdf';//ok
        //$filename = 'BLACK COLOUR - NO-337.pdf';//ok
        //$filename = 'BODO MOLLER CHEMIE - DK618744.pdf';//ok
        //$filename = 'BYIC - consolidated-invoice-9895-2024-03-22-14-01-11.pdf';//ok
        //$filename = 'CM DISTRIBUTION DENMARK - Invoice 100677.pdf';//ok
        //$filename = 'COMMITTEE - commercial_invoice_1311.pdf';//ok
        //$filename = 'GUARDIAN PROTECTION PRODUCTS - 1448.pdf';//ok
        //$filename = 'HALO DESIGN - SAMLEFAKTURA  NORGE 259.pdf';//ok
        //$filename = 'HORN BORDPLADER - 2024-02-01 Proformafaktura 6205.pdf';//ok
        //$filename = 'IMERCO - Samlefaktura NO 11-03-24 SFB8005619.pdf';//ok
        //$filename = 'JUST SUPREME - Economic_ConsolidatedInvoice_2024-03-21_2024-03-22.pdf';//ok
        //$filename = 'LYNGSOE RAINWEAR - NOS-003113 SHIPMENT - SF-1300197.pdf';//ok
        //$filename = 'MILLARCO - Millarco_PROFORMA_faktura_NOPF52071.pdf';//ok
        //$filename = 'NORR AS - commercial_invoice_531.pdf';//ok
        //$filename = 'NOSCOMED - 14948FakturaNOSAM2925_250324_094043.pdf';//ok
        //$filename = 'OUR UNITS - 220324.pdf';//ok
        //$filename = 'PANDORA KITCHEN LIVING - Proforma 5 07-03-2024.pdf';//ok
        //$filename = 'QNUZ - commercial_invoice_334.pdf';//ok
        //$filename = 'REX HOLM (ID) - Proformafaktura PROF02421.pdf';//ok
        //$filename = 'RIEKER - 980827682MVA_CI_24802686-24803079_END.pdf';//ok
        //$filename = 'SEBRA INTERIOR - Samlefaktura 11231.pdf';//ok
        //$filename = 'SPORTS GROUP DENMARK - commercial_invoice_6751.pdf';//ok
        //$filename = 'VILLY JENSEN - Report50022.pdf';//ok

        //Non-OCR Pdf
        $flepath .= 'No-OCR/';

        //$filename = 'AUBO - ESCANI1680187f26c10c4-8d15-4da6-a59e-7a267e6d42a2.pdf';//ok
        //$filename = 'STOF - Samlefaktura 638 Norge.pdf';//ok
        $filename = 'VERNON SPORT (COMMERCIAL INVOICE ON PAGE 3) - EX0414 -doc56197220240206144006.pdf';//ok

        $file = public_path($flepath . $filename);       
        $x = $commercialInvoicesClass->extractFromPdfText('ci', $file);
        dd($x);   
    }  

    public function sendreminderemail()
    { 
        try 
        {                 
            //$result = $this->commonClass->scheduleReminderEmail($this->authUser);    

            //dd($result);      
        }
        catch (\Exception $e) 
        {
            return  $e->getMessage();
        }
    }  

    public function compliancesread()
    { 
        try 
        {                 
            $result = $this->commonClass->readComplianceFile('');    

            dd($result);      
        }
        catch (\Exception $e) 
        {
            return  $e->getMessage();
        }
    }  

    public function excutescript()
    { 
        try 
        {                 
            $result = $this->commonClass->executeScript();    

            dd($result);      
        }
        catch (\Exception $e) 
        {
            return  $e->getMessage();
        }
    } 

    public function checkvatnumbers()
    { 
        try 
        {        
            $chunkSize = 100;
            $maxRow = 250;                     
            $result = $this->hmrcApiClass->checkVatNumberByBatch($chunkSize, $maxRow);

            dd($result);      
        }
        catch (\Exception $e) 
        {
            return  $e->getMessage();
        }
    }  

    public function checksinglevatnumber()
    { 
        try 
        {                                    
            $result = $this->hmrcApiClass->checkVATNumber();

            dd($result);      
        }
        catch (\Exception $e) 
        {
            return  $e->getMessage();
        }
    }

    public function recheckvatnumbers()
    { 
        try 
        {     
            $startRow = 1;    //next 102649  109142
            $result = $this->hmrcApiClass->recheckVATNumbers($startRow);//true

            dd($result);      
        }
        catch (\Exception $e) 
        {
            return  $e->getMessage();
        }
    }  

    public function emailforcompany()
    { 
        try 
        {  
            /* -- LIST -- */
            $email_lists = $this->emailBoxApiClass->getEmailLists();
            /* --end LIST -- */            

            /* -- ADD -- */
            /* -- GET VAT REG. MAIN -- */
            $vatregmains = $this->commonClass->getVatRegMainLazy();
            /* --end GET VAT REG. MAIN -- */

            $email_created = [];
            foreach($vatregmains as $key => $vatregmain)
            {
                $country = strtolower($vatregmain->country);

                $client = $vatregmain->client;                
                $client_name = str_replace(' ', '', $this->commonClass->replaceSpecialCharForFolderName(strtolower($client->client_name)));

                $create_email = $country . '.' . $client_name . '@intravat.cloud';
                $password = '12345678';

                $email_exist = array_values(array_filter($email_lists, function ($email) use($create_email) {                    
                    return $create_email == $email;
                }));

                if(count($email_exist) == 0)
                {
                    $result = $this->emailBoxApiClass->createEmailForCompany($create_email, $password);                   
                    $email_created[] = $create_email;
                }
            }            
            /* --end ADD -- */

            //$result = $this->emailBoxApiClass->getEmailLists();
            //$result = $this->emailBoxApiClass->createEmailForCompany($email, $password);
            //$result = $this->emailBoxApiClass->deleteEmailForCompany($email);
            if(count($email_created) > 0)
                dd($email_created);
            else
                dd("Emails already exists for the list");
                       
        }
        catch (\Exception $e) 
        {
            return  $e->getMessage();
        }
    } 

    public function reademailsrawphp()
    { 
        try 
        { 
            $storage_path = storage_path('app/public/mailbox/');

            /* -- TEST CONNECTION -- */
            $hostname = '{box.intravat.cloud:993/imap/ssl}INBOX';
            $username = 'info@intravat.cloud';
            $password = 'Urges905@';

            // Try to connect
            $inbox = imap_open($hostname, $username, $password);

            if ($inbox) {
                echo "Connection successful!";

                // Search for all messages
                $emails = imap_search($inbox, 'ALL');

                if ($emails === false) {
                    die('Failed to search for emails: ' . imap_last_error());
                }

                // Sort emails by date
                rsort($emails);

                foreach ($emails as $key=>$email_number) {
                    if($key == 0)
                    {
                        // Fetch the email overview
                        $overview = imap_fetch_overview($inbox, $email_number, 0);
                        $message = $overview[0];

                        echo "Subject: " . htmlspecialchars($message->subject) . "<br>";
                        echo "From: " . htmlspecialchars($message->from) . "<br>";
                        echo "Date: " . htmlspecialchars($message->date) . "<br>";

                        // Fetch the email body
                        $structure = imap_fetchstructure($inbox, $email_number);

                        if (isset($structure->parts) && count($structure->parts)) {
                            foreach ($structure->parts as $part_number => $part) {
                                $body = imap_fetchbody($inbox, $email_number, $part_number + 1);
                                if (strtoupper($part->subtype) === 'PLAIN') {
                                    echo "Body: " . nl2br(htmlspecialchars($body)) . "<br>";
                                }

                                // Check if the part is an attachment
                                if (isset($part->disposition) && strtoupper($part->disposition) == 'ATTACHMENT') {
                                   
                                    $attachment_name = $part->dparameters[0]->value;
                                    $attachment_body = imap_fetchbody($inbox, $email_number, $part_number + 1);

                                    // Decode base64 or quoted-printable attachments
                                    if ($part->encoding == 3) {
                                        $attachment_body = base64_decode($attachment_body);
                                    } elseif ($part->encoding == 4) {
                                        $attachment_body = quoted_printable_decode($attachment_body);
                                    }

                                    // Save the attachment to a file
                                    file_put_contents($storage_path . $attachment_name, $attachment_body);

                                    echo "Saved attachment: " . htmlspecialchars($attachment_name) . "<br>";
                                }
                            }
                        } else {
                            // If there's no structure, fetch the body directly
                            $body = imap_fetchbody($inbox, $email_number, 1);
                            echo "Body: " . nl2br(htmlspecialchars($body)) . "<br>";
                        }

                        echo "<hr>";  
                    }                  
                }

                // Close the connection
                imap_close($inbox);
            } else {
                echo "Connection failed: " . imap_last_error();
            }
            /* --end TEST CONNECTION -- */
        }
        catch (\Exception $e) 
        {
            dd($e);
            return  $e->getMessage();
        }
    }

    public function reademails()
    { 
        try 
        { 
            //$email_lists[0] = 'ch.minimumas@intravat.cloud';
            //$system = $this->emailBoxApiClass->readEmailForCompany($this->authUser, $email_lists);

            $email_read = $this->emailBoxApiClass->readEmailForCompany($this->authUser);
            dd("done");
        }
        catch (\Exception $e) 
        {   
            dd($e);
            return  $e->getMessage();
        }
    }
   
    public function cvrapi()
    { 
        try 
        {                             
            /* -- GET COMPLIANCE USERS CVR NUMBER -- */         
            //$result = $this->cvrApiClass->getCVRCompany('41955619');

            $clients = Client::where('status', 1)
                        //->where('id', 125)
                        ->get();
            foreach($clients as $key=>$client)
            {                         
                $refresh = ['client_id' => $client->id];
                $result = $this->cvrApiClass->getCVRCompany($client->vatno, null, $refresh);

                if($result)
                    echo "CVR details updated successfully for " . $client->client_name . "<br>";
            }
            /* --end GET COMPLIANCE USERS CVR NUMBER-- */  
        }
        catch (\Exception $e) 
        {dd($e, $client);
            return  $e->getMessage();
        }
    }

    public function generateinvoice()
    { 
        try 
        {                             
            /* -- GENERATE INVOICE -- */         
            $result = $this->invoiceClass->generateInvoice();
            /* --end GENERATE INVOICE -- */         
        }
        catch (\Exception $e) 
        {
            return  $e->getMessage();
        }
    }  

    public function readinvoice()
    { 
        try 
        {                             
            /* -- GENERATE INVOICE -- */  
            $file_name = 'SI_5790000252381_10958642_1_20240902172712541.xml';
            //$file_name = 'testinvoice.xml';
            $result = $this->invoiceClass->readInvoice($file_name);
            /* --end GENERATE INVOICE -- */ 

            dd($result);
        }
        catch (\Exception $e) 
        {
            return  $e->getMessage();
        }
    } 

    public function openai()
    { 
        try 
        {                             
            $result = OpenAI::chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => 'Hello!'],
                ],
            ]);

            echo $result->choices[0]->message->content; // Hello! How can I assist you today?


        }
        catch (\Exception $e) 
        {
            return  $e->getMessage();
        }
    } 

    public function scanreceipt()
    { 
        try 
        {    
            /*                                 
            //$storage_path = storage_path('app/public/invoices/');
            //$file_name = 'Faktura_nr_10921824.pdf';
            //$file_name = '300003.pdf';

            $storage_path = storage_path('app/public/invoices/');
            $file_name = 'commercial_invoice_50376.pdf';

            $textPdf = Text::pdf(file_get_contents($storage_path.$file_name));

            //dd($textPdf->toString());
            //ReceiptScanner::scan($textPdf);

            //Default - gpt-3.5-turbo-instruct
            //$result = ReceiptScanner::scan(text: $textPdf->toString(), template: 'invoice', asArray: true);
            $result = ReceiptScanner::scan(text: $textPdf->toString(), template: 'invoice-full', asArray: true);

            //gpt-4
            //$result = ReceiptScanner::scan(text: $textPdf->toString(), model: Model::GPT4, template: 'invoice', asArray: true);
            */

            $result = $this->commonClass->extractTextViaOpenAi('ci');

            dd($result);
        }
        catch (\Exception $e) 
        {dd($e);
            return  $e->getMessage();
        }
    }

    public function azureDb()
    {
        //Auzure DB connection

        // Querying Azure SQL Database
        try {
            // Test if the Azure SQL connection is working
            //$result = DB::connection('azure_sql')->select("SELECT 1");
            // Field3 : Freight 
            // Field7 : Currency
            // Field8 : Document Status
            // Field10 : Swiss Declaration Sub Type
            // Field12 : VAT
            // Field13 : Country
            // Field17 : Commercial Invoice No
            // Field19 : Document Type
            // Field20 : Client Name
            // Field25 : Net Amount
            // Field26 : Credit Note
            // Field27 : Client Number
            // Field28 : ProcessID
            // Field29 : Total Amount
            // $result = DB::connection('azure_sql')->select("SELECT Field3 AS freight, Field7 AS currency, Field8 AS document_status, Field10 AS swiss_declaration_sub_type, Field12 AS vat, Field13 AS country, Field17 AS commercial_invoice_no, Field19 AS document_type, Field20 AS client_name, Field25 AS net_amount, Field26 AS credit_note, Field27 AS client_number, Field28 AS process_id, Field29 AS total_amount FROM ssFields WHERE Field29 IS NOT NULL");

            $query = "SELECT " .
                        "f.Field3 AS freight, " .
                        "f.Field7 AS currency, " .
                        "f.Field8 AS document_status, " .
                        "f.Field10 AS swiss_declaration_sub_type, " .
                        "f.Field12 AS vat, " .
                        "f.Field13 AS country, " .
                        "f.Field17 AS commercial_invoice_no, " .
                        "f.Field19 AS document_type, " .
                        "f.Field20 AS client_name, " .
                        "f.Field25 AS net_amount, " .
                        "f.Field26 AS credit_note, " .
                        "f.Field27 AS client_number, " .
                        "f.Field28 AS process_id, " .
                        "f.Field29 AS total_amount, " .
                        "m.Field15 As sales_invoice_reference_numbers " .
                        "FROM ssFields f " .
                        "LEFT JOIN ssMVFields m ON f.DocID = m.DocID " .
                        //"WHERE f.Field29 IS NOT NULL";
                        //"WHERE f.Field27 = '925716065'";
                        "WHERE f.Field27 IS NOT NULL";

            $result = DB::connection('azure_sql')->select($query);            
        dd($result);
            // If no error is thrown, the connection is established
            dd("Connection to Azure SQL Database is successful!");
        } catch (\Exception $e) {
            // Catch any connection errors
            dd("Could not connect to Azure SQL Database: " . $e->getMessage());
        }
        dd("done");
    }

    public function importReXml()
    {               
        try {
            $xmlString = file_get_contents(public_path('NF25780.xml'));
            
            $xmlNamespaces = simplexml_load_string($xmlString)->getDocNamespaces(true);
            $namespaces = array_values(array_filter(array_keys($xmlNamespaces), function ($k) {
                return !empty($k);
            })); 
            $namespaces = array_map(function ($ns) {
                return "$ns:";
            }, $namespaces);    
              
            $xmlObject = simplexml_load_string(str_replace($namespaces, '', $xmlString)); 
            
            $json = json_encode($xmlObject);            
            $phpArray = json_decode($json, true);         

            return $phpArray;
        } 
        catch (\Exception $e) {
            dd($e);  
        }       
    }

    public function skippedCasDDaMonths()
    {               
        try {
            /*CAS/DDA Month*/
            $vatregsmainlist = $this->commonClass->getVATRegMainList();

            foreach($vatregsmainlist as $key=>$vatregsmain)
            {                
                if($vatregsmain->cash_acc_stmt || $vatregsmain->duty_defer_acc)
                {
                    $vatregmainmonths = VATRegistrationMainCasDdaMonths::where('vat_reg_main_id', $vatregsmain->id)
                                            ->orderBy('id', 'DESC')            
                                            ->first(); 

                    if($vatregmainmonths)
                    {
                        
                    } 
                    else
                    {
                        $start_month = Carbon::parse($vatregsmain->service_start);
                        $end_month = Carbon::now();

                        $interval = date_diff($start_month, $end_month);

                        $y_to_m = ($interval->y == 0) ? 0 : (12 * $interval->y);

                        $end = $y_to_m + $interval->m;
                                             
                        for ($i = 0; $i <= $end; $i++)  
                        {
                            $next_month = Carbon::parse($vatregsmain->service_start)->addMonth($i)->format('Y-m-d');

                            $month_year = Carbon::parse($next_month)->format('m-Y');
                            
                            $_insert = 1;
                            foreach($vatregsmain->vatreg as $vatreg)
                            {
                                $vat_reg_id = $vatreg->id;                                

                                $disregardedtasks = [];
                                if($vatregsmain->cash_acc_stmt)
                                    $disregardedtasks = CashAccountStatement::where('vat_reg_id', $vat_reg_id)
                                                            ->where('month_year', $month_year)
                                                            ->where('file_id', NULL)                          
                                                            ->first(); 
                                else if($vatregsmain->duty_defer_acc)
                                    $disregardedtasks = DutyDefermentAccount::where('vat_reg_id', $vat_reg_id)
                                                            ->where('month_year', $month_year)
                                                            ->where('file_id', NULL)                          
                                                            ->first();            

                                if($disregardedtasks)
                                {
                                    echo $vatregsmain->id . ' ==> ' . $disregardedtasks->vat_reg_id . ' -- ' . $disregardedtasks->month_year . '<br>';                               
                                    $_insert = 0;     
                                }                                                              
                            }

                            if($_insert)
                            {
                                if($next_month <= Carbon::now()->format('Y-m-d'))
                                //if(($next_month <= Carbon::now()->format('Y-m-d')) && ($next_month == '11-2024' || $next_month == '12-2024'))
                                {
                                    $vatRegMainCasDdaMonths = VATRegistrationMainCasDdaMonths::updateOrCreate(    
                                      [
                                        'vat_reg_main_id' => $vatregsmain->id,                
                                        'month_year' => (Carbon::parse($next_month)->format('m-Y'))
                                      ],         
                                      [                
                                        'vat_reg_main_id' => $vatregsmain->id,                
                                        'month_year' => (Carbon::parse($next_month)->format('m-Y'))                    
                                      ]
                                    );
                                }
                            }
                        }
                    }     
                }
            }
            /*end CAS/DDA Month*/
        } 
        catch (\Exception $e) {
            dd($e);  
        }       
    }    

    public function fetchImportReconciliationDatas()
    {               
        try 
        {    
            //LIVE            
            //NO
            //133, 75, 71, 72, 73, 140, 240, 76, 77, 171, 81, 180, 143, 214, 86, 88, 87, 146, 90, 94, 95, 150, 109, 155, 117, 156, 157, 118, 119, 122, 125, 196, 229, 127, 197, 201, 161, 162, 163, 132

            //GB, CH
            //12, 249, 83, 6, 173, 28, 4, 217, 120, 67, 37, 38, 68, 223, 69

            //FTP 
            //125, 117, 88, 133, 140, 12, 

            //FTP -AUBO
            //75 - 802, 1081, 1219, 1488, 1673

            //STAGING            
            //NO
            //59, 63, 65, 79, 83, 85, 86, 87, 88, 89, 90, 91, 92, 93     

            //$authUser = $this->commonClass->getAuthUser(1); 
            $system = $this->commonClass->getSystemInfoLazy(); 
            $systemapi = $system->systemapi->first();

            $vatregs = VATRegistration::with(['vatregmain','client','importreconciliationfiles'
                                      // ,'importreconciliationcominvoices' => function($query) {
                                      //     //$query->whereNotNull('saved_at')->orderBy('saved_at', 'desc')->limit(1);
                                      //   $query->where('data_from', '!=', 'ivf')
                                      //       ->where('data_from', '!=', 'ftp')
                                      //       ->orderBy('saved_at', 'desc')                                           
                                      //       ->get();
                                      // }
                                    ])
                                    ->withCount('importreconciliationcominvoices')
                                    ->withCount('importreconciliationsalesinvoices')                                    
                                    ->whereHas('vatregmain', function ($subquery) {                                        
                                        $subquery->where('product_type', 2)
                                            ->orWhere('product_type', 3)
                                            ->orWhere('product_type', 5); 
                                    })  
                                    ->whereHas('client', function ($subquery) {                                        
                                        $subquery->whereIn('id', [85]); //84 -AUBO; 85 -BECK
                                    })
                                    // ->whereHas('client', function ($subquery) {                                        
                                    //     // $subquery->where('client_name', 'LIKE', '%aubo%');
                                    //     // $subquery->orWhere('client_name', 'LIKE', '%beck%');
                                    //     // $subquery->orWhere('client_name', 'LIKE', '%geisler%');
                                    //     // $subquery->orWhere('client_name', 'LIKE', '%noscomed%');
                                    //     // $subquery->orWhere('client_name', 'LIKE', '%rexholm%');
                                    //     // $subquery->orWhere('client_name', 'LIKE', '%villy%');

                                    //     $subquery->where('client_name', 'LIKE', '%noscomed%');
                                    //     $subquery->orWhere('client_name', 'LIKE', '%rexholm%');
                                    // })      
                                    //->where('id', 454)                             
                                    ->get();
 
            // $noInvoices = $vatregs->filter(function($vatreg, $key){
            //     return ($vatreg->importreconciliationcominvoices_count == 0 || $vatreg->importreconciliationcominvoices_count == 0);
            // });

            $unique_countries = [];

            $noInvoices = $vatregs; 
            foreach($noInvoices as $key => $noInvoice)
            {       
                $client_name = $noInvoice->client->client_name;

                //$refresh = true;
                $full_refresh = true;                
                //$from = 'cron';
                              
                // //From AZURE
                // $data = $this->commonClass->loadImportReconciliationDatasFromAzureDb($this->authUser, $noInvoice, 'azure', $full_refresh); 

                // echo (($data) ? (($data > 0) ? '<span style="background-color: green;">' : '<del>') : '<del>') . "Data's fetched from AZURE - " . $client_name . " - " . $noInvoice->country . 
                //         " - " . $noInvoice->service_start . 
                //         " - " . $noInvoice->id . 
                //         " - " . (($data) ? 'Data found' : 'NOT FOUND') .
                //         (($data) ? (($data > 0) ? '</span>' : '</del>') : '</del>') .
                //         "<br>";

                //if($key == 0)
                if(!in_array($noInvoice->country, $unique_countries, true))
                {
                    if (stripos(strtolower($client_name), "aubo") !== false || stripos(strtolower($client_name), "beck") !== false ||
                    stripos(strtolower($client_name), "geisler") !== false || stripos(strtolower($client_name), "noscomed") !== false ||
                    stripos(strtolower($client_name), "rexholm") !== false || stripos(strtolower($client_name), "villy") !== false
                    ) 
                    {      
                        $which_folder = 'archive';

                        //From FTP
                        // $ftpdata = $this->commonClass->loadImportReconciliationDatasFromFtp($this->authUser, $noInvoice, $systemapi, $refresh, 'ftp', $include_archive);

                        $ftpClass = new FtpClass();
                        /* -- READ XML FILE FROM FTP -- */
                        $ftpdata = $ftpClass->getImportReconciliationFilesFromFtp($noInvoice, $this->authUser, $which_folder); 
                        /* --end READ XML FILE FROM FTP -- */
                        
                        /* -- READ XML FILE FROM E-FACTO -- */
                        if (stripos(strtolower($client_name), "noscomed") !== false ||
                            stripos(strtolower($client_name), "rexholm") !== false)
                        { 
                          $ftpdata = $ftpClass->getImportReconciliationFilesFromFtp($noInvoice, $this->authUser, $which_folder, true);
                          //$ftpdata = array_merge($ftpdata, $efacto_ftp_data);                          
                        }
                        /* --end READ XML FILE FROM E-FACTO -- */
                      
                        if(!in_array($noInvoice->country, $unique_countries, true))                
                            array_push($unique_countries, $noInvoice->country);

                        echo (($ftpdata) ? '<span style="background-color: lightgreen;">' : '<del>') . "Data's fetched from FTP - " . $noInvoice->client->client_name . " - " . $noInvoice->country . 
                                " - " . $noInvoice->service_start . 
                                " - " . $noInvoice->id . 
                                " - " . (($ftpdata) ? 'Data found' : 'NOT FOUND') .
                                (($ftpdata) ? '</span>' : '</del>') .                              
                                "<br><br>";
                    }
                } //read all at a time                 
            }
        } 
        catch (\Exception $e) {
            dd($e);  
        }       
    } 

    public function efactoPayload()
    {               
        try 
        {                
            $efacto = $this->eFactoClass->getAllInvoicesLazy();

            dd($efacto);
        } 
        catch (\Exception $e) {
            dd($e);  
        }       
    } 

    public function readCargoEmails()
    {               
        try 
        {                
            //$cargomailbox = $this->emailBoxApiClass->readEmailForCargoDeclarationFiles($this->authUser);
            $cargomailbox = $this->cargoDeclarationClass->readCargoDeclarationFile();
/*
            $storage_path = storage_path('app/public/mailbox/cargodeclarationfiles/');            
            //$sub_folder = 'AUBO/';           
            //$file_name = '24-59781_20241204.pdf';
           //dd(count(scandir($storage_path)));
            //$attachmentName = $file_name;
     
            // Get all files in the folder and sort by name
            $files = scandir($storage_path);
            // Remove '.' and '..' from the result
            $files = array_diff($files, ['.', '..']);
            // Sort files by name
            sort($files);
            // Get the first file after sorting
            $firstFile = reset($files); // This gets the first element in the sorted array
//dd($firstFile);
            // dd($this->commonClass->extractTextViaOpenAi('pdf', $storage_path.'../DONE/04946960175_4410012500049567.pdf'))      ;
            //Read and assign to relevant folder
            //$readcargofiles = $this->cargoDeclarationClass->readCargoDeclarationFile($attachmentName, $sub_folder);
            $readcargofiles = $this->cargoDeclarationClass->readCargoDeclarationFile('514A94SRQPY_4420012500028888.pdf', NULL, true);//('0858359.pdf');            
            //$readcargofiles = $this->cargoDeclarationClass->readCargoDeclarationFile($firstFile);

dd($firstFile, $readcargofiles);
            if(count($readcargofiles) > 0)
            {
                $vatreg = $readcargofiles['match_vatreg'];
               
                // Store it in One-drive and Mailbox table
                $request_pass = [
                    'file' => $attachmentBody,                       
                    'file_name' => $attachmentName,
                    'email_datetime' => Carbon::parse($message->getDate()->toDate())->format('Y-m-d H:i:s'),
                    'email_id' => $message->getFrom()[0]->mail,    
                    'email_subject' => $decoded_subject,//htmlspecialchars($message->getSubject()),    
                    'file_type' => 'cargo_mailbox',
                    'file_type_title' => 'Cargo MailBox',
                    'expo_no' => $readcargofiles['expo_no'],
                    'lope_no' => $readcargofiles['lope_no'],
                    'cargo_date' => $readcargofiles['cargo_date'],
                    'com_invoice_nos' => $readcargofiles['com_invoice_nos']
                ];
                $uploadedfile = $this->apiClass->uploadFileToOneDriveLazy($request_pass, $vatreg, $authUser, $systemapi, 'cargo_mailbox');                           
                // Delete it from storage                                    
                //Storage::disk('public')->delete('mailbox/cargodeclarationfiles/'. $attachmentName);

                echo "Saved attachment: " . htmlspecialchars($attachmentName) . "<br>";                 
            }
            else                            
                echo '<span style="background-color: red;">NOT STORED - NO VATREG. - Attachment: '. $attachmentName.'</span><br />';
*/
            dd($cargomailbox);
        } 
        catch (\Exception $e) {
            dd($e);  
        }       
    } 

    public function fetchIvfDatas()
    {               
        try 
        {                           
            $system = $this->commonClass->getSystemInfoLazy(); 
            $systemapi = $system->systemapi->first();

            //Cargo Files
            // $vatregs = VATRegistration::with(['vatregmain','client'])
            //                         ->withCount('importreconciliationcominvoices')                                   
            //                         ->withCount('importvatfiles') 
            //                         ->whereHas('vatregmain', function ($subquery) {                                        
            //                             $subquery->where('product_type', 2)
            //                                 ->orWhere('product_type', 3)
            //                                 ->orWhere('product_type', 5); 
            //                         })  
            //                         ->whereHas('client', function ($subquery) {                                        
            //                             $subquery->whereIn('id', [119]); //84 -AUBO; 85 -BECK
            //                             //$subquery->whereNotIn('id', [59, 63]); //84 -AUBO; 85 -BECK
            //                         })
            //                         // ->whereHas('client', function ($subquery) {                                        
            //                         //     $subquery->where('client_name', 'LIKE', '%aubo%');
            //                         //     $subquery->orWhere('client_name', 'LIKE', '%beck%');
            //                         //     $subquery->orWhere('client_name', 'LIKE', '%geisler%');
            //                         //     $subquery->orWhere('client_name', 'LIKE', '%noscomed%');
            //                         //     $subquery->orWhere('client_name', 'LIKE', '%rexholm%');
            //                         //     $subquery->orWhere('client_name', 'LIKE', '%villy%');
            //                         // }) 
            //                         //->where('id', 336)
            //                         ->get();
            //Cargo Files

            //Reload IVF xml files
            $vatregs = VATRegistration::with(['vatregmain','client', 'importvatfiles'])    
                            ->withCount('importvatfiles')                                
                            ->whereHas('client', function ($subquery) {                                        
                                $subquery->whereIn('id', [119]);
                            })
                            ->where('country', 'NO')
                            ->where('service_start', '2025-11-01')
                            //->where('id', 596)                                   
                            ->get();
            //Reload IVF xml files
 
            $noInvoices = $vatregs->filter(function($vatreg, $key){
                //return ($vatreg->importreconciliationcominvoices_count == 0 && $vatreg->importvatfiles_count > 0);
                return ($vatreg->importvatfiles_count > 0);//Reload IVF xml files
            });
           
            foreach($noInvoices as $key => $noInvoice)
            {               
                $vat_reg_id = $noInvoice->id;
                $importvatfiles = $noInvoice->importvatfiles;
                foreach($importvatfiles as $importvatfile)       
                {       
                    if($importvatfile->file_type == 'xml')
                    {
                        if($importvatfile->file_id != NULL)
                        {                     
                            $importvatfileName = $this->apiClass->loadFromOneDriveLazy($importvatfile, $systemapi);              
                            if(isset($importvatfileName->error))   
                            {
                                echo $noInvoice->client->client_name . " ERROR in Reloading IVF datas - " . $vat_reg_id . ' - ' . $import_vat_id . '<br>';
                            } 
                            else
                            {    
                                $xmlvalue = $this->apiClass->xmlExtract($importvatfileName['download_url'], $importvatfile->month_year);

                                $import_vat_id = $importvatfile->id;

                                if($importvatfile->statistical_number == $xmlvalue['statvalue'])
                                {
                                    echo $noInvoice->client->client_name . " - SAME IVF datas - " . $importvatfile->statistical_number . ' - ' . $xmlvalue['statvalue'] . '<br>';
                                }
                                else
                                {
                                    //Reload IVF xml files
                                    $importvatfile->fee_number = $xmlvalue['fee'];                                
                                    $importvatfile->e_fee_number = $xmlvalue['fee_ex'];
                                    $importvatfile->statistical_number = $xmlvalue['statvalue'];
                                    $importvatfile->e_statistical_number = $xmlvalue['statvalue_ex'];
                                    $importvatfile->adjustment_no = $xmlvalue['adjustment'];
                                    $importvatfile->invoice_total = $xmlvalue['invoice_total'];

                                    $importvatfile->box_85 = $xmlvalue['box_85'];

                                    //$importvatfile->save();

                                    echo $noInvoice->client->client_name . " - Reloaded IVF datas - " . $vat_reg_id . ' - ' . $import_vat_id . '<br>';
                                }
                                //Reload IVF xml files

                                //Cargo Files
                                //$importvatfile->xml = $xmlvalue;
                                // $expedition_list = $xmlvalue['expedition_list'];
                                // foreach($expedition_list as $key => $expedition)
                                // {
                                //     $ivf_com_invoice_nos = '';
                                //     $ivf_com_invoice_dates = '';
                                //     foreach($expedition['com_invoices'] as $com_invoice)
                                //     {                                   
                                //         $commercial_invoice_no = isset($com_invoice['com_invoice_no']) ? $com_invoice['com_invoice_no'] : '';
                                //         $com_invoice_date = isset($com_invoice['com_invoice_date']) ? $com_invoice['com_invoice_date'] : '';
                                        
                                //         if($ivf_com_invoice_nos == '')
                                //         {
                                //             $ivf_com_invoice_nos = $commercial_invoice_no;
                                //             $ivf_com_invoice_dates = $com_invoice_date;
                                //         }
                                //         else
                                //         {
                                //             $ivf_com_invoice_nos .= ',' . $commercial_invoice_no;
                                //             $ivf_com_invoice_dates .= ',' . $com_invoice_date;
                                //         }
                                        
                                //         $com_invoice_net_amount = isset($expedition['com_invoice_net_amount']) ? $expedition['com_invoice_net_amount'] : null;
                                //         $com_invoice_omr_kurs = isset($expedition['com_invoice_omr_kurs']) ? $expedition['com_invoice_omr_kurs'] : null;
                                //         $com_invoice_currency_code = isset($expedition['com_invoice_currency_code']) ? $expedition['com_invoice_currency_code'] : 'NOK';

                                //         //INSERT INTO COM. INVOICE TABLE
                                //         $already_exists_cominvoice = ImportReconciliationComInvoices::where('vat_reg_id', $vat_reg_id)
                                //             ->where('invoice_no', $commercial_invoice_no)
                                //             ->where('lope_no', $expedition['run_no'])
                                //             ->first();

                                //         if($already_exists_cominvoice)
                                //         {                                            
                                //             $already_exists_cominvoice->data_from = 'ivf';
                                //             $already_exists_cominvoice->month_year = $importvatfile->month_year;
                                //             $already_exists_cominvoice->invoice_date = Carbon::parse($com_invoice_date)->format('Y-m-d');
                                //             $already_exists_cominvoice->expo_no = ($already_exists_cominvoice->expo_no == NULL) ? $expedition['expo_no'] : $already_exists_cominvoice->expo_no;
                                //             $already_exists_cominvoice->lope_no = ($already_exists_cominvoice->lope_no == NULL) ? $expedition['run_no'] : $already_exists_cominvoice->lope_no;
                                //             $already_exists_cominvoice->duties = $expedition['duties'];
                                //             $already_exists_cominvoice->adjustment = $expedition['adjustment'];
                                //             $already_exists_cominvoice->statistical_value = $expedition['statistical_value'];
                                //             $already_exists_cominvoice->category_type = $expedition['category_type'];
                                //             $already_exists_cominvoice->category_desc = $expedition['category_desc'];
                                //             $already_exists_cominvoice->ivf_net_amount = $com_invoice_net_amount;
                                //             $already_exists_cominvoice->omr_kurs = $com_invoice_omr_kurs;
                                //             $already_exists_cominvoice->currency_code = $com_invoice_currency_code;
                                //             $already_exists_cominvoice->updated_by = $this->authUser->id;

                                //             $already_exists_cominvoice->save();
                                //         }
                                //         else
                                //         {
                                //             $insert_cominvoice = ImportReconciliationComInvoices::updateOrCreate(
                                //                 [
                                //                     'vat_reg_id' => $vat_reg_id,
                                //                     'invoice_no' => $commercial_invoice_no,
                                //                     'lope_no' => $expedition['run_no']
                                //                 ],
                                //                 [                
                                //                     'vat_reg_id' => $vat_reg_id,

                                //                     'data_from' => 'ivf',
                                //                     'month_year' => $importvatfile->month_year,

                                //                     'invoice_no' => $commercial_invoice_no,                                             
                                //                     'invoice_date' => Carbon::parse($com_invoice_date)->format('Y-m-d'),
                                //                     'expo_no' => $expedition['expo_no'],
                                //                     'lope_no' => $expedition['run_no'],
                                //                     'duties' => $expedition['duties'],
                                //                     'adjustment' => $expedition['adjustment'],
                                //                     'statistical_value' => $expedition['statistical_value'],
                                //                     'category_type' => $expedition['category_type'],
                                //                     'category_desc' => $expedition['category_desc'],
                                //                     'doc_status' => ($commercial_invoice_no) ? 'Validated' : 'Validation',
                                //                     'country' => $noInvoice->country,
                                //                     'currency_code' => $com_invoice_currency_code,
                                //                     'ivf_net_amount' => $com_invoice_net_amount,
                                //                     'omr_kurs' => $com_invoice_omr_kurs,
                                //                     'created_by' => $this->authUser->id
                                //                 ]
                                //             );
                                //         }                                        
                                //     } //for commercial_invoice_no

                                //     // $already_exists_cargo_file = CargoDeclarationFiles::where('o_file_name', 'LIKE', '%'. $expedition['expo_no'] . $expedition['run_no'] .'%')
                                //     //         ->first();
                                //     $already_exists_cargo_file = CargoDeclarationFiles::where('expo_no', $expedition['expo_no'])
                                //             ->where('run_no', $expedition['run_no'])
                                //             ->first();
                                    
                                //     if($already_exists_cargo_file)
                                //     {
                                //         //$cargo_com_invoice_no = $already_exists_cargo_file->com_invoice_no . ',' . $commercial_invoice_no;

                                //         $already_exists_cargo_file->import_vat_id = $import_vat_id;
                                //         $already_exists_cargo_file->ivf_com_invoice_nos = $ivf_com_invoice_nos;//$cargo_com_invoice_no;
                                //         $already_exists_cargo_file->ivf_com_invoice_dates = $ivf_com_invoice_dates;
                                //         //$already_exists_cargo_file->expo_no = $expedition['expo_no'];
                                //         //$already_exists_cargo_file->run_no = $expedition['run_no'];
                                //         //$already_exists_cargo_file->expo_run_no = $expedition['expo_no'] . $expedition['run_no'];
                                //         $already_exists_cargo_file->status = 2;
                                //         $already_exists_cargo_file->updated_by = $this->authUser->user_id;

                                //         $already_exists_cargo_file->save();
                                //     }
                                //     else
                                //     {
                                //         //$cargo_com_invoice_no = $commercial_invoice_no;

                                //         $cargo_file = CargoDeclarationFiles::updateOrCreate(
                                //             [
                                //                 //'o_file_name' => $expedition['expo_no'] . $expedition['run_no'] . '.pdf'
                                //                 'expo_no' => $expedition['expo_no'],
                                //                 'run_no' => $expedition['run_no']
                                //             ],
                                //             [           
                                //                 'import_vat_id' => $import_vat_id,
                                //                 'ivf_com_invoice_nos' => $ivf_com_invoice_nos,//$cargo_com_invoice_no,
                                //                 'ivf_com_invoice_dates' => $ivf_com_invoice_dates,
                                //                 'expo_no' => $expedition['expo_no'],
                                //                 'run_no' => $expedition['run_no'],
                                //                 'expo_run_no' => $expedition['expo_no'] . $expedition['run_no'],
                                //                 'status' => 2,
                                //                 'created_by' => $this->authUser->user_id
                                //             ]
                                //         );
                                //     }
                                    
                                //     echo $noInvoice->client->client_name . " - IVF datas - " . $vat_reg_id . ' - ' . $import_vat_id . '<br>';

                                // } //for Cargo Files
                            }
                        } 
                    }                   
                }
            }
        } 
        catch (\Exception $e) {
            dd($e);  
        }       
    } 

    public function rematchComInvoice()
    {               
        try 
        {   
            $client_id = 89;            
            $rematch = $this->commonClass->rematchComInvoices($client_id, true);
        } 
        catch (\Exception $e) {
            dd($e);  
        }       
    } 

    public function fetchInvoiceNoFromImportReconciliationFiles()
    {               
        try 
        {   
            //FTP 
            //125(REX), 117(NOSCOMED), 88(DFI), 133(VILLY), 140(BECK), 12(BECK-GB), 75(AUBO)

            //vat_reg_main_id - 183, 148, 126, 286, 295, 59, 293

            //$vat_reg_main_id = 80;
         
            $system = $this->commonClass->getSystemInfoLazy(); 
            $systemapi = $system->systemapi->first();

            //$files = $this->commonClass->getImportReconciliationFilesLazy();
 
            // $importreconciliationfiles = $files->filter(function($file, $key){                          
            //     return ($file->vat_reg_id == 802 || $file->vat_reg_id == 1081 || $file->vat_reg_id == 1219
            //              || $file->vat_reg_id == 1488 || $file->vat_reg_id == 1673);           
            // });

            $_with = ['vatreg', 'vatreg.client'];
            $_where = [];      
            $_whereHas = [];

            $_orderBy = [
                'id' => 'DESC'
            ];  
            $_final = 'get';      
            $files = $this->commonClass->getLazy('importreconciliationfiles', $_with, $_where, $_whereHas, $_orderBy, $_final);

            $importreconciliationfiles = $files->filter(function($file, $key){                          
                //return ($file->vat_reg_id == 302);           
                //return ($file->invoice_no == 'NF25820');
                return ($file->vatreg->client_id == 140);                      
            });

            //$importreconciliationfiles = $files;
                            
            foreach ($importreconciliationfiles as $importreconciliationfile)
            {
              $importreconciliationfileid = $importreconciliationfile->id;
              if($importreconciliationfile->file_id)
              {
                $downloadurl = $this->apiClass->loadFromOneDriveLazy($importreconciliationfile, $systemapi);
               
                if(isset($downloadurl->error))
                {
                 
                } /* --end if DOWNLOAD URL ERROR -- */
                else if($downloadurl == null)
                {
                  
                } /* --end else DOWNLOAD URL NULL -- */
                else
                {
                    $vatreg = $this->commonClass->getVatRegLazy($importreconciliationfile->vat_reg_id);
                    $vat_reg_main_id = $vatreg->vat_reg_main_id;

                    $o_filename = $importreconciliationfile->o_file_name;
                    /* -- READ EXCEL FILE -- */                 
                    $read_ftp_data = $this->commonClass->readImportReconciliationFile($downloadurl['download_url'], $vat_reg_main_id, $o_filename, $downloadurl['file_extension'], []); 

                    $invoice_row = $read_ftp_data['invoice_rows'][0];

                    //update ImportReconciliationSalesInvoicesData converted_note                    
                    $ir_file_id = $importreconciliationfile->id;
                    $already_exists_sales_invoice_data = ImportReconciliationSalesInvoicesData::where('ir_file_id', $ir_file_id)
                                                                      ->where('invoice_no', $invoice_row['invoice_no'])
                                                                      ->first();

                    if($already_exists_sales_invoice_data)   
                    {
                        if($already_exists_sales_invoice_data->invoice_date == $invoice_row['invoice_date'])
                        {

                        }
                        else
                        {
                            $already_exists_sales_invoice_data->invoice_date = $invoice_row['invoice_date'];
                            $already_exists_sales_invoice_data->save();
                        }

                        /*
                        if(!$already_exists_sales_invoice_data->converted_note)
                        {
                            $already_exists_sales_invoice_data->converted_note = $invoice_row['note'];
                            $already_exists_sales_invoice_data->save();

                            echo "Updated converted_note for invoice no: " . $invoice_row['invoice_no']
                                 . "<br>"; 
                        }
                        */
                    }
                   
                    //update ImportReconciliationFiles month_year and vat_reg_id                    
                    if($read_ftp_data['matched_vatreg'])
                    { 
                        $matched_vatregid = $read_ftp_data['matched_vatreg']->id;
                        $matched_monthyear = $read_ftp_data['month_year'];

                        $previous_vatregid = $importreconciliationfile->vat_reg_id;
                        $previous_monthyear = $importreconciliationfile->month_year;

                        if($importreconciliationfile->invoice_no == $invoice_row['invoice_no'] &&
                            $importreconciliationfile->o_file_name == $invoice_row['o_filename']
                        )                                           
                        {
                            if($matched_vatregid == $previous_vatregid &&
                                $matched_monthyear == $previous_monthyear
                            ) 
                            {

                            } 
                            else
                            {
                                $importreconciliationfile->vat_reg_id = $matched_vatregid;
                                $importreconciliationfile->month_year = $matched_monthyear;

                                $importreconciliationfile->save();



                                echo "Updated vat_reg_id from: " . $previous_vatregid . ' - - ' . $matched_vatregid
                                     . " and month_year from " . $previous_monthyear . ' to ' . $matched_monthyear
                                     . "<br>"; 
                            }
                            /*
                            //insert into Sales Invoice Data table                                                         
                              $ir_file_id = $importreconciliationfile->id;

                              $already_exists_sales_invoice_data = ImportReconciliationSalesInvoicesData::where('ir_file_id', $ir_file_id)
                                                                      ->where('invoice_no', $invoice_row['invoice_no'])
                                                                      ->first();

                              if($already_exists_sales_invoice_data)              
                                $sales_invoice_data_id = $already_exists_sales_invoice_data->id;
                                           
                              $insert_salesinvoicedata = ImportReconciliationSalesInvoicesData::updateOrCreate(
                                [
                                  'ir_file_id' => $ir_file_id,
                                  'invoice_no' => $invoice_row['invoice_no']
                                ],
                                [    
                                  'ir_file_id' => $ir_file_id,
                                  'invoice_no' => $invoice_row['invoice_no'],
                                  'invoice_date' => $invoice_row['invoice_date'],
                                
                                  'currency_code' => $invoice_row['invoice_currency'],
                                 
                                  'buyer_name' => $invoice_row['account_name'],
                                  'buyer_street' => $invoice_row['client_street'],
                                  'buyer_houseno' => $invoice_row['client_houseno'],
                                  'buyer_city' => $invoice_row['client_city'],
                                  'buyer_postcode' => $invoice_row['client_postcode'],
                                  'buyer_countrycode' => $invoice_row['client_countrycode'],
                                  'buyer_vatno' => $invoice_row['vat_no'],
                                  'buyer_contact_name' => $invoice_row['account_name'],
                                  
                                  'tax_total_amount' => $invoice_row['invoice_vat_amount'],
                                  'tax_total_amount_currency_code' => $invoice_row['invoice_currency'],
                                  'tax_total_net_amount' => $invoice_row['invoice_net_amount'],
                                  'tax_total_net_amount_currency_code' => $invoice_row['invoice_currency'],
                                  'tax_total_percent' => ($invoice_row['invoice_vat_amount']/$invoice_row['invoice_net_amount']) * 100,
                                  
                                  'created_by' => isset($sales_invoice_data_id) ? $already_exists_sales_invoice_data->created_by : $this->authUser->id,
                                  'updated_by' => $this->authUser->id
                                ]
                              );                                        
                            //END insert into Sales Invoice Data table
                               
                            echo "Updated vat_reg_id from: " . $previous_vatregid . ' - - ' . $matched_vatregid
                                 . " and month_year from " . $previous_monthyear . ' to ' . $matched_monthyear
                                 . "<br>"; 
                            */ 
                        } 

                        // //also update the net/vat amounts via Sales Invoice Data table
                        // $salesinvoice = ImportReconciliationSalesInvoicesData::where('vat_reg_id' $matched_vatregid)
                        //                             ->where('invoice_no' $invoice_row['invoice_no'])
                        //                             ->first();

                        // if($salesinvoice)
                        // {
                        //     $salesinvoice->net_amount = $invoice_row['invoice_net_amount'];
                        //     $salesinvoice->vat_amount = $invoice_row['invoice_vat_amount'];
                        //     $salesinvoice->total_amount = $invoice_row['invoice_total_amount'];
                        //     $salesinvoice->shipping = $invoice_row['invoice_shipping'];
                        //     $salesinvoice->credit_note = $invoice_row['invoice_credit_note'];
                        // }//$salesinvoice
                    }                    

                    //update ImportReconciliationFiles invoice_no
                    /*
                    $matched_vatregid = $read_ftp_data['matched_vatreg']->id;
                  
                    //update ImportReconciliationFiles invoice no.
                    $chk_imr_file = ImportReconciliationFiles::where('vat_reg_id', $matched_vatregid)
                        ->where('o_file_name', $invoice_row['o_filename'])                                     
                        ->first();

                    if($chk_imr_file)                
                    {
                        $chk_imr_file->invoice_no = $invoice_row['invoice_no'];

                        $chk_imr_file->save();

                        echo "Read: " . $o_filename . ' - - ' . $invoice_row['invoice_no'] . "<br>"; 
                    }
                    */ 


                  /* --end READ EXCEL FILE -- */ 
                } /* --end else DOWNLOAD URL NOT NULL -- */
              }
            } //for            
        } 
        catch (\Exception $e) {
            dd($e);  
        }       
    } 

    public function readSwissFiles()
    {               
        try 
        {     
            /*-- For new file --*/           
            $readswissfiles = $this->swissImportReconciliationClass->readSwissFile();            
            dd($readswissfiles);
            /*--end For new file --*/   

            /*-- For already read file - re-read --*/
            // $storage_path = storage_path('app/public/');

            // $system = $this->commonClass->getSystemInfoLazy(); 
            // $systemapi = $system->systemapi->first();

            // $files = $this->commonClass->getImportReconciliationSwissFilesLazy();
            // // $importreconciliationswissfiles = $files->filter(function($file, $key){                          
            // //     return ($file->vat_reg_id == 460);           
            // // });
            // $importreconciliationswissfiles = $files;
            
            // foreach ($importreconciliationswissfiles as $importreconciliationswissfile)
            // {
            //     $importreconciliationfileid = $importreconciliationswissfile->id;
            //     $vat_reg_id = $importreconciliationswissfile->vat_reg_id;

            //     if($importreconciliationswissfile->file_id)
            //     {
            //         $downloadurl = $this->apiClass->loadFromOneDriveLazy($importreconciliationswissfile, $systemapi);

            //         if(isset($downloadurl->error))
            //         {

            //         } /* --end if DOWNLOAD URL ERROR -- */
            //         else if($downloadurl == null)
            //         {

            //         } /* --end else DOWNLOAD URL NULL -- */
            //         else
            //         {                        
            //             $file = file_put_contents($storage_path . 'swisstemp.pdf', $downloadurl['file']);

            //             $readswissfiles = $this->swissImportReconciliationClass->readSwissFile($storage_path . 'swisstemp.pdf');

            //             $commercial_invoice_no = $readswissfiles['com_invoice_no'];
            //             $commercial_invoice_date = $readswissfiles['com_invoice_date'];
            //             $lope_no = $readswissfiles['decalration_no'];
            //             $category_type = $readswissfiles['category_type'];
            //             $currency_code = $readswissfiles['currency_code'];
            //             $net_amount = $readswissfiles['com_invoice_net_amount'];
            //             $vat_amount = $readswissfiles['com_invoice_vat_amount'];
            //             $total_amount = $net_amount + $vat_amount;

                           // $importreconciliationswissfile->invoice_no = $commercial_invoice_no;
                           // $importreconciliationswissfile->save();

            //             $check_already_exist_cominvoice = ImportReconciliationComInvoices::where('vat_reg_id', $vat_reg_id)
            //                                               //->where('invoice_no', $commercial_invoice_no)      
            //                                               ->where('lope_no', $lope_no)         
            //                                               ->first();
                 
            //             if($check_already_exist_cominvoice)              
            //                 $com_invoice_id = $check_already_exist_cominvoice->id;

            //             $insert_cominvoice = ImportReconciliationComInvoices::updateOrCreate(
            //                 [
            //                     'vat_reg_id' => $vat_reg_id,
            //                     //'invoice_no' => $commercial_invoice_no,
            //                     'lope_no' => $lope_no
            //                 ],
            //                 [                
            //                     'vat_reg_id' => $vat_reg_id,

            //                     'data_from' => 'swiss',
            //                     'month_year' => Carbon::parse($commercial_invoice_date)->format('m-Y'),

            //                     'invoice_no' => $commercial_invoice_no,
            //                     'invoice_date' => $commercial_invoice_date,
            //                     'gs_invoice_date' => $commercial_invoice_date,

            //                     'doc_status' => 'Validated',
            //                     'lope_no' => $lope_no,

            //                     'category_type' => $category_type,
            //                     'category_desc' => ($category_type) ? (($category_type == 1) ? 'Declaration' : 'Correction') : NULL,

            //                     'country' => 'CH',
            //                     'currency_code' => $currency_code,
            //                     'statistical_value' => $net_amount,
            //                     'net_amount' => $net_amount,
            //                     'vat_amount' => $vat_amount,
            //                     'total_amount' => $total_amount,

            //                     'created_by' => $this->authUser->id                    
            //                 ]
            //             ); 

            //             echo "Swiss file read for "  . $importreconciliationswissfile->o_file_name . " --- " . $commercial_invoice_no . "<br>";
            //         } /* --end else DOWNLOAD URL NOT NULL -- */
            //     }
            // } //for 
            /*-- end For already read file - re-read --*/  
        } 
        catch (\Exception $e) {
            dd($e);  
        }       
    } 

    public function disregardPeriodFromSpecificDate()
    {               
        try 
        {   
            $vatreturn_date = '2024-12-31';
            $ir_date = '2024-09-30';

            //VAT Returns
            $vatreturn_periods = VATRegistration::select('dv_vat_registration.*')
                                    ->leftJoin('dv_vat_returns', 'dv_vat_registration.id', '=', 'dv_vat_returns.vat_reg_id')
                                    ->where('dv_vat_registration.service_start', '<=', $vatreturn_date)
                                    ->where('dv_vat_registration.status', 2)
                                    ->where('dv_vat_registration.is_disregard', 0)
                                    ->groupBy('dv_vat_registration.id')
                                    ->havingRaw('COUNT(dv_vat_returns.id) = 0')
                                    ->get();

            if(count($vatreturn_periods) == 0)
                echo "No VAT return period to disregard<br>";
            else
            {
                foreach ($vatreturn_periods as $vatreturn_period)
                {
                    $vatreturn_period->is_disregard = 1;
                    $vatreturn_period->save();

                    echo "VAT return period " . $vatreturn_period->service_start . " disregarded -- VAT REG ID: " . $vatreturn_period->id . "<br>";
                } 
            } 

            //Import Reconciliation
            $ir_periods = VATRegistration::select('dv_vat_registration.*')
                                    ->leftJoin('dv_import_reconciliation_com_invoices', 'dv_vat_registration.id', '=', 'dv_import_reconciliation_com_invoices.vat_reg_id')
                                    ->where('dv_vat_registration.service_start', '<=', $ir_date)
                                    ->where('dv_vat_registration.status_import_re', 2)
                                    ->where('dv_vat_registration.is_disregard_import_re', 0)
                                    ->groupBy('dv_vat_registration.id')
                                    ->havingRaw('COUNT(dv_import_reconciliation_com_invoices.id) = 0')
                                    ->get();

            if(count($ir_periods) == 0)
                echo "No Import Reconciliation period to disregard<br>";
            else
            {
                foreach ($ir_periods as $ir_period)
                {
                    $ir_period->is_disregard_import_re = 1;
                    $ir_period->save();

                    echo "Import Reconciliation period " . $ir_period->service_start . " disregarded -- VAT REG ID: " . $ir_period->id . "<br>";
                } 
            }
        } 
        catch (\Exception $e) {
            dd($e);  
        }       
    } 

    public function missingSalesInvoiceRefFiles()
    {               
        try 
        {          
            //$sales_invoice_nos = ImportReconciliationSalesInvoices::pluck('invoice_no')->toArray();
            
            // $matching_invoices = ImportReconciliationFiles::with(['vatreg', 'vatreg.client'])
            //                         // ->whereNotIn('invoice_no', function ($query) {
            //                         //     $query->select('invoice_no')->from('dv_import_reconciliation_sales_invoices');
            //                         // })
            //                         ->whereIn('invoice_no', function ($query) {
            //                             $query->select('invoice_no')->from('dv_import_reconciliation_sales_invoices');
            //                         })                                    
            //                         ->whereHas('vatreg.client', function ($subquery) {                                        
            //                             $subquery->whereIn('id', [84]);                                     
            //                         })
            //                         //->where('invoice_no', 'N00211412')
            //                         ->get();

            // foreach ($matching_invoices as $matching_invoice)
            // {
            //     $sales_invoice = ImportReconciliationSalesInvoices::where('invoice_no', $matching_invoice->invoice_no)->first();

            //     if($matching_invoice->vat_reg_id == $sales_invoice->vat_reg_id)
            //     {

            //     }
            //     else
            //     {
            //         //$invoice_file = ImportReconciliationFiles::where('id', $matching_invoice->id)->first();
            //         $matching_invoice->vat_reg_id = $sales_invoice->vat_reg_id;
            //         $matching_invoice->save();
            //     }
            // }

            // dd($matching_invoices);

            $mismatched_month_invoices = ImportReconciliationFiles::with(['vatreg','vatreg.client'])
                                ->join('dv_import_reconciliation_sales_invoices as s', 'dv_import_reconciliation_files.invoice_no', '=', 's.invoice_no')
                                ->whereColumn('dv_import_reconciliation_files.vat_reg_id', '!=', 's.vat_reg_id')                              
                                ->select('dv_import_reconciliation_files.*', 's.vat_reg_id as invoice_vat_reg_id') 
                                ->whereHas('vatreg.client', function ($subquery) {                                        
                                    $subquery->whereIn('id', [75]); //84 -AUBO; 85 -BECK                                       
                                })                              
                                ->get();

            //dd($mismatched_month_invoices);

            foreach ($mismatched_month_invoices as $mismatched_month_invoice)
            {
                $vat_reg_id = $mismatched_month_invoice->vat_reg_id;

                $sales_invoice = ImportReconciliationSalesInvoices::where('invoice_no', $mismatched_month_invoice->invoice_no)->get();

                if(count($sales_invoice) == 1)
                {                    
                    $mismatched_month_invoice->vat_reg_id = $mismatched_month_invoice->invoice_vat_reg_id;
                    //$mismatched_month_invoice->save();

                    echo "Sales invoice no. " . $mismatched_month_invoice->invoice_no . " vat_reg_id changed from " . $vat_reg_id . " to " . $mismatched_month_invoice->invoice_vat_reg_id . "<br>";
                }
                else 
                {
                    //if($mismatched_month_invoice->invoice_no =='N144107')
                    //{ 
                    $update_sale_invoice = '';  
                    $delete_sale_invoice = '';
                    foreach ($sales_invoices as $sales_invoice)
                    {  
                        $com_invoice_id = $sales_invoice->com_invoice_id;
                        $com_invoice = ImportReconciliationComInvoices::where('id', $com_invoice_id)->first();    

                        if($com_invoice->data_from == 'ftp' && $com_invoice->invoice_no == '-')
                            $delete_sale_invoice = $sales_invoice;  
                        else
                            $update_sale_invoice = $sales_invoice;  
                    }
                    
                    if($update_sale_invoice && $delete_sale_invoice)
                    {
                        if(!$update_sale_invoice->net_amount)
                            $update_sale_invoice->net_amount = $delete_sale_invoice->net_amount;

                        if(!$update_sale_invoice->vat_amount)
                            $update_sale_invoice->vat_amount = $delete_sale_invoice->vat_amount;

                        if(!$update_sale_invoice->total_amount)
                            $update_sale_invoice->total_amount = $delete_sale_invoice->total_amount;

                        if(!$update_sale_invoice->shipping)
                            $update_sale_invoice->shipping = $delete_sale_invoice->shipping;

                        if(!$update_sale_invoice->variance)
                            $update_sale_invoice->variance = $delete_sale_invoice->variance;

                        if($update_sale_invoice->credit_note != $delete_sale_invoice->credit_note)
                            $update_sale_invoice->credit_note = $delete_sale_invoice->credit_note;

                        //$update_sale_invoice->save();

                        $mismatched_month_invoice->vat_reg_id = $update_sale_invoice->vat_reg_id;
                        //$mismatched_month_invoice->save();

                        //$delete_sale_invoice->delete();
                    }
                    //}//dummy               
                    echo "DOUBLE Sales invoice no. " . $mismatched_month_invoice->invoice_no . "<br>";    
                }
            }
        } 
        catch (\Exception $e) 
        {
            dd($e);  
        }       
    } 

    public function missingIrFilesInDatabase()
    {               
        try 
        {
            $efacto = false;

            $vatregs = VATRegistration::with(['client'])
                        ->whereHas('client', function ($subquery) {                                        
                            $subquery->whereIn('id', [75]);
                        })
                        ->get();

            $vatreg = $vatregs->first();

            $files = ImportReconciliationFiles::with(['vatreg','vatreg.client'])
                ->whereHas('vatreg.client', function ($subquery) {                                        
                    $subquery->whereIn('id', [75]);
                })
                ->get();
            
            $system_ftp = $this->commonClass->getSystemInfoLazy('FTP', 'Production');
            $efacto = false;
            $which_folder = 'archive';

            $clientapi = $system_ftp->systemapi->first();
            $client = $vatreg->client;      

            $vat_reg_id = $vatreg->id;
            
            $ftp_connection = $clientapi;

            $sftp_server = $ftp_connection->api_base_url;
            $sftp_username = $ftp_connection->api_client_id; 
            $sftp_password = $ftp_connection->api_secret_key; 
            $sftp_foldername = ($efacto) ? 'efacto' : (preg_replace('/[^A-Za-z0-9\-]/', '', 
                                preg_replace('/\s+/', '', 
                                    strtolower(
                                        iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $client->client_name)
                                    )
                                )
                            )); 
          
            if($sftp_foldername == 'auboproductionas')      
                $sftp_foldername = 'aubo';
            else if($sftp_foldername == 'becksondergaardaps' || $sftp_foldername == 'becksndergaardaps')        
                $sftp_foldername = 'becksondergaard';
            else if($sftp_foldername == 'asvillyjensenbesaetningsartiklerengros')       
                $sftp_foldername = 'villyjensen';
            else if($sftp_foldername == 'dfi-geisleras')
                $sftp_foldername = 'dfigeisler';

            $driver = Storage::createSFtpDriver([
                    'host'     => $sftp_server,
                    'username' => $sftp_username,
                    'password' => $sftp_password,
                    //'port'     => 65002,                         
                    'timeout'  => 10,
                ]);
            
            $sftp_path = '/var/sftp/uploads/';      
                    
            $sftp_subfoldername = $sftp_foldername;
            
            if($which_folder == 'main')
                $importreconciliationfiles = collect($driver->listContents($sftp_path . $sftp_foldername. "/".$sftp_subfoldername, false));
            else if($which_folder == 'archive')     
                $importreconciliationfiles = collect($driver->listContents($sftp_path . $sftp_foldername. "/Archive", false));              
            else if($which_folder == 'both')
            {
                $main_importreconciliationfiles = collect($driver->listContents($sftp_path . $sftp_foldername. "/".$sftp_subfoldername, false));

                $archive_importreconciliationfiles = collect($driver->listContents($sftp_path . $sftp_foldername. "/Archive", false));

                $importreconciliationfiles = $main_importreconciliationfiles->merge($archive_importreconciliationfiles);
            }
            
            $o_file_names = $files->pluck('o_file_name')->toArray();
         
            if($o_file_names)   
            {           
                $filtered_importreconciliationfiles = $importreconciliationfiles->filter(function ($importreconciliationfile)  use ($o_file_names) {
                    $filepath = $importreconciliationfile['path'];
                    $filename = basename($filepath);

                    return !in_array($filename, $o_file_names);
                });
                $missingimportreconciliationfiles = $filtered_importreconciliationfiles;                

                if(count($missingimportreconciliationfiles) > 0) 
                {           
                    $system = $this->commonClass->getSystemInfoLazy();
                    $systemapi = $system->systemapi->first();

                    $sftp_details = [
                        'host'     => $sftp_server,
                        'username' => $sftp_username,
                        'password' => $sftp_password,                
                        'path' => $sftp_path,
                        'foldername' => $sftp_foldername,
                        'subfoldername' => $sftp_subfoldername                              
                    ];
                    $jobs = [];

                    $arr_importreconciliationfiles = $missingimportreconciliationfiles->toArray();
                    $chunks = array_chunk($arr_importreconciliationfiles, 10);
                    
                    foreach ($chunks as $chunk)
                    {               
                        $jobs[] = (new ReadFtpFiles($chunk, $vatreg, $sftp_details, $efacto, $this->authUser, $systemapi))->delay(now()->addSeconds(5));
                    }
                    
                    $batch = Bus::batch($jobs)->dispatch();

                    dd($missingimportreconciliationfiles);  
                } //if    
            } 

            
        }
        catch (\Exception $e) 
        {
            dd($e);  
        } 
    }

    public function convertHtmlTableToExcel(Request $request, TableToExcelService $service)
    {
        try 
        {
            $filePath = $service->convert($request->table);

            return response()->download($filePath)->deleteFileAfterSend(true);
        } 
        catch (\Exception $e) 
        {
            dd($e);  
        }
    }

    public function analyzepdf()
    {   
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
        /* --end PAGE CONFIG -- */
        
        /* -- RETURN VIEW -- */
        return view('content.analyze', [
          'pageConfigs' => $pageConfigs, 
          'authUser' => $this->authUser                
        ]);
        /* --end RETURN VIEW -- */
    }

    public function analyze(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf'
        ]);

        $filePath = $request->file('file')->getRealPath();

        // $endpoint = env('AZURE_FORM_ENDPOINT');
        // $apiKey = env('AZURE_FORM_KEY');

        $endpoint = config('services.azure_form.endpoint');
        $apiKey = config('services.azure_form.key');

        if (!$endpoint || !$apiKey) {
            dd('Endpoint or API key missing!');
        }

        $url = $endpoint . "/formrecognizer/documentModels/prebuilt-read:analyze?api-version=2023-07-31";

        // Initialize Guzzle client
        $guzzleclient = new GuzzleClient();

        // Step 1: Send the PDF to Azure
        $response = $guzzleclient->post($url, [
            'headers' => [
                'Ocp-Apim-Subscription-Key' => $apiKey,
                'Content-Type' => 'application/pdf',
            ],
            'body' => file_get_contents($filePath),
        ]);

        // Step 2: Get operation-location header
        $operationLocation = $response->getHeaderLine('operation-location');

        // Step 3: Wait a few seconds for processing
        sleep(3); // optional: you can implement polling for longer documents

        // Step 4: Poll the operation result
        $resultResponse = $guzzleclient->get($operationLocation, [
            'headers' => [
                'Ocp-Apim-Subscription-Key' => $apiKey,
            ]
        ]);

        // Step 5: Decode JSON response
        $result = json_decode($resultResponse->getBody(), true);
dd($result);
        // Step 6: Return result
        return $result;

        /*
        // Step 1: Send PDF to Azure for analysis
        $response = Http::withHeaders([
            'Ocp-Apim-Subscription-Key' => $apiKey,
            'Content-Type' => 'application/pdf'
        ])->withBody(file_get_contents($filePath), 'application/pdf')
          ->post($url);

        if (!$response->successful()) {
            return response()->json($response->json(), 500);
        }

        $operationLocation = $response->header('operation-location');

        // Step 2: Get result (poll the operation)
        sleep(3);

        $result = Http::withHeaders([
            'Ocp-Apim-Subscription-Key' => $apiKey,
        ])->get($operationLocation);

        return $result->json();
        */
    }
}
