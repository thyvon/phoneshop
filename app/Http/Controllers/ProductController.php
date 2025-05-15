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

    public function edit($id)
    {
        try {
            $product = Product::with([
                'variants.values.attribute' // eager load related variant values and their attributes
            ])->findOrFail($id);

            return response()->json([
                'product' => $product
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching product for editing', [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to fetch product',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'sku' => 'nullable|unique:products,sku,' . $id, // allow current product SKU
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'has_variants' => 'boolean',
            'price' => 'nullable|numeric|required_if:has_variants,false',
            'stock' => 'nullable|integer|required_if:has_variants,false',
            'variants' => 'array|nullable',
            'variants.*.id' => 'nullable|exists:product_variants,id', // existing variants may have IDs
            'variants.*.description' => 'required_if:has_variants,true|string',
            'variants.*.price' => 'required_if:has_variants,true|numeric',
            'variants.*.stock' => 'required_if:has_variants,true|integer',
            'variants.*.variant_value_ids' => 'required_with:variants|array|min:1',
            'variants.*.variant_value_ids.*' => 'exists:variant_values,id',
        ]);

        DB::beginTransaction();

        try {
            $product = Product::findOrFail($id);

            // Update product basic info
            $product->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'has_variants' => $validated['has_variants'],
                // SKU usually doesn't change on update, but you can update if needed:
                // 'sku' => $validated['sku'] ?? $product->sku,
            ]);

            // Handle variants update
            if ($validated['has_variants']) {
                // Keep track of variant IDs from request
                $requestVariantIds = collect($validated['variants'])->pluck('id')->filter()->all();

                // Delete variants that are no longer present in the request
                $product->variants()->whereNotIn('id', $requestVariantIds)->each(function ($variant) {
                    $variant->values()->detach();
                    $variant->delete();
                });

                foreach ($validated['variants'] as $index => $variantData) {
                    if (!empty($variantData['id'])) {
                        // Update existing variant
                        $variant = ProductVariant::findOrFail($variantData['id']);
                        $variant->update([
                            'description' => $variantData['description'],
                            'price' => $variantData['price'],
                            'stock' => $variantData['stock'],
                            // You can update SKU if needed, but generally keep it stable
                        ]);

                        // Sync variant values
                        $variant->values()->sync($variantData['variant_value_ids']);
                    } else {
                        // New variant: generate SKU based on product SKU and variant index
                        $variantSku = $this->generateVariantSku($product->sku, $index + 1);

                        // Create new variant
                        $newVariant = $product->variants()->create([
                            'description' => $variantData['description'],
                            'price' => $variantData['price'],
                            'stock' => $variantData['stock'],
                            'sku' => $variantSku,
                        ]);

                        // Attach variant values
                        $newVariant->values()->attach($variantData['variant_value_ids']);
                    }
                }
            } else {
                // No variants: remove existing variants and create default variant

                // Delete existing variants and detach values
                foreach ($product->variants as $variant) {
                    $variant->values()->detach();
                    $variant->delete();
                }

                // Create default variant
                $variantSku = $this->generateVariantSku($product->sku, 1);

                $product->variants()->create([
                    'description' => 'Default',
                    'price' => $validated['price'],
                    'stock' => $validated['stock'],
                    'sku' => $variantSku,
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Product updated successfully']);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error updating product', [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to update product',
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
