<?php

namespace App\Models;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehiclestatus extends Model
{
    // use SoftDeletes;
    use HasFactory;

    // protected $table = 'vehiclestatuses';
    protected $fillable= [
        'name',
    ];
}
