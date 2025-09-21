<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Control extends Model
{
    protected $fillable = [
        'name', 'km_point', 'responsible', 'phone', 'status',
        'passed', 'missing', 'abandoned',
    ];

    public function checks(): HasMany
    {
        return $this->hasMany(Check::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ControlEvent::class);
    }

    /**
     * Inicializa los contadores en función del total de participantes
     */
    public function resetCounters(int $participantsTotal): void
    {
        $this->update([
            'passed' => 0,
            'abandoned' => 0,
            'missing' => $participantsTotal,
        ]);
    }
}
