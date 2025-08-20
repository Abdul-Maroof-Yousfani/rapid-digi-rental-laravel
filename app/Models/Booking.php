<?php

namespace App\Models;

use App\Models\Deposit;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\CreditNote;
use App\Models\SalePerson;
use App\Models\BookingData;
use App\Models\DepositHandling;
use Spatie\Activitylog\LogOptions;
use App\Models\BookingPaymentHistory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use SoftDeletes;
    use HasFactory;
    use LogsActivity;
    protected $fillable = [
        'customer_id',
        'agreement_no',
        'notes',
        'total_price',
        'sale_person_id',
        'deposit_id',
        'non_refundable_amount',
        'deposit_type',
        'booking_status',
        'started_at',
        'booking_cancel',
    ];

    /**
     * Get the options for activity logging.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'customer_id',
                'agreement_no',
                'notes',
                'total_price',
                'sale_person_id',
                'deposit_id',
                'booking_status',
                'started_at',
                'booking_cancel',
            ])
            ->logOnlyDirty()
            ->useLogName('booking');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Booking has been {$eventName} by " . (auth()->check() ? auth()->user()->name : 'system');
    }

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


    public function bookingData()
    {
        return $this->hasMany(bookingData::class, 'booking_id', 'id');
    }

    public function salePerson()
    {
        return $this->belongsTo(SalePerson::class, 'sale_person_id', 'id');
    }

    public function creditNote()
    {
        return $this->hasOne(CreditNote::class, 'booking_id', 'id');
    }


    public function payment_status()
    {
        return $this->hasMany(Payment::class, 'booking_id', 'id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'booking_id', 'id');
    }
    public function bookingHistory()
    {
        return $this->hasMany(BookingPaymentHistory::class, 'booking_id', 'id');
    }
}
