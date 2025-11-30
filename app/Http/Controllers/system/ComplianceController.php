<?php

namespace App\Http\Controllers\system;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

use \App\Classes\CommonClass;
use \App\Classes\CVRApiClass;

class ComplianceController extends Controller
{
    public $authUser;    
    public $commonClass;
    public $cvrApiClass;
   
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {          
            
            $this->commonClass = new CommonClass();
            $this->cvrApiClass = new CVRApiClass();
            $this->authUser = $this->commonClass->getAuthUser();              
                       
            return $next($request);
        });                   
    }
   
    /* -- GET /compliance-user -- */
    public function complianceUser(Request $request)
    {             
        try 
        {
            /* -- PAGE CONFIG -- */
            $pageConfigs = $this->commonClass->getPageConfig($this->authUser);   
            /* --end PAGE CONFIG -- */   
                       
            /* -- LOG -- */                    
            $this->commonClass->addLog($this->authUser, 'compliance-user-list');
            /* --end LOG -- */
            
            /* -- GET COMPLIANCE USERS -- */
            $matched_users = $this->getComplianceUser();
            /* --end GET COMPLIANCE USERS -- */

            /* -- GET COMPLIANCE USERS -- */
            $matched_cvr_users = $this->getCVRComplianceUser();
            /* --end GET COMPLIANCE USERS -- */

            /* -- RETURN VIEW -- */
            return view('content.compliance.index', ['pageConfigs' => $pageConfigs, 'authUser' => $this->authUser, 'matched_users' => $matched_users, 'matched_cvr_users' => $matched_cvr_users]); 
            /* --end RETURN VIEW -- */              
        } 
        catch (\Exception $e) 
        {
            /* -- GET COMPLIANCE USERS -- */
            $matched_users = $this->getComplianceUser();
            /* --end GET COMPLIANCE USERS -- */

            /* -- GET COMPLIANCE USERS -- */
            $matched_cvr_users = $this->getCVRComplianceUser();
            /* --end GET COMPLIANCE USERS -- */

            /* -- LOG -- */
            $this->commonClass->addLog($this->authUser, 'error-log', 
              [
                'status' => 'Error',
                'controller' => 'Compliance Controller',
                'method' => 'complianceUser',
                'message' => $e->getMessage()
              ]
            );
            /* --end LOG -- */

            /* -- RETURN JSON -- */
            return response()->json([   
              'status' => 'Error',        
              'matched_users' => $matched_users,
              'matched_cvr_users' => $matched_cvr_users,
              'message' => $e->getMessage()
            ]);
            /* --end RETURN JSON -- */ 
        } 
    }  
    /* --end GET /compliance-user -- */ 

    /* -- POST compliance -- */
    public function readComplianceFile(Request $request)
    {     
        try    
        {   
            if($request->file_type == 'split')
            {       
                $filename = $request->file['name'];        
                $compliance = $this->commonClass->readComplianceFile($filename, $request->file_type);

                /* -- DELETE SPLITTED FILE -- */
                $delete_file = $this->deleteSplittedFiles($filename);
                /* --end DELETE SPLITTED FILE -- */

                if($request->file['last_file'] == "true")
                {  
                    /* -- GET COMPLIANCE USERS -- */
                    $matched_users = $this->getComplianceUser();
                    /* --end GET COMPLIANCE USERS -- */
                    
                    /* -- GET COMPLIANCE USERS -- */
                    $matched_cvr_users = $this->getCVRComplianceUser();
                    /* --end GET COMPLIANCE USERS -- */

                    /* -- RETURN JSON -- */            
                    return response()->json([   
                      'status' => 'success',        
                      'matched_users' => $matched_users, 
                      'matched_cvr_users' => $matched_cvr_users,
                      'message' => 'uploaded'            
                    ],200);
                    /* --end RETURN JSON -- */
                }
                /* -- RETURN JSON -- */            
                return response()->json([   
                  'status' => 'success',
                  'message' => 'uploaded'            
                ],200);
                /* --end RETURN JSON -- */
            } /* --end if SPLIT FILES -- */
            else
            {           
                $file = $request->file('file');
                $extension = (strpos($file, "https://") !== false) ? '' : $file->getClientOriginalExtension();
                $file_size = ($file->getSize()/1024)/1024;

                if($extension == 'csv' && $file_size > 5)
                {
                    $split_files = $this->commonClass->split_file($file);
            
                    /* -- RETURN JSON -- */            
                    return response()->json([   
                      'status' => 'success',                          
                      'message' => 'splitted'            
                    ],200);
                    /* --end RETURN JSON -- */
                }
                else
                {                    
                    $compliance = $this->commonClass->readComplianceFile($request->file('file'));
                            
                    $this->commonClass->addLog($this->authUser, 'compliance-read-file');

                    /* -- GET COMPLIANCE USERS -- */
                    $matched_users = $this->getComplianceUser();
                    /* --end GET COMPLIANCE USERS -- */
                    
                    /* -- GET COMPLIANCE USERS -- */
                    $matched_cvr_users = $this->getCVRComplianceUser();
                    /* --end GET COMPLIANCE USERS -- */

                    /* -- RETURN JSON -- */            
                    return response()->json([   
                      'status' => 'success',        
                      'matched_users' => $matched_users, 
                      'matched_cvr_users' => $matched_cvr_users,
                      'message' => 'uploaded'            
                    ],200);
                    /* --end RETURN JSON -- */
                }
            }
        }
        catch (\Exception $e) 
        {
            /* -- GET COMPLIANCE USERS -- */
            $matched_users = $this->getComplianceUser();
            /* --end GET COMPLIANCE USERS -- */

            /* -- GET COMPLIANCE USERS -- */
            $matched_cvr_users = $this->getCVRComplianceUser();
            /* --end GET COMPLIANCE USERS -- */

            /* -- LOG -- */
            $this->commonClass->addLog($this->authUser, 'error-log', 
              [
                'status' => 'Error',
                'controller' => 'Compliance Controller',
                'method' => 'readComplianceFile',
                'message' => $e->getMessage()
              ]
            );
            /* --end LOG -- */

            /* -- RETURN JSON -- */
            return response()->json([   
              'status' => 'Error',        
              'matched_users' => $matched_users,
              'matched_cvr_users' => $matched_cvr_users,
              'message' => $e->getMessage()
            ]);
            /* --end RETURN JSON -- */ 
        }
    }
    /* --end POST compliance -- */

    /* -- GET splitted-files -- */
    public function getSplittedFiles()
    {     
        try    
        {              
            $directory = storage_path('app/public/splits/');
            
            /* -- GET SPLITTED FILES -- */
            $files = [];
            foreach (scandir($directory) as $file)
            {
                if ($file !== '.' && $file !== '..')
                {                    
                    $size = filesize($directory.$file);
                    array_push($files, ['name' => $file, 'size' => $size]);
                } /* --end if FILE -- */     
            } /* --end for FILES -- */         
            /* --end GET SPLITTED FILES -- */
           
            /* -- RETURN JSON -- */            
            return response()->json([   
              'status' => 'success',        
              'files' => $files, 
              'message' => 'splitted files'            
            ],200);
            /* --end RETURN JSON -- */            
        }
        catch (\Exception $e) 
        {            
            /* -- LOG -- */
            $this->commonClass->addLog($this->authUser, 'error-log', 
              [
                'status' => 'Error',
                'controller' => 'Compliance Controller',
                'method' => 'getSplittedFiles',
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
    /* --end GET splitted-files -- */

    /* -- DELETE splitted-files -- */
    public function deleteSplittedFiles($file)
    {     
        try    
        {             
            $directory = storage_path('app/public/splits/');
            
            $filePath = $directory.$file;

            /* -- DELETE SPLITTED FILES -- */            
            if (File::exists($filePath))            
                File::delete($filePath);            
            /* --end DELETE SPLITTED FILES -- */                            
        }
        catch (\Exception $e) 
        {            
            /* -- LOG -- */
            $this->commonClass->addLog($this->authUser, 'error-log', 
              [
                'status' => 'Error',
                'controller' => 'Compliance Controller',
                'method' => 'deleteSplittedFiles',
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
    /* --end GET splitted-files -- */

    /* -- compliance user -- */
    public function getComplianceUser()
    {
        try
        {
            /* -- GET COMPLIANCE USERS -- */
            $with_users = ['dvuser', 'roles', 'userclient', 'userclient.client'];
            $where_users = []; 
            $whereHas_users = [
              'dvuser' => ['field' => 'is_compliance', 'value' => 1],
              'dvuser' => ['field' => 'is_deleted', 'value' => 0]
            ];
            $orderBy_users = [];           
            $matched_users = $this->commonClass->getLazy('user', $with_users, $where_users, $whereHas_users, $orderBy_users);   
            /* --end GET COMPLIANCE USERS -- */

            /* -- RETURN -- */
            return $matched_users;
            /* --end RETURN -- */ 
        }
        catch (\Exception $e) 
        {
            /* -- GET COMPLIANCE USERS -- */
            $matched_users = [];
            /* --end GET COMPLIANCE USERS -- */

            /* -- LOG -- */
            $this->commonClass->addLog($this->authUser, 'error-log', 
              [
                'status' => 'Error',
                'controller' => 'Compliance Controller',
                'method' => 'getComplianceUser',
                'message' => $e->getMessage()
              ]
            );
            /* --end LOG -- */

            /* -- RETURN JSON -- */
            return response()->json([   
              'status' => 'Error',        
              'matched_users' => [],
              'message' => $e->getMessage()
            ]);
            /* --end RETURN JSON -- */ 
        }
    }
    /* --end compliance user -- */  

    /* -- compliance user -- */
    public function getCVRComplianceUser()
    {
        try
        {           
            /* -- GET CVR USERS -- */
            $with_cvr_users = ['client'];
            $where_cvr_users = [
              'is_compliance' => ['operator' => '=', 'value' => 1]
            ]; 
            $whereHas_cvr_users = [];            
            $orderBy_cvr_users = [];           
            $matched_cvr_users = $this->commonClass->getLazy('clientcvr', $with_cvr_users, $where_cvr_users, $whereHas_cvr_users, $orderBy_cvr_users);             
            /* --end GET CVR USERS -- */
                return $matched_cvr_users;
            /* --end RETURN -- */ 
        }
        catch (\Exception $e) 
        {
            /* -- GET COMPLIANCE USERS -- */
            $matched_cvr_users = [];
            /* --end GET COMPLIANCE USERS -- */

            /* -- LOG -- */
            $this->commonClass->addLog($this->authUser, 'error-log', 
              [
                'status' => 'Error',
                'controller' => 'Compliance Controller',
                'method' => 'getCVRComplianceUser',
                'message' => $e->getMessage()
              ]
            );
            /* --end LOG -- */

            /* -- RETURN JSON -- */
            return response()->json([   
              'status' => 'Error',        
              'matched_cvr_users' => [],
              'message' => $e->getMessage()
            ]);
            /* --end RETURN JSON -- */ 
        }
    }
    /* --end compliance user -- */  

    /* -- GET /vatnos -- */     
    public function loadVatNos(Request $request)
    {
      try { 
        /* -- GET CLIENT VAT NOS (DK)-- */                
         $vatnos = $this->commonClass->getCompanyLazy(null, true);
        /* -- GET CLIENT VAT NOS (DK) -- */   

        return $vatnos;
      }
      catch (Exception $e) {
        return  $e->getMessage();
      }
    }
    /* --end GET /vatnos -- */    

    /**
     * Get All CVR Company
     *
     * @param  
     * @return \Illuminate\Http\Response
     */
    public function loadCVRCompany(Request $request)
    {             
        $cvr_client_details = $this->cvrApiClass->getCVRCompany($request->vat_no, $request->client_id); 
       
        return response()->json([   
            'status' => 200,        
            'cvr_client_details' => ($cvr_client_details) ? $cvr_client_details : null, 
            'client_name' => ($cvr_client_details) ? $cvr_client_details->person_name : null, 
            'message' => 'updated'            
        ]);             
    }         
}
