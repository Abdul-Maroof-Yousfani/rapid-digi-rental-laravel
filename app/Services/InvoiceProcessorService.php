<?php

namespace App\Services;

use App\Models\{Booking, BookingData, Deposit, Invoice, Vehicle};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceProcessorService
{
    public function processInvoiceGroup($invoiceNumber, $items)
    {
        DB::beginTransaction();

        try {
            $existingInvoice = Invoice::where('zoho_invoice_number', $invoiceNumber)->first();

            if ($existingInvoice) {
                $booking = $existingInvoice->booking;
                $invoice = $existingInvoice;
            } else {
                $lastBooking = Booking::orderBy('id', 'desc')->first();
                if ($lastBooking && is_numeric($lastBooking->agreement_no)) {
                    $nextAgreementNo = str_pad($lastBooking->agreement_no + 1, 5, '0', STR_PAD_LEFT);
                } else {
                    $nextAgreementNo = '00001';
                }

                // Debug keys for safety
                Log::info('processInvoiceGroup first item keys', array_keys($items[0]));

                $zohoInvoiceId = $items[0]['zoho_invoice_id'] ?? null;
                $customerId = $items[0]['customer_id'] ?? null;
                $customerName = $items[0]['customer_name'] ?? 'Unknown Customer';
                $notes = $items[0]['notes'] ?? '';
                $salesPersonName = $items[0]['sales_person_name'] ?? null;
                $invoiceDate = $items[0]['invoice_date'] ?? now();

                $salesPersonId = \App\Models\SalePerson::where('name', $salesPersonName)->value('id');

                $customerDBID = \App\Models\Customer::where('zoho_customer_id', $customerId)->value('id');
                if (!$customerDBID && $customerName) {
                    $customerDBID = \App\Models\Customer::where('customer_name', $customerName)->value('id');
                }

                if (!$customerDBID) {
                    throw new \Exception("Customer not found for Zoho ID: {$customerId} or name: {$customerName}");
                }

                // $deposit = Deposit::create([
                //     'deposit_amount' => 1000,
                //     'initial_deposit' => 1000,
                // ]);

                $booking = Booking::create([
                    'customer_id' => $customerDBID,
                    'agreement_no' => $nextAgreementNo,
                    'notes' => $notes,
                    'sale_person_id' => $salesPersonId,
                    'deposit_id' => null,
                    'started_at' => \Carbon\Carbon::parse($invoiceDate),
                ]);

                Log::channel('daily')->info('Created booking', [
                    'booking_id' => $booking->id,
                    'agreement_no' => $nextAgreementNo,
                    'customer_id' => $customerDBID,
                ]);

                $zohoInvoiceNumber = $items[0]['zoho_invoice_number'] ?? $invoiceNumber;
                $zohoInvoiceTotal = array_sum(array_map(fn($i) => ($i['item_price'] ?? 0) * ($i['quantity'] ?? 0), $items));

                $invoice = Invoice::create([
                    'booking_id' => $booking->id,
                    'zoho_invoice_id' => $zohoInvoiceId,
                    'zoho_invoice_number' => $zohoInvoiceNumber,
                    'total_amount' => number_format($zohoInvoiceTotal, 2, '.', ''),
                    'status' => 1,
                ]);

                Log::channel('daily')->info('Created invoice', [
                    'invoice_id' => $invoice->id,
                    'zoho_invoice_id' => $zohoInvoiceId,
                    'zoho_invoice_number' => $zohoInvoiceNumber,
                    'total_amount' => $zohoInvoiceTotal,
                ]);
            }

            // ✅ Now process all booking_data records
            foreach ($items as $index => $item) {
                $quantity = $item['quantity'] ?? 1;
                if ($quantity < 1) {
                    Log::warning('Skipping item due to invalid quantity', [
                        'invoice_number' => $invoiceNumber,
                        'item_index' => $index,
                        'quantity' => $quantity,
                    ]);
                    continue;
                }

                // --- Extract vehicle model & number plate ---
                $itemDesc = trim($item['item_desc'] ?? '');
                $itemDescLines = preg_split("/[\r\n]+/", $itemDesc);

                $vehicleModelLine = trim($itemDescLines[0] ?? '');
                $vehicleModel = $vehicleModelLine;
                $numberPlate = '';

                // 🔹 Try to extract number plate (like I-21028) from the first line
                if (preg_match('/([A-Z]{1,3}-\d{3,6})/', $vehicleModelLine, $matches)) {
                    $numberPlate = $matches[1]; // e.g. I-21028
                    $vehicleModel = trim(str_replace($numberPlate, '', $vehicleModelLine));
                }

                // --- Try finding the vehicle ---
                $vehicle = Vehicle::where('temp_vehicle_detail', 'LIKE', "%{$vehicleModel}%")
                    ->where('number_plate', 'LIKE', "%{$numberPlate}%")
                    ->first();

                if (!$vehicle) {
                    Log::warning('Vehicle not found after parsing', [
                        'invoice_number' => $invoiceNumber,
                        'item_index' => $index,
                        'vehicle_model' => $vehicleModel,
                        'number_plate' => $numberPlate,
                        'raw_item_desc' => $item['item_desc'] ?? '',
                    ]);
                    continue;
                }

                $vehicleId = $vehicle->id;
                Log::info('Vehicle matched successfully', [
                    'invoice_number' => $invoiceNumber,
                    'vehicle_id' => $vehicleId,
                    'vehicle_model' => $vehicleModel,
                    'number_plate' => $numberPlate,
                ]);

                // ✅ Now you can safely create BookingData
                $bookingData = BookingData::create([
                    'booking_id' => $booking->id,
                    'vehicle_id' => $vehicleId,
                    'invoice_id' => $invoice->id,
                    'start_date' => !empty($item['start_date']) ? \Carbon\Carbon::parse($item['start_date']) : null,
                    'end_date' => !empty($item['end_date']) ? \Carbon\Carbon::parse($item['end_date']) : null,
                    'price' => $item['item_price'] ?? 0,
                    'transaction_type' => !empty($item['end_date']) ? 2 : 1,
                    'description' => $item['item_desc'] ?? '',
                    'quantity' => $quantity,
                    'tax_percent' => $item['tax_percent'] ?? 0,
                    'item_total' => number_format(($item['item_price'] ?? 0) * $quantity, 2, '.', ''),
                    'tax_name' => $item['tax_name'] ?? '',
                    'deductiontype_id' => $item['deductiontype'] ?? null,
                    'view_type' => 2,
                ]);

                // Update vehicle status
                Vehicle::where('id', $vehicleId)->update(['vehicle_status_id' => 33]);
            }


            DB::commit();
            Log::info('Invoice processed successfully', [
                'invoice_number' => $invoiceNumber,
                'item_count' => count($items),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Invoice processing failed', [
                'invoice_number' => $invoiceNumber,
                'error' => $e->getMessage(),
                'items' => $items,
            ]);
            throw $e;
        }
    }





}
