<?php

namespace App\Models;

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
}
