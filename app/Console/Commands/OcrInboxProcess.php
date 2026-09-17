<?php

namespace App\Console\Commands;

use App\Services\OcrProcessingService;
use Illuminate\Console\Command;

class OcrInboxProcess extends Command
{
    protected $signature = 'ocrinbox:process';

    protected $description = 'Fetch unread inbox emails and queue them for OCR processing';

    public function handle(OcrProcessingService $inboxService): int
    {
        $this->info('Starting OCR inbox processing...');

        try {

            $result = $inboxService->fetchAndQueueInbox();

            $this->info(
                "Queued {$result['total']} email(s) for OCR processing."
            );

            return self::SUCCESS;

        } catch (\Throwable $e) {

            $this->error(
                'OCR Inbox processing failed: ' . $e->getMessage()
            );

            report($e);

            return self::FAILURE;
        }
    }
}