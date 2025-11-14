<?php

namespace App\Models;

use App\Models\User;
use App\Models\Investor;
use App\Models\BookingData;
use App\Models\Vehicletype;
use App\Models\VehicleStatus;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vehicle extends Model
{
    use HasFactory;
    use SoftDeletes;
    use LogsActivity;
    protected $fillable = [
        'vehicle_name',
        'temp_vehicle_detail',
        'vehicletypes',
        'investor_id',
        'car_make',
        'year',
        'number_plate',
        'status',
        'vehicle_status_id',
        'remarks',
    ];

    /**
     * Get the options for activity logging.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'vehicle_name',
                'temp_vehicle_detail',
                'vehicletypes',
                'investor_id',
                'car_make',
                'year',
                'number_plate',
                'status',
                'vehicle_status_id',
                'remarks',
            ])
            ->logOnlyDirty()
            ->useLogName('Vehicle');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Vehicle has been {$eventName} by " . (auth()->check() ? auth()->user()->name : 'system');
    }

    public function investor(): BelongsTo
    {
        return $this->belongsTo(Investor::class, 'investor_id', 'id');
    }

    public function vehicletype(): BelongsTo
    {
        return $this->belongsTo(Vehicletype::class, 'vehicletypes', 'id');
    }

    public function vehiclestatus()
    {
        return $this->belongsTo(VehicleStatus::class, 'vehicle_status_id');
    }

    public function bookingData()
    {
        return $this->hasMany(BookingData::class, 'vehicle_id', 'id');
    }

}
