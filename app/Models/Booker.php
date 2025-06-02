<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Booker extends Model
{
    use HasFactory;
    use SoftDeletes;
    use LogsActivity;
    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'address',
        'gender',
        'dob',
        'cnic',
        'postal_code',
        'city',
        'state',
        'country',
        'status',
    ];

    /**
     * Get the options for activity logging.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'user_id',
                'name',
                'phone',
                'address',
                'gender',
                'dob',
                'cnic',
                'postal_code',
                'city',
                'state',
                'country',
                'status',
            ])
            ->logOnlyDirty()
            ->useLogName('Booking User');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Booking User has been {$eventName} by " . (auth()->check() ? auth()->user()->name : 'system');
    }

    /**
     * Get the user that owns the Booker
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
