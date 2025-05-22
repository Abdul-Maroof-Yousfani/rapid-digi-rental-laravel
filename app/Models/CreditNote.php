<?php

namespace App\Models;

use App\Models\Booking;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditNote extends Model
{
    use HasFactory;
    protected $fillable= [
        'credit_note_no',
        'booking_id',
        'payment_method',
        'bank_id',
        'remaining_deposit',
        'refund_amount',
        'remarks',
        'refund_date',
        'status',
    ];

    /**
     * Get the paymentMethod that owns the CreditNote
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method', 'id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id');
    }
}
