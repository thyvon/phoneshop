<?php

namespace Database\Factories\Product;

use App\Models\Product\VariantValue;
use Illuminate\Database\Eloquent\Factories\Factory;

class VariantValueFactory extends Factory
{
    protected $model = VariantValue::class;

    public function definition(): array
    {
        return [
            'variant_attribute_id' => \App\Models\Product\VariantAttribute::factory(), // create related attribute
            'value' => $this->faker->word,
        ];
    }
}
