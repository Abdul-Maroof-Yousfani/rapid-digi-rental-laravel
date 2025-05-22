<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepositHandling extends Model
{
    use HasFactory;
    protected $fillable= [
        'payment_data_id',
        'booking_id',
        'deduct_deposit',
    ];

    /**
     * Get the deposit that owns the DepositHandling
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id');
    }
}
