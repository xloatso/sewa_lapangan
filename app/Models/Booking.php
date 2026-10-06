<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = ['customer_name', 'customer_phone', 'booking_date', 'total_price', 'status'];

    protected function casts(): array
    {
        return ['booking_date' => 'date', 'total_price' => 'integer'];
    }

    public function details(): HasMany
    {
        return $this->hasMany(BookingDetail::class);
    }
}
