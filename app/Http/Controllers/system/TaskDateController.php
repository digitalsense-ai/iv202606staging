<?php

namespace App\Http\Controllers\system;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

use App\Models\SystemTaskDate;
use App\Models\UserClient;
use App\Models\VATRegistration;

use \App\Classes\CommonClass;

use Mail;

class TaskDateController extends Controller
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
   
    /* -- GET /task dates -- */
    public function loadTaskDates()
    {   
      try
      {    
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
        /* --end PAGE CONFIG -- */
                      
        /* -- GET TASK DATES -- */
        $taskdates = $this->commonClass->getTaskDatesLazy();
        /* --end GET TASK DATES -- */
      
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'taskdate-list');
        /* --end LOG -- */

        /* -- RETURN VIEW -- */
        return view('content.taskdates.index', [
          'pageConfigs' => $pageConfigs, 
          'authUser' => $this->authUser,                                
          'taskdates' => $taskdates
        ]);
        /* --end RETURN VIEW -- */
      }      
      catch (\Exception $e) 
      {           
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'Task Date Controller',
            'method' => 'loadTaskDates',
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
    /* --end GET /task dates -- */

    /* -- POST /task dates -- */
    public function postTaskDate(Request $request)
    {
      try
      {        
        $taskdateID = $request->taskdate_id;

        $system = $this->commonClass->getSystemInfoLazy();
        $system_id=$system->id;

        $_where = [];
        $_fields = [];
        /* -- if HAS TASK DATE EDIT -- */
        if ($taskdateID) 
        {    
          $_where = ['id' => $taskdateID];
          $_fields = [                                                          
            'task_date' => ($request->task_date == 0) ? 0 : Carbon::parse($request->task_date)->format('d'),  
            'task_description' => $request->task_description,          
            'status' => 1,
            'system_id'=>$system_id,
            'updated_by' => $this->authUser->user_id
          ];          
          /* --end UPDATE TASK DATE -- */
        } /* -- else HAS TASK DATE EDIT -- */  
        else 
        { 
          $_where = ['task_name'=>$request->task_name];
          $_fields = [                          
                              
            'task_date' => ($request->task_date == 0) ? 0 : Carbon::parse($request->task_date)->format('d'),
            'task_name' => $request->task_name,
            'task_description' => $request->task_description,
            'status' => 1,
            'system_id'=>$system_id,
            'created_by' => $this->authUser->user_id
          ];
        } /* --end if HAS TASK DATE EDIT -- */
       
        /* -- CREATE/UPDATE TASK DATE -- */
        if($_where)
          $taskdate = SystemTaskDate::updateOrCreate(            
            $_where,
            $_fields
          );
        else
          $taskdate = SystemTaskDate::updateOrCreate(                      
            $_fields
          );
        /* --end CREATE/UPDATE TASK DATE -- */
       
        /* -- GET TASK DATES -- */
        $taskdates = $this->commonClass->getTaskDatesLazy();
        /* --end GET TASK DATES -- */
        
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'taskdate-add',
          [
            'Taskname' => $request->task_name,
            'Taskdate' => $request->task_date,
            'Taskdescription' => $request->task_description
          ]
        );
        /* --end LOG -- */

        /* -- RETURN JSON -- */
        return response()->json([
          'status' => 200,             
          'taskdates' => $taskdates,
          'message' => 'Created'
        ]);
        /* --end RETURN JSON -- */
      }      
      catch (\Exception $e) 
      {           
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'error-log', 
          [
            'status' => 'Error',
            'controller' => 'Task Date Controller',
            'method' => 'postTaskDate',
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
    /* --end POST /task dates -- */

    /* -- GET /taskdates/{taskdate_id}/edit -- */
    public function editTaskdate(Request $request, $taskdate_id)
    {
      try
      {    
        $taskdate = [];    
        if($taskdate_id != 0)
        {
          /* -- GET TASK DATES -- */
          $taskdate = $this->commonClass->getTaskDatesLazy($taskdate_id);
          /* --end GET TASK DATES -- */  

          /* -- LOG -- */
          $this->commonClass->addLog($this->authUser, 'taskdate-edit',
            [
              'Taskdate' => $taskdate->task_name
            ]
          );
          /* --end LOG -- */ 
        }        

        /* -- RETURN JSON -- */
        return response()->json(
          [
            'status' => 200,             
            'taskdate' => $taskdate
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
            'controller' => 'Task Date Controller',
            'method' => 'editTaskdate',
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
    /* --end GET /taskdates/{taskdate_id}/edit -- */

    /* -- DELETE /taskdates/{taskdate_id}/delete -- */
    public function deleteTaskdate(Request $request, $taskdate_id)
    {
      try
      {        
        if($taskdate_id != 0)
        {
          /* -- DELETE TASK DATES -- */
          $taskdate = $this->commonClass->getTaskDatesLazy($taskdate_id);
          
          $taskdate_name = $taskdate->task_name;
          $taskdate->delete();
          /* --end DELETE TASK DATES -- */ 

          /* -- LOG -- */
          $this->commonClass->addLog($this->authUser, 'taskdate-delete',
            [
              'Taskdate' => $taskdate_name
            ]
          );
          /* --end LOG -- */  
        } 

        /* -- GET TASK DATES -- */
        $taskdates = $this->commonClass->getTaskDatesLazy();
        /* --end GET TASK DATES -- */       

        /* -- RETURN JSON -- */
        return response()->json(
          [
            'status' => 200,             
            'taskdates' => $taskdates,
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
            'controller' => 'Task Date Controller',
            'method' => 'deleteTaskdate',
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
    /* --end DELETE /taskdates/{taskdate_id}/delete -- */
}
