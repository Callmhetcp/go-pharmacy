<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    public function getTypedValueAttribute(): mixed
    {
        return match ($this->type) {
            'boolean' => filter_var(
                $this->value,
                FILTER_VALIDATE_BOOLEAN
            ),

            'integer' => (int) $this->value,

            'float' => (float) $this->value,

            'json' => json_decode(
                $this->value,
                true
            ),

            default => $this->value,
        };
    }
}