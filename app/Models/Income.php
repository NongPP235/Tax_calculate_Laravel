<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    protected $fillable = [
        'tax_record_id',
        'income_type_id',
        'type_name',
        'amount',
        'details',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'details' => 'array',
        ];
    }

    public function taxRecord()
    {
        return $this->belongsTo(TaxRecord::class);
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
