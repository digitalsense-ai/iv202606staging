<?php

namespace App\Http\Controllers\ocr;

use App\Http\Controllers\Controller;
use App\Jobs\ManualInputUpdateJob;
use App\Models\Client;
use App\Models\OcrPdf;
use App\Models\VATRegistrationMain;
use App\Services\OcrAnalyzeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManualInputController extends Controller
{
    public function index()
    {
        return view('content.ocr.manual-input');
    }

    public function queue(): JsonResponse
    {
        $items = $this->baseQueueQuery()
            ->orderBy('updated_at')
            ->orderBy('id')
            ->get()
            ->map(fn (OcrPdf $invoice) => $this->summaryPayload($invoice))
            ->values();

        return response()->json([
            'total' => $items->count(),
            'items' => $items,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $invoice = OcrPdf::query()->findOrFail($id);

        return response()->json([
            'item' => $this->detailPayload($invoice),
            'position' => $this->positionFor($invoice->id),
            'total' => $this->baseQueueQuery()->count(),
        ]);
    }

    public function save(Request $request, int $id): JsonResponse
    {
        $invoice = OcrPdf::query()->findOrFail($id);

        $invoice->update([
            'manual_input_status' => 'queued',
            'manual_input_at' => now(),
            'manual_input_by' => auth()->id(),
        ]);

        ManualInputUpdateJob::dispatch(
            $id,
            $request->all(),
            false,
            auth()->id()
        );

        return response()->json($this->queuedResponse($id));
    }

    public function forceSubmit(Request $request, int $id): JsonResponse
    {
        $invoice = OcrPdf::query()->findOrFail($id);

        $invoice->update([
            'manual_input_status' => 'queued',
            'manual_input_at' => now(),
            'manual_input_by' => auth()->id(),
            'force_submitted' => true,
        ]);

        ManualInputUpdateJob::dispatch(
            $id,
            $request->all(),
            true,
            auth()->id()
        );

        return response()->json($this->queuedResponse($id));
    }

    public function destroy(int $id): JsonResponse
    {
        $invoice = OcrPdf::query()->findOrFail($id);

        $invoice->update([
            'is_deleted' => 1,
            'deleted_reason' => 'Deleted from manual input workflow',
            'is_locked' => 0,
        ]);

        return response()->json($this->nextResponse($invoice->id));
    }

    public function clientLookup(Request $request): JsonResponse
    {
        $clientNo = trim((string) $request->query('client_no'));

        if ($clientNo === '') {
            return response()->json(['client' => null]);
        }

        $vatRegistration = $this->vatRegistrationForOrgNo($clientNo);

        if ($vatRegistration && $vatRegistration->client) {
            return response()->json([
                'client' => [
                    'id' => $vatRegistration->client->id,
                    'name' => $vatRegistration->client->client_name,
                    'client_no' => $clientNo,
                    'country_code' => $vatRegistration->country,
                ],
            ]);
        }

        $client = Client::query()
            ->where('client_name', 'like', '%' . $clientNo . '%')
            ->first();

        return response()->json([
            'client' => $client ? [
                'id' => $client->id,
                'name' => $client->client_name,
                'client_no' => $clientNo,
                'country_code' => null,
            ] : null,
        ]);
    }

    private function baseQueueQuery()
    {
        return OcrPdf::query()
            ->where('is_deleted', 0)
            ->where('status', 'failed')
            ->where(function ($query) {
                $query->whereNull('manual_input_status')
                    ->orWhereNotIn('manual_input_status', ['queued', 'processing', 'validation_queued']);
            });
    }

    private function summaryPayload(OcrPdf $invoice): array
    {
        $data = $invoice->extracted_data ?? [];
        $clientNo = data_get($data, 'recipient.org_number') ?? data_get($data, 'supplier.org_number');
        $vatRegistration = $this->vatRegistrationForOrgNo($clientNo);

        return [
            'id' => $invoice->id,
            'file_name' => $invoice->file_name,
            'invoice_type' => $invoice->invoice_type,
            'invoice_type_name' => $this->invoiceTypeName($invoice->invoice_type),
            'client_no' => $clientNo,
            'client_name' => $vatRegistration?->client?->client_name
                ?? data_get($data, 'recipient.name')
                ?? data_get($data, 'supplier.name'),
            'country_code' => $vatRegistration?->country,
            'invoice_no' => data_get($data, 'invoice_number'),
            'invoice_date' => data_get($data, 'invoice_date'),
            'error' => $invoice->error,
            'status' => $invoice->status,
            'validation_status' => $invoice->validation_status,
            'manual_input_status' => $invoice->manual_input_status,
            'updated_at' => optional($invoice->updated_at)->format('d-m-Y'),
            'created_at' => optional($invoice->created_at)->format('d-m-Y'),
        ];
    }

    private function detailPayload(OcrPdf $invoice): array
    {
        $data = $invoice->extracted_data ?? [];
        $clientNo = data_get($data, 'recipient.org_number') ?? data_get($data, 'supplier.org_number');
        $vatRegistration = $this->vatRegistrationForOrgNo($clientNo);

        return array_merge($this->summaryPayload($invoice), [
            'client_name' => $vatRegistration?->client?->client_name
                ?? data_get($data, 'recipient.name')
                ?? data_get($data, 'supplier.name'),
            'country_code' => $vatRegistration?->country,
            'credit_note' => (bool) data_get($data, 'credit_note', false),
            'currency' => data_get($data, 'currency'),
            'exchange_currency' => data_get($data, 'exchange_currency'),
            'vat_rate' => data_get($data, 'vat_rate'),
            'exchange_rate' => data_get($data, 'exchange_rate'),
            'net_amount' => data_get($data, 'net_amount'),
            'exchange_net_amount' => data_get($data, 'exchange_net_amount'),
            'vat_amount' => data_get($data, 'vat_amount'),
            'exchange_vat_amount' => data_get($data, 'exchange_vat_amount'),
            'total_amount' => data_get($data, 'total_amount'),
            'exchange_total_amount' => data_get($data, 'exchange_total_amount'),
            'related_sales_invoices' => $this->referencesAsArray(data_get($data, 'related_sales_invoices')),
            'note' => data_get($data, 'manual_note') ?? $invoice->manual_note,
            'azure_url' => $invoice->azure_url,
            'sas_url' => app(OcrAnalyzeService::class)->getSasUrl($invoice->id),
        ]);
    }

    private function nextResponse(int $currentId): array
    {
        $next = $this->nextQueueItem($currentId);
        $total = $this->baseQueueQuery()->count();

        return [
            'total' => $total,
            'next' => $next,
            'position' => $next ? $this->positionFor($next['id']) : null,
        ];
    }

    private function queuedResponse(int $currentId): array
    {
        $next = $this->nextQueueItem($currentId);
        $total = $this->baseQueueQuery()->count();

        return [
            'queued' => true,
            'total' => $total,
            'next' => $next,
            'position' => $next ? $this->positionFor($next['id']) : null,
        ];
    }

    private function nextQueueItem(int $currentId): ?array
    {
        $next = $this->baseQueueQuery()
            ->where('id', '!=', $currentId)
            ->orderBy('updated_at')
            ->orderBy('id')
            ->first();

        return $next ? $this->detailPayload($next) : null;
    }

    private function positionFor(int $id): ?int
    {
        $ids = $this->baseQueueQuery()
            ->orderBy('updated_at')
            ->orderBy('id')
            ->pluck('id')
            ->values();

        $index = $ids->search($id);

        return $index === false ? null : $index + 1;
    }

    private function vatRegistrationForOrgNo(?string $orgNo): ?VATRegistrationMain
    {
        $normalized = preg_replace('/\D+/', '', (string) $orgNo);

        if ($normalized === '') {
            return null;
        }

        return VATRegistrationMain::query()
            ->with('client')
            ->get()
            ->first(function (VATRegistrationMain $vatRegistration) use ($normalized) {
                return $this->normalizedEquals($vatRegistration->org_no, $normalized)
                    || $this->normalizedEquals($vatRegistration->vat_no, $normalized)
                    || $this->normalizedEquals($vatRegistration->cvr_no, $normalized)
                    || $this->normalizedEquals($vatRegistration->mva_no, $normalized)
                    || $this->normalizedEquals($vatRegistration->eori_no, $normalized);
            });
    }

    private function normalizedEquals(?string $value, string $normalized): bool
    {
        return preg_replace('/\D+/', '', (string) $value) === $normalized;
    }

    private function referencesAsArray($value): array
    {
        if (is_array($value)) {
            return array_values(array_filter($value));
        }

        if (is_string($value)) {
            return array_values(array_filter(array_map('trim', explode(',', $value))));
        }

        return [];
    }

    private function invoiceTypeName(?string $invoiceType): string
    {
        switch ($invoiceType) {
            case 'com':
                return 'Commercial Invoice';
            case 'multi-invoices':
                return 'Multi invoices in single PDF';
            case 'sales':
                return 'Sales Invoice';
            default:
                return ucfirst((string) $invoiceType);
        }
    }
}
