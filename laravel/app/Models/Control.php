<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Control extends Model
{
    protected $fillable = [
        'name', 'km_point', 'responsible', 'phone', 'status'
    ];

    public function checks(): HasMany
    {
        return $this->hasMany(Check::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ControlEvent::class);
    }
}
