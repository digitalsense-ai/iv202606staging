<?php

namespace App\Console\Commands;

use App\Services\OcrProcessingService;
use Illuminate\Console\Command;

class OcrSFtpProcess extends Command
{
    protected $signature = 'ocrsftp:process {--which_folder=main}';

    protected $description = 'Fetch unread sftp files and queue them for OCR processing';

    public function handle(OcrProcessingService $sFtpService): int
    {
        $this->info('Starting OCR SFTP processing...');

        try {
            $whichFolder = $this->option('which_folder');
            $result = $sFtpService->fetchAndQueueSFtp($whichFolder);

            $this->info(
                "Queued {$result['total']} file(s) for OCR processing."
            );

            return self::SUCCESS;

        } catch (\Throwable $e) {

            $this->error(
                'OCR SFTP processing failed: ' . $e->getMessage()
            );

            report($e);

            return self::FAILURE;
        }
    }
}