<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable('name', 'is_active')]
class Category extends Model
{
    public function cast(): array
    {
        return [
            'is_active' => 'boolean'
        ];
    }
}
