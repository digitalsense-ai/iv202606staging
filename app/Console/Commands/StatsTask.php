<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Client;
use App\Models\VATRegistrationMain;
use App\Models\User;
use App\Models\System;
use App\Models\SystemFiles;

use \App\Classes\CommonClass;
use \App\Classes\ApiClass;

use Illuminate\Support\Carbon;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use Storage;
use Illuminate\Support\Facades\Log;

class StatsTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Stats on 15th of every month';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {        
        try 
        {      
            $commonClass =  new CommonClass();
            $apiClass =  new ApiClass();

            $system = $commonClass->getSystemInfoLazy();
            $systemapi = $system->systemapi->first();
            $api_base_url = $systemapi->api_base_url;
            $apiUserId = $systemapi->api_user_id;
            $oneDriveRootId = $systemapi->one_drive_root_id;

            $systemfiles = $system->systemfiles;
            $file_type = 'stats';           
            $system_file = $systemfiles->filter(function ($systemfile, $key) use($file_type) {
                return $file_type == $systemfile->file_type;
            })->first();  

            //Check file exists
            $existing_file = false;            
            if($system_file)
            {
                if($system_file->file_id != null)           
                    $existing_file = $apiClass->loadFromOneDriveLazy($system_file, $systemapi);
            }

            $filename_instorage = "Stats.xlsx";
            $storage_path = storage_path('app/public/');
            $filename = $storage_path.$filename_instorage;

            if($existing_file)
            {
                //Store in public folder                
                $url = $existing_file['download_url'];
                $contents = (strpos($url, "https://") !== false) ? file_get_contents($url) : $url;  
                file_put_contents($filename, $contents);

                //Open existing file
                $spreadSheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filename);
                $sheet = $spreadSheet->getActiveSheet();
            }
            else
            {
                //Create new file
                $spreadSheet = new Spreadsheet();           
                $sheet = $spreadSheet->getActiveSheet();

                //Create header
                $sheet->setCellValue('A1', 'Date');
                $sheet->setCellValue('B1', 'Companies');
                $sheet->setCellValue('C1', 'Active companies');
                $sheet->setCellValue('D1', 'VAT regs.');
                $sheet->setCellValue('E1', 'Active VAT regs.');
                $sheet->setCellValue('F1', 'Client users');
                $sheet->setCellValue('G1', 'Active client users');
                $sheet->setCellValue('H1', 'Team users');
                $sheet->setCellValue('I1', 'Active team users');
                $sheet->setCellValue('J1', 'Admin users');
                $sheet->setCellValue('K1', 'Active admin users');
                $sheet->setCellValue('L1', 'Company Admin users');
                $sheet->setCellValue('M1', 'Active company admin users');

                //Header style
                $range = 'A1:M1';       
                $style = [
                    'font'  => [
                        'bold'  => true,
                        'color' => array('rgb' => 'FFFFFF'),                
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => '4F81BD']
                    ],
                ];
                $sheet->getStyle($range)->applyFromArray($style);       

                //Cell width
                foreach (range('A', 'M') as $letra) {            
                    $sheet->getColumnDimension($letra)->setAutoSize(false);
                    $sheet->getColumnDimension($letra)->setWidth(20);
                }
            }

            //Get Companies       
            $companies       = Client::get();
            $total_companies  = $companies->count();
            $active_companies   = $companies->where('status', 1)->count();
            
            //Get VAT Reg. Main       
            $vat_reg_main       = VATRegistrationMain::get();
            $total_vat_reg_main  = $vat_reg_main->count();
            $active_vat_reg_main   = $vat_reg_main->where('status', 1)->count();
            
            //Get Users  
            $users = $commonClass->getUsersLazy();
            $total_users = $users->count();
            $active_users = $users->filter(function ($user, $key) {                   
                return $user->dvuser->status; 
            })->count();

            //Get Client Users 
            $client_users = $users->filter(function ($user, $key) {
                return $user->roles->contains('name', 'client-user');
            });
            $total_client_users = $client_users->count();
            $active_client_users = $client_users->filter(function ($user, $key) {                   
                return $user->dvuser->status; 
            })->count();

            //Get Team Users 
            $team_users = $users->filter(function ($user, $key) { 
                return $user->roles->contains('name', 'team-user');
            });
            $total_team_users = $team_users->count();
            $active_team_users = $team_users->filter(function ($user, $key) {                   
                return $user->dvuser->status; 
            })->count();

            //Get Admin Users 
            $admin_users = $users->filter(function ($user, $key) {
                return $user->roles->contains('name', 'super-admin'); 
            });
            $total_admin_users = $admin_users->count();
            $active_admin_users = $admin_users->filter(function ($user, $key) {                   
                return $user->dvuser->status; 
            })->count();

            //Get Company Admin Users 
            $company_admin_users = $users->filter(function ($user, $key) {
                return $user->roles->contains('name', 'company-admin'); 
            });
            $total_company_admin_users = $company_admin_users->count();
            $active_company_admin_users = $company_admin_users->filter(function ($user, $key) {                   
                return $user->dvuser->status; 
            })->count();
         
            //Get row height
            $row = $sheet->getHighestRow()+1;

            $sheet->setCellValue('A'.$row, Carbon::now()->format('d.m.Y'));
            $sheet->setCellValue('B'.$row, $total_companies);
            $sheet->setCellValue('C'.$row, $active_companies);
            $sheet->setCellValue('D'.$row, $total_vat_reg_main);
            $sheet->setCellValue('E'.$row, $active_vat_reg_main);
            $sheet->setCellValue('F'.$row, $total_client_users);
            $sheet->setCellValue('G'.$row, $active_client_users);
            $sheet->setCellValue('H'.$row, $total_team_users);
            $sheet->setCellValue('I'.$row, $active_team_users);
            $sheet->setCellValue('J'.$row, $total_admin_users);
            $sheet->setCellValue('K'.$row, $active_admin_users);
            $sheet->setCellValue('L'.$row, $total_company_admin_users);
            $sheet->setCellValue('M'.$row, $active_company_admin_users);
                
            //Save file 
            $Excel_writer = new Xlsx($spreadSheet);         
            $Excel_writer->save($filename);

            //Move to Onedrive
            $filecontent = file_get_contents($filename);
            $file[0] = $filecontent;

            $access_token = $apiClass->getMicrosoftGraphAccessTokenLazy($systemapi); 
            $access_token = ($access_token == "not expired") ?  $systemapi->api_token : $access_token;

            $fileDetails = $apiClass->uploadSystemFileInOneDrive($api_base_url, $access_token, $apiUserId, $oneDriveRootId, $file, $filename_instorage);

            //Update the file ID in DB
            if(count($fileDetails) > 0)
            {                
                $system_files = SystemFiles::updateOrCreate(  
                    [
                        'file_type' => 'stats'
                    ],                    
                    [
                        'system_id' => $system->id,
                        'file_id' => $fileDetails[0]['fileId'], 
                        'file_name' => $fileDetails[0]['fileName'], 
                        'file_size' => $fileDetails[0]['fileSize']
                    ]
                );
            }

            //Delete from public folder
            Storage::disk('public')->delete($filename_instorage);

            $this->info('Stats has been updated successfully');
            Log::info('Stats has been updated successfully');          
      }
      catch (Exception $e) {
        Log::error('stats:task - ' . $e->getMessage());
        return  $e->getMessage();
      }
    }
}
