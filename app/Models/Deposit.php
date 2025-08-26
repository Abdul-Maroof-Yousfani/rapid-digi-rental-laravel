<?php

namespace App\Models;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;

class Deposit extends Model
{
    use HasFactory;
    protected $fillable= [
        'deposit_amount',
        'initial_deposit',
    ];


    public function booking()
    {
        return $this->hasOne(Booking::class, 'deposit_id', 'id');
    }
}
