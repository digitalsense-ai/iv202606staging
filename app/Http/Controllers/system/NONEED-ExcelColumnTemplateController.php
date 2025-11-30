<?php

namespace App\Http\Controllers\system;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use App\Models\ExcelColumnTemplates;

use \App\Classes\CommonClass;

class ExcelColumnTemplateController extends Controller
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

    /* -- GET /excel-column-templates -- */
    public function index()
    {   
      try
      {    
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
        /* --end PAGE CONFIG -- */
            
        /* -- GET EXCEL COLUMNS -- */
        $excel_columns = $this->commonClass->listExcelColumns();
        /* --/ GET EXCEL COLUMNS -- */
                        
        /* -- GET EXCEL COLUMN TEMPLATES -- */
        $excelcolumntemplates = $this->commonClass->getExcelColumnTemplatesLazy();
        /* --end GET EXCEL COLUMN TEMPLATES -- */
      
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'excelcolumntemplate-list');
        /* --end LOG -- */

        /* -- RETURN VIEW -- */
        return view('content.excelcolumntemplate.index', [
          'pageConfigs' => $pageConfigs, 
          'authUser' => $this->authUser,   
          'excel_columns' => $excel_columns,                             
          'excelcolumntemplates' => $excelcolumntemplates
        ]);
        /* --end RETURN VIEW -- */
      }      
      catch (\Exception $e) 
      {           
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'Excel Column Template Controller',
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
    /* --end GET /excel-column-templates -- */

    /* -- GET /excel-column-templates/create -- */
    public function create()
    {  
      try
      {    
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
        /* --end PAGE CONFIG -- */
                                      
        /* -- RETURN VIEW -- */
        return view('content.excelcolumntemplate.create', [
          'pageConfigs' => $pageConfigs, 
          'authUser' => $this->authUser,                                          
        ]);
        /* --end RETURN VIEW -- */
      }      
      catch (\Exception $e) 
      {           
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'Excel Column Template Controller',
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
    /* --end GET /excel-column-templates/create -- */  

    /* -- POST /excel-column-templates -- */
    public function store(Request $request)
    {
      try
      {       
        $templateID = $request->template_id;

        $sort_template_columns = $request->template_columns;
        $this->commonClass->ksort_recursive($sort_template_columns);
        
        $_where = [];
        $_fields = [];
        /* -- if HAS TEMPLATE ID EDIT -- */
        if ($templateID) 
        {              
          /* -- GET EXCEL COLUMN TEMPLATE -- */
          $excelcolumntemplate = $this->commonClass->getExcelColumnTemplatesLazy($templateID);
          /* --end GET EXCEL COLUMN TEMPLATE -- */
          
          if($excelcolumntemplate->name == $request->template_name)
          {
            if($request->edit_type == 'Clone')
              /* -- RETURN JSON -- */
              return response()->json([
                'status' => 400,             
                'excelcolumntemplate' => $excelcolumntemplate,
                'message' => 'Template name already exists'
              ]);
              /* --end RETURN JSON -- */
            else
              $_where = ['id' => $templateID];  
          }
          else 
          {
            $already_exists =  $excelcolumntemplate::whereJsonContains('columns', $sort_template_columns)
                                ->count();
                                
            if($already_exists > 0)
            {            
              /* -- RETURN JSON -- */
              return response()->json([
                'status' => 400,             
                'excelcolumntemplate' => $excelcolumntemplate,
                'message' => 'Template column mapping already exists'
              ]);
              /* --end RETURN JSON -- */     
            }
            else
            {
              if($request->edit_type == 'Edit')
                $_where = ['id' => $templateID];          
            }
          }
                           
          $_fields = [                                                          
            'name' => $request->template_name,            
            'columns' => json_encode($sort_template_columns),         
            'version' => ($request->edit_type == 'Clone') ? ($excelcolumntemplate->version + 1) : $excelcolumntemplate->version,
            'status' => 1,            
            'created_by' => $this->authUser->user_id
          ];          
          /* --end UPDATE TEMPLATE -- */
        } /* -- else HAS TEMPLATE ID EDIT -- */  
        else 
        {         
          $_fields = [      
            'name' => $request->template_name,            
            'columns' => json_encode($sort_template_columns), 
            'version' => 1,
            'status' => 1,            
            'created_by' => $this->authUser->user_id
          ];          
        } /* --end if HAS TEMPLATE ID EDIT -- */
     
        /* -- CREATE/UPDATE TEMPLATE -- */
        if($_where)
          $excelcolumntemplate = ExcelColumnTemplates::updateOrCreate(         
            $_where,
            $_fields
          );
        else
          $excelcolumntemplate = ExcelColumnTemplates::updateOrCreate(                      
            $_fields
          );         
        /* --end CREATE/UPDATE TEMPLATE -- */
              
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'excelcolumntemplate-add',
          [
            'Template Name' => $excelcolumntemplate->name,
            'Template Columns' => $excelcolumntemplate->columns,
            'Template Version' => $excelcolumntemplate->version
          ]
        );
        /* --end LOG -- */

            
        /* -- RETURN JSON -- */        
        /* -- GET EXCEL COLUMN TEMPLATES -- */
        $excel_column_templates = $this->commonClass->getExcelColumnTemplatesLazy();
        /* --end GET EXCEL COLUMN TEMPLATES -- */

        return response()->json([
          'status' => 200,             
          'excelcolumntemplate' => $excelcolumntemplate,
          'excelcolumntemplates' => $excel_column_templates,
          'message' => ($templateID) ? 'Updated' : 'Created'
        ]);        
        /* --end RETURN JSON -- */
      }      
      catch (\Exception $e) 
      {           
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'Excel Column Template Controller',
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
    /* --end POST /excel-column-templates -- */

    /* -- GET /excel-column-templates/{template_id}/edit -- */
    public function edit(Request $request, $template_id)
    {
      try
      {    
        $excelcolumntemplate = [];    
        if($template_id != 0)
        {
          /* -- GET EXCEL COLUMNS -- */
          $excel_columns = $this->commonClass->listExcelColumns();
          /* --/ GET EXCEL COLUMNS -- */
          
          /* -- GET EXCEL COLUMN TEMPLATE -- */
          $excelcolumntemplate = $this->commonClass->getExcelColumnTemplatesLazy($template_id);
          /* --end GET EXCEL COLUMN TEMPLATE -- */

          /* -- LOG -- */
          $this->commonClass->addLog($this->authUser, 'excelcolumntemplate-edit',
            [              
              'Template Name' => $excelcolumntemplate->name,            
              'Template Version' => $excelcolumntemplate->version
            ]
          );
          /* --end LOG -- */           
        }        

        /* -- RETURN JSON -- */
        return response()->json(
          [
            'status' => 200,             
            'excelcolumntemplate' => $excelcolumntemplate,            
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
            'controller' => 'Excel Column Template Controller',
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
    /* --end GET /excel-column-templates/{template_id}/edit -- */

    /* -- PUT /excel-column-templates/{template_id} -- */
    public function update(Request $request, $template_id)
    {
      try
      {  
        $file_type = $request->file_type;

        /* -- GET EXCEL COLUMN TEMPLATE -- */
        $excelcolumntemplate = $this->commonClass->getExcelColumnTemplatesLazy($template_id);
        /* --end GET EXCEL COLUMN TEMPLATE -- */
        
        /* -- GET VAT REG. -- */
        $vat_reg_id = $request->vat_reg_id;
        $vatreg = $this->commonClass->getVatRegLazy($vat_reg_id);

        $client = $vatreg->client;          
        $vat_period = ($vatreg->frequency > 1) ? (\Carbon\Carbon::parse($vatreg->service_start)->format('M y') . '-' . \Carbon\Carbon::parse($vatreg->service_start)->addMonth(($vatreg->frequency-1))->format('M y')) : (\Carbon\Carbon::parse($vatreg->service_start)->format('M y'));
        /* --end GET VAT REG. -- */

        if($file_type == 'vatreturn')
        {
          /* -- UPDATE TEMPLATE ID IN VAT REG. AND MAIN -- */
          $vatregmain = $vatreg->vatregmain;
          $vatregmain->excel_column_template_id = ($template_id == 0) ? NULL : $template_id;
          $vatregmain->save();

          $vatreg->excel_column_template_id = ($template_id == 0) ? NULL : $template_id;
          $vatreg->save();
          /* --end UPDATE TEMPLATE ID IN VAT REG. AND MAIN -- */
        }
        else if($file_type == 'iranyexcel')
        {
        }

        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'excelcolumntemplate-update',
            [              
              'Client Name' => $client->client_name,  
              'Period' => $vat_period,
              'Template Name' => ($excelcolumntemplate) ? $excelcolumntemplate->name : 'Default Template',            
              'Template Version' => ($excelcolumntemplate) ? $excelcolumntemplate->version : 1
            ]
          );        
        /* --end LOG -- */ 
        
        /* -- RETURN JSON -- */
        return response()->json(
          [
            'status' => 200,             
            'excelcolumntemplate' => $excelcolumntemplate
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
            'controller' => 'Excel Column Template Controller',
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
    /* --end PUT /excel-column-templates/{template_id} -- */

    /* -- DELETE /excel-column-templates/{template_id}/delete -- */
    public function destroy(Request $request, $template_id)
    {
      try
      {        
        if($template_id != 0)
        {
          /* -- DELETE EXCEL COLUMN TEMPLATE -- */          
          $excelcolumntemplate = $this->commonClass->getExcelColumnTemplatesLazy($template_id);
          
          $template_name = $excelcolumntemplate->name;
          $template_version = $excelcolumntemplate->version;
          $excelcolumntemplate->delete();
          /* --end DELETE EXCEL COLUMN TEMPLATE -- */ 

          /* -- LOG -- */
          $this->commonClass->addLog($this->authUser, 'excelcolumntemplate-delete',
            [
              'Template Name' => $template_name,            
              'Template Version' => $template_version
            ]
          );
          /* --end LOG -- */  
        } 

        /* -- GET EXCEL COLUMN TEMPLATES -- */      
        $excelcolumntemplates = $this->commonClass->getExcelColumnTemplatesLazy();
        /* --end GET EXCEL COLUMN TEMPLATES -- */ 
       
        /* -- RETURN JSON -- */
        return response()->json(
          [
            'status' => 200,             
            'excelcolumntemplates' => $excelcolumntemplates,
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
            'controller' => 'Excel Column Template Controller',
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
    /* --end DELETE /excel-column-templates/{template_id}/delete -- */

    /* -- GET /excel-column-templates/sheet/{sheet_no} -- */  
    public function excelColumnTemplatesSheet($pass_sheet_no)
    {
        try
        {
          /* -- GET EXCEL COLUMNS -- */
          $excel_columns = $this->commonClass->listExcelColumns();
          /* --/ GET EXCEL COLUMNS -- */

          $sheet_no = $pass_sheet_no - 1;
          $sheetName = "Sheet " . $pass_sheet_no;
                    
          $columnIndex = $pass_sheet_no;

          $activeSheet = ($sheet_no == 0) ? true : false;

          $add_tabs = true;

          $sheet_file_no = 0;

          $worksheet_tab_li_modal = view('_partials._modals.modal-excel-column-template-worksheet-tab-li', compact('sheetName', 'columnIndex', 'activeSheet', 'sheet_no', 'sheet_file_no', 'add_tabs'))->render(); 

          $worksheet_tab_content_modal = view('_partials._modals.modal-excel-column-template-worksheet-tab-content', compact('sheetName', 'columnIndex', 'activeSheet', 'sheet_no', 'sheet_file_no', 'add_tabs', 'excel_columns'))->render();  

          $worksheets[$pass_sheet_no] = [
              'sheetName' => $sheetName,
              'columnIndex' => $columnIndex
          ];

          return response()->json([                
            'status' => 200,
            'message' => 'success',          
            'worksheets' => $worksheets,            
            'worksheet_tab_li_modal' => $worksheet_tab_li_modal,
            'worksheet_tab_content_modal' => $worksheet_tab_content_modal,           
          ]);
        }
        catch (\Exception $e) 
        {           
          /* -- LOG -- */
          $this->commonClass->addLog($this->authUser, 'error-log', 
              [
              'status' => 'Error',
              'controller' => 'Excel Column Template Controller',
              'method' => 'excelColumnMappingTemplateSheet',
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
    /* --end GET /excel-column-templates/sheet/{sheet_no} -- */  

    /* -- GET /excel-column-templates/filenew/{file_no} -- */  
    public function excelColumnTemplatesFileNEW(Request $request, $file_no)
    {
        try
        {
          /* -- GET EXCEL COLUMNS -- */
          $excel_columns = $this->commonClass->listExcelColumns();
          /* --/ GET EXCEL COLUMNS -- */

          $file_no = $request->file_no;
          $sheet_tab_no = 0;
          
          $activeSheet = ($sheet_tab_no == 0) ? true : false;
          $standard = ($sheet_tab_no == 0) ? true : false;
          
          $file_modal = view('_partials._modals.modal-excel-column-template-new-file-repeater', compact('file_no', 'sheet_tab_no', 'activeSheet', 'standard', 'excel_columns'))->render();           

          return response()->json([                
            'status' => 200,
            'message' => 'success',                     
            'file_modal' => $file_modal
          ]);
        }
        catch (\Exception $e) 
        {           
          /* -- LOG -- */
          $this->commonClass->addLog($this->authUser, 'error-log', 
              [
              'status' => 'Error',
              'controller' => 'Excel Column Template Controller',
              'method' => 'excelColumnTemplatesFileNEW',
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
    /* --end GET /excel-column-templates/filenew/{file_no} -- */

    /* -- GET /excel-column-templates/sheetnew/{sheet_no} -- */  
    public function excelColumnTemplatesSheetNEW(Request $request, $sheet_no)
    {
        try
        {
          /* -- GET EXCEL COLUMNS -- */
          $excel_columns = $this->commonClass->listExcelColumns();
          /* --/ GET EXCEL COLUMNS -- */

          $file_no = $request->file_no;
          $sheet_tab_no = $sheet_no;

          $sheet_name = ($request->sheet_name) ? $request->sheet_name : '';
          $header_row = ($request->header_row) ? $request->header_row : '';
          $calc_type = ($request->calc_type) ? $request->calc_type : '';
          
          $activeSheet = ($sheet_tab_no == 0) ? true : false;
          $standard = ($sheet_tab_no == 0) ? true : false;
         
          $worksheet_tab_li_modal = view('_partials._modals.modal-excel-column-template-new-worksheet-tab-li', compact('file_no', 'sheet_tab_no', 'activeSheet', 'standard'))->render(); 

          $worksheet_tab_content_modal = view('_partials._modals.modal-excel-column-template-new-worksheet-tab-content', compact('file_no', 'sheet_tab_no', 'activeSheet', 'standard', 'excel_columns', 'sheet_name', 'header_row', 'calc_type'))->render();  
          
          return response()->json([                
            'status' => 200,
            'message' => 'success',                    
            'worksheet_tab_li_modal' => $worksheet_tab_li_modal,
            'worksheet_tab_content_modal' => $worksheet_tab_content_modal,           
          ]);
        }
        catch (\Exception $e) 
        {           
          /* -- LOG -- */
          $this->commonClass->addLog($this->authUser, 'error-log', 
              [
              'status' => 'Error',
              'controller' => 'Excel Column Template Controller',
              'method' => 'excelColumnMappingTemplateSheetNEW',
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
    /* --end GET /excel-column-templates/sheetnew/{sheet_no} -- */

    /* -- GET /excel-column-templates/rownew/{row_no} -- */  
    public function excelColumnTemplatesRowNEW(Request $request, $row_no)
    {
        try
        {
          /* -- GET EXCEL COLUMNS -- */
          $excel_columns = $this->commonClass->listExcelColumns();
          /* --/ GET EXCEL COLUMNS -- */

          $file_no = $request->file_no;
          $sheet_tab_no = $request->sheet_tab_no;

          $column = ($request->column) ? $request->column : '';
          $columnmapping = ($request->columnmapping) ? $request->columnmapping : '';
          $remarks = ($request->remarks) ? $request->remarks : '';
          $special = ($request->special) ? $request->special : '';
                      
          $row_modal = view('_partials._modals.modal-excel-column-template-new-row-repeater', compact('file_no', 'sheet_tab_no', 'row_no', 'column', 'columnmapping', 'remarks', 'special', 'excel_columns'))->render();  
         
          return response()->json([                
            'status' => 200,
            'message' => 'success',                    
            'row_modal' => $row_modal         
          ]);
        }
        catch (\Exception $e) 
        {           
          /* -- LOG -- */
          $this->commonClass->addLog($this->authUser, 'error-log', 
              [
              'status' => 'Error',
              'controller' => 'Excel Column Template Controller',
              'method' => 'excelColumnTemplatesRowNEW',
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
    /* --end GET /excel-column-templates/rownew/{row_no} -- */
}
