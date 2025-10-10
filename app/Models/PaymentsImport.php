<?php

namespace App\Models;

use App\Models\Booking;
use App\Models\BookingPaymentHistory;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentData;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PaymentsImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    public function collection(Collection $rows)
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '-1');

        Log::info('Starting payment import. Total rows to process: ' . count($rows));

        $processed = 0;
        $skipped = 0;

        foreach ($rows as $index => $row) {
            $data = $row->toArray();
            $rowNumber = $index + 2; 
// dd($data);
            Log::info("Processing row {$rowNumber}", $data); // Log each row for debugging

            $validator = Validator::make($data, [
                'invoice_number' => 'required|string',
                'customerid' => 'required|string',
                'amount' => 'required|numeric|min:0',
                'date' => 'required',
                'mode' => 'nullable|string',
                'reference_number' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                Log::warning("Row {$rowNumber} validation failed: " . implode(', ', $validator->errors()->all()));
                $skipped++;
                continue;
            }

            try {
                DB::beginTransaction();

                $invoiceNumber = trim($data['invoice_number']);
                $customerZohoId = $data['customerid'] ?? null;
                $paymentAmount = floatval($data['amount'] ?? 0);
                $paymentDate = $this->excelDateToCarbon($data['date'] ?? null);
                $mode = $data['mode'] ?? 'Cash';
                $reference = $data['reference_number'] ?? null;

                Log::info("Row {$rowNumber}: Looking for invoice with zoho_invoice_number = '{$invoiceNumber}'");

                // 1. Find related invoice
                $invoice = Invoice::where('zoho_invoice_number', $invoiceNumber)->first();

                if (!$invoice) {
                    Log::warning("Row {$rowNumber}: Skipping payment - Invoice not found for zoho_invoice_number '{$invoiceNumber}'. Available invoices: " . Invoice::pluck('zoho_invoice_number')->toJson());
                    DB::rollBack();
                    $skipped++;
                    continue;
                }

                Log::info("Row {$rowNumber}: Found invoice ID {$invoice->id}, total_amount: {$invoice->total_amount}");

                $booking = $invoice->booking;
                if (!$booking) {
                    Log::warning("Row {$rowNumber}: Skipping payment - Booking not found for invoice ID {$invoice->id}");
                    DB::rollBack();
                    $skipped++;
                    continue;
                }

                Log::info("Row {$rowNumber}: Found booking ID {$booking->id}");

                $excelInvoiceAmount = floatval($data['invoice_amount'] ?? $paymentAmount);
                if (abs($invoice->total_amount - $excelInvoiceAmount) > 0.01) {
                    Log::warning("Row {$rowNumber}: Amount mismatch - Excel: {$excelInvoiceAmount}, DB: {$invoice->total_amount}. Proceeding anyway.");
                }

                $pendingAmount = max(0, $invoice->total_amount - $paymentAmount);
                $paymentStatus = ($invoice->total_amount <= $paymentAmount) ? 'paid' : 'pending';

                $payment = Payment::create([
                    'booking_id' => $booking->id,
                    'payment_method' => $this->getPaymentMethodId($mode),
                    'booking_amount' => $invoice->total_amount,
                    'paid_amount' => $paymentAmount,
                    'pending_amount' => $pendingAmount,
                    'payment_status' => $paymentStatus,
                    'receipt' => null,
                    'created_at' => $paymentDate,
                ]);

                Log::info("Row {$rowNumber}: Created payment ID {$payment->id}");

                $paymentDataStatus = ($invoice->total_amount <= $paymentAmount) ? 'paid' : 'partially paid';

                $paymentData = PaymentData::create([
                    'invoice_id' => $invoice->id,
                    'payment_id' => $payment->id,
                    'invoice_amount' => $invoice->total_amount,
                    'paid_amount' => $paymentAmount,
                    'pending_amount' => $pendingAmount,
                    'status' => $paymentDataStatus,
                    'reference_invoice_number' => $reference,
                ]);

                Log::info("Row {$rowNumber}: Created PaymentData ID {$paymentData->id}");

                BookingPaymentHistory::create([
                    'booking_id' => $booking->id,
                    'payment_id' => $payment->id,
                    'invoice_id' => $invoice->id,
                    'payment_method_id' => $this->getPaymentMethodId($mode),
                    'paid_amount' => $paymentAmount,
                    'payment_date' => $paymentDate,
                    'user_id' => 1, 
                ]);

                Log::info("Row {$rowNumber}: Created BookingPaymentHistory for payment {$payment->id}");

                $invoice->update([
                    'invoice_status' => $paymentDataStatus,
                ]);

                Log::info("Row {$rowNumber}: Updated invoice status to '{$paymentDataStatus}'");

                DB::commit();
                $processed++;
                Log::info("Row {$rowNumber}: Successfully processed payment for invoice '{$invoiceNumber}'");

            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("Row {$rowNumber}: Payment import failed - {$e->getMessage()}", [
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                    'data' => $data,
                    'trace' => $e->getTraceAsString()
                ]);
                $skipped++;
            }
        }

        Log::info("Payment import completed. Processed: {$processed}, Skipped: {$skipped}");
    }

   
    public function chunkSize(): int
    {
        return 100;
    }

    private function excelDateToCarbon($excelDate)
    {
        try {
            if (is_numeric($excelDate)) {
                return Carbon::createFromTimestampUTC(($excelDate - 25569) * 86400);
            }
            return Carbon::parse($excelDate);
        } catch (\Exception $e) {
            Log::warning("Invalid date '{$excelDate}' in Excel. Using current date.");
            return Carbon::now();
        }
    }

    private function getPaymentMethodId($mode)
    {
        $mode = strtolower(trim($mode));
        return match ($mode) {
            'bank transfer' => 3,
            'cash' => 1,
            'credit card', 'card' => 2,
            default => 1, 
        };
    }
}