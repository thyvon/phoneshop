<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'sub_taxonomy',
        'parent_id',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

}