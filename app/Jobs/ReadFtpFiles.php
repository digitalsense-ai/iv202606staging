<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Storage;

use \App\Classes\CommonClass;
use \App\Classes\ApiClass;

class ReadFtpFiles implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $importreconciliationfiles;
    protected $vatreg;     
    protected $sftp_details;    
    protected $efacto;    
    protected $authUser;
    protected $systemapi;
    
    protected $commonClass;   
    protected $apiClass; 

    /**
     * Create a new job instance.
     *
     * @return void
     */   
    public function __construct($importreconciliationfiles, $vatreg, $sftp_details, $efacto, $authUser, $systemapi)
    {                  
      $this->importreconciliationfiles = $importreconciliationfiles;
      $this->vatreg = $vatreg;        
      $this->sftp_details = $sftp_details;    
      $this->efacto = $efacto;     
      $this->authUser = $authUser;     
      $this->systemapi = $systemapi;      
      
      $this->commonClass = new CommonClass();
      $this->apiClass = new ApiClass();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {       
        try
        {    
          $driver = Storage::createSFtpDriver([
              'host'     => $this->sftp_details['host'],
              'username' => $this->sftp_details['username'],
              'password' => $this->sftp_details['password'],                  
              'timeout'  => 10,
          ]);

          $sftp_path = $this->sftp_details['path'];
          $sftp_foldername = $this->sftp_details['foldername'];
          $sftp_subfoldername = $this->sftp_details['subfoldername'];

            foreach ($this->importreconciliationfiles as $key => $importreconciliationfile) 
            {                
              $filepath = $importreconciliationfile['path'];
              $filename = basename($filepath);
        
              // if($filename == 'N00211455.xml')             
              // { 
              //Check Filename already exists       
              $file_already_exists = $this->commonClass->getImportReconciliationFilesLazy(null, $filename);
              //Check Filename already exists

              if($file_already_exists)
              {
                //Applicable only for LIVE
                if(strtolower(env('APP_URL')) === "https://app.intravat.cloud" || strtolower(config('app.url')) === "https://app.intravat.cloud")
                {
                  //Move to main folder - if it is archive
                  $oldDirectoryPath = $filepath;     

                  if (strpos($oldDirectoryPath, '/Archive/') !== false)
                  {                 
                  }
                  else
                  {
                    $newFileName = $sftp_path . $sftp_foldername . '/Archive/' . $filename;
                    $driver->move($oldDirectoryPath, $newFileName);

                    //Delete from Old Path - ONLY FROM MAIN FOLDER                  
                    $driver->delete($oldDirectoryPath);
                  }    
                } //if - Applicable only for LIVE
              }
              else
              {
                $proceed = ($sftp_foldername == 'dfigeisler') ? true : false;
                if($this->efacto) 
                  $proceed = true;          
                else
                {
                  $start_char = substr($this->vatreg->country, 0, 1);       
                  if (str_starts_with($filename, $start_char))
                    $proceed = true;
                }
                
                if ($proceed)
                {
                  $filecontent = $driver->get($filepath);
                        
                  $extension = $this->commonClass->getFileExtension($filename);
                                           
                  $read_data = $this->commonClass->readImportReconciliationFile($filecontent, $this->vatreg->vat_reg_main_id, $filename, $extension);

                  $matched_vatreg = $read_data['matched_vatreg'];
                  $month_year = $read_data['month_year'];                     
                 
                  if($matched_vatreg)
                  {                    
                    $invoice_rows = $read_data['invoice_rows'];

                    $account_data = $read_data;
                                                        
                    //Move the file to ONEDRIVE                              
                    $file_details = [
                      'filecontent' => $filecontent, 
                      'o_filename' => $filename,
                      'month_year' => $month_year,
                      'invoice_no' => $read_data['invoice_no'],
                      'invoice_row' => $invoice_rows[0]
                    ];

                    $uploadtoOneDrive = $this->apiClass->uploadFileToOneDriveLazy($file_details, $matched_vatreg, $this->authUser, $this->systemapi, 'import_reconciliation');

                    //Move the file to "Archive"
                    $oldDirectoryPath = $filepath;          
                   
                    if (strpos($oldDirectoryPath, '/Archive/') !== false)
                    {                 
                    }
                    else
                    {
                      //Applicable only for LIVE
                      if(strtolower(env('APP_URL')) === "https://app.intravat.cloud" || strtolower(config('app.url')) === "https://app.intravat.cloud")
                      {
                        $newFileName = $sftp_path . $sftp_foldername . '/Archive/' . $filename;
                        $driver->move($oldDirectoryPath, $newFileName);

                        //Delete from Old Path - ONLY FROM MAIN FOLDER                  
                        $driver->delete($oldDirectoryPath);
                      } //if - Applicable only for LIVE
                    } 
                  } //if any match found    
                  else
                  {
                    //Applicable only for LIVE
                    if(strtolower(env('APP_URL')) === "https://app.intravat.cloud" || strtolower(config('app.url')) === "https://app.intravat.cloud")
                    {
                      //Move to main folder - if it is archive
                      $oldDirectoryPath = $filepath;     

                      if (strpos($oldDirectoryPath, '/Archive/') !== false)
                      {
                        $newFileName = $sftp_path . $sftp_foldername . '/' . $sftp_subfoldername . '/' . $filename;
                       
                        $driver->move($oldDirectoryPath, $newFileName);

                        //Delete from Old Path - ONLY FROM ATCHIVE FOLDER                  
                        $driver->delete($oldDirectoryPath);                 
                      }         

                      Log::channel('single')->error('Unmatched FTP files-'.$this->vatreg->client->client_name.': ' . $filename. ' -- ' . $read_data['invoice_no']);
                    } //if - Applicable only for LIVE
                  } // if no match found      
                } //check filename                        
              } // file not exists in db
              //}//dummy if      
            } //loop unique_vat_reg_ids                 
        }       
        catch (\Exception $e) {
            // Handle the exception (e.g., log the error, retry, etc.)
            Log::error('Error in reading the FTP files: ' . $e->getMessage());
        }
    }

    public function failed(\Exception $exception) {
        // Log the error or send a notification
        dd($exception);
    }
}