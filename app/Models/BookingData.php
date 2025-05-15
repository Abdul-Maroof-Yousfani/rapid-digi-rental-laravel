<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingData extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $fillable = [
        'booking_id',
        'vehicle_id',
        'invoice_id',
        'start_date',
        'end_date',
        'price',
        'description',
        'rate',
        'quantity',
        'tax_percent',
        'item_total',
        'tax_name',
        'transaction_type'
    ];
}
