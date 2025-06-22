<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingPaymentHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'invoice_id',
        'payment_id',
        'paid_amount',
        'payment_method_name',
        'user_id',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
