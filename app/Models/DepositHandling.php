<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepositHandling extends Model
{
    use HasFactory;
    protected $fillable= [
        'payment_data_id',
        'deposit_id',
        'deduct_deposit',
    ];
}
