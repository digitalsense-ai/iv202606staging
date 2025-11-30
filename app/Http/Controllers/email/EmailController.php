<?php

namespace App\Http\Controllers\email;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Mail\Markdown;
use Illuminate\Support\Facades\App;

use \App\Classes\CommonClass;

class EmailController extends Controller
{  
    public $authUser;

    public $commonClass;

    public $emailTemplates;
    
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {                    
            $this->commonClass = new CommonClass();
            $this->authUser = $this->commonClass->getAuthUser();   

            $this->emailTemplates = $this->emailTemplateData();   
           
            return $next($request);
        });
    }
        
    public function emailTemplateData()
    {
      $availLocale = ['en' => '', 'dk' => 'dk'];

      $emailTemplates = [];
      foreach($availLocale as $key => $locale)
      {        
        $emailKey = ($locale == '') ? '' : $locale.'-';

        $emailLangTemplates = [
          $emailKey.'newuser' => [             
              'data' =>  [             
                  'subject' => __('User created', [], $key),  
                  'lang' => $key,
                  'app_name' => '[app_name]',
                  'user' => [
                    'firstname' => '[firstname]',      
                    'lastname' => '[lastname]',            
                    'password' => '[password]',  
                    'email' => '[email]',    
                    'role' => '[role]',         
                  ]                  
              ],      
              'template_lang' => strtoupper($key),
              'template_name' => __('New User Email', [], $key),
              'template' => 'emails.'.$emailKey.'newuser',
              'template_edit' => 'emails.edit.'.$emailKey.'newuser'
          ],
          $emailKey.'draft' => [
              'data' =>  [             
                  'subject' => '[subject]',
                  'lang' => $key,
                  'app_name' => '[app_name]',
                  'client' => [
                    'client_name' => '[client_name]',            
                    'client_firstname' => '[client_firstname]',          
                    'client_lastname' => '[client_lastname]',              
                    'team_user_firstname' => '[team_user_firstname]',     
                    'team_user_designation' => '[team_user_designation]',
                    'currency_code' => '[currency_code]',
                    'sale' => [],
                    'purchase' => []
                  ],            
                  'url' => '[url]',
                  'message' => '[email_message]',
                  'attachment' => []                   
              ],   
              'template_lang' => strtoupper($key),
              'template_name' => __('Approve Numbers Email', [], $key),
              'template' => 'emails.'.$emailKey.'draft',
              'template_edit' => 'emails.edit.'.$emailKey.'draft'
          ],
          $emailKey.'pivs' => [
              'data' =>  [                               
                  'subject' => '[subject]',        
                  'lang' => $key,  
                  'app_name' => '[app_name]',
                  'client' => [
                    'client_name' => '[client_name]',
                    'client_firstname' => '[client_firstname]',          
                    'client_lastname' => '[client_lastname]',
                    'team_user_firstname' => '[team_user_firstname]',
                    'team_user_designation' => '[team_user_designation]',
                  ],
                  'message' => '[email_message]',                  
                  'attachment' => [],
                  'align' => 'left'
              ],      
              'template_lang' => strtoupper($key),
              'template_name' => __('Postponed import VAT statement Email', [], $key),
              'template' => 'emails.'.$emailKey.'pivs',
              'template_edit' => 'emails.edit.'.$emailKey.'pivs'
          ],
          $emailKey.'documents' => [
              'data' =>  [                               
                  'subject' => '[subject]',  
                  'lang' => $key,   
                  'app_name' => '[app_name]',   
                  'client' => [
                    'client_name' => '[client_name]',
                    'client_firstname' => '[client_firstname]',          
                    'client_lastname' => '[client_lastname]',
                    'team_user_firstname' => '[team_user_firstname]',
                    'team_user_designation' => '[team_user_designation]',
                  ],
                  'message' => '[email_message]',                  
                  'attachment' => [],
                  'align' => 'left'
              ],       
              'template_lang' => strtoupper($key),
              'template_name' => __('Documents Email', [], $key),
              'template' => 'emails.'.$emailKey.'documents',
              'template_edit' => 'emails.edit.'.$emailKey.'documents'
          ],
          $emailKey.'c79' => [
              'data' =>  [                               
                  'subject' => '[subject]',      
                  'lang' => $key,  
                  'app_name' => '[app_name]',
                  'client' => [
                    'client_name' => '[client_name]',
                    'client_firstname' => '[client_firstname]',          
                    'client_lastname' => '[client_lastname]',
                    'team_user_firstname' => '[team_user_firstname]',
                    'team_user_designation' => '[team_user_designation]',
                  ],
                  'message' => '[email_message]',                  
                  'attachment' => [],
                  'align' => 'left'
              ],         
              'template_lang' => strtoupper($key),
              'template_name' => __('C79 Email', [], $key),
              'template' => 'emails.'.$emailKey.'c79',
              'template_edit' => 'emails.edit.'.$emailKey.'c79'
          ],
          // $emailKey.'comments' => [
          //     'data' =>  [                               
          //         'subject' => '[subject]',  
          //         'lang' => $key, 
          //         'app_name' => '[app_name]',
          //         'client' => [
          //           'client_name' => '[client_name]',
          //           'client_firstname' => '[client_firstname]',          
          //           'client_lastname' => '[client_lastname]',
          //           'team_user_firstname' => '[team_user_firstname]',
          //           'team_user_designation' => '[team_user_designation]',
          //         ],
          //         'message' => '[email_message]',                  
          //         'attachment' => [],
          //         'align' => 'left'
          //     ],      
          //     'template_lang' => strtoupper($key),
          //     'template_name' => __('Comments Email', [], $key),
          //     'template' => 'emails.'.$emailKey.'comments',
          //     'template_edit' => 'emails.edit.'.$emailKey.'comments'
          // ],
          $emailKey.'reopen' => [
              'data' =>  [                               
                  'subject' => '[subject]',  
                  'lang' => $key, 
                  'app_name' => '[app_name]',
                  'client' => [
                    'client_name' => '[client_name]',
                    'client_firstname' => '[client_firstname]',          
                    'client_lastname' => '[client_lastname]',
                    'team_user_firstname' => '[team_user_firstname]',
                    'team_user_designation' => '[team_user_designation]',
                  ],
                  'message' => '[email_message]',                  
                  'attachment' => [],
                  'align' => 'left'
              ],      
              'template_lang' => strtoupper($key),
              'template_name' => __('Re-open VAT Return Folder Email', [], $key),
              'template' => 'emails.'.$emailKey.'reopen',
              'template_edit' => 'emails.edit.'.$emailKey.'reopen'
          ],
          $emailKey.'importvatfile' => [
              'data' =>  [                               
                  'subject' => '[subject]',   
                  'lang' => $key,  
                  'app_name' => '[app_name]',
                  'client' => [
                    'client_name' => '[client_name]',
                    'client_firstname' => '[client_firstname]',          
                    'client_lastname' => '[client_lastname]',
                    'team_user_firstname' => '[team_user_firstname]',
                    'team_user_designation' => '[team_user_designation]',
                  ],
                  'message' => '[email_message]',                  
                  'attachment' => [],
                  'align' => 'left'
              ],  
              'template_lang' => strtoupper($key),
              //'template_name' => __('Import VAT File Email' . ' - NO', [], $key),
              'template_name' => __('Customs declaration', [], $key),
              'template' => 'emails.'.$emailKey.'importvatfile',
              'template_edit' => 'emails.edit.'.$emailKey.'importvatfile'
          ],
          $emailKey.'cashaccountstatement' => [
              'data' =>  [                               
                  'subject' => '[subject]',    
                  'lang' => $key,   
                  'app_name' => '[app_name]',
                  'client' => [
                    'client_name' => '[client_name]',
                    'client_firstname' => '[client_firstname]',          
                    'client_lastname' => '[client_lastname]',
                    'team_user_firstname' => '[team_user_firstname]',
                    'team_user_designation' => '[team_user_designation]',
                  ],
                  'message' => '[email_message]',                  
                  'attachment' => [],
                  'align' => 'left'
              ], 
              'template_lang' => strtoupper($key),
              'template_name' => __('Cash Account Statement Email', [], $key),
              'template' => 'emails.'.$emailKey.'cashaccountstatement',
              'template_edit' => 'emails.edit.'.$emailKey.'cashaccountstatement'
          ],
          $emailKey.'dutydefermentaccount' => [
              'data' =>  [                               
                  'subject' => '[subject]',    
                  'lang' => $key, 
                  'app_name' => '[app_name]',
                  'client' => [
                    'client_name' => '[client_name]',
                    'client_firstname' => '[client_firstname]',          
                    'client_lastname' => '[client_lastname]',
                    'team_user_firstname' => '[team_user_firstname]',
                    'team_user_designation' => '[team_user_designation]',
                  ],
                  'message' => '[email_message]',                  
                  'attachment' => [],
                  'align' => 'left'
              ], 
              'template_lang' => strtoupper($key),
              'template_name' => __('Duty Deferment Account Email', [], $key),
              'template' => 'emails.'.$emailKey.'dutydefermentaccount',
              'template_edit' => 'emails.edit.'.$emailKey.'dutydefermentaccount'
          ],
          $emailKey.'lockgb' => [
              'data' =>  [                              
                  'vat_heading' => '[Vat Reg. Heading]',
                  'subject' => '[subject]',
                  'lang' => $key,
                  'app_name' => '[app_name]',
                  'payment_date' => '[payment_date]',
                  'client' => [
                    'client_name' => '[client_name]',
                    'client_firstname' => '[client_firstname]',          
                    'client_lastname' => '[client_lastname]',
                    'team_user_firstname' => '[team_user_firstname]',
                    'team_user_designation' => '[team_user_designation]',
                  ],   
                  'payment_info' => [                
                    'bankname' => '[bankname]',
                    'address' => '[address]',
                    'city' => '[city]',
                    'country' => '[country]',
                    'postcode' => '[postcode]',
                    'sortcode' => '[sortcode]',
                    'accountno' => '[accountno]',
                    'accountname' => '[accountname]',
                    'paymentref' => '[paymentref]',
                    'bic' => '[bic]',
                    'iban' => '[iban]'
                  ], 
                  'message' => '[email_message]',
                  'attachment' => [],
                  'align' => 'left'
              ],   
              'template_lang' => strtoupper($key),
              'template_name' => __('Complete Task Email - GB', [], $key),
              'template' => 'emails.'.$emailKey.'lockgb',
              'template_edit' => 'emails.edit.'.$emailKey.'lockgb'
          ],
          $emailKey.'lockno' => [
              'data' =>  [                              
                  'vat_heading' => '[Vat Reg. Heading]',
                  'subject' => '[subject]',
                  'lang' => $key,
                  'app_name' => '[app_name]',
                  'payment_date' => '[payment_date]',
                  'client' => [
                    'client_name' => '[client_name]',
                    'client_firstname' => '[client_firstname]',          
                    'client_lastname' => '[client_lastname]',
                    'team_user_firstname' => '[team_user_firstname]',
                    'team_user_designation' => '[team_user_designation]',
                  ],   
                  'payment_info' => [                
                    'bankname' => '[bankname]',
                    'address' => '[address]',
                    'city' => '[city]',
                    'country' => '[country]',
                    'postcode' => '[postcode]',
                    'sortcode' => '[sortcode]',
                    'accountno' => '[accountno]',
                    'accountname' => '[accountname]',
                    'paymentref' => '[paymentref]',
                    'bic' => '[bic]',
                    'iban' => '[iban]'
                  ], 
                  'message' => '[email_message]',
                  'attachment' => [],
                  'align' => 'left'
              ],   
              'template_lang' => strtoupper($key),
              'template_name' => __('Complete Task Email - NO', [], $key),
              'template' => 'emails.'.$emailKey.'lockno',
              'template_edit' => 'emails.edit.'.$emailKey.'lockno'
          ],
          $emailKey.'reminder-nodatainfolder' => [
              'data' =>  [                                                
                  'subject' => '[subject]',
                  'lang' => $key,
                  'app_name' => '[app_name]',                  
                  'user_firstname' => '[user_firstname]',          
                  'user_lastname' => '[user_lastname]',
                  'sender_firstname' => '[sender_firstname]',
                  'sender_designation' => '[sender_designation]',
                  'message' => '[email_message]',
                  'attachment' => [],
                  'align' => 'left'
              ],   
              'template_lang' => strtoupper($key),
              'template_name' => __('Reminder', [], $key) . ': ' . __('No data in folder', [], $key),
              'template' => 'emails.'.$emailKey.'reminder-nodatainfolder',
              'template_edit' => 'emails.edit.'.$emailKey.'reminder-nodatainfolder'
          ],
          $emailKey.'reminder-uploadmissed' => [
              'data' =>  [                                                
                  'subject' => '[subject]',
                  'lang' => $key,
                  'app_name' => '[app_name]',                  
                  'user_firstname' => '[user_firstname]',          
                  'user_lastname' => '[user_lastname]',
                  'sender_firstname' => '[sender_firstname]',
                  'sender_designation' => '[sender_designation]',                     
                  'message' => '[email_message]',
                  'attachment' => [],
                  'align' => 'left'
              ],   
              'template_lang' => strtoupper($key),
              'template_name' => __('Reminder', [], $key) . ': ' . __('Upload missed', [], $key),
              'template' => 'emails.'.$emailKey.'reminder-uploadmissed',
              'template_edit' => 'emails.edit.'.$emailKey.'reminder-uploadmissed'
          ],
          $emailKey.'reminder-pivsnotuploaded' => [
              'data' =>  [                                                
                  'subject' => '[subject]',
                  'lang' => $key,
                  'app_name' => '[app_name]',                  
                  'user_firstname' => '[user_firstname]',          
                  'user_lastname' => '[user_lastname]',
                  'sender_firstname' => '[sender_firstname]',
                  'sender_designation' => '[sender_designation]',                 
                  'message' => '[email_message]',
                  'attachment' => [],
                  'align' => 'left'
              ],   
              'template_lang' => strtoupper($key),
              'template_name' => __('Reminder', [], $key) . ': ' . __('Pivs not uploaded', [], $key),
              'template' => 'emails.'.$emailKey.'reminder-pivsnotuploaded',
              'template_edit' => 'emails.edit.'.$emailKey.'reminder-pivsnotuploaded'
          ],
          $emailKey.'reminder-casnotuploaded' => [
              'data' =>  [                                                
                  'subject' => '[subject]',
                  'lang' => $key,
                  'app_name' => '[app_name]',                  
                  'user_firstname' => '[user_firstname]',          
                  'user_lastname' => '[user_lastname]',
                  'sender_firstname' => '[sender_firstname]',
                  'sender_designation' => '[sender_designation]',                  
                  'message' => '[email_message]',
                  'attachment' => [],
                  'align' => 'left'
              ],   
              'template_lang' => strtoupper($key),
              'template_name' => __('Reminder', [], $key) . ': ' . __('Cash Account Statement not uploaded', [], $key),
              'template' => 'emails.'.$emailKey.'reminder-casnotuploaded',
              'template_edit' => 'emails.edit.'.$emailKey.'reminder-casnotuploaded'
          ],
          $emailKey.'reminder-ddanotuploaded' => [
              'data' =>  [                                                
                  'subject' => '[subject]',
                  'lang' => $key,
                  'app_name' => '[app_name]',                  
                  'user_firstname' => '[user_firstname]',          
                  'user_lastname' => '[user_lastname]',
                  'sender_firstname' => '[sender_firstname]',
                  'sender_designation' => '[sender_designation]',                  
                  'message' => '[email_message]',
                  'attachment' => [],
                  'align' => 'left'
              ],   
              'template_lang' => strtoupper($key),
              'template_name' => __('Reminder', [], $key) . ': ' . __('Duty Deferment Account not uploaded', [], $key),
              'template' => 'emails.'.$emailKey.'reminder-ddanotuploaded',
              'template_edit' => 'emails.edit.'.$emailKey.'reminder-ddanotuploaded'
          ],
          $emailKey.'reminder-general' => [
              'data' =>  [                                                
                  'subject' => '[subject]',
                  'lang' => $key,
                  'app_name' => '[app_name]',                  
                  'user_firstname' => '[user_firstname]',          
                  'user_lastname' => '[user_lastname]',
                  'sender_firstname' => '[sender_firstname]',
                  'sender_designation' => '[sender_designation]',                  
                  'message' => '[email_message]',
                  'attachment' => [],
                  'align' => 'left'
              ],   
              'template_lang' => strtoupper($key),
              'template_name' => __('Reminder', [], $key) . ': ' . __('General Reminder', [], $key),
              'template' => 'emails.'.$emailKey.'reminder-general',
              'template_edit' => 'emails.edit.'.$emailKey.'reminder-general'
          ]          
        ];

        $emailTemplates = array_merge($emailTemplates, $emailLangTemplates);
      }
      
      return $emailTemplates; 
    } 

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */    
    public function index(Request $request)
    {
      try {  
          $pageConfigs = $this->commonClass->getPageConfig($this->authUser);  
          
          return view('content.emailtemplate.index', ['pageConfigs' => $pageConfigs, 'authUser' => $this->authUser, 'emailTemplates' => $this->emailTemplates]);
      }
      catch (Exception $e) {
        return  $e->getMessage();
      }
    }    

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */    
    public function show(Request $request, $emailtype)
    {
      try {            
          $markdown = new Markdown(view(), config('mail.markdown'));
          $data = $this->emailTemplates[$emailtype]['data'];
          if($request->query('type'))
            return $markdown->render($this->emailTemplates[$emailtype]['template_edit'], compact('data'));
          else
            return $markdown->render($this->emailTemplates[$emailtype]['template'], compact('data'));
      }
      catch (Exception $e) {
        return  $e->getMessage();
      }
    } 

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */    
    public function update(Request $request, $emailtype)
    {
      try {  
          $editor = $request->text_quill;
          $arr_emailtype = explode('-',$emailtype);
          $emaillang = (count($arr_emailtype) > 1) ? $arr_emailtype[0] : 'en';
                   
          if(strpos($emailtype, "newuser") !== false)
          {
            $replaceeditor = str_replace(
              array(
                "[firstname]", 
                "[lastname]", 
                "[password]", 
                "[email]", 
                "[role]",               
                "[app_name]"
              ),
              array(
                "{{ \$data['user']['firstname'] }}", 
                "{{ \$data['user']['lastname'] }}", 
                "{{ \$data['user']['password'] }}", 
                "{{ \$data['user']['email'] }}", 
                "{{ \$data['user']['role'] }}",               
                "{{ \$data['app_name'] }}",
              ),           
              $editor);
          
            $subject_line = __('New User', [], $emaillang);          
            $emailtemplate = '<x-mail::message :subject="\''.$subject_line.'\'" :lang="\''.$emaillang.'\'">'. $replaceeditor .'</x-mail::message>';
          }        
          else if(strpos($emailtype, "draft") !== false)
          {
            $replaceeditor = str_replace(
                array(
                  "[subject]", 
                  "[client_name]", 
                  "[client_firstname]", 
                  "[client_lastname]", 
                  "[team_user_firstname]",
                  "[team_user_designation]", 
                  "[app_name]",                 
                  "[email_message]", 
                  "%5Burl%5D"
                ),
                array(
                  "{{ \$data['subject'] }}", 
                  "{{ \$data['client']['client_name'] }}", 
                  "{{ \$data['client']['client_firstname'] }}", 
                  "{{ \$data['client']['client_lastname'] }}", 
                  "{{ \$data['client']['team_user_firstname'] }}", 
                  "{{ \$data['client']['team_user_designation'] }}",
                  "{{ \$data['app_name'] }}",                  
                  "{{ \$data['message'] }}", 
                  "{{ \$data['url'] }}"
                ),
                $editor);

            $subject_line = __('New VAT report for approval', [], $emaillang);
            $emailtemplate = '<x-mail::message :subject="\''.$subject_line.' (\'.$data[\'subject\'].\')\'" :lang="\''.$emaillang.'\'">'. $replaceeditor .'</x-mail::message>';
          }
          else if(strpos($emailtype, "pivs") !== false)
          {
            $replaceeditor = str_replace(
                array(                 
                  "[subject]", 
                  "[client_name]",                   
                  "[client_firstname]", 
                  "[client_lastname]", 
                  "[team_user_firstname]", 
                  "[team_user_designation]", 
                  "[app_name]",                 
                  "[email_message]"
                ),
                array(                  
                  "{{ \$data['subject'] }}", 
                  "{{ \$data['client']['client_name'] }}", 
                  "{{ \$data['client']['client_firstname'] }}", 
                  "{{ \$data['client']['client_lastname'] }}", 
                  "{{ \$data['client']['team_user_firstname'] }}", 
                  "{{ \$data['client']['team_user_designation'] }}",
                  "{{ \$data['app_name'] }}",                  
                  "{{ \$data['message'] }}"      
                ),
                $editor);

            $subject_line = __('Postponed import VAT statement', [], $emaillang);
            $emailtemplate = '<x-mail::message :subject="\''.$subject_line.' (\'.$data[\'subject\'].\')\'" :lang="\''.$emaillang.'\'">'. $replaceeditor .'</x-mail::message>';
          }
          else if(strpos($emailtype, "documents") !== false)
          {
            $replaceeditor = str_replace(
                array(                  
                  "[subject]", 
                  "[client_name]",                   
                  "[client_firstname]", 
                  "[client_lastname]", 
                  "[team_user_firstname]", 
                  "[team_user_designation]",
                  "[app_name]",                 
                  "[email_message]"                 
                ),
                array(                 
                  "{{ \$data['subject'] }}", 
                  "{{ \$data['client']['client_name'] }}", 
                  "{{ \$data['client']['client_firstname'] }}", 
                  "{{ \$data['client']['client_lastname'] }}", 
                  "{{ \$data['client']['team_user_firstname'] }}", 
                  "{{ \$data['client']['team_user_designation'] }}",
                  "{{ \$data['app_name'] }}",                  
                  "{{ \$data['message'] }}"  
                ),
                $editor);

            $subject_line = __('Documents', [], $emaillang);
            $emailtemplate = '<x-mail::message :subject="\''.$subject_line.' (\'.$data[\'subject\'].\')\'" :lang="\''.$emaillang.'\'">'. $replaceeditor .'</x-mail::message>';
          }
          else if(strpos($emailtype, "c79") !== false)
          {
            $replaceeditor = str_replace(
                array(                  
                  "[subject]", 
                  "[client_name]",                   
                  "[client_firstname]", 
                  "[client_lastname]", 
                  "[team_user_firstname]", 
                  "[team_user_designation]",
                  "[app_name]",                 
                  "[email_message]"           
                ),
                array(                 
                  "{{ \$data['subject'] }}", 
                  "{{ \$data['client']['client_name'] }}", 
                  "{{ \$data['client']['client_firstname'] }}", 
                  "{{ \$data['client']['client_lastname'] }}", 
                  "{{ \$data['client']['team_user_firstname'] }}", 
                  "{{ \$data['client']['team_user_designation'] }}",
                  "{{ \$data['app_name'] }}",                
                  "{{ \$data['message'] }}"
                ),
                $editor);

            $subject_line = __('C79', [], $emaillang);
            $emailtemplate = '<x-mail::message :subject="\''.$subject_line.' (\'.$data[\'subject\'].\')\'" :lang="\''.$emaillang.'\'">'. $replaceeditor .'</x-mail::message>';
          }
          // else if(strpos($emailtype, "comments") !== false)
          // {
          //   $replaceeditor = str_replace(
          //       array(                  
          //         "[subject]", 
          //         "[client_name]",                   
          //         "[client_firstname]", 
          //         "[client_lastname]", 
          //         "[team_user_firstname]", 
          //         "[team_user_designation]",
          //         "[app_name]",                 
          //         "[email_message]"                
          //       ),
          //       array(                  
          //         "{{ \$data['subject'] }}", 
          //         "{{ \$data['client']['client_name'] }}", 
          //         "{{ \$data['client']['client_firstname'] }}", 
          //         "{{ \$data['client']['client_lastname'] }}", 
          //         "{{ \$data['client']['team_user_firstname'] }}", 
          //         "{{ \$data['client']['team_user_designation'] }}",
          //         "{{ \$data['app_name'] }}",                 
          //         "{{ \$data['message'] }}"
          //       ),
          //       $editor);

          //   $subject_line = __('Comments', [], $emaillang);
          //   $emailtemplate = '<x-mail::message :subject="\''.$subject_line.' (\'.$data[\'subject\'].\')\'" :lang="\''.$emaillang.'\'">'. $replaceeditor .'</x-mail::message>';
          // }
          else if(strpos($emailtype, "reopen") !== false)
          {
            $replaceeditor = str_replace(
                array(                  
                  "[subject]", 
                  "[client_name]",                   
                  "[client_firstname]", 
                  "[client_lastname]", 
                  "[team_user_firstname]", 
                  "[team_user_designation]",
                  "[app_name]",                 
                  "[email_message]"                
                ),
                array(                  
                  "{{ \$data['subject'] }}", 
                  "{{ \$data['client']['client_name'] }}", 
                  "{{ \$data['client']['client_firstname'] }}", 
                  "{{ \$data['client']['client_lastname'] }}", 
                  "{{ \$data['client']['team_user_firstname'] }}", 
                  "{{ \$data['client']['team_user_designation'] }}",
                  "{{ \$data['app_name'] }}",                 
                  "{{ \$data['message'] }}"
                ),
                $editor);

            $subject_line = __('Re-open', [], $emaillang);
            $emailtemplate = '<x-mail::message :subject="\''.$subject_line.' (\'.$data[\'subject\'].\')\'" :lang="\''.$emaillang.'\'">'. $replaceeditor .'</x-mail::message>';
          }
          else if(strpos($emailtype, "importvatfile") !== false)
          {
            $replaceeditor = str_replace(
                array(                 
                  "[subject]", 
                  "[client_name]",                   
                  "[client_firstname]", 
                  "[client_lastname]", 
                  "[team_user_firstname]", 
                  "[team_user_designation]", 
                  "[app_name]",                
                  "[email_message]"                
                ),
                array(                 
                  "{{ \$data['subject'] }}", 
                  "{{ \$data['client']['client_name'] }}", 
                  "{{ \$data['client']['client_firstname'] }}", 
                  "{{ \$data['client']['client_lastname'] }}", 
                  "{{ \$data['client']['team_user_firstname'] }}",
                  "{{ \$data['client']['team_user_designation'] }}", 
                  "{{ \$data['app_name'] }}",                 
                  "{{ \$data['message'] }}"
                ),
                $editor);

            //$subject_line = __('Import VAT Files', [], $emaillang);
            $subject_line = __('Customs declaration', [], $emaillang);
            $emailtemplate = '<x-mail::message :subject="\''.$subject_line.' (\'.$data[\'subject\'].\')\'" :lang="\''.$emaillang.'\'">'. $replaceeditor .'</x-mail::message>';
          }
          else if(strpos($emailtype, "cashaccountstatement") !== false)
          {
            $replaceeditor = str_replace(
                array(                 
                  "[subject]", 
                  "[client_name]",                   
                  "[client_firstname]", 
                  "[client_lastname]", 
                  "[team_user_firstname]", 
                  "[team_user_designation]", 
                  "[app_name]",               
                  "[email_message]"              
                ),
                array(                  
                  "{{ \$data['subject'] }}", 
                  "{{ \$data['client']['client_name'] }}", 
                  "{{ \$data['client']['client_firstname'] }}", 
                  "{{ \$data['client']['client_lastname'] }}", 
                  "{{ \$data['client']['team_user_firstname'] }}", 
                  "{{ \$data['client']['team_user_designation'] }}",
                  "{{ \$data['app_name'] }}",                 
                  "{{ \$data['message'] }}"
                ),
                $editor);

            $subject_line = __('Cash Account Statement', [], $emaillang);
            $emailtemplate = '<x-mail::message :subject="\''.$subject_line.' (\'.$data[\'subject\'].\')\'" :lang="\''.$emaillang.'\'">'. $replaceeditor .'</x-mail::message>';
          }
          else if(strpos($emailtype, "dutydefermentaccount") !== false)
          {
            $replaceeditor = str_replace(
                array(                 
                  "[subject]", 
                  "[client_name]",                   
                  "[client_firstname]", 
                  "[client_lastname]", 
                  "[team_user_firstname]", 
                  "[team_user_designation]", 
                  "[app_name]",                
                  "[email_message]"                 
                ),
                array(                  
                  "{{ \$data['subject'] }}", 
                  "{{ \$data['client']['client_name'] }}", 
                  "{{ \$data['client']['client_firstname'] }}", 
                  "{{ \$data['client']['client_lastname'] }}", 
                  "{{ \$data['client']['team_user_firstname'] }}", 
                  "{{ \$data['client']['team_user_designation'] }}",
                  "{{ \$data['app_name'] }}",                  
                  "{{ \$data['message'] }}"
                ),
                $editor);

            $subject_line = __('Duty Deferment Account', [], $emaillang);
            $emailtemplate = '<x-mail::message :subject="\''.$subject_line.' (\'.$data[\'subject\'].\')\'" :lang="\''.$emaillang.'\'">'. $replaceeditor .'</x-mail::message>';
          }
          else if(strpos($emailtype, "lockgb") !== false)
          {
            $replaceeditor = str_replace(
                array(
                  "[Vat Reg. Heading]", 
                  "[payment_date]",                   
                  "[subject]", 
                  "[client_name]",                   
                  "[client_firstname]", 
                  "[client_lastname]", 
                  "[team_user_firstname]", 
                  "[team_user_designation]", 
                  "[app_name]",              
                  "[email_message]",
                  "[bankname]",
                  "[address]",
                  "[city]",
                  "[country]",
                  "[postcode]",
                  "[sortcode]",
                  "[accountno]",
                  "[accountname]",
                  "[paymentref]",
                  "[bic]",
                  "[iban]"                    
                ),
                array(
                  "{{ \$data['vat_heading'] }}", 
                  "{{ \$data['payment_date'] }}", 
                  "{{ \$data['subject'] }}", 
                  "{{ \$data['client']['client_name'] }}", 
                  "{{ \$data['client']['client_firstname'] }}", 
                  "{{ \$data['client']['client_lastname'] }}", 
                  "{{ \$data['client']['team_user_firstname'] }}", 
                  "{{ \$data['client']['team_user_designation'] }}",
                  "{{ \$data['app_name'] }}",                 
                  "{{ \$data['message'] }}", 
                  "{{ \$data['payment_info']['bankname'] }}",
                  "{{ \$data['payment_info']['address'] }}",
                  "{{ \$data['payment_info']['city'] }}",
                  "{{ \$data['payment_info']['country'] }}",
                  "{{ \$data['payment_info']['postcode'] }}",
                  "{{ \$data['payment_info']['sortcode'] }}",
                  "{{ \$data['payment_info']['accountno'] }}",
                  "{{ \$data['payment_info']['accountname'] }}",
                  "{{ \$data['payment_info']['paymentref'] }}",
                  "{{ \$data['payment_info']['bic'] }}",
                  "{{ \$data['payment_info']['iban'] }}"
                ),
                $editor);
        
            $subject_line = __('Reported today - Payable amount to be registered on authorities Account:', [], $emaillang);            
            $emailtemplate = '<x-mail::message :subject="\''.$subject_line.' \'.$data[\'payment_date\'].\'\'" :lang="\''.$emaillang.'\'">'. $replaceeditor .'</x-mail::message>';
          }
          else if(strpos($emailtype, "lockno") !== false)
          {
            $replaceeditor = str_replace(
                array(
                  "[Vat Reg. Heading]", 
                  "[payment_date]",                   
                  "[subject]", 
                  "[client_name]",                   
                  "[client_firstname]", 
                  "[client_lastname]", 
                  "[team_user_firstname]", 
                  "[team_user_designation]", 
                  "[app_name]",                  
                  "[email_message]",
                  "[bankname]",
                  "[address]",
                  "[city]",
                  "[country]",
                  "[postcode]",
                  "[sortcode]",
                  "[accountno]",
                  "[accountname]",
                  "[paymentref]",
                  "[bic]",
                  "[iban]"                    
                ),
                array(
                  "{{ \$data['vat_heading'] }}", 
                  "{{ \$data['payment_date'] }}", 
                  "{{ \$data['subject'] }}", 
                  "{{ \$data['client']['client_name'] }}", 
                  "{{ \$data['client']['client_firstname'] }}", 
                  "{{ \$data['client']['client_lastname'] }}", 
                  "{{ \$data['client']['team_user_firstname'] }}", 
                  "{{ \$data['client']['team_user_designation'] }}",
                  "{{ \$data['app_name'] }}",                
                  "{{ \$data['message'] }}", 
                  "{{ \$data['payment_info']['bankname'] }}",
                  "{{ \$data['payment_info']['address'] }}",
                  "{{ \$data['payment_info']['city'] }}",
                  "{{ \$data['payment_info']['country'] }}",
                  "{{ \$data['payment_info']['postcode'] }}",
                  "{{ \$data['payment_info']['sortcode'] }}",
                  "{{ \$data['payment_info']['accountno'] }}",
                  "{{ \$data['payment_info']['accountname'] }}",
                  "{{ \$data['payment_info']['paymentref'] }}",
                  "{{ \$data['payment_info']['bic'] }}",
                  "{{ \$data['payment_info']['iban'] }}"
                ),
                $editor);

            // $subject_line = __('Lock', [], $emaillang);            
            // $emailtemplate = '<x-mail::message :subject="\''.$subject_line.' (\'.$data[\'subject\'].\')\'" :lang="\''.$emaillang.'\'">'. $replaceeditor .'</x-mail::message>';

            $subject_line = __('Copy of recipt and paymentinformation for Norwegian MVA ', [], $emaillang);            
            $emailtemplate = '<x-mail::message :subject="\''.$subject_line.' \'.$data[\'payment_date\'].\'\'" :lang="\''.$emaillang.'\'">'. $replaceeditor .'</x-mail::message>';            
          }
          else if(strpos($emailtype, "reminder-nodatainfolder") !== false)
          {
            $replaceeditor = str_replace(
                array(                                  
                  "[subject]",                   
                  "[user_firstname]", 
                  "[user_lastname]", 
                  "[sender_firstname]", 
                  "[sender_designation]", 
                  "[app_name]",                   
                  "[email_message]"                            
                ),
                array(                 
                  "{{ \$data['subject'] }}",                   
                  "{{ \$data['user_firstname'] }}", 
                  "{{ \$data['user_lastname'] }}", 
                  "{{ \$data['sender_firstname'] }}", 
                  "{{ \$data['sender_designation'] }}",
                  "{{ \$data['app_name'] }}",                  
                  "{{ \$data['message'] }}"                  
                ),
                $editor);

            $subject_line = __('Reminder', [], $emaillang) . ': ' . __('No data in folder', [], $emaillang);            
            $emailtemplate = '<x-mail::message :subject="\''.$subject_line.' - \'.$data[\'subject\'].\'\'" :lang="\''.$emaillang.'\'">'. $replaceeditor .'</x-mail::message>';
          }
          else if(strpos($emailtype, "reminder-uploadmissed") !== false)
          {
            $replaceeditor = str_replace(
                array(                                  
                  "[subject]",                   
                  "[user_firstname]", 
                  "[user_lastname]", 
                  "[sender_firstname]", 
                  "[sender_designation]", 
                  "[app_name]",                   
                  "[email_message]"                            
                ),
                array(                 
                  "{{ \$data['subject'] }}",                   
                  "{{ \$data['user_firstname'] }}", 
                  "{{ \$data['user_lastname'] }}", 
                  "{{ \$data['sender_firstname'] }}", 
                  "{{ \$data['sender_designation'] }}",
                  "{{ \$data['app_name'] }}",                  
                  "{{ \$data['message'] }}"                  
                ),
                $editor);
            $subject_line = __('Reminder', [], $emaillang) . ': ' . __('Upload missed', [], $emaillang);            
            $emailtemplate = '<x-mail::message :subject="\''.$subject_line.' - \'.$data[\'subject\'].\'\'" :lang="\''.$emaillang.'\'">'. $replaceeditor .'</x-mail::message>';
          }
          else if(strpos($emailtype, "reminder-pivsnotuploaded") !== false)
          {
            $replaceeditor = str_replace(
                array(                                  
                  "[subject]",                   
                  "[user_firstname]", 
                  "[user_lastname]", 
                  "[sender_firstname]", 
                  "[sender_designation]", 
                  "[app_name]",                   
                  "[email_message]"                            
                ),
                array(                 
                  "{{ \$data['subject'] }}",                   
                  "{{ \$data['user_firstname'] }}", 
                  "{{ \$data['user_lastname'] }}", 
                  "{{ \$data['sender_firstname'] }}", 
                  "{{ \$data['sender_designation'] }}",
                  "{{ \$data['app_name'] }}",                  
                  "{{ \$data['message'] }}"                  
                ),
                $editor);

            $subject_line = __('Reminder', [], $emaillang) . ': ' . __('Pivs not uploaded', [], $emaillang);            
            $emailtemplate = '<x-mail::message :subject="\''.$subject_line.' - \'.$data[\'subject\'].\'\'" :lang="\''.$emaillang.'\'">'. $replaceeditor .'</x-mail::message>';
          }
          else if(strpos($emailtype, "reminder-casnotuploaded") !== false)
          {
            $replaceeditor = str_replace(
                array(                                  
                  "[subject]",                   
                  "[user_firstname]", 
                  "[user_lastname]", 
                  "[sender_firstname]", 
                  "[sender_designation]", 
                  "[app_name]",                   
                  "[email_message]"                            
                ),
                array(                 
                  "{{ \$data['subject'] }}",                   
                  "{{ \$data['user_firstname'] }}", 
                  "{{ \$data['user_lastname'] }}", 
                  "{{ \$data['sender_firstname'] }}", 
                  "{{ \$data['sender_designation'] }}",
                  "{{ \$data['app_name'] }}",                  
                  "{{ \$data['message'] }}"                  
                ),
                $editor);

            $subject_line = __('Reminder', [], $emaillang) . ': ' . __('Cash Account Statement not uploaded', [], $emaillang);            
            $emailtemplate = '<x-mail::message :subject="\''.$subject_line.' - \'.$data[\'subject\'].\'\'" :lang="\''.$emaillang.'\'">'. $replaceeditor .'</x-mail::message>';
          }
          else if(strpos($emailtype, "reminder-ddanotuploaded") !== false)
          {
            $replaceeditor = str_replace(
                array(                                  
                  "[subject]",                   
                  "[user_firstname]", 
                  "[user_lastname]", 
                  "[sender_firstname]", 
                  "[sender_designation]", 
                  "[app_name]",                   
                  "[email_message]"                            
                ),
                array(                 
                  "{{ \$data['subject'] }}",                   
                  "{{ \$data['user_firstname'] }}", 
                  "{{ \$data['user_lastname'] }}", 
                  "{{ \$data['sender_firstname'] }}", 
                  "{{ \$data['sender_designation'] }}",
                  "{{ \$data['app_name'] }}",                  
                  "{{ \$data['message'] }}"                  
                ),
                $editor);

            $subject_line = __('Reminder', [], $emaillang) . ': ' . __('Duty Deferment Account not uploaded', [], $emaillang);            
            $emailtemplate = '<x-mail::message :subject="\''.$subject_line.' - \'.$data[\'subject\'].\'\'" :lang="\''.$emaillang.'\'">'. $replaceeditor .'</x-mail::message>';
          }
          else if(strpos($emailtype, "reminder-general") !== false)
          {
            $replaceeditor = str_replace(
                array(                                  
                  "[subject]",                   
                  "[user_firstname]", 
                  "[user_lastname]", 
                  "[sender_firstname]", 
                  "[sender_designation]", 
                  "[app_name]",                   
                  "[email_message]"                            
                ),
                array(                 
                  "{{ \$data['subject'] }}",                   
                  "{{ \$data['user_firstname'] }}", 
                  "{{ \$data['user_lastname'] }}", 
                  "{{ \$data['sender_firstname'] }}", 
                  "{{ \$data['sender_designation'] }}",
                  "{{ \$data['app_name'] }}",                  
                  "{{ \$data['message'] }}"                  
                ),
                $editor);

            $subject_line = __('Reminder', [], $emaillang) . ': ' . __('General Reminder', [], $emaillang);            
            $emailtemplate = '<x-mail::message :subject="\''.$subject_line.' - \'.$data[\'subject\'].\'\'" :lang="\''.$emaillang.'\'">'. $replaceeditor .'</x-mail::message>';
          }          
          
          $path = base_path('resources/views/emails/'.$emailtype.'.blade.php');
         
          if (file_exists($path)) {
              file_put_contents($path, $emailtemplate);

              //Edit
              $editpath = base_path('resources/views/emails/edit/'.$emailtype.'.blade.php');
              if (file_exists($editpath))
                file_put_contents($editpath, $replaceeditor);

              return "success";
          }
      }
      catch (Exception $e) {
        return  $e->getMessage();
      }
    }    
}
