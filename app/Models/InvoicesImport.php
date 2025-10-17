<?php

namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use App\Models\Vehicle;
use App\Models\Deductiontype;
use App\Services\InvoiceProcessorService; 

class InvoicesImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    public function collection(Collection $rows)
    {
        ini_set('max_execution_time', 0); 
        ini_set('memory_limit', '-1');    
        Log::info('Processing chunk of ' . count($rows) . ' rows.');

        $invoices = [];
        foreach ($rows as $row) {
            $data = $row->toArray();
// dd($data);
            $validator = Validator::make($data, [
                'invoice_number' => 'required',
                'customer_id' => 'required',
                'quantity' => 'required|numeric|min:1',
                'item_price' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                Log::warning('Invalid row data: ' . json_encode($validator->errors()->all()));
                continue;
            }

            $invoiceData = [
                'zoho_invoice_id' => $data['invoice_id'],
                'zoho_invoice_number' => $data['invoice_number'],
                'customer_id' => $data['customer_id'],
                'invoice_date' => $this->formatExcelDate($data['invoice_date'] ?? null),
                'item_name' => $data['item_name'] ?? '',
                'item_desc' => $data['item_desc'] ?? '',
                'quantity' => (int) ($data['quantity'] ?? 1),
                'item_price' => (float) ($data['item_price'] ?? 0),
                'item_total' => (float) ($data['item_total'] ?? 0),
                'tax_id' => $data['tax_id'] ?? null,
                'tax_percent' => (float) ($data['item_tax'] ?? 0),
                'tax_name' => $data['item_tax'] ? 'VAT ' . $data['item_tax'] . '%' : null,
                'sales_person_name' => $data['sales_person'] ?? null,
                'notes' => $data['notes'] ?? '',
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'deductiontype' => $this->getDeductionType($data['item_name'] ?? '', $data['item_desc'] ?? ''),
                'vehicle_id' => $this->getVehicleId($data['item_name'] ?? ''),
            ];

            $invoices[$data['invoice_number']][] = $invoiceData;
        }

        $service = app(InvoiceProcessorService::class); 

        foreach ($invoices as $invoiceNumber => $items) {
            $service->processInvoiceGroup($invoiceNumber, $items);
        }
    }

    public function chunkSize(): int
    {
        return 500;
    }

    private function getVehicleId($itemName)
    {
        if (empty($itemName)) return null;

        try {
            $searchTerm = explode(' ', $itemName)[0];
            $vehicle = Vehicle::where('vehicle_name', 'like', '%' . $searchTerm . '%')->first();
            return $vehicle?->id;
        } catch (\Exception $e) {
            Log::error('Error in getVehicleId: ' . $e->getMessage());
            return null;
        }
    }

    private function getDeductionType($itemName, $itemDesc)
    {
        try {
            if (stripos($itemDesc, 'RENT') !== false) {
                return Deductiontype::whereRaw('LOWER(name) = ?', ['renew'])->value('id');
            } elseif (stripos($itemName, 'SALIK') !== false) {
                return Deductiontype::whereRaw('LOWER(name) = ?', ['salik'])->value('id');
            }
            return null;
        } catch (\Exception $e) {
            Log::error('Error in getDeductionType: ' . $e->getMessage());
            return null;
        }
    }

    private function formatExcelDate($value)
    {
        if (empty($value)) {
            return null;
        }

        if (!is_numeric($value)) {
            return date('Y-m-d', strtotime($value));
        }

        try {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
        } catch (\Exception $e) {
            \Log::error("Invalid Excel date format: $value");
            return null;
        }
    }
}
