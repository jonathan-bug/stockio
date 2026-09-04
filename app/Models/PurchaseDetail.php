<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable('purchase_id', 'product_id', 'quantity', 'unit_cost', 'is_applied')]
class PurchaseDetail extends Model
{
    protected function cast(): array
    {
        return [
            'is_applied' => 'boolean'
        ];
    }
}
