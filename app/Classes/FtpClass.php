<?php

namespace App\Classes;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;


use Str;
use Storage;

use App\Models\VATRegistration;
use App\Models\VATReturns;
use App\Models\ImportReconciliationFiles;

use \App\Classes\CommonClass;
use \App\Classes\ApiClass;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use App\Jobs\ReadFtpFiles;

class FtpClass
{    
    /*Lazy*/
    public function getVatReturnFilesFromFtpLazy($vatreg, $authUser, $filenameeixsts)
    {
    	$commonClass = new CommonClass();
    	$apiClass = new ApiClass();

    	$vatregmain = $vatreg->vatregmain;
      	$clientapi = $vatregmain->clientapi;     
      	$client = $vatreg->client;  	

    	$vat_reg_id = $vatreg->vat_reg_id;
    	
    	$ftp_connection = $clientapi;

    	$sftp_server = $ftp_connection->api_base_url;
    	$sftp_username = $ftp_connection->api_client_id; 
    	$sftp_password = $ftp_connection->api_secret_key; 
    	$sftp_foldername = preg_replace('/[^A-Za-z0-9\-]/', '', 
				    		preg_replace('/\s+/', '', 
				    			strtolower(
				    				iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $client->client_name)
    							)
    						)
				    	); 
    
    	$driver = Storage::createSFtpDriver([
                'host'     => $sftp_server,
                'username' => $sftp_username,
                'password' => $sftp_password,
                //'port'     => 65002,                         
                'timeout'  => 10,
            ]);
    	
    	$sftp_path = '/var/sftp/uploads/';    	

		$archive_vatreturnfiles = collect($driver->listContents($sftp_path . $sftp_foldername. "/Archive", false));			
		$sftp_subfoldername = $sftp_foldername;
		if($sftp_foldername == 'stofas')
			$sftp_subfoldername = 'stof';
		else if($sftp_foldername == 'kaffekapslenaps')
		{
			if($vatreg->country == 'NO' || $vatreg->country == 'GB')
				$sftp_subfoldername =  $vatreg->country;
		}
		$main_vatreturnfiles = collect($driver->listContents($sftp_path . $sftp_foldername. "/".$sftp_subfoldername, false));

		$vatreturnfiles = $main_vatreturnfiles->merge($archive_vatreturnfiles);

