<?php

namespace App\Services;

use App\Models\OcrPdf;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OcrInflowMetadataBackfillService
{
    public function run(
        int $pageSize = 50,
        bool $classifyUnmatchedAsBulkUpload = true,
        ?callable $onProgress = null
    ): array
    {
        $mailService = new MicrosoftMailService();
        $folderId = $mailService->getSubFolderId('OCR-Test', 'Done');

        if (! $folderId) {
            throw new RuntimeException('The OCR-Test/Done mailbox folder could not be found.');
        }

        $mailbox = config('services.ms.mailbox');
        $accessToken = $mailService->getAccessToken();
        $url = "https://graph.microsoft.com/v1.0/users/{$mailbox}/mailFolders/{$folderId}/messages"
            . "?\$select=id,subject,receivedDateTime,hasAttachments"
            . "&\$orderby=receivedDateTime desc"
            . "&\$top=" . max(1, min($pageSize, 999));

        $result = [
            'emails_checked' => 0,
            'attachments_checked' => 0,
            'inbox_records_updated' => 0,
            'bulk_upload_records_updated' => 0,
        ];

        while ($url) {
            $response = Http::withToken($accessToken)->get($url);

            if (! $response->successful()) {
                throw new RuntimeException(
                    'Microsoft Graph could not return the Done folder (HTTP ' . $response->status() . ').'
                );
            }

            $data = $response->json();

            foreach ($data['value'] ?? [] as $email) {
                $result['emails_checked']++;

                if (empty($email['id']) || empty($email['hasAttachments'])) {
                    if ($onProgress) {
                        $onProgress($result, 'email');
                    }

                    continue;
                }

                $attachmentResponse = Http::withToken($accessToken)->get(
                    "https://graph.microsoft.com/v1.0/users/{$mailbox}/messages/{$email['id']}/attachments"
                    . "?\$select=id,name"
                );

                if (! $attachmentResponse->successful()) {
                    throw new RuntimeException(
                        'Microsoft Graph could not return attachments for a Done email (HTTP '
                        . $attachmentResponse->status() . ').'
                    );
                }

                foreach ($attachmentResponse->json('value', []) as $attachment) {
                    $attachmentName = $attachment['name'] ?? null;

                    if (! $attachmentName || strtolower(pathinfo($attachmentName, PATHINFO_EXTENSION)) !== 'pdf') {
                        continue;
                    }

                    $result['attachments_checked']++;
                    $records = $this->recordsForAttachment(
                        $attachmentName,
                        $email['receivedDateTime'] ?? null
                    );

                    foreach ($records as $record) {
                        $record->data_from = $this->mergeSource($record->data_from, 'inbox');
                        $record->subject = $email['subject'] ?? '(No subject)';
                        $record->save();
                        $result['inbox_records_updated']++;
                    }
                }

                if ($onProgress) {
                    $onProgress($result, 'email');
                }
            }

            $url = $data['@odata.nextLink'] ?? null;
        }

        // Once every retained Done email has been checked, the remaining
        // unclassified historical rows are the bulk uploads in this dataset.
        // Preserve workflow markers such as split/recapture after the origin.
        if ($classifyUnmatchedAsBulkUpload) {
            OcrPdf::query()
                ->where(function ($query) {
                    $query->whereNull('data_from')
                        ->orWhere(function ($query) {
                            $query->where('data_from', 'not like', '%inbox%')
                                ->where('data_from', 'not like', '%bulkupload%')
                                ->where('data_from', 'not like', '%sftp%');
                        });
                })
                ->orderBy('id')
                ->chunkById(500, function ($records) use (&$result, $onProgress) {
                    foreach ($records as $record) {
                        $workflowSources = collect(explode(' - ', (string) $record->data_from))
                            ->map(fn ($source) => strtolower(trim($source)))
                            ->filter()
                            ->unique();

                        if ($workflowSources->diff(['split', 'recapture'])->isNotEmpty()) {
                            continue;
                        }

                        $record->data_from = $this->mergeSource($record->data_from, 'bulkupload');
                        $record->save();
                        $result['bulk_upload_records_updated']++;
                    }

                    if ($onProgress) {
                        $onProgress($result, 'bulk');
                    }
                });
        }

        return $result;
    }

    private function mergeSource(?string $currentSource, string $origin): string
    {
        $sources = collect(explode(' - ', (string) $currentSource))
            ->map(fn ($source) => strtolower(trim($source)))
            ->filter()
            ->reject(fn ($source) => $source === $origin)
            ->unique()
            ->values();

        return $sources->prepend($origin)->implode(' - ');
    }

    private function recordsForAttachment(string $attachmentName, ?string $receivedAt): Collection
    {
        $extension = preg_quote(pathinfo($attachmentName, PATHINFO_EXTENSION), '/');
        $baseName = preg_replace(
            '/_\d{8}_\d{6}_[a-zA-Z0-9]{4}$/',
            '',
            pathinfo($attachmentName, PATHINFO_FILENAME)
        );
        $filePattern = '/^' . preg_quote($baseName, '/')
            . '(?:_\d{8}_\d{6}_[a-zA-Z0-9]{4})?(?:_\d+)?\.' . $extension . '$/i';
        $likePrefix = addcslashes($baseName, '\\%_') . '%';

        $candidates = OcrPdf::query()
            ->where(function ($query) {
                $query->whereNull('data_from')
                    ->orWhere(function ($query) {
                        $query->whereNull('subject')
                            ->where('data_from', 'not like', '%bulkupload%')
                            ->where('data_from', 'not like', '%sftp%');
                    });
            })
            ->where('file_name', 'like', $likePrefix)
            ->get()
            ->filter(fn (OcrPdf $record) => preg_match($filePattern, $record->file_name) === 1);

        if ($candidates->isEmpty()) {
            return collect();
        }

        $received = $receivedAt ? Carbon::parse($receivedAt) : null;
        $closest = $received
            ? $candidates->sortBy(fn (OcrPdf $record) => abs($record->created_at->diffInSeconds($received, false)))->first()
            : $candidates->first();

        // One email may produce commercial/sales copies or several split rows.
        // batch_id ties those OCR rows back to the same fetched email.
        return $closest->batch_id
            ? $candidates->where('batch_id', $closest->batch_id)->values()
            : collect([$closest]);
    }
}