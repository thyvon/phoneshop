<?php

namespace App\Http\Controllers;

use App\Models\Product\Product;
use App\Models\Product\ProductVariant;
use App\Models\Product\VariantValue;
use App\Models\Product\VariantAttribute;
use App\Models\Product\Brand;
use App\Models\Product\Category;
use App\Models\Product\Unit;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

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

        $query = Product::with(['brand', 'category']);

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

    private function productValidationRules($productId = null, $variantIds = [])
    {
    return [
        'sku' => [
            'nullable',
            'unique:products,sku' . ($productId ? ',' . $productId : ''),
        ],
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'has_variants' => 'boolean',
        'barcode' => 'required|string|max:255',
        'brand_id' => 'required|exists:brands,id',
        'category_id' => 'required|exists:categories,id',
        'sub_category_id' => 'nullable|exists:categories,id',
        'unit_id' => 'required|exists:units,id',
        'manage_stock' => 'boolean',
        'alert_qty' => 'nullable|numeric',
        'image' => 'nullable|string|max:255|file',
        'not_sale' => 'boolean',
        'serial_des' => 'boolean',
        'tax' => 'nullable|numeric',
        'include_tax' => 'nullable|integer',
        'is_active' => 'boolean',
        'variants' => 'array|nullable',
        'variants.*.id' => 'nullable|exists:product_variants,id',
        'variants.*.sku' => [
            'nullable',
            'string',
            function ($attribute, $value, $fail) use ($variantIds) {
                $index = (int) explode('.', $attribute)[1];
                $variantId = $variantIds[$index] ?? null;
                $query = ProductVariant::where('sku', $value);
                if ($variantId) {
                    $query->where('id', '!=', $variantId);
                }
                if ($query->exists()) {
                    $fail('The SKU has already been taken.');
                }
            }
        ],
        'variants.*.price' => 'required|numeric',
        'variants.*.stock' => 'required|numeric',
        'variants.*.default_purchase_price' => 'nullable|numeric',
        'variants.*.default_sale_price' => 'nullable|numeric',
        'variants.*.default_margin' => 'nullable|numeric',
        'image' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
        'variants.*.variant_value_ids' => 'array',
        'variants.*.variant_value_ids.*' => 'exists:variant_values,id',
        'variants.*.is_active' => 'boolean',
    ];
    }


    public function store(Request $request)
    {
        $this->authorize('create', Product::class);

        $validated = $request->validate($this->productValidationRules());

        DB::beginTransaction();

        try {
            // Handle main product image upload
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products', 'public');
            }

            $baseSku = $validated['sku'] ?? $this->generateBaseSku();

            $product = Product::create([
                'sku' => $baseSku,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'has_variants' => $validated['has_variants'],
                'barcode' => $validated['barcode'] ?? null,
                'brand_id' => $validated['brand_id'] ?? null,
                'category_id' => $validated['category_id'] ?? null,
                'sub_category_id' => $validated['sub_category_id'] ?? null,
                'unit_id' => $validated['unit_id'] ?? null,
                'manage_stock' => $validated['manage_stock'] ?? true,
                'alert_qty' => $validated['alert_qty'] ?? 0,
                'image' => $imagePath ?? ($validated['image'] ?? null),
                'not_sale' => $validated['not_sale'] ?? false,
                'serial_des' => $validated['serial_des'] ?? false,
                'tax' => $validated['tax'] ?? 0,
                'include_tax' => $validated['include_tax'] ?? 0,
                'is_active' => $validated['is_active'] ?? true,
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['variants'] as $index => $variant) {
                // Handle variant image upload
                $variantImagePath = null;
                if ($request->hasFile("variants.$index.image")) {
                    $variantImagePath = $request->file("variants.$index.image")->store('variants', 'public');
                }

                $variantSku = $variant['sku'] ?? $this->generateVariantSku($baseSku, $index + 1);
                $createdVariant = $product->variants()->create([
                    'sku' => $variantSku,
                    // 'description' => $variant['description'] ?? null,
                    'price' => $variant['price'],
                    'stock' => $variant['stock'],
                    'default_purchase_price' => $variant['default_purchase_price'] ?? null,
                    'default_sale_price' => $variant['default_sale_price'] ?? null,
                    'default_margin' => $variant['default_margin'] ?? null,
                    'image' => $variantImagePath ?? ($variant['image'] ?? null),
                    'is_active' => array_key_exists('is_active', $variant) ? $variant['is_active'] : true,
                ]);
                $createdVariant->values()->attach($variant['variant_value_ids'] ?? []);
            }

            DB::commit();

            return response()->json(['message' => 'Product created successfully'], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            // Add error log here
            \Log::error('Error creating product', [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'message' => 'Failed to create product',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

public function edit(Product $product)
{
    $this->authorize('update', $product);

    try {
        $product->load(['variants.values.attribute']);

        // Add image URL for product
        $product->image_url = $product->image ? asset('storage/' . $product->image) : null;

        // Add image URL for each variant
        foreach ($product->variants as $variant) {
            $variant->image_url = $variant->image ? asset('storage/' . $variant->image) : null;
        }

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

        $variantIds = [];
        if ($request->has('variants')) {
            foreach ($request->input('variants') as $i => $variant) {
                $variantIds[$i] = $variant['id'] ?? null;
            }
        }

        $validated = $request->validate($this->productValidationRules($product->id, $variantIds));

        DB::beginTransaction();

        try {
            // Handle main product image upload
            $imagePath = $product->image;
            if ($request->hasFile('image')) {
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                $imagePath = $request->file('image')->store('products', 'public');
            }

            $product->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'has_variants' => $validated['has_variants'],
                'barcode' => $validated['barcode'] ?? null,
                'brand_id' => $validated['brand_id'] ?? null,
                'category_id' => $validated['category_id'] ?? null,
                'sub_category_id' => $validated['sub_category_id'] ?? null,
                'unit_id' => $validated['unit_id'] ?? null,
                'manage_stock' => $validated['manage_stock'] ?? true,
                'alert_qty' => $validated['alert_qty'] ?? 0,
                'image' => $imagePath,
                'not_sale' => $validated['not_sale'] ?? false,
                'serial_des' => $validated['serial_des'] ?? false,
                'tax' => $validated['tax'] ?? 0,
                'include_tax' => $validated['include_tax'] ?? 0,
                'is_active' => $validated['is_active'] ?? true,
                'updated_by' => auth()->id(),
            ]);

            // Remove variants not present in the request
            $requestVariantIds = collect($validated['variants'])->pluck('id')->filter()->all();
            $product->variants()->whereNotIn('id', $requestVariantIds)->each(function ($variant) {
                if ($variant->image) {
                    Storage::disk('public')->delete($variant->image);
                }
                $variant->values()->detach();
                $variant->delete(); // Soft delete
            });

            foreach ($validated['variants'] as $index => $variantData) {
                if (!empty($variantData['id'])) {
                    $variant = ProductVariant::findOrFail($variantData['id']);
                    // Handle variant image upload
                    $variantImagePath = $variant->image;
                    if ($request->hasFile("variants.$index.image")) {
                        if ($variant->image) {
                            Storage::disk('public')->delete($variant->image);
                        }
                        $variantImagePath = $request->file("variants.$index.image")->store('variants', 'public');
                    }
                    $variant->update([
                        'sku' => $variantData['sku'] ?? $variant->sku,
                        // 'description' => $variantData['description'] ?? $variant->description,
                        'price' => $variantData['price'],
                        'stock' => $variantData['stock'],
                        'default_purchase_price' => $variantData['default_purchase_price'] ?? $variant->default_purchase_price,
                        'default_sale_price' => $variantData['default_sale_price'] ?? $variant->default_sale_price,
                        'default_margin' => $variantData['default_margin'] ?? $variant->default_margin,
                        'image' => $variantImagePath,
                        'is_active' => array_key_exists('is_active', $variantData) ? $variantData['is_active'] : $variant->is_active,
                    ]);
                    $variant->values()->sync($variantData['variant_value_ids'] ?? []);
                } else {
                    $variantImagePath = null;
                    if ($request->hasFile("variants.$index.image")) {
                        $variantImagePath = $request->file("variants.$index.image")->store('variants', 'public');
                    }
                    $variantSku = $variantData['sku'] ?? $this->generateVariantSku($product->sku, $index + 1);
                    $newVariant = $product->variants()->create([
                        'sku' => $variantSku,
                        'description' => $variantData['description'] ?? null,
                        'price' => $variantData['price'],
                        'stock' => $variantData['stock'],
                        'default_purchase_price' => $variantData['default_purchase_price'] ?? null,
                        'default_sale_price' => $variantData['default_sale_price'] ?? null,
                        'default_margin' => $variantData['default_margin'] ?? null,
                        'image' => $variantImagePath ?? ($variantData['image'] ?? null),
                        'is_active' => array_key_exists('is_active', $variantData) ? $variantData['is_active'] : true,
                    ]);
                    $newVariant->values()->attach($variantData['variant_value_ids'] ?? []);
                }
            }

            DB::commit();

            return response()->json(['message' => 'Product updated successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
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
                if ($variant->image) {
                    Storage::disk('public')->delete($variant->image);
                }
                $variant->values()->detach();
                $variant->delete();
                Log::info('Deleted variant', ['variant_id' => $variant->id]);
            }

            if ($product->image) {
                Storage::disk('public')->delete($product->image);
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
        $nextNumber = 1;
        do {
            $sku = 'SKU-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            $exists = Product::withTrashed()->where('sku', $sku)->exists();
            $nextNumber++;
        } while ($exists);

        return $sku;
    }

    protected function generateVariantSku($baseSku, $index)
    {
        $sku = $baseSku . '-' . str_pad($index, 2, '0', STR_PAD_LEFT);
        $exists = ProductVariant::withTrashed()->where('sku', $sku)->exists();
        $suffix = $index;
        while ($exists) {
            $suffix++;
            $sku = $baseSku . '-' . str_pad($suffix, 2, '0', STR_PAD_LEFT);
            $exists = ProductVariant::withTrashed()->where('sku', $sku)->exists();
        }
        return $sku;
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

    public function getBrands()
    {
        // Optionally add policy here if needed
        return Brand::select('id', 'name', 'code', 'description', 'is_active')->get();
    }
    public function getCategories()
    {
        // Optionally add policy here if needed
        return Category::with('children')->select('id', 'name', 'code', 'description', 'is_active', 'sub_taxonomy', 'parent_id')->get();
    }
    public function getUnits()
    {
        // Optionally add policy here if needed
        return Unit::select('id', 'name', 'short_name', 'allow_decimal', 'is_active')->get();
    }
}
