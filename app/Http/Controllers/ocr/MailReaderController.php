<?php

namespace App\Http\Controllers\ocr;

use App\Http\Controllers\Controller;

use App\Services\MicrosoftMailService;

use \App\Classes\CommonClass;

use App\Jobs\ProcessEmailJob;

class MailReaderController extends Controller
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

    // public function fetchInbox()
    // {
    //     /* -- PAGE CONFIG -- */
    //     $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
    //     /* --end PAGE CONFIG -- */

    //     $mailService = new MicrosoftMailService();

    //     // Fetch all emails in the inbox
    //     $emails = $mailService->getAllInboxEmails();

    //     foreach ($emails as &$email) {
    //         $attachments = $mailService->downloadPdfAttachments(
    //             $email['id'],
    //             $email['subject'] ?? ''
    //         );
    //         $email['attachments'] = $attachments;
    //     }

    //     return view('content.ocr.email-list', compact('pageConfigs', 'emails'));
    // }

    public function fetchInbox()
    {
        /* -- PAGE CONFIG -- */
        $pageConfigs = $this->commonClass->getPageConfig($this->authUser);      
        /* --end PAGE CONFIG -- */

        $mailService = new MicrosoftMailService();

        // Fetch all unread emails
        $emails = $mailService->getAllInboxEmails();

        foreach ($emails as &$email) {

            // Queue the processing instead of running it directly
            ProcessEmailJob::dispatch($email['id'], $email['subject'] ?? '')->onQueue('ocrpdfinvoices');
            
            // You can optionally mark that it is queued
            $email['attachments'] = ['status' => 'queued'];
        }

        return view('content.ocr.email-list', compact('pageConfigs', 'emails'));
    }
}