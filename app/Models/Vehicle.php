<?php

namespace App\Models;

use App\Models\Investor;
use App\Models\User;
use App\Models\Vehiclestatus;
use App\Models\Vehicletype;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Vehicle extends Model
{
    use HasFactory;
    use SoftDeletes;
    use LogsActivity;
    protected $fillable= [
        'vehicle_name',
        'temp_vehicle_detail',
        'vehicletypes',
        'investor_id',
        'car_make',
        'year',
        'number_plate',
        'status',
        'vehicle_status_id',
    ];

    /**
     * Get the options for activity logging.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'bank_name',
                'account_name',
                'account_number',
                'iban',
                'swift_code',
                'branch',
                'currency',
                'notes',
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
        return $this->belongsTo(Vehiclestatus::class, 'vehicle_status_id');
    }

    public function bookingData()
    {
        return $this->hasMany(Comment::class, 'foreign_key', 'local_key');
    }

}
