<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class VehicleStatus extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $fillable= [
        'name'
    ];
}
