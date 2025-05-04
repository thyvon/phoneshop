<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use App\Models\Product\VariantValue;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        foreach (range(1, 10) as $index) {
            $hasVariants = $faker->boolean;

            $product = Product::create([
                'name' => $faker->unique()->word,
                'description' => $faker->paragraph,
                'has_variants' => $hasVariants,
            ]);

            if ($hasVariants) {
                $this->createProductVariants($product, $faker);
            } else {
                $this->createSingleVariant($product, $faker);
            }
        }
    }

    private function createProductVariants(Product $product, $faker)
    {
        foreach (range(1, 2) as $i) {
            // Create 2 variant values (e.g., Color: Red, Size: M)
            $values = VariantValue::factory()->count(2)->create();

            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'sku' => $this->generateSku($product->name, $values->pluck('id')->toArray()),
                'price' => $faker->randomFloat(2, 10, 200),
                'stock' => $faker->numberBetween(5, 100),
            ]);

            // Attach values to the variant
            $variant->values()->sync($values->pluck('id')->toArray());
        }
    }

    private function createSingleVariant(Product $product, $faker)
    {
        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $this->generateSku($product->name),
            'price' => $faker->randomFloat(2, 5, 100),
            'stock' => $faker->numberBetween(10, 50),
        ]);
    }

    private function generateSku($productName, $valueIds = [])
    {
        $code = strtoupper(Str::slug($productName, '-'));
        $suffix = '';

        if (!empty($valueIds)) {
            $values = VariantValue::whereIn('id', $valueIds)->get();
            $suffix = $values->pluck('value')->map(function ($v) {
                return strtoupper(Str::substr($v, 0, 3));
            })->implode('-');
        }

        return $suffix ? "{$code}-{$suffix}" : $code;
    }
}
