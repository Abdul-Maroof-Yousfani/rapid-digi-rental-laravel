<?php

namespace App\Models;

use App\Models\Booking;
use App\Models\BookingData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $fillable = [
        'booking_id',
        'zoho_invoice_id',
        'zoho_invoice_number',
        'invoice_status',
        'total_amount',
        'status',
        'deposit_amount'
    ];

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
}