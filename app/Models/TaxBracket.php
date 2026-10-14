<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxBracket extends Model
{
    protected $fillable = [
        'tax_year',
        'lower_bound',
        'upper_bound',
        'rate',
    ];

    protected function casts(): array
    {
        return [
            'tax_year' => 'integer',
            'lower_bound' => 'decimal:2',
            'upper_bound' => 'decimal:2',
            'rate' => 'decimal:4',
        ];
    }
}
