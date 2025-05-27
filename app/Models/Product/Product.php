<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory, SoftDeletes;

        protected $fillable = [
        'sku',
        'name',
        'description',
        'has_variants',
        'barcode',
        'brand_id',
        'category_id',
        'sub_category_id',
        'unit_id',
        'manage_stock',
        'alert_qty',
        'image',
        'not_sale',
        'serial_des',
        'tax',
        'include_tax',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
    ];

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function subCategory()
    {
        return $this->belongsTo(Category::class);
    }
}
