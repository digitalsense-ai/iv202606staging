<?php

namespace App\Services;

class PdfInvoiceParser
{
    protected string $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function parse(): array
    {
        $lines = explode("\n", $this->content);
        $structured = [];
        $structured['client_details'] = [];
        // Keep a counter of VAT numbers
        $vatCounter = 0;

        // First pass: collect all VAT numbers
        foreach ($lines as $line) {
            $line = trim($line);
            if (stripos($line, 'Momsnummer') !== false) {
                $vatNumbers[] = trim(str_ireplace('Momsnummer', '', $line));
            }
        }

        for ($i = 0; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (empty($line)) continue;

            // Headline
            if (!isset($structured['headline']) && preg_match('/^Faktura$/i', $line)) {
                $structured['headline'] = $line;
                continue;
            }

            // Invoice number (starts with NO)
            if (!isset($structured['invoice_number']) && preg_match('/^NO\d+/', $line, $matches)) {
                $structured['invoice_number'] = $matches[0];
                continue;
            }

            // Invoice date (dd-mm-yyyy)
            if (!isset($structured['invoice_date']) && preg_match('/\d{2}-\d{2}-\d{4}/', $line, $matches)) {
                $structured['invoice_date'] = $matches[0];
                continue;
            }

            // Terms of payment
            if (!isset($structured['terms_of_payment']) && stripos($line, 'Betalingsbetingelser') !== false) {
                $structured['terms_of_payment'] = trim($lines[$i + 1] ?? '');
                $i++;
                continue;
            }
            
            // Ordrebehandler (value in next line)
            if (!isset($structured['order_processor']) && stripos($line, 'Ordrebehandler') !== false) {
                $nextLine = trim($lines[$i + 1] ?? '');
                // If next line is "Ordre nr.", use hyphen
                if (stripos($nextLine, 'Ordre nr.') !== false || empty($nextLine)) {
                    $structured['order_processor'] = '-';
                } else {
                    $structured['order_processor'] = $nextLine;
                }
                $i++; // Skip the next line
                continue;
            }

            // Order no (value in next line)
            if (!isset($structured['order_no']) && stripos($line, 'Ordre nr.') !== false) {
                $structured['order_no'] = trim($lines[$i + 1] ?? '');
                $i++;
                continue;
            }

            // Referanse (value in next line)
            if (!isset($structured['referanse']) && stripos($line, 'Referanse') !== false) {
                $structured['referanse'] = trim($lines[$i + 1] ?? '');
                $i++;
                continue;
            }

            // Invoice address
            if (!isset($structured['invoice_address']) && stripos($line, 'Fakturaadresse') !== false) {
                $addressLine = trim(str_ireplace('Fakturaadresse', '', $line));
                $vat_number = $vatNumbers[$vatCounter] ?? null;
                $vatCounter++;
                $structured['invoice_address'] = $this->parseAddressLine($addressLine, $vat_number);
                continue;
            }

            // Delivery address
            if (!isset($structured['delivery_address']) && stripos($line, 'Leveringsadresse') !== false) {
                $addressLine = trim(str_ireplace('Leveringsadresse', '', $line));
                $vat_number = $vatNumbers[$vatCounter] ?? null;
                $vatCounter++;
                $structured['delivery_address'] = $this->parseAddressLine($addressLine, $vat_number);
                continue;
            }

            // // Item fields
            // if (!isset($structured['item_number']) && stripos($line, 'Produktbeskrivelse') !== false) {
            //     $structured['item_number'] = trim($lines[$i + 1] ?? '');
            //     $i++;
            //     continue;
            // }
            // if (!isset($structured['item_code']) && stripos($line, 'Artikkelkode') !== false) {
            //     $structured['item_code'] = trim($lines[$i + 1] ?? '');
            //     $i++;
            //     continue;
            // }

            // Currency
            if (!isset($structured['currency']) && (stripos($line, 'Valuta') !== false || stripos($line, 'Merverdiavgift valuta') !== false)) {
                // Extract the last uppercase word as currency code
                if (preg_match('/([A-Z]{2,3})$/', $line, $match)) {
                    $structured['currency'] = $match[1];
                }
                continue;
            }

            // Net, VAT, Total (values in next line)
            if (stripos($line, 'Netto') === 0 && !isset($structured['net_amount'])) {
                $structured['net_amount'] = trim($lines[$i + 1] ?? '');
                $i++;
                continue;
            }
            if (stripos($line, 'Mva-beløp') !== false && !isset($structured['vat_amount'])) {
                $structured['vat_amount'] = trim($lines[$i + 1] ?? '');
                $i++;
                continue;
            }
            if (stripos($line, 'Total inkl. moms') !== false && !isset($structured['total_amount'])) {
                $structured['total_amount'] = trim($lines[$i + 1] ?? '');
                $i++;
                continue;
            }

            // Client Name, Address etc.,
            if ($line === 'Nobb') {
                $client_lines = [];
                $j = $i + 1;

                // Collect lines until 'Faktura'
                while ($j < count($lines)) {
                    $currentLine = trim($lines[$j]);
                    if (stripos($currentLine, 'Faktura') !== false) {
                        break;
                    }
                    $client_lines[] = $currentLine;
                    $j++;
                }

                $client_text = implode(' ', $client_lines);

                // Extract Tel, Fax, Website, Email
                preg_match('/Tel:\s*([\+\d\s]+)/i', $client_text, $telMatch);
                preg_match('/Fax:\s*([\+\d\s]+)/i', $client_text, $faxMatch);
                preg_match('/www\.[\w\.\-\/]+/i', $client_text, $webMatch);
                preg_match('/[\w\.\-]+@[\w\.\-]+/i', $client_text, $emailMatch);

                $telephone = $telMatch[1] ?? null;
                $fax = $faxMatch[1] ?? null;
                $website = $webMatch[0] ?? null;
                $email = $emailMatch[0] ?? null;

                // Remove Tel, Fax, Website, Email
                $client_text = preg_replace([
                    '/Tel:\s*[\+\d\s]+/i',
                    '/Fax:\s*[\+\d\s]+/i',
                    '/www\.[\w\.\-\/]+/i',
                    '/[\w\.\-]+@[\w\.\-]+/i'
                ], '', $client_text);

                // Cut everything after 'EAN-Kode'
                if (($pos = stripos($client_text, 'EAN-Kode')) !== false) {
                    $client_text = substr($client_text, 0, $pos);
                }

                // Split at first 'A/S' (company name)
                if (preg_match('/^(.*A\/S)\s+(.*)$/i', $client_text, $matches)) {
                    $company_name = trim($matches[1]);
                    $address_line = trim($matches[2]);
                } else {
                    $company_name = trim($client_text);
                    $address_line = '';
                }

                // Extract pincode, city, country from address line
                // Assuming format: street + pincode + city + country
                preg_match('/(.*)\s+(\d{4})\s+(\S+)\s+(\S+)$/', $address_line, $addrMatches);

                $street  = $addrMatches[1] ?? null;
                $pincode = $addrMatches[2] ?? null;
                $city    = $addrMatches[3] ?? null;
                $country = $addrMatches[4] ?? null;
                
                // Extract org_no from CVR/ORG No               
                preg_match('/CVR\/ORG\s+No\s+[A-Z]{2}\s*([\d\s]+)/i', implode(' ', $client_lines), $orgMatch);
                $org_no = isset($orgMatch[1]) ? trim($orgMatch[1]) : null;

                $structured['client_details'] = [
                    'company_name' => $company_name,
                    'address'      => $street,
                    'pincode'      => $pincode,
                    'city'         => $city,
                    'country'      => $country,
                    'telephone'    => $telephone,
                    'fax'          => $fax,
                    'website'      => $website,
                    'email'        => $email,
                    'org_no'       => $org_no,
                ];

                $i = $j;
                continue;
            }


        }

        return $structured;
    }

    function parseAddressLine(string $line, ?string $vat_number = null): array {
        $line = trim($line);
        
        // Remove label if exists
        $line = preg_replace('/^(Fakturaadresse|Leveringsadresse)\s*/i', '', $line);

        // Extract account number
        preg_match('/KONTO:\s*(\d+)/i', $line, $accountMatch);
        $account_no = $accountMatch[1] ?? null;

        // Remove account number from line
        $line = preg_replace('/KONTO:\s*\d+\s*/i', '', $line);

        // Split into words
        $parts = preg_split('/\s+/', $line);

        // Country is last token
        $country = array_pop($parts) ?? '';

        // City is last token before country
        $city = array_pop($parts) ?? '';

        // Check if last token is pincode (numeric)
        $pincode = null;
        if (is_numeric(end($parts))) {
            $pincode = array_pop($parts);
        }

        // Remaining parts are address
        $address = implode(' ', $parts);

        return [
            'account_no' => $account_no,
            'address'    => $address,
            'pincode'    => $pincode,
            'city'       => $city,
            'country'    => $country,
            'vat_number' => $vat_number, // include VAT number
        ];
    }

}
