<?php

namespace App\Http\Controllers\ocr;

use App\Http\Controllers\Controller;
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
        $this->applyManualInput($invoice, $request, false);

        return response()->json($this->nextResponse($invoice->id));
    }

    public function forceSubmit(Request $request, int $id): JsonResponse
    {
        $invoice = OcrPdf::query()->findOrFail($id);
        $this->applyManualInput($invoice, $request, true);

        return response()->json($this->nextResponse($invoice->id));
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

        $vatRegistration = VATRegistrationMain::query()
            ->with('client')
            ->get()
            ->first(function (VATRegistrationMain $vatRegistration) use ($clientNo) {
                return in_array($clientNo, array_filter([
                    $vatRegistration->org_no,
                    $vatRegistration->vat_no,
                    $vatRegistration->cvr_no,
                    $vatRegistration->mva_no,
                    $vatRegistration->eori_no,
                ]), true);
            });

        if ($vatRegistration && $vatRegistration->client) {
            return response()->json([
                'client' => [
                    'id' => $vatRegistration->client->id,
                    'name' => $vatRegistration->client->client_name,
                    'client_no' => $clientNo,
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
            ] : null,
        ]);
    }

    private function baseQueueQuery()
    {
        return OcrPdf::query()
            ->where('is_deleted', 0)
            ->where(function ($query) {
                $query->where('status', 'failed')
                    ->orWhere('validation_status', 'not_yet_validated')
                    ->orWhereNotNull('error');
            });
    }

    private function summaryPayload(OcrPdf $invoice): array
    {
        $data = $invoice->extracted_data ?? [];

        return [
            'id' => $invoice->id,
            'file_name' => $invoice->file_name,
            'invoice_type' => $invoice->invoice_type,
            'invoice_type_name' => $this->invoiceTypeName($invoice->invoice_type),
            'client_no' => data_get($data, 'recipient.org_number') ?? data_get($data, 'supplier.org_number'),
            'client_name' => data_get($data, 'recipient.name') ?? data_get($data, 'supplier.name'),
            'invoice_no' => data_get($data, 'invoice_number'),
            'invoice_date' => data_get($data, 'invoice_date'),
            'error' => $invoice->error,
            'status' => $invoice->status,
            'validation_status' => $invoice->validation_status,
            'updated_at' => optional($invoice->updated_at)->format('d-m-Y'),
            'created_at' => optional($invoice->created_at)->format('d-m-Y'),
        ];
    }

    private function detailPayload(OcrPdf $invoice): array
    {
        $data = $invoice->extracted_data ?? [];

        return array_merge($this->summaryPayload($invoice), [
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
            'note' => data_get($data, 'manual_note'),
            'sas_url' => app(OcrAnalyzeService::class)->getSasUrl($invoice->id),
        ]);
    }

    private function applyManualInput(OcrPdf $invoice, Request $request, bool $force): void
    {
        $data = $invoice->extracted_data ?? [];

        data_set($data, 'invoice_type', $request->input('invoice_type'));
        data_set($data, 'recipient.org_number', $request->input('client_no'));
        data_set($data, 'recipient.name', $request->input('client_name'));
        data_set($data, 'supplier.org_number', $request->input('client_no'));
        data_set($data, 'supplier.name', $request->input('client_name'));
        data_set($data, 'invoice_date', $request->input('invoice_date'));
        data_set($data, 'invoice_number', $request->input('invoice_no'));
        data_set($data, 'credit_note', $request->boolean('credit_note'));
        data_set($data, 'currency', $request->input('currency'));
        data_set($data, 'exchange_currency', $request->input('exchange_currency'));
        data_set($data, 'vat_rate', $request->input('vat_rate'));
        data_set($data, 'exchange_rate', $request->input('exchange_rate'));
        data_set($data, 'net_amount', $request->input('net_amount'));
        data_set($data, 'exchange_net_amount', $request->input('exchange_net_amount'));
        data_set($data, 'vat_amount', $request->input('vat_amount'));
        data_set($data, 'exchange_vat_amount', $request->input('exchange_vat_amount'));
        data_set($data, 'total_amount', $request->input('total_amount'));
        data_set($data, 'exchange_total_amount', $request->input('exchange_total_amount'));
        data_set($data, 'related_sales_invoices', array_values(array_filter((array) $request->input('related_sales_invoices', []))));
        data_set($data, 'manual_note', $request->input('note'));
        data_set($data, 'manual_input.force_submitted', $force);
        data_set($data, 'manual_input.updated_at', now()->toDateTimeString());

        $missing = $this->missingRequiredFields($data);

        $invoice->update([
            'invoice_type' => $request->input('invoice_type', $invoice->invoice_type),
            'extracted_data' => $data,
            'status' => ($missing && !$force) ? 'failed' : 'completed',
            'error' => ($missing && !$force) ? implode("\n", $missing) : null,
            'validation_status' => ($missing && !$force) ? 'not_yet_validated' : 'validated_with_changes',
            'sync_status' => 0,
            'is_locked' => 0,
        ]);
    }

    private function missingRequiredFields(array $data): array
    {
        $missing = [];

        foreach ([
            'invoice_type' => 'Document type missing',
            'invoice_date' => 'Invoice Date missing',
            'invoice_number' => 'Invoice no. missing',
            'currency' => 'Currency missing',
            'total_amount' => 'Total amount missing',
        ] as $field => $message) {
            if (blank(data_get($data, $field))) {
                $missing[] = $message;
            }
        }

        if (blank(data_get($data, 'recipient.org_number')) && blank(data_get($data, 'supplier.org_number'))) {
            $missing[] = 'Client no. missing';
        }

        return $missing;
    }

    private function nextResponse(int $currentId): array
    {
        $items = $this->baseQueueQuery()
            ->orderBy('updated_at')
            ->orderBy('id')
            ->get();

        $next = $items->firstWhere('id', '!=', $currentId);

        return [
            'total' => $items->count(),
            'next' => $next ? $this->detailPayload($next) : null,
            'position' => $next ? $this->positionFor($next->id) : null,
        ];
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

    private function referencesAsArray(mixed $value): array
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
        return match ($invoiceType) {
            'com' => 'Commercial Invoice',
            'multi-invoices' => 'Multi invoices in single PDF',
            'sales' => 'Sales Invoice',
            default => ucfirst((string) $invoiceType),
        };
    }
}
