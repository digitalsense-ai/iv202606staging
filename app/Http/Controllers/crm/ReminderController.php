<?php

namespace App\Http\Controllers\crm;

use App\Http\Controllers\Controller;
//use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

use App\Models\CRMLead;
use App\Models\CRMQuote;
use App\Models\CRMReminder;

use \App\Classes\CommonClass;

class ReminderController extends Controller
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

    /* -- GET /reminders/ -- */
    public function index(Request $request)
    {       
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
        /* --end PAGE CONFIG -- */

        $reminders = CRMReminder::get();
        foreach($reminders as $reminder)
        {
            if($reminder->module_type == 'lead') 
            {           
                $lead = CRMLead::with(['contact'])->where('id', $reminder->module_id)->first();
                $reminder->lead = $lead;
            }
            else if($reminder->module_type == 'quote')  
            {         
                $quote = CRMQuote::with(['lead', 'lead.contact', 'addons'])->where('id', $reminder->module_id)->first();
                $reminder->quote = $quote;
            }            
        }
        
        /* -- LOG -- */
        $this->commonClass->addLog($this->authUser, 'crm-reminder-list');
        /* --end LOG -- */

        /* -- RETURN VIEW -- */
        return view('content.crm.reminders.index', [
          'pageConfigs' => $pageConfigs, 
          'authUser' => $this->authUser, 
         
          'reminders' => $reminders
        ]);
        /* --end RETURN VIEW -- */
    }
    /* --end GET /reminders/ -- */

    // /**
    //  * Create reminder form
    //  */
    // public function create(Request $request)
    // {
    //     $moduleType = $request->module_type;
    //     $moduleId = $request->module_id;

    //     $module = null;

    //     if ($moduleType == 'lead') {
    //         $module = CRMLead::with('contact')
    //             ->findOrFail($moduleId);
    //     }

    //     if ($moduleType == 'quote') {
    //         $module = CRMQuote::with([
    //                 'lead.contact'
    //             ])
    //             ->findOrFail($moduleId);
    //     }

    //     return view('crm.reminders.create', compact(
    //         'module',
    //         'moduleType'
    //     ));
    // }    

    // /**
    //  * Show reminder details
    //  */
    // public function show(string $id)
    // {
    //     $reminder = CRMReminder::findOrFail($id);

    //     return view('crm.reminders.show', compact(
    //         'reminder'
    //     ));
    // }

    // /**
    //  * Edit reminder
    //  */
    // public function edit(string $id)
    // {
    //     $reminder = CRMReminder::findOrFail($id);        

    //     return view('crm.reminders.edit', compact(
    //         'reminder'
    //     ));
    // }

    // /**
    //  * Update reminder
    //  */
    // public function update(Request $request, string $id)
    // {
    //     $reminder = CRMReminder::findOrFail($id);

    //     $reminder->update([
    //         'reminder_date' => $request->reminder_date,
    //         'reminder_time' => $request->reminder_time,
    //         'notes' => $request->notes,
    //         //'status' => $request->status ?? 'pending'
    //         'email_sent' => 0
    //     ]);

    //     /* -- LOG -- */
    //     $this->commonClass->addLog($this->authUser, 'crm-reminder-edit',
    //         [

    //         ]
    //     );
    //     /* --end LOG -- */

    //     return redirect()
    //         ->route('reminders.index')
    //         ->with('success', 'Reminder updated');
    // }

    // /**
    //  * Delete reminder
    //  */
    // public function destroy(string $id)
    // {
    //     $reminder = CRMReminder::findOrFail($id);

    //     $reminder->delete();

    //     return back()->with(
    //         'success',
    //         'Reminder deleted'
    //     );
    // }  

    // /**
    //  * Mark reminder completed
    //  */
    // public function complete($id)
    // {
    //     $reminder = CRMReminder::findOrFail($id);

    //     $reminder->update([
    //         'status' => 'completed'
    //     ]);

    //     return back()->with(
    //         'success',
    //         'Reminder completed'
    //     );
    // }
}