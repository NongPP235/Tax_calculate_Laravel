<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'income_id',
        'expense_type_id',
        'type_name',
        'method',
        'rate',
        'amount',
        'allowed_amount',
        'details',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:4',
            'amount' => 'decimal:2',
            'allowed_amount' => 'decimal:2',
            'details' => 'array',
        ];
    }

    public function income()
    {
        return $this->belongsTo(Income::class);
    }

    public function expenseType()
    {
        return $this->belongsTo(ExpenseType::class);
    }
}
