<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

        protected $fillable = [
        'product_id',
        'sku',
        'price',
        'stock',
        'default_purchase_price',
        'default_sale_price',
        'default_margin',
        'image',
        'is_active',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function values()
    {
        return $this->belongsToMany(VariantValue::class, 'product_variant_value');
    }
}
