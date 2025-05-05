<?php

namespace App\Http\Controllers;

use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use App\Models\Product\VariantValue;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::all();
        return view('products.index', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'has_variants' => 'required|boolean',
            'variants' => 'nullable|array',
        ]);

        // Create Product
        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'has_variants' => $request->has_variants,
        ]);

        // If product has variants
        if ($product->has_variants && $request->has('variants')) {
            foreach ($request->variants as $variantData) {
                // Expecting: ['price' => ..., 'stock' => ..., 'value_ids' => [1, 2]]
                $sku = $this->generateSku($product->name, $variantData['value_ids']);

                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $sku,
                    'price' => $variantData['price'],
                    'stock' => $variantData['stock'],
                ]);

                $variant->values()->sync($variantData['value_ids']);
            }
        } else {
            // Single product variant
            ProductVariant::create([
                'product_id' => $product->id,
                'sku' => $this->generateSku($product->name),
                'price' => $request->price,
                'stock' => $request->stock,
            ]);
        }

        return response()->json(['product' => $product->load('variants')]);
    }

    protected function generateSku($productName, $valueIds = [])
    {
        $code = strtoupper(Str::slug($productName, '-'));
        $suffix = '';

        if (!empty($valueIds)) {
            $values = VariantValue::whereIn('id', $valueIds)->get();
            $suffix = $values->pluck('value')->map(function ($v) {
                return strtoupper(Str::substr($v, 0, 3));
            })->implode('-');
        }

        return $suffix ? "$code-$suffix" : $code;
    }
}
