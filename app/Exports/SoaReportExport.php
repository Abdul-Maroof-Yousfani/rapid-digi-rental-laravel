<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;

class SoaReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents, WithCustomStartCell
{
    protected $soaData;

    public function __construct($soaData)
    {
        $this->soaData = $soaData;
    }

    public function collection()
    {
        return collect($this->soaData);
    }

    public function headings(): array
    {
        return [
            'INVOICE NO',
            'Plate No',
            'Car Make-Model & Year',
            'Rental Period',
            'Rental Amount',
        ];
    }

    public function startCell(): string
    {
        return 'A4'; // Start data from row 4 (after company name, TRN, and headers)
    }

    public function map($item): array
    {
        return [
            $item->invoice_number ?? '',
            $item->plate_no ?? '',
            $item->car_details ?? '',
            $item->rental_period ?? '',
            number_format($item->rental_amount ?? 0, 2),
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,  // INVOICE NO
            'B' => 15,  // Plate No
            'C' => 30,  // Car Make-Model & Year
            'D' => 20,  // Rental Period
            'E' => 15,  // Rental Amount
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Styles will be applied in AfterSheet event
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Set default font to Arial, size 10
                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Arial');
                $sheet->getParent()->getDefaultStyle()->getFont()->setSize(10);
                
                // Since startCell is A4, headings are at row 4, data starts at row 5
                // We need to set row 2 (Company Name) and row 3 (TRN)
                // Row 4 already has headers from WithHeadings
                
                // Row 2: Company Name
                $sheet->setCellValue('A2', 'Rapid Rentals - FZCO');
                $sheet->mergeCells('A2:E2');
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12)->setName('Arial');
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Row 3: TRN
                $sheet->setCellValue('A3', 'TRN:104137158200003');
                $sheet->mergeCells('A3:E3');
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(12)->setName('Arial');
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Row 4: Table Headers (overwrite the default headers with correct spelling)
                $sheet->setCellValue('A4', 'INVOICE NO');
                $sheet->setCellValue('B4', 'Plate No');
                $sheet->setCellValue('C4', 'Car Make-Model & Year');
                $sheet->setCellValue('D4', 'Retal Period');
                $sheet->setCellValue('E4', 'Rental Amoun');
                
                // Style header row (row 4)
                $headerStyle = [
                    'font' => [
                        'bold' => true,
                        'size' => 10,
                        'color' => ['rgb' => 'FFFFFF'],
                        'name' => 'Arial',
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '006100']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ];
                
                $sheet->getStyle('A4:E4')->applyFromArray($headerStyle);
                
                // Set row height for header
                $sheet->getRowDimension(4)->setRowHeight(20);
                
                // Style all data rows (starting from row 5) - set font to Arial
                $highestRow = $sheet->getHighestRow();
                if ($highestRow > 4) {
                    $sheet->getStyle('A5:E' . $highestRow)->getFont()->setName('Arial')->setSize(10);
                    
                    // Center align Invoice No (Column A), Plate No (Column B), and Rental Amount (Column E)
                    $sheet->getStyle('A5:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('B5:B' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('E5:E' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
            },
        ];
    }
}

