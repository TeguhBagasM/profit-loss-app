<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'date',
        'chart_of_account_id',
        'description',
        'debit',
        'credit',
    ];

    protected $casts = [
        'date'   => 'date',
        'debit'  => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class);
    }
}