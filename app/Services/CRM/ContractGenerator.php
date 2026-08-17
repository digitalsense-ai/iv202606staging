<?php

namespace App\Services\CRM;

use App\Models\CRMContractTemplate;
use App\Models\CRMQuote;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

class ContractGenerator
{
    public function build(CRMQuote $quote): array
    {
        $quote->loadMissing(['lead.contact', 'addons']);

        $outputDirectory = storage_path('app/crm/contracts/' . $quote->id);
        File::ensureDirectoryExists($outputDirectory);

        $baseName = $this->safeFilename($quote);
        $html = view('content.crm.contracts.generated', [
            'quote' => $quote,
            'mergeValues' => $this->mergeValues($quote),
            'clauses' => $this->renderedClauses($quote),
            'productRows' => $this->productRows($quote),
            'templateIncludesProductOverview' => $this->templateIncludesProductOverview(),
        ])->render();

        $docxPath = $outputDirectory . '/' . $baseName . '.docx';
        $pdfPath = $outputDirectory . '/' . $baseName . '.pdf';

        $this->writeDocx($docxPath, $html);
        $this->writePdf($pdfPath, $html);

        return [
            'docx' => $docxPath,
            'pdf' => $pdfPath,
        ];
    }

    public function mergeValues(CRMQuote $quote): array
    {
        $lead = $quote->lead;
        $contact = $lead?->contact;
        $countryCode = $this->firstValue($lead?->potential_countries) ?: $lead?->company_country;

        return [
            '[[DATE]]' => now()->format('d-m-Y'),
            '[[CLIENT_NAME]]' => $lead?->company_name,
            '[[CLIENT_ADDRESS]]' => $lead?->company_address,
            '[[CLIENT_POSTCODE]]' => $lead?->company_postcode,
            '[[CLIENT_CITY]]' => $lead?->company_city,
            '[[CLIENT_REG_NO]]' => $lead?->cvr_number,
            '[[COUNTRY_CODE]]' => $countryCode,
            '[[COUNTRY_NAME]]' => $lead?->company_country,
            '[[AGREEMENT_TYPE]]' => Str::headline($quote->package),
            '[[CONTACT_PERSON]]' => trim(($contact?->first_name ?? '') . ' ' . ($contact?->last_name ?? '')) ?: $lead?->company_name,
            '[[PRICE_YEAR]]' => Carbon::parse($quote->created_at ?: now())->format('Y'),
            '[[PAYMENT_TERMS]]' => 'As stated in the agreement.',
            '[[SPECIAL_NOTES]]' => '',
            '[[COMPANY_OWNER_SIGNATORY]]' => config('app.name'),
            '[[CLIENT_SIGNATORY]]' => trim(($contact?->first_name ?? '') . ' ' . ($contact?->last_name ?? '')),
            '[[DISCOUNT_PERIOD]]' => '',
        ];
    }

    public function productRows(CRMQuote $quote): array
    {
        $rows = [];

        if ((float) $quote->registration_price > 0) {
            $rows[] = $this->productRow('Registration & Setup', 'Registration and setup services', 'one_time', (float) $quote->registration_price);
        }

        if ((float) $quote->base_price > 0) {
            $rows[] = $this->productRow(Str::headline($quote->package), 'Package service for the selected country and client scope', 'monthly', (float) $quote->base_price);
        }

        foreach ($quote->addons->where('enabled', true) as $addon) {
            $standardPrice = (float) ($addon->standard_price ?? $addon->price ?? 0);
            $discountAmount = (float) ($addon->discount_amount ?? max(0, $standardPrice - (float) $addon->price));

            $rows[] = $this->productRow(
                $addon->addon_name,
                $addon->description ?: 'Country-specific service: ' . $addon->addon_name,
                $addon->interval ?: 'Add-on',
                $standardPrice
            );

            if ($discountAmount > 0) {
                $rows[] = $this->productRow(
                    $addon->addon_name,
                    'Discount',
                    $addon->interval ?: 'Add-on',
                    -1 * $discountAmount
                );
            }
        }

        return $rows;
    }

    private function renderedClauses(CRMQuote $quote): array
    {
        $mergeValues = $this->mergeValues($quote);

        return CRMContractTemplate::orderBy('clause_number')
            ->get()
            ->map(function (CRMContractTemplate $template) use ($mergeValues) {
                $content = str_replace(array_keys($mergeValues), array_values($mergeValues), $template->content);
                $content = str_replace(['[[PRODUCT_OVERVIEW]]', '[[PRODUCT_TABLE]]'], $this->productTableHtml($quote), $content);
                $content = $this->processConditionalText($content);

                return [
                    'number' => $template->clause_number,
                    'title' => str_replace(array_keys($mergeValues), array_values($mergeValues), $template->title ?? ''),
                    'content' => $content,
                ];
            })
            ->filter(fn ($clause) => trim(strip_tags($clause['content'] . $clause['title'])) !== '')
            ->values()
            ->all();
    }

    private function processConditionalText(string $content): string
    {
        $content = preg_replace('/^\s*YES\|\|\|\s*/mi', '', $content);
        $content = preg_replace('/^\s*NO\|\|\|.*(?:\R|$)/mi', '', $content);

        return $content ?? '';
    }

    private function productTableHtml(CRMQuote $quote): string
    {
        return view('content.crm.contracts.partials.product-table', [
            'productRows' => $this->productRows($quote),
        ])->render();
    }

    private function templateIncludesProductOverview(): bool
    {
        return CRMContractTemplate::where('content', 'like', '%[[PRODUCT_OVERVIEW]]%')
            ->orWhere('content', 'like', '%[[PRODUCT_TABLE]]%')
            ->exists();
    }

    private function productRow(string $service, string $description, string $interval, float $price): array
    {
        return compact('service', 'description', 'interval', 'price');
    }

    private function writePdf(string $path, string $html): void
    {
        app('dompdf.wrapper')->loadHTML($html)->setPaper('a4')->save($path);
    }

    private function writeDocx(string $path, string $html): void
    {
        $text = htmlspecialchars(trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html))), ENT_XML1 | ENT_COMPAT, 'UTF-8');
        $paragraphs = collect(preg_split('/\R+/', $text) ?: [])
            ->map(fn ($line) => '<w:p><w:r><w:t xml:space="preserve">' . $line . '</w:t></w:r></w:p>')
            ->implode('');

        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>');
        $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>' . $paragraphs . '<w:sectPr/></w:body></w:document>');
        $zip->close();
    }

    private function firstValue($value): ?string
    {
        if (is_array($value)) {
            return $value[0] ?? null;
        }

        return $value;
    }

    private function safeFilename(CRMQuote $quote): string
    {
        return Str::slug(($quote->lead?->company_name ?: 'contract') . '-quote-' . $quote->id . '-v' . $quote->version);
    }
}
