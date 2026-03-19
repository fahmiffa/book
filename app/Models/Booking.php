<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;

class Booking extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'nik',
        'jmo_number',
        'whatsapp_number',
        'booking_date',
        'booking_time',
        'service',
        'location_id',
        'loket_id',
        'status',
        'catatan',
    ];

    /**
     * Boot function from Laravel.
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    protected $casts = [
        'nik' => 'encrypted',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function loket()
    {
        return $this->belongsTo(Loket::class);
    }
}
