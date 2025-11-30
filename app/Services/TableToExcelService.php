<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TableToExcelService
{
    /**
     * Convert pasted HTML/TSV table to Excel file
     * Returns: file path
     */
    public function convert(string $input): string
    {
        $input = trim($input);

        if (!$input) {
            throw new \Exception("No table data provided.");
        }

        // Detect HTML table
        if (stripos($input, '<table') !== false) {
            $rows = $this->parseHtmlTable($input);
        } else {
            $rows = $this->parseTabSeparated($input);
        }

        return $this->createExcel($rows);
    }

    /**
     * Parse an HTML <table> into a PHP array
     */
    private function parseHtmlTable($html): array
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);

        $rows = [];

        foreach ($dom->getElementsByTagName('tr') as $tr) {
            $cols = [];

            foreach ($tr->getElementsByTagName('th') as $th) {
                $cols[] = trim($th->textContent);
            }

            foreach ($tr->getElementsByTagName('td') as $td) {
                $cols[] = trim($td->textContent);
            }

            if (!empty($cols)) {
                $rows[] = $cols;
            }
        }

        return $rows;
    }

    /**
     * Parse Excel-pasted tab-separated values (TSV)
     */
    private function parseTabSeparated($text): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($text));

        return array_map(function ($line) {
            return explode("\t", $line);
        }, $lines);
    }

    /**
     * Create Excel (.xlsx) file and return its path
     */
    private function createExcel(array $rows): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($rows as $rowIndex => $row) {
            foreach ($row as $colIndex => $value) {
                $sheet->setCellValueByColumnAndRow(
                    $colIndex + 1,
                    $rowIndex + 1,
                    $value
                );
            }
        }

        $fileName = 'converted_table_' . time() . '.xlsx';
        $filePath = storage_path($fileName);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $filePath;
    }
}
