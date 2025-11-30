<?php

namespace App\Http\Controllers\anyexcel;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use App\Models\AnyExcelTemplates;

use \App\Classes\CommonClass;
use \App\Classes\ApiClass;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

use Str;
use Storage;

class AnyExcelController extends Controller
{
    public $authUser;
    
    public $commonClass;
    public $apiClass;
   
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {                     
            $this->commonClass = new CommonClass();
            $this->authUser = $this->commonClass->getAuthUser();  
            $this->apiClass = new ApiClass();   
          
            return $next($request);
        });
    } 

    /* -- GET /anyexcel-template -- */
    public function index()
    {   
      try
      {    
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
        /* --end PAGE CONFIG -- */
            
        /* -- GET ANY EXCEL SYSTEM COLUMNS -- */
        $anyexcel_system_columns = $this->commonClass->listExcelColumns();
        /* --end GET ANY EXCEL SYSTEM COLUMNS -- */
                        
        /* -- GET ANY EXCEL TEMPLATES -- */
        $anyexcel_templates = $this->commonClass->getAnyExcelTemplates();
        /* --end GET ANY EXCEL TEMPLATES -- */
      
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'anyexceltemplate-list');
        /* --end LOG -- */

        /* -- RETURN VIEW -- */
        return view('content.anyexcel.index', [
          'pageConfigs' => $pageConfigs, 
          'authUser' => $this->authUser,   
          'anyexcel_system_columns' => $anyexcel_system_columns,                             
          'anyexcel_templates' => $anyexcel_templates
        ]);
        /* --end RETURN VIEW -- */
      }      
      catch (\Exception $e) 
      {           
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'Any Excel Template Controller',
            'method' => 'index',
            'message' => $e->getMessage()
          ]
        );
        /* --end LOG -- */

        /* -- RETURN JSON -- */
        return response()->json([   
          'status' => 'Error',                 
          'message' => $e->getMessage()
        ]);
        /* --end RETURN JSON -- */ 
      }  
    }  
    /* --end GET /anyexcel-template -- */

    /* -- GET /anyexcel-template/create -- */
    public function create(Request $request)
    {  
      try
      {    
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
        /* --end PAGE CONFIG -- */
               
        $vatreturn_file_id = $request->query('id');

        if($vatreturn_file_id)          
          $vatreturnfile = $this->commonClass->getVatReturnFileLazy($vatreturn_file_id);

        /* -- GET COMPANY/CLIENT -- */
        $clients = $this->commonClass->getCompanyLazy();        
        $clients = $clients
                      ->filter(fn($client) => $client->status == 1)
                      // ->map(function ($client) {                         
                      //     $client->vatregmain = $client->vatregmain->filter(fn($vrmain) => ($vrmain->status == 1 && $vrmain->product_type > 1));
                      //     return $client;
                      // })
                      //->filter(fn($client) => $client->vatregmain->isNotEmpty()) // keep only clients with at least one item left
                      ->sortBy('client_name')
                      ->values();            
        /* --end GET COMPANY/CLIENT -- */

        /* -- RETURN VIEW -- */
        return view('content.anyexcel.create', [
          'pageConfigs' => $pageConfigs, 
          'authUser' => $this->authUser, 
          'vatreturnfile' => isset($vatreturnfile) ? $vatreturnfile : null,
          'clients' => $clients
        ]);
        /* --end RETURN VIEW -- */
      }      
      catch (\Exception $e) 
      {           
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'Any Excel Template Controller',
            'method' => 'create',
            'message' => $e->getMessage()
          ]
        );
        /* --end LOG -- */

        /* -- RETURN JSON -- */
        return response()->json([   
          'status' => 'Error',                 
          'message' => $e->getMessage()
        ]);
        /* --end RETURN JSON -- */ 
      }        
    }
    /* --end GET /anyexcel-template/create -- */ 

    /* -- GET /anyexcel-template/{template_id}/edit -- */
    public function edit(Request $request, $template_id)
    {  
      try
      { 
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
        /* --end PAGE CONFIG -- */
               
        // $vatreturn_file_id = $request->query('id');

        // if($vatreturn_file_id)          
        //   $vatreturnfile = $this->commonClass->getVatReturnFileLazy($vatreturn_file_id);

        /* -- GET ANYEXCEL TEMPLATE -- */
        $anyexceltemplate = $this->commonClass->getAnyExcelTemplates($template_id);
        /* --end GET ANYEXCEL TEMPLATE -- */
   
        /* -- GET COMPANY/CLIENT -- */
        $clients = $this->commonClass->getCompanyLazy();        
        $clients = $clients
                      ->filter(fn($client) => $client->status == 1)                      
                      ->sortBy('client_name')
                      ->values();            
        /* --end GET COMPANY/CLIENT -- */

        /* -- RETURN VIEW -- */
        return view('content.anyexcel.create', [
          'pageConfigs' => $pageConfigs, 
          'authUser' => $this->authUser, 
          'anyexcel_template_id' => $template_id,
          'anyexceltemplate' => $anyexceltemplate,
          'clients' => $clients
        ]);
        /* --end RETURN VIEW -- */
      }      
      catch (\Exception $e) 
      {           
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'Any Excel Template Controller',
            'method' => 'edit',
            'message' => $e->getMessage()
          ]
        );
        /* --end LOG -- */

        /* -- RETURN JSON -- */
        return response()->json([   
          'status' => 'Error',                 
          'message' => $e->getMessage()
        ]);
        /* --end RETURN JSON -- */ 
      }        
    }
    /* -- GET /anyexcel-template/{template_id}/edit -- */
    
    /* -- POST /anyexcel-template/upload -- */
    public function upload(Request $request)
    { 
      try
      { 
        $vatreturn_file_id = $request->vatreturn_file_id;
        $anyexcel_template_id = $request->anyexcel_template_id;
        
        /* -- GET ANY EXCEL SYSTEM COLUMNS -- */
        $system_columns = $this->commonClass->listExcelColumns();
        /* --end GET ANY EXCEL SYSTEM COLUMNS -- */

        /* -- UPLOAD TEMPLATE FILE -- */
        if(!$anyexcel_template_id)        
        {
          $system = $this->commonClass->getSystemInfoLazy(); 
          $systemapi = $system->systemapi->first();    
         
          $anyexcelfile = $this->apiClass->uploadAnyExcelTemplateFilesToOneDrive($request, $this->authUser, $systemapi);      
        }
        /* --end UPLOAD TEMPLATE FILE -- */

        if($vatreturn_file_id)   
        {          
          $uploaded_file_name = $request->uploaded_file_name;

          if($request->anyexceltemplate)
          {
            $anyexceltemplate = json_decode($request->anyexceltemplate['columns']);

            $storage_path = storage_path('app/public/');
            $original_inputFile = $storage_path . $uploaded_file_name;

            // Load the spreadsheet
            $spreadsheet = IOFactory::load($original_inputFile);
          }
          else
            return abort(404, 'Page not found.');
        }
        else
        {
          $file = $request->file('file');
        
          // Load the spreadsheet
          $spreadsheet = IOFactory::load($file->getPathname());
        }

        $sheets = $spreadsheet->getAllSheets(); // Get all sheet objects
        $total_sheets = count($sheets);
       
        $anyexcel_template_preview_tab_li = '';
        $anyexcel_template_preview_tab_content = '';
        foreach ($sheets as $sheet_index => $sheet) 
        {
          $active_sheet = ($sheet_index == 0) ? true : false;

          $sheet_title = $sheet->getTitle();
          \Log::info("Sheet $sheet_index: $sheet_title");
          
          $highestRow = $sheet->getHighestDataRow();
          $highestColumn = $sheet->getHighestDataColumn();
          $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
   
          // Limit to first 10 rows and reasonable number of columns (e.g., A to H)        
          $maxRows = min($highestRow, 25);         
          $maxCols = $highestColumnIndex;
   
          $col_header = [];
          for ($col = 0; $col <= $maxCols-1; $col++) {
            $col_header[] = $this->commonClass->getExcelColumnLetter($col);
          }

          $preview = [];               
          for ($row = 1; $row <= $maxRows; $row++) 
          {
            // if ($sheet->getRowDimension($row)->getVisible())
            // {
              $rowData = [];
                          
              for ($col = 1; $col <= $maxCols; $col++) 
              {
                  // Debug each coordinate
                  \Log::info("Row: $row, Col: $col");

                  try {
                      $cell = $sheet->getCellByColumnAndRow($col, $row);  
                      
                      $value = '';
                      if ($cell->isFormula())
                      {
                        try {
                          $oldvalue = $cell->getOldCalculatedValue();
                          $value = (is_numeric($oldvalue)) ? number_format((float)$oldvalue, 2) : $oldvalue;
                        } catch (\PhpOffice\PhpSpreadsheet\Calculation\Exception $e) {
                          $value = $cell->getValue(); // fallback to formula text
                        }
                      }
                      else
                          $value = $cell->getFormattedValue(); // Safe for non-formula cells
                                          
                      $rowData[] = $value;
                  } catch (\Throwable $e) {
                      \Log::error("Cell fetch error at row $row, col $col: " . $e->getMessage());
                      return response()->json([
                          'status' => 'error',
                          'message' => "Error at row $row, col $col: " . $e->getMessage()
                      ]);
                  }
              } //column

              $preview[] = $rowData;   
            //} //visible row
          } //row
          
          $anyexcel_template_preview_tab_li .= view('_partials._content._vatreturn.anyexcel-template-preview-tab-li', 
            compact(
              'sheet_title', 'sheet_index', 'active_sheet'
            )
          )->render(); 

          if($vatreturn_file_id)
            $anyexcel_template_preview_tab_content .= view('_partials._content._vatreturn.anyexcel-template-preview-tab-content', 
              compact(
                'total_sheets', 'sheet_title', 'sheet_index', 'active_sheet', 'col_header', 'system_columns', 'preview', 'anyexceltemplate'
              )
            )->render(); 
          else
            $anyexcel_template_preview_tab_content .= view('_partials._content._vatreturn.anyexcel-template-preview-tab-content', 
              compact(
                'total_sheets', 'sheet_title', 'sheet_index', 'active_sheet', 'col_header', 'system_columns', 'preview'
              )
            )->render(); 
        } //for all sheets       

        /* -- RETURN JSON -- */
        return response()->json([   
          'status' => 'success', 
          'anyexcel_template_id' => isset($anyexcelfile) ? $anyexcelfile[0]->id : $anyexcel_template_id,
          'anyexcel_template_preview_tab_li' => $anyexcel_template_preview_tab_li,
          'anyexcel_template_preview_tab_content' => $anyexcel_template_preview_tab_content
        ]);
        /* --end RETURN JSON -- */ 
      }      
      catch (\Exception $e) 
      {
        dd($e);
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'Any Excel Template Controller',
            'method' => 'upload',
            'message' => $e->getMessage()
          ]
        );
        /* --end LOG -- */

        /* -- RETURN JSON -- */
        return response()->json([   
          'status' => 'Error',                 
          'message' => $e->getMessage()
        ]);
        /* --end RETURN JSON -- */ 
      }            
    } 
    /* --end POST /anyexcel-template/upload -- */  

    /* -- POST /anyexcel-template/store -- */
    public function store(Request $request)
    { 
      try
      {        
        // $already_exists =  AnyExcelTemplates::where('name', $request->template_name)
        //                         ->whereJsonContains('columns', json_encode($request->template))
        //                         ->count();

        $already_exists =  AnyExcelTemplates::where('id', $request->anyexcel_template_id)
                                //->whereNull('columns')
                                //->whereNotNull('file_id')
                                //->where('status', 0)
                                ->first();
        
        // $_fields = [  
        //   'client_id' => $request->client_id,
        //   'name' => $request->template_name,            
        //   'columns' => json_encode($request->template),         
        //   'version' => 1,
        //   'status' => 1,            
        //   'created_by' => $this->authUser->user_id
        // ]; 

        //if($already_exists && $already_exists->file_id)
        if($already_exists)
        {
          $already_exists->status = 1;
          $already_exists->columns = json_encode($request->template);
          $already_exists->updated_by = $this->authUser->user_id;

          $already_exists->save();

          $template_id = $already_exists->id;
        } 
        // else    
        // {      
        //   $anyexceltemplate = AnyExcelTemplates::updateOrCreate(
        //     $_fields
        //   );   

        //   $template_id = $anyexceltemplate->id;
        // }

        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'anyexceltemplate-add',
          [
            'Template Name' => $request->template_name,
            'Template Columns' => $request->template,
            'Template Version' => 1
          ]
        );
        /* --end LOG -- */
        
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
        /* --end PAGE CONFIG -- */
            
        /* -- GET ANY EXCEL SYSTEM COLUMNS -- */
        $anyexcel_system_columns = $this->commonClass->listExcelColumns();
        /* --end GET ANY EXCEL SYSTEM COLUMNS -- */
                        
        /* -- GET ANY EXCEL TEMPLATES -- */
        $anyexcel_templates = $this->commonClass->getAnyExcelTemplates();
        /* --end GET ANY EXCEL TEMPLATES -- */
              
        if($request->vatreturn_file_id)  
        {        
          $vatreturnfile = $this->commonClass->getVatReturnFileLazy($request->vatreturn_file_id);

          $vat_reg_id = $vatreturnfile->vatreg->id;

          $vatreg = $this->commonClass->getVatRegLazy($vat_reg_id);

          /* -- UPDATE TEMPLATE ID IN VAT REG. AND MAIN -- */
          $vatregmain = $vatreg->vatregmain;
          $vatregmain->anyexcel_template_id = ($template_id == 0) ? NULL : $template_id;
          $vatregmain->save();

          $vatreg->anyexcel_template_id = ($template_id == 0) ? NULL : $template_id;
          $vatreg->save();

          $vatreturnfile->anyexcel_template_id = ($template_id == 0) ? NULL : $template_id;
          $vatreturnfile->save();
          /* --end UPDATE TEMPLATE ID IN VAT REG. AND MAIN -- */

          /* -- RETURN JSON -- */
          return response()->json([   
            'status' => 'Success',                 
            'client_id' => $vatreg->client_id,
            'vat_reg_id' => $vat_reg_id
          ]);
          /* --end RETURN JSON -- */ 
        }
        else
          /* -- RETURN JSON -- */
          return response()->json([   
            'status' => 'Success'
          ]);
          /* --end RETURN JSON -- */ 
      }
      catch (\Exception $e) 
      {           
        //dd($e);
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'Any Excel Template Controller',
            'method' => 'store',
            'message' => $e->getMessage()
          ]
        );
        /* --end LOG -- */

        /* -- RETURN JSON -- */
        return response()->json([   
          'status' => 'Error',                 
          'message' => $e->getMessage()
        ]);
        /* --end RETURN JSON -- */ 
      }            
    } 
    /* --end POST /anyexcel-template/store -- */  

    /* -- PUT /anyexcel-template/{template_id} -- */
    public function update(Request $request, $template_id)
    {
      try
      {  
        $file_type = $request->file_type;

        /* -- GET ANYEXCEL TEMPLATE -- */
        $anyexceltemplate = $this->commonClass->getAnyExcelTemplates($template_id);
        /* --end GET ANYEXCEL TEMPLATE -- */
        
        /* -- GET VAT REG. -- */
        $vat_reg_id = $request->vat_reg_id;
        $vatreg = $this->commonClass->getVatRegLazy($vat_reg_id);

        $client = $vatreg->client;          
        $vat_period = ($vatreg->frequency > 1) ? (\Carbon\Carbon::parse($vatreg->service_start)->format('M y') . '-' . \Carbon\Carbon::parse($vatreg->service_start)->addMonth(($vatreg->frequency-1))->format('M y')) : (\Carbon\Carbon::parse($vatreg->service_start)->format('M y'));
        /* --end GET VAT REG. -- */

        if($file_type == 'vatreturn' || $file_type == 'vatcontrol' || $file_type == 'ircontrol')
        {
          /* -- UPDATE TEMPLATE ID IN VAT REG. AND MAIN -- */
          $vatregmain = $vatreg->vatregmain;
          $vatregmain->anyexcel_template_id = ($template_id == 0) ? NULL : $template_id;
          $vatregmain->save();

          $vatreg->anyexcel_template_id = ($template_id == 0) ? NULL : $template_id;
          $vatreg->save();
          /* --end UPDATE TEMPLATE ID IN VAT REG. AND MAIN -- */
        }
        else if($file_type == 'iranyexcel')
        {
        }

        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'anyexceltemplate-update',
            [              
              'Client Name' => $client->client_name,  
              'Period' => $vat_period,
              'Template Name' => ($anyexceltemplate) ? $anyexceltemplate->name : 'Default Template',            
              'Template Version' => ($anyexceltemplate) ? $anyexceltemplate->version : 1
            ]
          );        
        
        /* -- RETURN JSON -- */
        return response()->json(
          [
            'status' => 200,             
            'anyexceltemplate' => $anyexceltemplate
          ]
        );
        /* --end RETURN JSON -- */    
      }      
      catch (\Exception $e) 
      {           
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'Any Excel Template Controller',
            'method' => 'edit',
            'message' => $e->getMessage()
          ]
        );
        /* --end LOG -- */

        /* -- RETURN JSON -- */
        return response()->json([   
          'status' => 'Error',                 
          'message' => $e->getMessage()
        ]);
        /* --end RETURN JSON -- */ 
      }
    } 
    /* --end PUT /anyexcel-template/{template_id} -- */

    /* -- DELETE /anyexcel-template/{template_id}/delete -- */
    public function destroy(Request $request, $template_id)
    {
      try
      {        
        if($template_id != 0)
        {
          /* -- DELETE ANYEXCEL TEMPLATE -- */                   
          $anyexceltemplate = $this->commonClass->getAnyExcelTemplates($template_id);        
          
          $template_name = $anyexceltemplate->name;
          $template_version = $anyexceltemplate->version;
          $anyexceltemplate->delete();
          /* --end DELETE ANYEXCEL TEMPLATE -- */ 

          /* -- LOG -- */
          $this->commonClass->addLog($this->authUser, 'anyexceltemplate-delete',
            [
              'Template Name' => $template_name,            
              'Template Version' => $template_version
            ]
          );
          /* --end LOG -- */  
        } 

        /* -- GET ANY EXCEL TEMPLATES -- */
        $anyexcel_templates = $this->commonClass->getAnyExcelTemplates();
        /* --end GET ANY EXCEL TEMPLATES -- */
       
        /* -- RETURN JSON -- */
        return response()->json(
          [
            'status' => 200,             
            'anyexceltemplates' => $anyexcel_templates,
            'message' => 'Deleted'
          ]
        );
        /* --end RETURN JSON -- */    
      }      
      catch (\Exception $e) 
      {           
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'Any Excel Template Controller',
            'method' => 'destroy',
            'message' => $e->getMessage()
          ]
        );
        /* --end LOG -- */

        /* -- RETURN JSON -- */
        return response()->json([   
          'status' => 'Error',                 
          'message' => $e->getMessage()
        ]);
        /* --end RETURN JSON -- */ 
      }
    } 
    /* --end DELETE /anyexcel-template/{template_id}/delete -- */

    /* -- GET /anyexcel-template/{vatreturn_file_id} -- */
    public function show($id)
    { 
      try
      { 
        $vatreturn_file_id = $id;

        if($vatreturn_file_id)   
        {
          if (stripos(url()->previous(), 'edit')) 
            $anyexcelfile = $this->commonClass->getAnyExcelTemplates($vatreturn_file_id);            
          else
          {  
            $vatreturnfile = $this->commonClass->getVatReturnFileLazy($vatreturn_file_id);
            $vatreturnofile = $vatreturnfile->vatreturnofiles->first();
            $anyexcelfile = $vatreturnfile->anyexceltemplate;
          }

          //if($vatreturnfile)   
          if($anyexcelfile)
          {
            /* -- DOWNLOAD FILE -- */
            $system = $this->commonClass->getSystemInfoLazy(); 
            $systemapi = $system->systemapi->first();

            $passfile = isset($vatreturnofile) ? $vatreturnofile : $anyexcelfile;

            $downloadurl = $this->apiClass->loadFromOneDriveLazy($passfile, $systemapi);
            /* --end DOWNLOAD FILE -- */

            /* -- SAVE DOWNLOAD FILE TO STORAGE -- */
            $original_file = $downloadurl['download_url'];
            $original_file_name = $downloadurl['name'];
            $original_file_extension = $downloadurl['file_extension'];

            $contents = (strpos($original_file, "https://") !== false) ? file_get_contents($original_file) : $original_file;        
            
            $original_filename = Str::random(10) . '.' . $original_file_extension;
            
            Storage::disk('public')->put($original_filename, $contents);
            
            $storage_path = storage_path('app/public/');
            $original_inputFile = $storage_path . $original_filename;
            /* --end SAVE DOWNLOAD FILE TO STORAGE -- */

            /* -- RETURN JSON -- */
            return response()->json(
              [
                'status' => 200,             
                'anyexceltemplate' => isset($anyexcelfile->anyexceltemplate) ? $anyexcelfile->anyexceltemplate : $anyexcelfile,
                'file' => [
                  'url' => $original_inputFile,
                  'name' => $original_filename,
                  'size' => '1024',
                  'type' => $original_file_extension
                ]
              ]
            );
            /* --end RETURN JSON -- */  
          }
          else
            return abort(404, 'Page not found.');
        }
      }      
      catch (\Exception $e) 
      { 
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'Any Excel Template Controller',
            'method' => 'destroy',
            'message' => $e->getMessage()
          ]
        );
        /* --end LOG -- */

        /* -- RETURN JSON -- */
        return response()->json([   
          'status' => 'Error',                 
          'message' => $e->getMessage()
        ]);
        /* --end RETURN JSON -- */ 
      }
    } 
    /* --end GET /anyexcel-template/{vatreturn_file_id} -- */
}
