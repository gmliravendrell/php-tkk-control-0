<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ControlEvent extends Model
{
    protected $fillable = [
        'control_id', 'sso_user_id', 'sso_user_name', 'action'
    ];

    public function control(): BelongsTo
    {
        return $this->belongsTo(Control::class);
    }
}
