<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'email',
        'phone',
        'cnic',
        'dob',
        'address',
        'licence',
        'gender',
        'city',
        'state',
        'country',
        'postal_code',
        'status',
    ];
}
