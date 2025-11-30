<?php

namespace App\Http\Controllers\chat;

use App\Http\Controllers\Controller;

use Nahid\Talk\Facades\Talk;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use \App\Classes\CommonClass;
use App\Models\User;

use Storage;

class ChatTalkController extends Controller
{
    public $authUser;
    
    public $commonClass;    
   
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('talk');
        $this->middleware(function ($request, $next) {                            
            $this->commonClass = new CommonClass();
            $this->authUser = $this->commonClass->getAuthUser();              
                       
            return $next($request);
        });                   
    }    

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {        
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);    

        $allUsers = $this->commonClass->getAllUsers($this->authUser->user_id);    
        $threads = Talk::threads();

        foreach($threads as $inbox)
        {
            if(!is_null($inbox->withUser))
            {
                $user = $this->commonClass->getSpecificUser($inbox->withUser->id);               
                $inbox->withUser->name = $user->firstname . ' ' . $user->lastname;                
                $inbox->withUser->profile_photo_url = User::defaultProfilePhotoUrl($user);
                $inbox->withUser->role = $user->role;
            }
        }
      
        $user = '';
        $messages = [];

        return view('content.chat.index', ['pageConfigs' => $pageConfigs, 'authUser' => $this->authUser, 'threads' => $threads, 'allUsers' => $allUsers, 'messages' => $messages, 'user' => $user]);  
    }

    public function chatHistory($id)
    {
        $conversations = Talk::getMessagesByUserId($id, 0, 50);
        $user = '';
        $messages = [];
        if(!$conversations) {            
            $user = $this->commonClass->getSpecificUser($id);
        } else {            
            $user = $this->commonClass->getSpecificUser($conversations->withUser->id);
            $messages = $conversations->messages;
        }

        if (count($messages) > 0) {
            $messages = $messages->sortBy('id');
        }

        $authUser = $this->authUser;
        
        $chat_header = view('_partials._content._chat.chatheader', compact('user'))->render();
        $chat_body = view('_partials._content._chat.chatbody', compact('authUser', 'messages', 'user'))->render();
        $chat_footer = view('_partials._content._chat.chatfooter', compact('user'))->render();
        $right_sidebar = view('_partials._content._chat.rightsidebar', compact('user'))->render();

         return response()->json(
          [
            'status' => 200,            
            'chat_header' => $chat_header,
            'chat_body' => $chat_body,
            'chat_footer' => $chat_footer,
            'right_sidebar' => $right_sidebar
          ]
        );
    }

    public function ajaxSendMessage(Request $request)
    {
        if ($request->ajax()) {
            if($request->file('attach-doc'))
                $rules = [
                    'attach-doc'=>'required',
                    '_id'=>'required'
                ];
            else            
                $rules = [
                    'message-data'=>'required',
                    '_id'=>'required'
                ];
          
            $this->validate($request, $rules);

            $attach_doc = "";
            if($request->file('attach-doc'))
            {
                $filename = $request->file('attach-doc')->getClientOriginalName();               
                Storage::disk('public')->put($filename, file_get_contents($request->file('attach-doc')));   
               
                $attach_doc = $filename;        
            }

            $body = ($request->file('attach-doc')) ? $attach_doc : $request->input('message-data');
            $userId = $request->input('_id');

            if ($message = Talk::sendMessageByUserId($userId, $body)) {
                
                $updateMsgTable = DB::table('messages')                            
                                    ->where('id', $message->id)                            
                                    ->update(
                                      [                            
                                        'is_file' => ($request->file('attach-doc')) ? 1 : 0
                                      ]
                                    ); 
                $message->is_file = ($request->file('attach-doc')) ? 1 : 0;
                
                $authUser = $this->authUser;
                $html = view('_partials._content._chat.newmessage', compact('message', 'authUser'))->render();
                return response()->json(['status'=>'success', 'html'=>$html], 200);
            }
            
        }
    }

    public function ajaxDeleteMessage(Request $request, $id)
    {
        if ($request->ajax()) {
            if(Talk::deleteMessage($id)) {
                return response()->json(['status'=>'success'], 200);
            }

            return response()->json(['status'=>'errors', 'msg'=>'something went wrong'], 401);
        }
    } 
}
