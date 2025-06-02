<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

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

}
