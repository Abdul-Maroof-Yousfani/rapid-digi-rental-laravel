<?php

namespace App\Jobs;

use App\Services\ZohoInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateZohoInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $invoiceId;
    public $updates;

    public function __construct($invoiceId, $updates)
    {
        $this->invoiceId = $invoiceId;
        $this->updates = $updates;
    }

    public function handle(): void
    {
        $zoho = app()->make(ZohoInvoice::class);

        $invoiceData = $zoho->getInvoice($this->invoiceId);
        if (!isset($invoiceData['invoice'])) return;

        $originalInvoice = $invoiceData['invoice'];
        $lineItems = $originalInvoice['line_items'];

        foreach ($lineItems as &$lineItem) {
            foreach ($this->updates as $update) {
                if (
                    strtolower(trim($lineItem['name'])) === strtolower(trim($update['name'])) &&
                    strtolower(trim($lineItem['description'])) === strtolower(trim($update['description']))
                ) {
                    $lineItem['rate'] = $update['rate'];
                    $lineItem['item_total'] = $update['rate'] * $lineItem['quantity'];
                }
            }
        }

        $updatePayload = [
            'customer_id' => $originalInvoice['customer_id'],
            'currency_code' => $originalInvoice['currency_code'],
            'notes' => $originalInvoice['notes'] ?? '',
            'line_items' => $lineItems,
        ];

        $zoho->updateInvoice($this->invoiceId, $updatePayload);
        Log::info("Zoho Invoice updated in background for Invoice ID: {$this->invoiceId}");
    }
}
