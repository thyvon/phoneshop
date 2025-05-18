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
        $this->authorize('viewAny', Product::class);

        return view('products.index');
    }

    public function getProducts(Request $request)
    {
        $this->authorize('viewAny', Product::class);

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
            $sortColumn = 'created_at';
        }
        if (!in_array(strtolower($sortDirection), ['asc', 'desc'])) {
            $sortDirection = 'desc';
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
        $this->authorize('create', Product::class);

        $validated = $request->validate([
            'sku' => 'nullable|unique:products,sku',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'has_variants' => 'boolean',
            'price' => 'nullable|numeric|required_if:has_variants,false',
            'stock' => 'nullable|integer|required_if:has_variants,false',
            'variants' => 'array|nullable',
            'variants.*.description' => 'required_if:has_variants,true|string',
            'variants.*.price' => 'required_if:has_variants,true|numeric',
            'variants.*.stock' => 'required_if:has_variants,true|integer',
            'variants.*.variant_value_ids' => 'required_with:variants|array|min:1',
            'variants.*.variant_value_ids.*' => 'exists:variant_values,id',
        ]);

        Log::info('Product creation request', ['request' => $validated]);

        DB::beginTransaction();

        try {
            $baseSku = $this->generateBaseSku();
            Log::info('Generated base SKU', ['baseSku' => $baseSku]);

            $product = Product::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'has_variants' => $validated['has_variants'],
                'sku' => $baseSku,
            ]);

            Log::info('Product created successfully', ['product_id' => $product->id, 'sku' => $product->sku]);

            if ($validated['has_variants']) {
                foreach ($validated['variants'] as $index => $variant) {
                    $variantSku = $this->generateVariantSku($baseSku, $index + 1);
                    Log::info('Generated variant SKU', ['variantSku' => $variantSku, 'variant_index' => $index + 1]);

                    $createdVariant = $product->variants()->create([
                        'description' => $variant['description'],
                        'price' => $variant['price'],
                        'stock' => $variant['stock'],
                        'sku' => $variantSku,
                    ]);

                    $createdVariant->values()->attach($variant['variant_value_ids']);
                    Log::info('Variant values attached', ['variant_id' => $createdVariant->id, 'variant_value_ids' => $variant['variant_value_ids']]);
                }
            } else {
                $variantSku = $this->generateVariantSku($baseSku, 1);
                Log::info('Generated default variant SKU', ['variantSku' => $variantSku]);

                $product->variants()->create([
                    'description' => 'Default',
                    'price' => $validated['price'],
                    'stock' => $validated['stock'],
                    'sku' => $variantSku,
                ]);
            }

            DB::commit();

            Log::info('Product and variants created successfully', ['product_id' => $product->id]);

            return response()->json(['message' => 'Product created successfully'], 201);

        } catch (\Exception $e) {
            DB::rollBack();

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

    // Use route model binding for all resource methods
    public function edit(Product $product)
    {
        $this->authorize('edit', $product);

        try {
            $product->load(['variants.values.attribute']);
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

    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'sku' => 'nullable|unique:products,sku,' . $product->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'has_variants' => 'boolean',
            'price' => 'nullable|numeric|required_if:has_variants,false',
            'stock' => 'nullable|integer|required_if:has_variants,false',
            'variants' => 'array|nullable',
            'variants.*.id' => 'nullable|exists:product_variants,id',
            'variants.*.description' => 'required_if:has_variants,true|string',
            'variants.*.price' => 'required_if:has_variants,true|numeric',
            'variants.*.stock' => 'required_if:has_variants,true|integer',
            'variants.*.variant_value_ids' => 'required_with:variants|array|min:1',
            'variants.*.variant_value_ids.*' => 'exists:variant_values,id',
        ]);

        DB::beginTransaction();

        try {
            $product->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'has_variants' => $validated['has_variants'],
            ]);

            if ($validated['has_variants']) {
                $requestVariantIds = collect($validated['variants'])->pluck('id')->filter()->all();

                $product->variants()->whereNotIn('id', $requestVariantIds)->each(function ($variant) {
                    $variant->values()->detach();
                    $variant->delete();
                });

                foreach ($validated['variants'] as $index => $variantData) {
                    if (!empty($variantData['id'])) {
                        $variant = ProductVariant::findOrFail($variantData['id']);
                        $variant->update([
                            'description' => $variantData['description'],
                            'price' => $variantData['price'],
                            'stock' => $variantData['stock'],
                        ]);
                        $variant->values()->sync($variantData['variant_value_ids']);
                    } else {
                        $variantSku = $this->generateVariantSku($product->sku, $index + 1);
                        $newVariant = $product->variants()->create([
                            'description' => $variantData['description'],
                            'price' => $variantData['price'],
                            'stock' => $variantData['stock'],
                            'sku' => $variantSku,
                        ]);
                        $newVariant->values()->attach($variantData['variant_value_ids']);
                    }
                }
            } else {
                foreach ($product->variants as $variant) {
                    $variant->values()->detach();
                    $variant->delete();
                }

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

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        DB::beginTransaction();

        try {
            Log::info('Deleting product', ['product_id' => $product->id]);

            foreach ($product->variants as $variant) {
                $variant->values()->detach();
                $variant->delete();
                Log::info('Deleted variant', ['variant_id' => $variant->id]);
            }

            $product->delete();

            DB::commit();

            Log::info('Product deleted successfully', ['product_id' => $product->id]);

            return response()->json(['message' => 'Product deleted successfully'], 200);

        } catch (\Exception $e) {
            DB::rollBack();

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

    protected function generateBaseSku()
    {
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
        // Optionally add policy here if needed
        return VariantValue::with('attribute')->get();
    }

    public function getAttributes()
    {
        // Optionally add policy here if needed
        return VariantAttribute::with('values')->get();
    }
}
