<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncomeType extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function expenseTypes()
    {
        return $this->hasMany(ExpenseType::class);
    }

    public function incomes()
    {
        return $this->hasMany(Income::class);
    }
}
