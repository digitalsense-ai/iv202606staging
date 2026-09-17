<?php

namespace App\Jobs;

use App\Services\OcrProcessingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutoRefreshNewDeclarationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected $authUser
    ) {
    }

    public function handle(OcrProcessingService $syncService): void
    {
        $result = $syncService->autoRefreshNewDeclaration(
            $this->authUser
        );

        Log::info('OCR auto refresh completed', $result);
    }
}
