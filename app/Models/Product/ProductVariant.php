<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'sku', 'price', 'stock'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function values()
    {
        return $this->belongsToMany(VariantValue::class, 'product_variant_value');
    }
}
