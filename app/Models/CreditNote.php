<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditNote extends Model
{
    use HasFactory;
    protected $fillable= [
        'booking_id',
        'payment_method',
        'bank_id',
        'deposit_id',
        'remaining_deposit',
        'refund_amount',
        'remarks',
        'refund_date',
        'status',
    ];
}
