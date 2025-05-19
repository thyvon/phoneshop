<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'code',
        'description',
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