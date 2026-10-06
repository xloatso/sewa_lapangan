<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingDetail extends Model
{
    protected $fillable = ['court_id', 'start_time', 'duration_hours', 'subtotal'];

    protected function casts(): array
    {
        return ['duration_hours' => 'integer', 'subtotal' => 'integer'];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }
}
