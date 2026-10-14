<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeductionType extends Model
{
    protected $fillable = [
        'code',
        'name',
        'tax_year',
        'max_amount',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tax_year' => 'integer',
            'max_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function deductions()
    {
        return $this->hasMany(Deduction::class);
    }
}
