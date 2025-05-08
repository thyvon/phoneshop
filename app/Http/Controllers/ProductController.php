<?php

namespace App\Http\Controllers;

use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use App\Models\Product\VariantValue;
use App\Models\Product\VariantAttribute;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    // web: return the Blade + Vue “shell”
    public function index()
    {
        return view('products.index');  
    }

    // api: return JSON to the Vue component
    public function getProducts()
    {
        return Product::all();
    }

    public function storeOrUpdate(Request $request, $id = null)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'has_variants' => 'required|boolean',
            'variants' => 'nullable|array',
        ]);

        // Check if updating an existing product
        $product = $id ? Product::findOrFail($id) : new Product;

        // Update or create product
        $product->name = $request->name;
        $product->description = $request->description;
        $product->has_variants = $request->has_variants;
        $product->save();

        // Handle product variants if any
        if ($product->has_variants && $request->has('variants')) {
            foreach ($request->variants as $variantData) {
                // Expecting: ['price' => ..., 'stock' => ..., 'value_ids' => [1, 2]]
                $sku = $this->generateSku($product->name, $variantData['value_ids']);
                
                // Check if variant exists for update or create new
                $variant = isset($variantData['id']) ? ProductVariant::find($variantData['id']) : new ProductVariant;
                $variant->product_id = $product->id;
                $variant->sku = $sku;
                $variant->price = $variantData['price'];
                $variant->stock = $variantData['stock'];
                $variant->save();

                // Sync the variant values
                $variant->values()->sync($variantData['value_ids']);
            }
        } else {
            // Handle single product variant if no variants are provided
            if (!$product->has_variants) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $this->generateSku($product->name),
                    'price' => $request->price,
                    'stock' => $request->stock,
                ]);
            }
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

    public function getVariantValues()
    {
        return VariantValue::with('attribute')->get();
    }

    public function getAttributes()
    {
        return VariantAttribute::with('values')->get();
    }
}
