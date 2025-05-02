<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'zoho_customer_id',
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
