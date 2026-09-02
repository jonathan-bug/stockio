<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable('name', 'phone', 'email', 'is_active')]
class Supplier extends Model
{
    public function cast(): array
    {
        return [
            'is_active' => 'boolean'
        ];
    }
}
