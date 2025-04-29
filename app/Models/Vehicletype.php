<?php

namespace App\Models;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vehicletype extends Model
{
    use HasFactory;

    /**
     * Get all of the vehicle for the Vehicletype
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function vehicle(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'vehicletypes', 'id');
    }
}