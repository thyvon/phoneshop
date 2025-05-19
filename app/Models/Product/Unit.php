<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'short_name',
        'allow_decimal',
        'is_active',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}
