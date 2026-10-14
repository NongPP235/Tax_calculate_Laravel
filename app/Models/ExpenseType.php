<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseType extends Model
{
    protected $fillable = [
        'income_type_id',
        'code',
        'name',
        'tax_year',
        'method',
        'rate',
        'max_amount',
        'limit_group',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'tax_year' => 'integer',
            'rate' => 'decimal:4',
            'max_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function incomeType()
    {
        return $this->belongsTo(IncomeType::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
