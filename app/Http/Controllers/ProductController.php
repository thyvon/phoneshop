<?php

namespace App\Http\Controllers;

use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use App\Models\Product\VariantValue;
use App\Models\Product\VariantAttribute;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    // web: return the Blade + Vue “shell”
    public function index()
    {
        return view('products.index');
    }

    public function getProducts(Request $request)
    {
        $query = Product::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $allowedSortColumns = ['name', 'description', 'created_at', 'updated_at'];
        $sortColumn = $request->get('sortColumn', 'created_at');
        $sortDirection = $request->get('sortDirection', 'desc');

        if (!in_array($sortColumn, $allowedSortColumns)) {
            $sortColumn = 'created_at'; // Default to 'created_at' if not in allowed list
        }
        if (!in_array(strtolower($sortDirection), ['asc', 'desc'])) {
            $sortDirection = 'desc'; // Default to 'desc' if direction is not valid
        }

        $query->orderBy($sortColumn, $sortDirection);
        $limit = intval($request->get('limit', 10));
        $products = $query->paginate($limit);

        return response()->json([
            'data' => $products->items(),
            'recordsTotal' => $products->total(),
            'recordsFiltered' => $products->total(),
            'draw' => intval($request->get('draw')),
        ]);
    }

public function store(Request $request)
{
    $validated = $request->validate([
        'sku' => 'nullable|unique:products,sku', // SKU should be nullable for auto generation
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'has_variants' => 'boolean',
        'price' => 'nullable|numeric|required_if:has_variants,false',
        'stock' => 'nullable|integer|required_if:has_variants,false',
        'variants' => 'array|nullable',
        'variants.*.description' => 'required_if:has_variants,true|string',
        'variants.*.price' => 'required_if:has_variants,true|numeric',
        'variants.*.stock' => 'required_if:has_variants,true|integer',
        'variants.*.variant_value_ids' => 'required_with:variants|array|min:1', // Ensure that variant values are passed with variants
        'variants.*.variant_value_ids.*' => 'exists:variant_values,id', // Validate the variant values exist
    ]);

    // Log the incoming request for debugging
    Log::info('Product creation request', ['request' => $validated]);

    DB::beginTransaction();

    try {
        // Generate base product SKU if not provided
        $baseSku = $this->generateBaseSku();
        Log::info('Generated base SKU', ['baseSku' => $baseSku]);

        // Create product and ensure 'sku' is always included
        $product = Product::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'has_variants' => $validated['has_variants'],
            'sku' => $baseSku, // Ensure SKU is being saved
        ]);

        // Log product creation
        Log::info('Product created successfully', ['product_id' => $product->id, 'sku' => $product->sku]);

        // Variant handling if 'has_variants' is true
        if ($validated['has_variants']) {
            foreach ($validated['variants'] as $index => $variant) {
                $variantSku = $this->generateVariantSku($baseSku, $index + 1); // Generate SKU for variant
                Log::info('Generated variant SKU', ['variantSku' => $variantSku, 'variant_index' => $index + 1]);

                // Create the variant
                $createdVariant = $product->variants()->create([
                    'description' => $variant['description'],
                    'price' => $variant['price'],
                    'stock' => $variant['stock'],
                    'sku' => $variantSku,  // Ensure SKU is being saved
                ]);

                // Attach variant values (assuming the relationship is defined in the ProductVariant model)
                $createdVariant->values()->attach($variant['variant_value_ids']);
                Log::info('Variant values attached', ['variant_id' => $createdVariant->id, 'variant_value_ids' => $variant['variant_value_ids']]);
            }
        } else {
            // Default variant logic
            $variantSku = $this->generateVariantSku($baseSku, 1);
            Log::info('Generated default variant SKU', ['variantSku' => $variantSku]);

            // Create the default variant for products without variants
            $product->variants()->create([
                'description' => 'Default',
                'price' => $validated['price'],
                'stock' => $validated['stock'],
                'sku' => $variantSku,  // Ensure SKU is always added for default variant
            ]);
        }

        DB::commit();

        // Log success message
        Log::info('Product and variants created successfully', ['product_id' => $product->id]);

        return response()->json(['message' => 'Product created successfully'], 201);

    } catch (\Exception $e) {
        DB::rollBack();

        // Log error
        Log::error('Error creating product', [
            'error_message' => $e->getMessage(),
            'stack_trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'message' => 'Failed to create product',
            'error' => $e->getMessage(),
        ], 500);
    }
}


    protected function generateBaseSku()
    {
        // Count existing products to increment SKU number
        $lastProduct = Product::orderBy('id', 'desc')->first();
        $nextNumber = $lastProduct ? $lastProduct->id + 1 : 1;

        return 'SKU-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    protected function generateVariantSku($baseSku, $index)
    {
        return $baseSku . '-' . str_pad($index, 2, '0', STR_PAD_LEFT);
    }


    public function getVariantValues()
    {
        return VariantValue::with('attribute')->get();
    }

    public function getAttributes()
    {
        return VariantAttribute::with('values')->get();
    }

    public function destroy($id)
{
    DB::beginTransaction();

    try {
        // Find the product by ID
        $product = Product::findOrFail($id);

        // Log the product deletion attempt
        Log::info('Deleting product', ['product_id' => $product->id]);

        // Delete related variants and their values
        foreach ($product->variants as $variant) {
            // Detach any related variant values
            $variant->values()->detach();

            // Delete the variant
            $variant->delete();
            Log::info('Deleted variant', ['variant_id' => $variant->id]);
        }

        // Delete the product
        $product->delete();

        DB::commit();

        // Log success message
        Log::info('Product deleted successfully', ['product_id' => $product->id]);

        return response()->json(['message' => 'Product deleted successfully'], 200);

    } catch (\Exception $e) {
        DB::rollBack();

        // Log error
        Log::error('Error deleting product', [
            'error_message' => $e->getMessage(),
            'stack_trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'message' => 'Failed to delete product',
            'error' => $e->getMessage(),
        ], 500);
    }
}

}