		$account_data = [];
		if(count($vatreturnfiles) > 0) 
		{  
			foreach($vatreturnfiles as $vatreturnfile)
			{				
				$filepath = $vatreturnfile['path'];
				$filename = basename($filepath);
	
				if (strpos($filepath, $filenameeixsts) !== false)
				{
					$filecontent = $driver->get($filepath);
								
					$extension = $commonClass->getFileExtension($filename);
				
					$commonClass->addLog($authUser, 'invoice-reading-mapped-file', 
	                    [          
	                      'Client Name' => $client->client_name,
	                      'VAT Reg' => Carbon::parse($vatreg->service_start)->format('M Y') . ' ' . $vatreg->country . ' ' . $vatreg->general_periods
	                    ]
	                );

					$read_data = $commonClass->readVatReturnFile($filecontent, NULL, $extension);
					
					$commonClass->addLog($authUser, 'invoice-read-mapped-file', 
	                    [          
	                      'Client Name' => $client->client_name,
	                      'VAT Reg' => Carbon::parse($vatreg->service_start)->format('M Y') . ' ' . $vatreg->country . ' ' . $vatreg->general_periods
	                    ]
	                );   
					
					$account_data = $read_data;
										
					if (strpos($filepath, 'ARCHIVED-' . $filenameeixsts) !== false)
					{
					}
					else
					{
						//Move the file to ONEDRIVE						
						$system = $commonClass->getSystemInfoLazy();
						$systemapi = $system->systemapi->first();
						
						$uploadtoOneDrive = $apiClass->uploadFileToOneDriveLazy($filecontent, $client, $authUser, $systemapi);

						//Rename with "ARCHIVED-" and move the file to "Archive"
						$oldDirectoryPath = $filepath;					
						$newFileName = $sftp_path . $sftp_foldername . '/Archive/ARCHIVED-' . $filename;
						$driver->move($oldDirectoryPath, $newFileName);

						//Delete from Old Path
						$driver->delete($oldDirectoryPath);
					}
				}					
			}
		}
		return $account_data;
    }

    public function getImportReconciliationFilesFromFtp($vatreg, $authUser, $which_folder = 'main', $efacto = false)
    {
    	$commonClass = new CommonClass();
    	$apiClass = new ApiClass();

    	$system_ftp = $commonClass->getSystemInfoLazy('FTP', 'Production');
      	
    	$vatregmain = $vatreg->vatregmain;
      	$clientapi = $system_ftp->systemapi->first();
      	$client = $vatreg->client;  	

    	$vat_reg_id = $vatreg->id;
    	
    	$ftp_connection = $clientapi;

    	$sftp_server = $ftp_connection->api_base_url;
    	$sftp_username = $ftp_connection->api_client_id; 
    	$sftp_password = $ftp_connection->api_secret_key; 
    	$sftp_foldername = ($efacto) ? 'efacto' : (preg_replace('/[^A-Za-z0-9\-]/', '', 
				    		preg_replace('/\s+/', '', 
				    			strtolower(
				    				iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $client->client_name)
    							)
    						)
				    	)); 
      
		if($sftp_foldername == 'auboproductionas')		
			$sftp_foldername = 'aubo';
		else if($sftp_foldername == 'becksondergaardaps' || $sftp_foldername == 'becksndergaardaps')		
			$sftp_foldername = 'becksondergaard';
		else if($sftp_foldername == 'asvillyjensenbesaetningsartiklerengros' || $sftp_foldername == 'asvillyjensen')		
			$sftp_foldername = 'villyjensen';
		else if($sftp_foldername == 'dfi-geisleras')
			$sftp_foldername = 'dfigeisler';

    	$driver = Storage::createSFtpDriver([
                'host'     => $sftp_server,
                'username' => $sftp_username,
                'password' => $sftp_password,
                //'port'     => 65002,                         
                'timeout'  => 10,
            ]);
    	
    	$sftp_path = '/var/sftp/uploads/';    	
    			
		$sftp_subfoldername = $sftp_foldername;
		
		if($which_folder == 'main')
			$importreconciliationfiles = collect($driver->listContents($sftp_path . $sftp_foldername. "/".$sftp_subfoldername, false));
		else if($which_folder == 'archive')		
			$importreconciliationfiles = collect($driver->listContents($sftp_path . $sftp_foldername. "/Archive", false));				
		else if($which_folder == 'both')
		{
			$main_importreconciliationfiles = collect($driver->listContents($sftp_path . $sftp_foldername. "/".$sftp_subfoldername, false));

			$archive_importreconciliationfiles = collect($driver->listContents($sftp_path . $sftp_foldername. "/Archive", false));

			$importreconciliationfiles = $main_importreconciliationfiles->merge($archive_importreconciliationfiles);
		}
		
		$o_file_names = $vatreg->importreconciliationfiles->pluck('o_file_name')->toArray();
		if($o_file_names)	
		{			
			$filtered_importreconciliationfiles = $importreconciliationfiles->filter(function ($importreconciliationfile)  use ($o_file_names) {
				$filepath = $importreconciliationfile['path'];
				$filename = basename($filepath);

				return !in_array($filename, $o_file_names);
			});
			$importreconciliationfiles = $filtered_importreconciliationfiles;
		}		
	
		$account_data = [];
		if(count($importreconciliationfiles) > 0) 
		{  			
			$system = $commonClass->getSystemInfoLazy();
            $systemapi = $system->systemapi->first();

            $sftp_details = [
            	'host'     => $sftp_server,
                'username' => $sftp_username,
                'password' => $sftp_password,                
                'path' => $sftp_path,
                'foldername' => $sftp_foldername,
                'subfoldername' => $sftp_subfoldername                              
            ];
			$jobs = [];

			$arr_importreconciliationfiles = $importreconciliationfiles->toArray();
			$chunks = array_chunk($arr_importreconciliationfiles, 10);
			
			foreach ($chunks as $chunk)
			{				
				$jobs[] = (new ReadFtpFiles($chunk, $vatreg, $sftp_details, $efacto, $authUser, $systemapi))->delay(now()->addSeconds(5));
			}
			
			$batch = Bus::batch($jobs)->dispatch();

      		return $batch;  		  
		} //if
		
		return false;
    }
}
