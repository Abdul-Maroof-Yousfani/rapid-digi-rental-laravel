<?php

namespace App\Models;

use App\Models\Invoice;
use App\Models\BookingData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $fillable = [
        'customer_id',
        'agreement_no',
        'notes',
        'total_price',
    ];

    /**
     * Get all of the invoice for the Booking
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function invoice()
    {
        return $this->hasMany(Invoice::class, 'booking_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

}
