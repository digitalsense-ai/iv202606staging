<?php

namespace App\Console\Commands;

use App\Services\OcrProcessingService;
use Illuminate\Console\Command;

use \App\Classes\CommonClass;

class OcrSyncDbProcess extends Command
{
    protected $signature = 'ocrsyncdb:process';

    protected $description = 'Sync OCR completed datas into DB for System processing';

    public function handle(OcrProcessingService $syncService): int
    {
        $this->info('Starting OCR sync processing...');

        try {
            $commonClass =  new CommonClass();           
            $authUser = $commonClass->getAuthUser(1); 

            $result = $syncService->syncDbFromOcr($authUser);

            $this->info(
                "Queued {$result['totalSync']} OCR completed datas into DB for OCR syncing."
            );

            return self::SUCCESS;

        } catch (\Throwable $e) {

            $this->error(
                'OCR Sync processing failed: ' . $e->getMessage()
            );

            report($e);

            return self::FAILURE;
        }
    }
}