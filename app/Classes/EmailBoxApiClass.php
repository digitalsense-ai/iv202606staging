<?php

namespace App\Classes;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

use GuzzleHttp\Client as GuzzleClient;
use Webklex\IMAP\Facades\Client as MailBoxClient;

use \App\Classes\CommonClass;
use \App\Classes\ApiClass;
use \App\Classes\CargoDeclarationClass;

use Storage;

class EmailBoxApiClass
{       
  /* -- GET EMAIL LISTS -- */  
  public function getEmailLists()
  {
    try 
    {   
      $commonClass = new CommonClass();
      $system = $commonClass->getSystemInfoLazy('Email Box', 'Production');
      $systemapi = $system->systemapi->first();

      $api_base_url = $systemapi->api_base_url;
      $api_client_id = $systemapi->api_client_id;
      $api_secret_key = $systemapi->api_secret_key;
     
      $url = $api_base_url;

      $headers = [                            
        'Content-Type' => 'application/json'
      ];
                  
      $guzzleClient = new GuzzleClient();   
     
      $response = $guzzleClient->request('GET', $url, [
        'auth' => [
          $api_client_id, 
          $api_secret_key
        ],      
        'headers' => $headers
      ]);
  
      $response_data =  $response->getBody()->getContents();
  
      $email_lists = preg_split("/\\r\\n|\\r|\\n/", trim($response_data));

      return $email_lists;
    }
    catch (\Exception $e) 
    {
      if($e->getResponse())   
      {           
        $response = $e->getResponse();
        $response_data = $response->getBody()->getContents();

        return  $response_data;
      }
      else
        return $e->getMessage();
    }
  }
  /* --end GET EMAIL LISTS -- */ 

  /* -- CREATE EMAIL FOR COMPANY -- */  
  public function createEmailForCompany($email, $password)
  {
    try 
    { 
      $commonClass = new CommonClass();
      $system = $commonClass->getSystemInfoLazy('Email Box', 'Production');
      $systemapi = $system->systemapi->first();

      $api_base_url = $systemapi->api_base_url;
      $api_client_id = $systemapi->api_client_id;
      $api_secret_key = $systemapi->api_secret_key;

      $url = $api_base_url . "/add";
     
      $headers = [                                  
        'Content-Type' => 'application/x-www-form-urlencoded',          
      ];
           
      $params = [       
        'email' => $email,
        'password' => $password
      ]; 
                  
      $guzzleClient = new GuzzleClient();   
                                   
      $response = $guzzleClient->request('POST', $url, [
        'auth' => [
          $api_client_id, 
          $api_secret_key
        ],      
        'headers' => $headers,
        'form_params' => $params
      ]);
      
      $response_data =  $response->getBody()->getContents();
     
      return trim($response_data);
    }
    catch (\Exception $e) 
    {
      if($e->getResponse())   
      {           
        $response = $e->getResponse();
        $response_data = $response->getBody()->getContents();

        return  $response_data;
      }
      else
        return $e->getMessage();
    }
  }
  /* --end CREATE EMAIL FOR COMPANY -- */ 

  /* -- DELETE EMAIL FOR COMPANY -- */  
  public function deleteEmailForCompany($email)
  {
    try 
    {  
      $commonClass = new CommonClass();
      $system = $commonClass->getSystemInfoLazy('Email Box', 'Production');
      $systemapi = $system->systemapi->first();

      $api_base_url = $systemapi->api_base_url;
      $api_client_id = $systemapi->api_client_id;
      $api_secret_key = $systemapi->api_secret_key;

      $url = $api_base_url . "/remove";
     
      $headers = [                                    
        'Content-Type' => 'application/x-www-form-urlencoded'
      ];
           
      $params = [        
        'email' => $email                 
      ];   
                    
      $guzzleClient = new GuzzleClient();   
                                   
      $response = $guzzleClient->request('POST', $url, [
        'auth' => [
          $api_client_id, 
          $api_secret_key
        ],      
        'headers' => $headers,
        'form_params' => $params
      ]);
  
      $response_data =  $response->getBody()->getContents();
     
      return trim($response_data);
    }
    catch (\Exception $e) 
    {
      if($e->getResponse())   
      {           
        $response = $e->getResponse();
        $response_data = $response->getBody()->getContents();

        return  $response_data;
      }
      else
        return $e->getMessage();
    }
  }
  /* --end DELETE EMAIL FOR COMPANY -- */ 

