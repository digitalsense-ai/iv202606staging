<?php

namespace App\Classes;

use GuzzleHttp\Client as GuzzleClient;
use Storage;

use \App\Classes\CommonClass;
use \App\Classes\ApiClass;

class EFactoClass
{    	
    public function getAllInvoicesLazy()
    {
    	$commonClass = new CommonClass();
    	$apiClass = new ApiClass();

        $system_ftp = $commonClass->getSystemInfoLazy('FTP', 'Production');        
        $ftp_connection = $system_ftp->systemapi->first();
        
        $sftp_server = $ftp_connection->api_base_url;
        $sftp_username = $ftp_connection->api_client_id; 
        $sftp_password = $ftp_connection->api_secret_key; 
        $sftp_foldername = "efacto";

        $driver = Storage::createSFtpDriver([
            'host'     => $sftp_server,
            'username' => $sftp_username,
            'password' => $sftp_password,          
            'timeout'  => 10,
        ]);
        $sftp_path = '/var/sftp/uploads/';  
        $sftp_subfoldername = $sftp_foldername;
        $remoteFilePath = $sftp_path . $sftp_foldername . "/". $sftp_subfoldername . "/";
               
    	$system_efacto = $commonClass->getSystemInfoLazy('E-Facto', 'Production');
    	$clientapi = $system_efacto->systemapi->first();

    	$api_base_url = $clientapi->api_base_url;
    	$api_secret_key = $clientapi->api_secret_key;
    			
		$headers = [                                   
			'X-API-KEY' => $api_secret_key,			
			'Content-Type' => 'application/json'          
		];
               
        $allItems = $this->callApiRecursively($api_base_url, $headers, $driver, $remoteFilePath);

        return $allItems;
    }   

    public function callApiRecursively($api_base_url, $headers, $driver, $remoteFilePath, $results = [])
    {
        $guzzleClient = new GuzzleClient(); 
        
        $download_url = "$api_base_url/api/v1/download";       
        $download_response = $guzzleClient->request('GET', $download_url, [
            'headers' => $headers,
            'verify'  => false,
        ]);       
        $download_data = json_decode($download_response->getBody());  

        if($download_data)
        {
            $base64Payload = ($download_data->base64Content) ? $download_data->base64Content : '';

            if($base64Payload != '')      
            {
                $decodedData = base64_decode($base64Payload);
                $extension = 'xml';

                $file_id = $download_data->id;
                $filename = $file_id . '.' . $extension;
                
                $stored_file = $driver->put($remoteFilePath . $filename, $decodedData);

                if($stored_file)
                {
                    $download_results = [
                        'id' => $file_id,
                        'base64Content' => $base64Payload,
                    ];

                    // Merge the current data with the previous results
                    $results = array_merge($results, $download_results);

                    $acknowledge_download_url = "$api_base_url/api/v1/download/acknowledge/" . $file_id;
                    $acknowledge_download_response = $guzzleClient->request('PUT', $acknowledge_download_url, [
                        'headers' => $headers,
                        'verify'  => false,
                    ]);       
                    $acknowledge_download_data = json_decode($acknowledge_download_response->getBody());  
                    
                    if (stripos(trim($acknowledge_download_data), 'acknowledged') !== false) 
                        return $this->callApiRecursively($api_base_url, $headers, $driver, $remoteFilePath, $results);                   
                }
            }        
        }
            
        return $results; // Return all collected data
    }
}
