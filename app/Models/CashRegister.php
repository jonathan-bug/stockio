<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable('initial_amount', 'closing_amount', 'opened_at', 'closed_at', 'status', 'user_id')]
class CashRegister extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
