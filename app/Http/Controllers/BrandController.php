<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product\Brand;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BrandController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Brand::class);
        return view('brands.index');
    }

    public function getBrands(Request $request)
    {
        $this->authorize('viewAny', Brand::class);
        $query = Brand::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $allowedSortColumns = ['name', 'description', 'created_at', 'updated_at', 'is_active', 'code'];
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
        $brands = $query->paginate($limit);

        $data = collect($brands->items())->map(function ($brand) {
            return [
                'id' => $brand->id,
                'name' => $brand->name,
                'code' => $brand->code,
                'description' => $brand->description,
                'is_active' => $brand->is_active,
                'created_at' => $brand->created_at,
                'updated_at' => $brand->updated_at,
            ];
        });

        return response()->json([
            'data' => $data,
            'recordsTotal' => $brands->total(),
            'recordsFiltered' => $brands->total(),
            'draw' => intval($request->get('draw')),
        ]);
    }

    private function brandValidationRules($brandId = null)
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'code' => [
                'nullable',
                'string',
                'max:255',
                'unique:brands,code' . ($brandId ? ',' . $brandId : ''),
            ],
            'is_active' => 'boolean',
        ];
    }

    public function store(Request $request)
    {
        $this->authorize('create', Brand::class);
        $validated = validator($request->all(), $this->brandValidationRules())->validate();

        DB::beginTransaction();
        try {
            $brand = Brand::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'code' => $validated['code'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'created_by' => $request->user()->id ?? null,
            ]);
            DB::commit();

            Log::info('Brand created', [
                'id' => $brand->id,
                'name' => $brand->name,
                'created_by' => $request->user()->id ?? null,
            ]);

            return response()->json([
                'message' => 'Brand created successfully.',
                'data' => $brand
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Brand creation failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'message' => 'Failed to create brand.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function edit(Brand $brand)
    {
        $this->authorize('update', $brand);
        return response()->json([
            'data' => $brand
        ]);
    }

    public function update(Request $request, Brand $brand)
    {
        $this->authorize('update', $brand);
        $validated = validator($request->all(), $this->brandValidationRules($brand->id))->validate();

        DB::beginTransaction();
        try {
            $brand->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'code' => $validated['code'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'updated_by' => $request->user()->id ?? null,
            ]);
            DB::commit();

            Log::info('Brand updated', [
                'id' => $brand->id,
                'name' => $brand->name,
                'updated_by' => $request->user()->id ?? null,
            ]);

            return response()->json([
                'message' => 'Brand updated successfully.',
                'data' => $brand
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Brand update failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'message' => 'Failed to update brand.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Brand $brand)
    {
        $this->authorize('delete', $brand);
        try {
            $brand->delete();

            return response()->json([
                'message' => 'Brand deleted successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Brand deletion failed', [
                'error' => $e->getMessage(),
                'brand_id' => $brand->id
            ]);

            return response()->json([
                'message' => 'Failed to delete brand.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
