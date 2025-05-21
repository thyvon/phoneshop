<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product\Category;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    public function index()
    {
        return view('categories.index');
    }

    public function getCategories(Request $request)
    {
        $query = Category::with('parent');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $allowedSortColumns = ['name', 'description', 'created_at', 'updated_at', 'is_active', 'sub_taxonomy', 'parent_id'];
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
        $categories = $query->paginate($limit);

        // Add parent_name attribute for each category
        $categories->getCollection()->transform(function ($category) {
            $category->parent_name = $category->parent ? $category->parent->name : '-';
            return $category;
        });

        return response()->json([
            'data' => $categories->items(),
            'recordsTotal' => $categories->total(),
            'recordsFiltered' => $categories->total(),
            'draw' => intval($request->get('draw')),
        ]);
    }

    private function categoryValidationRules($categoryId = null)
    {
        return [
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:255',
                'unique:categories,code' . ($categoryId ? ',' . $categoryId : ''),
            ],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sub_taxonomy' => 'nullable|boolean',
            'parent_id' => 'nullable|integer|exists:categories,id',
        ];
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->categoryValidationRules());

        DB::beginTransaction();
        try {
            $category = Category::create([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'sub_taxonomy' => $validated['sub_taxonomy'] ?? false,
                'parent_id' => $validated['parent_id'] ?? null,
                'created_by' => $request->user()->id ?? null,
            ]);
            DB::commit();

            Log::info('Category created', [
                'id' => $category->id,
                'name' => $category->name,
                'created_by' => $request->user()->id ?? null
            ]);

            return response()->json([
                'message' => 'Category created successfully.',
                'data' => $category
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Category creation failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'message' => 'Failed to create category.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);

        return response()->json([
            'data' => $category
        ]);
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate($this->categoryValidationRules($category->id));

        DB::beginTransaction();
        try {
            $category->update([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'sub_taxonomy' => $validated['sub_taxonomy'] ?? false,
                'parent_id' => $validated['parent_id'] ?? null,
                'updated_by' => $request->user()->id ?? null,
            ]);
            DB::commit();

            Log::info('Category updated', [
                'id' => $category->id,
                'name' => $category->name,
                'updated_by' => $request->user()->id ?? null
            ]);

            return response()->json([
                'message' => 'Category updated successfully.',
                'data' => $category
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Category update failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'message' => 'Failed to update category.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        // Optional: check for children or associations before deleting

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully.'
        ]);
    }
}