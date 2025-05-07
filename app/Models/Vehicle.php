<?php

namespace App\Models;

use App\Models\User;
use App\Models\Investor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vehicle extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable= [
        'vehicle_name',
        'temp_vehicle_detail',
        'vehicletypes',
        'investor_id',
        'car_make',
        'year',
        'number_plate',
        'status',
    ];

    public function investor(): BelongsTo
    {
        return $this->belongsTo(Investor::class, 'investor_id', 'id');
    }

    public function vehicletype(): BelongsTo
    {
        return $this->belongsTo(Vehicletype::class, 'vehicletypes', 'id');
    }
}
