<?php

namespace App\Models;

use App\Models\Booking;
use App\Models\BookingData;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingData extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $fillable = [
        'booking_id',
        'vehicle_id',
        'invoice_id',
        'start_date',
        'end_date',
        'price',
        'description',
        'rate',
        'quantity',
        'tax_percent',
        'item_total',
        'tax_name',
        'transaction_type',
        'deductiontype_id',
        'view_type'
    ];

    /**
     * Get the Vehicle that owns the BookingData
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'id');
    }

    // public function bookingData(): BelongsTo
    // {
    //     return $this->belongsTo(BookingData::class);
    // }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function invoiceType(): BelongsTo
    {
        return $this->belongsTo(Deductiontype::class, 'transaction_type', 'id');
    }

    public function invoice_type(): BelongsTo
    {
        return $this->belongsTo(Deductiontype::class, 'deductiontype_id', 'id');
    }

}
