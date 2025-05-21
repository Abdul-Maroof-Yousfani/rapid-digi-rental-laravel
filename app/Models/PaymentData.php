<?php

namespace App\Models;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentData extends Model
{
    use HasFactory;
    protected $fillable= [
        'invoice_id',
        'payment_id',
        'status',
        'invoice_amount',
        'paid_amount',
        'pending_amount'
    ];

    /**
     * Get the invoice that owns the PaymentData
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'id');
    }
}
