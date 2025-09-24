<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class SalePerson extends Model
{
    use SoftDeletes;
    use HasFactory;
    use LogsActivity;

    // protected $table = 'sale_people';
    protected $fillable= [
        'name',
        'email',
        'zoho_salesperson_id',
        'status',
    ];

    /**
     * Get the options for activity logging.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                'status',
            ])
            ->logOnlyDirty()
            ->useLogName('Sale Person');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Sale Person has been {$eventName} by " . (auth()->check() ? auth()->user()->name : 'system');
    }
}
