<?php

namespace App\Services\Quotes;

use App\Data\QuoteLeadRow;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuoteLeadExportService
{
    /**
     * @return list<string>
     */
    private function headers(): array
    {
        return [
            'Legal Business Name',
            'First Name',
            'Last Name',
            'Email',
            'Business Email',
            'Phone',
            'ZIP Code',
            'State',
            'Quotes Count',
            'Registered',
        ];
    }

    /**
     * @param  Collection<int, QuoteLeadRow>  $leads
     * @param  array{number: int, label: string, description: string}  $filterDefinition
     */
    public function toSpreadsheet(array $filterDefinition, Collection $leads): Spreadsheet
    {
        $sheet = new Spreadsheet();
        $active = $sheet->getActiveSheet();
        $active->setTitle('Quote Leads');

        $active->setCellValue('A1', 'Filter segment');
        $active->setCellValue('B1', (string) $filterDefinition['number']);

        $active->setCellValue('A2', 'Filter name');
        $active->setCellValue('B2', $filterDefinition['label']);

        $active->setCellValue('A3', 'Description');
        $active->setCellValue('B3', $filterDefinition['description']);

        $active->setCellValue('A4', 'Exported at');
        $active->setCellValue('B4', now()->format('Y-m-d H:i:s'));

        $active->setCellValue('A5', 'Records');
        $active->setCellValue('B5', (string) $leads->count());

        $headerRow = 7;
        $headers = $this->headers();
        foreach ($headers as $index => $header) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $active->setCellValue($column.$headerRow, $header);
        }

        $rowIndex = $headerRow + 1;
        foreach ($leads as $lead) {
            $values = $this->rowValues($lead);
            foreach ($values as $index => $value) {
                $column = Coordinate::stringFromColumnIndex($index + 1);
                $active->setCellValue($column.$rowIndex, $value);
            }
            $rowIndex++;
        }

        $active->getColumnDimension('A')->setWidth(22);
        $active->getColumnDimension('B')->setWidth(72);

        return $sheet;
    }

    /**
     * @param  Collection<int, QuoteLeadRow>  $leads
     * @param  array{number: int, label: string, description: string}  $filterDefinition
     */
    public function downloadResponse(array $filterDefinition, Collection $leads, string $format): StreamedResponse
    {
        $spreadsheet = $this->toSpreadsheet($filterDefinition, $leads);
        $timestamp = now()->format('Y-m-d_His');
        $segment = $filterDefinition['number'];
        $filename = "quote_leads_segment_{$segment}_{$timestamp}.{$format}";

        if ($format === 'csv') {
            return response()->streamDownload(function () use ($spreadsheet) {
                $writer = new Csv($spreadsheet);
                $writer->setDelimiter(',');
                $writer->setEnclosure('"');
                $writer->save('php://output');
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * @return list<string|int>
     */
    private function rowValues(QuoteLeadRow $lead): array
    {
        return [
            $lead->legalBusinessName,
            $lead->firstName,
            $lead->lastName,
            $lead->email,
            $lead->businessEmail,
            $lead->phone,
            $lead->zipCode,
            $lead->state ?: '—',
            $lead->quotesCount,
            $lead->isRegistered ? 'Yes' : 'No',
        ];
    }
}
