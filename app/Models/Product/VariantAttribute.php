<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariantAttribute extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'ordinal'];

    public function values()
    {
        return $this->hasMany(VariantValue::class);
    }
}
