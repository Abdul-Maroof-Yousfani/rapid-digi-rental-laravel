<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CustomerLedgerExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $ledgerData;

    public function __construct($ledgerData)
    {
        $this->ledgerData = $ledgerData;
    }

    public function collection()
    {
        return collect($this->ledgerData);
    }

    public function headings(): array
    {
        return [
            'Date',
            'Invoice Number',
            'Description',
            'Item Desc',
            'Invoice Amount',
            'Payment Received',
            'Outstanding',
            'Invoice Status',
        ];
    }

    public function map($item): array
    {
        return [
            $item->date ?? '',
            $item->invoice_number ?? '',
            $item->description ?? '',
            $item->item_desc ?? '',
            number_format($item->invoice_amount ?? 0, 2),
            number_format($item->payment_receive ?? 0, 2),
            number_format($item->outstanding ?? 0, 2),
            $item->invoice_status ?? '',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,  // Date
            'B' => 18,  // Invoice Number
            'C' => 25,  // Description
            'D' => 20,  // Item Desc
            'E' => 15,  // Invoice Amount
            'F' => 18,  // Payment Received
            'G' => 15,  // Outstanding
            'H' => 18,  // Invoice Status
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text with background
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}

