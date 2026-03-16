<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'name',
        'jmo_number',
        'whatsapp_number',
        'booking_date',
        'booking_time',
        'service',
        'location_id',
        'status',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
