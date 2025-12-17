<?php

namespace App\Models;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Payment extends Model
{
    public $timestamps = true;
    use HasFactory;
    use LogsActivity;
    protected $fillable= [
        'booking_id',
        'payment_method',
        'bank_id',
        'receipt',
        'booking_amount',
        'paid_amount',
        'pending_amount',
        'payment_status',
        'payment_date',
    ];

    /**
     * Get the options for activity logging.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'booking_id',
                'payment_method',
                'bank_id',
                'receipt',
                'booking_amount',
                'paid_amount',
                'pending_amount',
                'payment_status',
            ])
            ->logOnlyDirty()
            ->useLogName('Payment');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Payment has been {$eventName} by " . (auth()->check() ? auth()->user()->name : 'system');
    }

    /**
     * Get the bookings that owns the Payment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method', 'id');
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id', 'id');
    }
}
