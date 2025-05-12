<?php

namespace App\Models;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Deposit extends Model
{
    use HasFactory;
    protected $fillable= [
        'booking_id',
        'deposit_amount',
    ];

    /**
     * Get the booking associated with the Deposit
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function booking(): HasOne
    {
        return $this->hasOne(Booking::class);
    }
}
