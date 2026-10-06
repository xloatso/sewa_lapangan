<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Court extends Model
{
    protected $fillable = ['name', 'type', 'price_per_hour', 'description', 'image'];

    protected function casts(): array
    {
        return ['price_per_hour' => 'integer'];
    }

    public function details(): HasMany
    {
        return $this->hasMany(BookingDetail::class);
    }
}
