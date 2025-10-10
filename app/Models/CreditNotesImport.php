<?php

namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use App\Models\Invoice;
use App\Models\CreditNote;

class CreditNotesImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    public function collection(Collection $rows)
{
    ini_set('max_execution_time', 0);
    ini_set('memory_limit', '-1');

    Log::info('Starting Credit Notes Import. Total rows: ' . count($rows));

    $processed = 0;
    $skipped = 0;

    foreach ($rows as $index => $row) {
        $data = array_map('trim', $row->toArray());
        $rowNumber = $index + 2; // +1 header, +1 zero-based
// dd($data);
        try {
            // 🧾 Validate row
            $validator = Validator::make($data, [
                'credit_note_date' => 'required',
                'credit_note_number' => 'required|string',
                'associated_invoice_number' => 'required|string',
                'customer_id' => 'required|string',
                'item_total' => 'required|numeric|min:0',
                'credit_note_status' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::warning("Row {$rowNumber} skipped: validation failed", $validator->errors()->toArray());
                $skipped++;
                continue;
            }

            DB::beginTransaction();

            // 🧠 Normalize data
            $creditNoteNumber = $data['credit_note_number'];
            $invoiceNumber = trim((string) $data['associated_invoice_number']);
            $customerZohoId = $data['customer_id'];
            $refundAmount = (float) $data['item_total'];
            $creditNoteDate = $this->excelDateToCarbon($data['credit_note_date']);
            $notes = $data['notes'] ?? null;
            $status = $this->mapStatus($data['credit_note_status']);
            Log::info("Row {$rowNumber}: Processing invoice number {$invoiceNumber}, date {$creditNoteDate}");

            // 🔍 Find invoice
            $invoice = Invoice::where('zoho_invoice_number', $invoiceNumber)->first();

            if (!$invoice) {
                Log::warning("Row {$rowNumber}: Invoice not found for number {$invoiceNumber}");
                DB::rollBack();
                $skipped++;
                continue;
            }

            Log::info("Row {$rowNumber}: Found invoice ID {$invoice->id}");

            // 🧾 Get related booking
            $booking = $invoice->booking;
            if (!$booking) {
                Log::warning("Row {$rowNumber}: No booking found for invoice ID {$invoice->id}");
                DB::rollBack();
                $skipped++;
                continue;
            }

            Log::info(message: "Row {$rowNumber}: Found booking ID {$booking->id}");
// dd($notes);

            // 🧍 Create Credit Note
            $creditNote = CreditNote::create([
                'credit_note_no'      => $creditNoteNumber,
                'booking_id'          => $booking->id,
                'invoice_id'          => $invoice->id,
                'customer_id'         => $customerZohoId,
                'refund_amount'       => $refundAmount,
                'refund_date'    => $creditNoteDate,
                'payment_method'      => 1,
                'status'              => $status,
                'remarks'             => $notes,
                
            ]);

            DB::commit();
            Log::info("Row {$rowNumber}: Credit Note #{$creditNoteNumber} created (ID {$creditNote->id})");
            $processed++;
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Row {$rowNumber}: Failed to import credit note", [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ]);
            $skipped++;
        }
    }

    $summary = "✅ Credit Notes Import Complete. Processed: {$processed}, Skipped: {$skipped}";
    Log::info($summary);
    return $summary;
}

    /**
     * Convert Excel serial date to Carbon instance
     */
    private function excelDateToCarbon($excelDate)
    {
        if (is_numeric($excelDate)) {
            return Carbon::createFromTimestamp(($excelDate - 25569) * 86400)->format('Y-m-d');
        }
        return Carbon::parse($excelDate)->format('Y-m-d');
    }

    /**
     * Map Zoho status to DB status
     */
    private function mapStatus($status)
    {
        $map = [
            'open' => 1,
            'closed' => 2,
            'void' => 3,
        ];
        return $map[strtolower(trim($status))] ?? 2;
    }

    /**
     * Process large files efficiently
     */
    public function chunkSize(): int
    {
        return 500;
    }
}
