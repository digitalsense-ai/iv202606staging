<?php

namespace App\Console\Commands;

use App\Services\OcrInflowMetadataBackfillService;
use Illuminate\Console\Command;
use Throwable;

class BackfillOcrInflowMetadata extends Command
{
    protected $signature = 'ocr:backfill-inflow-metadata
                            {--page-size=50 : Microsoft Graph page size}
                            {--no-classify-unmatched : Leave unmatched data_from values null}';

    protected $description = 'Backfill OCR source and email subject metadata for historical records';

    public function handle(OcrInflowMetadataBackfillService $service): int
    {
        $this->info('Scanning OCR-Test/Done emails and matching attachments...');

        try {
            $result = $service->run(
                (int) $this->option('page-size'),
                ! $this->option('no-classify-unmatched'),
                function (array $progress, string $phase): void {
                    $message = $phase === 'email'
                        ? sprintf(
                            'Emails: %d | Attachments: %d | Inbox rows: %d',
                            $progress['emails_checked'],
                            $progress['attachments_checked'],
                            $progress['inbox_records_updated']
                        )
                        : sprintf(
                            'Classifying bulk-upload rows: %d',
                            $progress['bulk_upload_records_updated']
                        );

                    $this->output->write("\r{$message}");
                }
            );
        } catch (Throwable $exception) {
            $this->newLine();
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->newLine(2);
        $this->table(['Metric', 'Count'], collect($result)->map(
            fn ($count, $metric) => [$metric, $count]
        )->values()->all());

        return self::SUCCESS;
    }
}