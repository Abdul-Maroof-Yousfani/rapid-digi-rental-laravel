<?php

namespace App\Models;

use App\Models\Booking;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use SoftDeletes;
    use HasFactory;
    use LogsActivity;
    protected $fillable = [
        'zoho_customer_id',
        'customer_name',
        'email',
        'phone',
        'cnic',
        'trn_no',
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

        /**
     * Get the options for activity logging.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
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
            ])
            ->logOnlyDirty()
            ->useLogName('Customer');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Customer has been {$eventName} by " . (auth()->check() ? auth()->user()->name : 'system');
    }

    public function booking()
    {
        return $this->hasMany(Booking::class);
    }
}
