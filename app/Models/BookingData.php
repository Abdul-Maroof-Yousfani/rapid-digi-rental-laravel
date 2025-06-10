<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'transaction_type'
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


}
