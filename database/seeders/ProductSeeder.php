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

            // Generate base product SKU
            $baseSku = $this->generateBaseSku($index);

            $product = Product::create([
                'sku' => $baseSku, // Save base SKU
                'name' => $faker->unique()->word,
                'description' => $faker->paragraph,
                'has_variants' => $hasVariants,
            ]);

            if ($hasVariants) {
                $this->createProductVariants($product, $faker, $baseSku);
            } else {
                $this->createSingleVariant($product, $faker, $baseSku);
            }
        }
    }

    private function createProductVariants(Product $product, $faker, $baseSku)
    {
        foreach (range(1, 2) as $i) {
            // Create 2 variant values (e.g., Color: Red, Size: M)
            $values = VariantValue::factory()->count(2)->create();

            $variantSku = $this->generateVariantSku($baseSku, $i);

            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'sku' => $variantSku,
                'price' => $faker->randomFloat(2, 10, 200),
                'stock' => $faker->numberBetween(5, 100),
            ]);

            // Attach values to the variant
            $variant->values()->sync($values->pluck('id')->toArray());
        }
    }

    private function createSingleVariant(Product $product, $faker, $baseSku)
    {
        $variantSku = $this->generateVariantSku($baseSku, 1);

        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $variantSku,
            'price' => $faker->randomFloat(2, 5, 100),
            'stock' => $faker->numberBetween(10, 50),
        ]);
    }

    private function generateBaseSku($index)
    {
        return 'SKU-' . str_pad($index, 4, '0', STR_PAD_LEFT);
    }

    private function generateVariantSku($baseSku, $variantIndex)
    {
        return $baseSku . '-' . str_pad($variantIndex, 2, '0', STR_PAD_LEFT);
    }
}
