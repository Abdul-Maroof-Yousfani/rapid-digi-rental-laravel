<?php

namespace App\Models;

use App\Models\Booking;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class CreditNote extends Model
{
    use HasFactory;
    use LogsActivity;
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
        'zoho_credit_note_id',
    ];

    /**
     * Get the options for activity logging.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'credit_note_no',
                'booking_id',
                'payment_method',
                'bank_id',
                'remaining_deposit',
                'refund_amount',
                'remarks',
                'refund_date',
                'status',
            ])
            ->logOnlyDirty()
            ->useLogName('Credit Note');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Credit Note has been {$eventName} by " . (auth()->check() ? auth()->user()->name : 'system');
    }

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
        return $this->belongsTo(Booking::class);
    }
}