  /* -- READ EMAIL FOR COMPANY -- */  
  public function readEmailForCompany($authUser, $email_lists = null)
  {
    try 
    {
      $commonClass = new CommonClass();
      $apiClass = new ApiClass();

      $system = $commonClass->getSystemInfoLazy(); 
      $systemapi = $system->systemapi->first();
            
      /* -- LIST -- */
      $email_lists = ($email_lists) ? $email_lists : $this->getEmailLists();
      /* --end LIST -- */ 

      foreach($email_lists as $key => $email)
      {
        if($email != 'info@intravat.cloud' && $email != 'import@intravat.cloud')
        {
          echo "Email: " . htmlspecialchars($email) . "<br>"; 

          //GET vatreg.
          $with_vatreg = ['vatregmain', 'client'];  
          $where_vatreg = []; 
          $whereHas_vatreg = [                    
              'vatregmain' => ['field' => 'email', 'value' => $email]
          ];    
          $orderBy_vatreg = [];            
          $vatreg = $commonClass->getLazy('vatreg', $with_vatreg, $where_vatreg, $whereHas_vatreg, $orderBy_vatreg, 'first');
         
          //Create a temporary configuration array for each email account
          $config = [
              'host'          => 'box.intravat.cloud',
              'port'          => '993',
              'encryption'    => 'ssl',
              'validate_cert' => true,
              'username'      => $email,
              'password'      => '12345678',
              'protocol'      => 'imap',
              'fetch'         => [
                  'fetch'  => 'Fast',
                  'search' => 'UNSEEN',
              ],
          ];

          // Connect to the IMAP server                      
          $mailboxclient = MailBoxClient::make($config);

          //Connect to the IMAP Server
          $mailboxclient->connect();

          //Get all Mailboxes
          /** @var \Webklex\PHPIMAP\Support\FolderCollection $folders */
          $folders = $mailboxclient->getFolders();
          //$archive = $mailboxclient->getFolder('Archive');

          //Loop through every Mailbox
          /** @var \Webklex\PHPIMAP\Folder $folder */
          foreach($folders as $folder)
          {
              if(strtoupper($folder->name) == 'INBOX')
              {
                  //Get all Messages of the current Mailbox $folder
                  /** @var \Webklex\PHPIMAP\Support\MessageCollection $messages */
                  $messages = $folder->messages()->all()->get();
                  
                  /** @var \Webklex\PHPIMAP\Message $message */
                  foreach($messages as $message)
                  {
                      // Fetch attachments
                      $attachments = $message->getAttachments();
                      if($attachments->count() > 0)
                      {
                          echo $message->getSubject().'<br />';
                          echo 'Attachments: '.$attachments->count().'<br />';
                          echo $message->getHTMLBody();

                          foreach ($attachments as $attachment) 
                          {
                            // Get attachment details
                            $attachmentName = $attachment->name;
                            $attachmentBody = $attachment->getContent();
                                                        
                            // Store it in One-drive and Mailbox table
                            $request_pass = [
                                'file' => $attachmentBody,                       
                                'file_name' => $attachmentName,
                                'email_datetime' => Carbon::parse($message->getDate()->toDate())->format('Y-m-d H:i:s'),
                                'email_id' => $message->getFrom()[0]->mail,    
                                'email_subject' => htmlspecialchars($message->getSubject()),    
                                'file_type' => 'mailbox',
                                'file_type_title' => 'MailBox'
                            ];
                            $uploadedfile = $apiClass->uploadFileToOneDriveLazy($request_pass, $vatreg, $authUser, $systemapi, 'mailbox');
                                                        
                            echo "Saved attachment: " . htmlspecialchars($attachmentName) . "<br>"; 
                          } //loop ATTACHMENTS

                          $message->move('Archive');
                          echo "Moved message: " . htmlspecialchars($message->getSubject()) . "<br>";
                          echo '<hr>';                               
                      } //ATTACHMENTS
                  } //Messages
              } //INBOX folder
          } //Loop through every Mailbox
        } //
      } // Loop emails
    }
    catch (\Exception $e) 
    {
      dd($e);
      if($e->getResponse())   
      {           
        $response = $e->getResponse();
        $response_data = $response->getBody()->getContents();

        return  $response_data;
      }
      else
        return $e->getMessage();
    }
  }
  /* --end READ EMAIL FOR COMPANY -- */  

