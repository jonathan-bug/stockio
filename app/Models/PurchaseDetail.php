<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable('purchase_id', 'product_id', 'quantity', 'unit_cost', 'is_applied')]
class PurchaseDetail extends Model
{
    protected function cast(): array
    {
        return [
            'is_applied' => 'boolean'
        ];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }
}
