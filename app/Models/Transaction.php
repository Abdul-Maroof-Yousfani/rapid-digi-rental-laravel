<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable= [
        'code',
        'booking_id',
        'customer_id',
        'deposit_id',
        'deposit_amount',
        'salik_amount',
        'fine_amount',
        'renew_amount',
        'net_amount',
    ];
}
