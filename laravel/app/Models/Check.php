<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Check extends Model
{
    protected $fillable = [
        'control_id', 'participant_id', 'type', 'checked_at'
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'participant_id', 'id');
    }

    public function control(): BelongsTo
    {
        return $this->belongsTo(Control::class);
    }
}