  /* -- READ EMAIL FOR CARGO DECLARATION FILES -- */  
  public function readEmailForCargoDeclarationFiles($authUser)
  {
    try 
    {
      $commonClass = new CommonClass();
      $apiClass = new ApiClass();
      $cargoDeclarationClass = new CargoDeclarationClass();

      $system = $commonClass->getSystemInfoLazy(); 
      $systemapi = $system->systemapi->first();

      $email = 'import@intravat.cloud';     
      $storage_path = 'mailbox/cargodeclarationfiles/';

      $config = [
          'host'          => 'box.intravat.cloud',
          'port'          => '993',
          'encryption'    => 'ssl',
          'validate_cert' => true,
          'username'      => $email,
          'password'      => 'Urges905@',
          'protocol'      => 'imap',
          'fetch'         => [
              'fetch'  => 'Fast',
              'search' => 'UNSEEN',
          ],
      ];

      // Connect to the IMAP server                   
      $mailboxclient = MailBoxClient::make($config);

      //Connect to the IMAP Server
      $mailboxclient->connect();

      //Get all Mailboxes
      /** @var \Webklex\PHPIMAP\Support\FolderCollection $folders */
      $folders = $mailboxclient->getFolders();
      
      //Loop through every Mailbox
      /** @var \Webklex\PHPIMAP\Folder $folder */
      foreach($folders as $folder)
      {
        if(strtoupper($folder->name) == 'INBOX' || strtoupper($folder->name) == 'SPAM')//SPAM ARCHIVE      
        {
          //Get all Messages of the current Mailbox $folder
          /** @var \Webklex\PHPIMAP\Support\MessageCollection $messages */
          try
          {            
            $query = $folder->query();

            $retryCount = 0;
            $maxRetries = 3;
            $chunk_size = 10;
            $start_chunk = 1;

            do {
              echo "-------------------------------------" . $retryCount . "***". $start_chunk . "-------------------------------------<br>";
                try {
                  
                  /** @var \Webklex\PHPIMAP\Query\WhereQuery $query */              
                  $query->unflagged()->fetchOrderAsc()->chunked(function($messages, $chunk)                
                    use($storage_path, $systemapi, $apiClass, $cargoDeclarationClass, $authUser) {
                      /** @var \Webklex\PHPIMAP\Support\MessageCollection $messages */
                      
                      if ($messages->isEmpty()) {
                          Log::info("Empty response received. Skipping chunk #$chunk");
                          return;
                      }

                   
                      $messages->each(function($message) use($storage_path, $systemapi, $apiClass, $cargoDeclarationClass, $authUser) {
                        $move_message = 0;
                        /** @var \Webklex\PHPIMAP\Message $message */
                            
                        $subject = $message->getSubject();
                        $decoded_subject = $subject ? iconv_mime_decode($subject, ICONV_MIME_DECODE_STRICT, 'UTF-8') : null;

                        echo ' -- SUBJECT: ' . $decoded_subject . "<br>";  

                        if (stripos(strtolower(trim(htmlspecialchars($message->getSubject()))), "undelivered") !== false ||
                          stripos(strtolower(trim(htmlspecialchars($message->getSubject()))), "undeliverable") !== false ||
                          stripos(strtolower(trim(htmlspecialchars($message->getSubject()))), "delayed mail") !== false
                        )
                        {
                          echo 'UNDELIVERED <br />';
                        }// OMIT UNDELIVER EMAILS
                        else
                        {
                          // if(stripos(trim(htmlspecialchars($message->getSubject())),"Innførsel fra UPS - Ikke svar på denne. Ref: 514A94VH83N 1Z514A940497742993TollID: 4420012500270925") !== false)
                          // { 
                          // Fetch attachments
                          $attachments = $message->getAttachments();

                          if($attachments->count() > 0)
                          {                           
                            echo 'Attachments: '.$attachments->count().'<br />';
                            
                            foreach ($attachments as $attachment) 
                            {
                              // Get attachment details
                              $attachmentName = $attachment->name;
                              $arr_attachmentName = explode('.', $attachmentName);
                              $file_extension = $arr_attachmentName[count($arr_attachmentName) - 1];

                              $attachmentBody = $attachment->getContent();
                                               
                              if (stripos(strtoupper(trim($attachmentName)), "ESCAN") !== false || 
                                stripos(strtoupper(trim($attachmentName)), "FAKTURA") !== false ||
                                stripos(strtoupper(trim($attachmentName)), "RECEIPTRESPONSE_RECEIPT_TAXATION") !== false ||
                                stripos(strtoupper(trim($attachmentName)), "KREDITNOTA") !== false ||
                                stripos(strtoupper(trim($attachmentName)), "_MCU") !== false)
                              {
                                echo 'NOT STORED - Attachment: '. $attachmentName.'<br />';
                              } 
                              else 
                              {
                                if($file_extension == 'pdf')
                                {
                                  echo 'ONLY STORED - Attachment: '. $attachmentName.'<br />';
                                 
                                  if (Storage::disk('public')->exists($storage_path)) 
                                  {
                                    echo "The folder exists.<br>";
                                    $fullPath = storage_path('app/public/' . $storage_path);
                                    if (is_writable($fullPath)) 
                                    {
                                      echo "The folder is writable.<br>";

                                      $result = Storage::disk('public')->put($storage_path . $attachmentName, $attachmentBody);

                                      if ($result)
                                        echo "File stored successfully in {$storage_path}{$attachmentName}<br>";
                                      else
                                        echo "Failed to store the file.<br>";
                                    } 
                                    else
                                      echo "The folder is not writable.<br>"; 
                                  } 
                                  else
                                    echo "The folder does not exist.<br>";
                                  
                                  //Read and assign to relevant folder
                                  $readcargofiles = $cargoDeclarationClass->readCargoDeclarationFile($attachmentName);

                                  if(is_array($readcargofiles))
                                  {
                                    if(count($readcargofiles) > 0)
                                    {
                                      $vatreg = $readcargofiles['match_vatreg'];
                                      
                                      // Store it in One-drive and Mailbox table
                                      $request_pass = [
                                          'file' => $attachmentBody,                       
                                          'file_name' => $attachmentName,
                                          'email_datetime' => Carbon::parse($message->getDate()->toDate())->format('Y-m-d H:i:s'),
                                          'email_id' => $message->getFrom()[0]->mail,    
                                          'email_subject' => $decoded_subject,
                                          'file_type' => 'cargo_mailbox',
                                          'file_type_title' => 'Cargo MailBox',
                                          'cargo_date' => $readcargofiles['cargo_date'],
                                          'expo_no' => $readcargofiles['expo_no'],
                                          'lope_no' => $readcargofiles['lope_no'],                                        
                                          'com_invoice_no' => $readcargofiles['com_invoice_no'],
                                          'com_invoice_date' => $readcargofiles['com_invoice_date']                                            
                                      ];
                                      $uploadedfile = $apiClass->uploadFileToOneDriveLazy($request_pass, $vatreg, $authUser, $systemapi, 'cargo_mailbox');                                      
                                      // Delete it from storage                                    
                                      Storage::disk('public')->delete('mailbox/cargodeclarationfiles/'. $attachmentName);

                                      echo "Saved attachment: " . htmlspecialchars($attachmentName) . "<br>"; 

                                      $move_message = 1;                                      
                                    }
                                    else                            
                                      echo '<span style="background-color: yellow;">NOT STORED - NO VATREG. - Attachment: '. $attachmentName . '</span><br />';
                                 }
                                 else                            
                                      echo '<span style="background-color: red;">NOT STORED - ERROR - Attachment: '. $attachmentName. 
                                   '<br />' . $readcargofiles . '</span><br />';
                                } //only PDF file                                
                              }//only CARGO file
                            } //loop ATTACHMENTS

                            if($move_message)
                            {                              
                              $message->setFlag(['Seen', 'Flagged']);
                              echo "<span style='background-color: green;''>Moved message: " . htmlspecialchars($message->getSubject()) . "</span><br>";                          
                            }//Message Moved
                            echo '<hr>'; 
                          } //ATTACHMENTS 
                          //}//dummy SUBJECT  
                          // else
                          //   Log::info('NOT MATCHED EMAIL <br />');                 
                        }// if PROPER EMAIL                  
                      });//chunck message                      
                  }, $chunk_size, $start_chunk);
                  break;  // Exit loop if the query is successful
                } catch (\Exception $e) {
                  Log::info($e->getMessage() . "<br>");                  
                    if (++$retryCount >= $maxRetries) {
                        dd("Max retries reached. Giving up.");
                        break;
                    }
                    // Wait before retrying
                    sleep(2);
                }
            } while ($retryCount < $maxRetries);
          }
          catch (\Exception $e) 
          {        
            dd($e);
            $errorMessage = $e->getMessage(); 

            return $errorMessage;  
          }
        } //INBOX folder
      } //Loop through every Mailbox       
    }
    catch (\Exception $e) 
    {
      dd($e);
      if($e->getResponse())   
      {           
        $response = $e->getResponse();
        $response_data = $response->getBody()->getContents();

        return  $response_data;
      }
      else
        return $e->getMessage();
    }
  }
  /* --end READ EMAIL FOR CARGO DECLARATION FILES -- */  
}
