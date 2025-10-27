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

class DepositsImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    public function headingRow(): int
    {
        return 3;
    }

    public function collection(Collection $rows)
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '-1');

        Log::info('Starting Deposits Import. Total rows: ' . count($rows));

        $processed = 0;
        $skipped = 0;

        foreach ($rows as $index => $row) {
            $data = array_map('trim', $row->toArray());
            $rowNumber = $index + 3;

            try {
                $validator = Validator::make($data, [
                    'invoice_no' => 'required',
                    'deposit' => 'nullable|numeric',
                    'deposit_return' => 'nullable|numeric',
                ]);

                if ($validator->fails()) {
                    Log::warning("Row {$rowNumber} skipped: validation failed", $validator->errors()->toArray());
                    $skipped++;
                    continue;
                }

                DB::beginTransaction();

                $invoiceNo = trim($data['invoice_no']);
                $depositAmount = (float) ($data['deposit'] ?? 0);
                $depositReturn = (float) ($data['deposit_return'] ?? 0);
                $remainingDeposit = $depositAmount - $depositReturn;

                $booking_id = Invoice::where('zoho_invoice_number', $invoiceNo)->value('booking_id');

                if (!$booking_id) {
                    Log::warning("Row {$rowNumber} skipped: No booking found for invoice {$invoiceNo}");
                    DB::rollBack();
                    $skipped++;
                    continue;
                }

                $deposit = Deposit::create([
                    'deposit_amount' => $remainingDeposit,
                    'initial_deposit' => $depositAmount,
                ]);

                Booking::where('id', $booking_id)->update(['deposit_id' => $deposit->id]);

                Log::info("Deposit created and linked", [
                    'invoice_no' => $invoiceNo,
                    'booking_id' => $booking_id,
                    'deposit_id' => $deposit->id,
                    'remaining' => $remainingDeposit,
                ]);

                $processed++;
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("Error on row {$rowNumber}: " . $e->getMessage(), [
                    'data' => $data,
                ]);
                $skipped++;
            }
        }

        $summary = [
            'processed' => $processed,
            'skipped' => $skipped,
            'total' => count($rows),
        ];

        Log::info('Deposits import summary', $summary);

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
