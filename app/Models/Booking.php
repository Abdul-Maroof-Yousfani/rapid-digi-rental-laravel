<?php

namespace App\Models;

use App\Models\BookingData;
use App\Models\Customer;
use App\Models\Deposit;
use App\Models\Invoice;
use App\Models\SalePerson;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $fillable = [
        'customer_id',
        'agreement_no',
        'notes',
        'total_price',
        'sale_person_id',
        'deposit_id',
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

    public function depositHandling()
    {
        return $this->hasMany(DepositHandling::class, 'booking_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function deposit()
    {
        return $this->belongsTo(Deposit::class, 'deposit_id', 'id');
    }

    public function salePerson()
    {
        return $this->belongsTo(SalePerson::class, 'sale_person_id', 'id');
    }


}
