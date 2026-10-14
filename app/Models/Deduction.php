<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deduction extends Model
{
    protected $fillable = [
        'tax_record_id',
        'deduction_type_id',
        'type_name',
        'amount',
        'allowed_amount',
        'details',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'allowed_amount' => 'decimal:2',
            'details' => 'array',
        ];
    }

    public function taxRecord()
    {
        return $this->belongsTo(TaxRecord::class);
    }

    public function deductionType()
    {
        return $this->belongsTo(DeductionType::class);
    }
}
