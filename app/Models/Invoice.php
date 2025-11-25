<?php

namespace App\Models;

use App\Models\Booking;
use App\Models\BookingData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Invoice extends Model
{
    use SoftDeletes;
    use HasFactory;
    use LogsActivity;
    protected $fillable = [
        'booking_id',
        'zoho_invoice_id',
        'zoho_invoice_number',
        'invoice_status',
        'total_amount',
        'invoice_date',
        'due_date',
        'status',
        'deposit_amount'
    ];

    /**
     * Get the options for activity logging.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'booking_id',
                'zoho_invoice_id',
                'zoho_invoice_number',
                'invoice_status',
                'total_amount',
                'invoice_date',
                'due_date',
                'status',
                'deposit_amount'
            ])
            ->logOnlyDirty()
            ->useLogName('Invoice');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Invoice has been {$eventName} by " . (auth()->check() ? auth()->user()->name : 'system');
    }

    /**
     * Get the booking that owns the Invoice
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'id');
    }

    /**
     * Get all of the comments for the Invoice
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bookingData()
    {
        return $this->hasMany(BookingData::class, 'invoice_id', 'id');
    }


    public function paymentData()
    {
        return $this->hasOne(PaymentData::class);
    }
}
