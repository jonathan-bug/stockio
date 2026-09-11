<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable('barcode', 'name', 'sales_price', 'minimum_stock', 'is_active', 'category_id')]
class Product extends Model
{
    public function cast(): array
    {
        return [
            'is_active' => 'boolean'
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stock(): HasOne
    {
        return $this->hasOne(ProductStock::class);
    }

    public function inventory_adjustments(): HasMany
    {
        return $this->hasMany(InventoryAdjustment::class);
    }
}
