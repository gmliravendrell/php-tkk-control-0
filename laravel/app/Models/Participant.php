<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Participant extends Model
{
    protected $primaryKey = 'id'; // dorsal

    public $incrementing = true;

    protected $fillable = [
        'dni', 'first_name', 'last_name', 'phone', 'emergency_phone', 'gender', 'birth_date',
        'status', 'lunch_sandwich', 'dinner_sandwich', 'shirt_size',
    ];

    public function checks(): HasMany
    {
        return $this->hasMany(Check::class, 'participant_id', 'id');
    }
}
