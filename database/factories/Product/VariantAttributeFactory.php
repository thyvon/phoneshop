<?php

namespace Database\Factories\Product;

use App\Models\Product\VariantAttribute;
use Illuminate\Database\Eloquent\Factories\Factory;

class VariantAttributeFactory extends Factory
{
    protected $model = VariantAttribute::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word,
        ];
    }
}