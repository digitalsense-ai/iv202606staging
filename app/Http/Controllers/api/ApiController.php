<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use \App\Classes\CommonClass;
use App\Models\User;

class ApiController extends Controller
{
    // public $authUser;
    
    // public $commonClass;    
   
    // public function __construct()
    // {        dd("zzzzzzz");
    //     $this->middleware('auth');  dd("dfdsfsfsdfd")      ;
    //     $this->middleware(function ($request, $next) {                            
    //         $this->commonClass = new CommonClass();
    //         $this->authUser = $this->commonClass->getAuthUser();              
    //                    dd($this->authUser);
    //         return $next($request);
    //     });          
    // }    
    
    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid login details'
            ], 401);
        }

        $user = User::where('email', $request['email'])->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    // public function sampleapitest(Request $request)
    // {         
    // dd( $request->bearerToken());        
    //     //$access_token = Auth::user()->currentAccessToken()->token; 
    //     $access_token = $request->user()->currentAccessToken()->token; 
    //     dd($access_token)  ;
    //     $headers = [                         
    //         'Content-Type' => 'application/json',           
    //         'Authorization' => 'Bearer ' . $access_token      
    //     ];

    //     $guzzleClient = new GuzzleClient();   
                
    //     //$url = 'http://localhost:8000/api/sampleapitest'; 
       
    //     $response = $guzzleClient->request('GET', $url, [              
    //         'headers' => $headers,
    //         'verify'  => false
    //     ]);

    //     $data = json_decode($response->getBody());   
    //     dd($data)  ;
    // }
}
