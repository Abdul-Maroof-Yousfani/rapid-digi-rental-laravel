<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Bank extends Model
{
    use HasFactory;
    use SoftDeletes;
    use LogsActivity;
    protected $fillable= [
        'bank_name',
        'account_name',
        'account_number',
        'iban',
        'swift_code',
        'branch',
        'currency',
        'notes',
    ];

    /**
     * Get the options for activity logging.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'bank_name',
                'account_name',
                'account_number',
                'iban',
                'swift_code',
                'branch',
                'currency',
                'notes',
            ])
            ->logOnlyDirty()
            ->useLogName('Bank');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Bank has been {$eventName} by " . (auth()->check() ? auth()->user()->name : 'system');
    }
}
