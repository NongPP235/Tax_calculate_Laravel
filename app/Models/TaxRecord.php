<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxRecord extends Model
{
    protected $fillable = [
        'user_id',
        'tax_year',
        'title',
        'married_status',
        'personal_details',
        'total_income',
        'exempt_income',
        'total_expense',
        'total_deduction',
        'donation_allowed',
        'net_income',
        'step_tax',
        'minimum_tax',
        'tax_payable',
        'withholding_tax',
        'half_year_tax',
        'additional_tax',
        'refund',
        'calculation_version',
    ];

    protected function casts(): array
    {
        return [
            'tax_year' => 'integer',
            'personal_details' => 'array',
            'total_income' => 'decimal:2',
            'exempt_income' => 'decimal:2',
            'total_expense' => 'decimal:2',
            'total_deduction' => 'decimal:2',
            'donation_allowed' => 'decimal:2',
            'net_income' => 'decimal:2',
            'step_tax' => 'decimal:2',
            'minimum_tax' => 'decimal:2',
            'tax_payable' => 'decimal:2',
            'withholding_tax' => 'decimal:2',
            'half_year_tax' => 'decimal:2',
            'additional_tax' => 'decimal:2',
            'refund' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function incomes()
    {
        return $this->hasMany(Income::class);
    }

    public function deductions()
    {
        return $this->hasMany(Deduction::class);
    }
}
