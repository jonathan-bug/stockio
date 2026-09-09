<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable('total', 'payment_method', 'payment_reference', 'payment_amount', 'payment_change')]
class Sale extends Model
{
    public function details(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }
}
