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
        $this->authorize('viewAny', Category::class);
        return view('categories.index');
    }

    public function getCategories(Request $request)
    {
        $this->authorize('viewAny', Category::class);
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

        // Transform each category to include parent_name
        $data = collect($categories->items())->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'code' => $category->code,
                'description' => $category->description,
                'is_active' => $category->is_active,
                'sub_taxonomy' => $category->sub_taxonomy,
                'parent_id' => $category->parent_id,
                'parent_name' => $category->parent ? $category->parent->name : null,
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
            ];
        });

        return response()->json([
            'data' => $data,
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
        $this->authorize('create', Category::class);
        // Convert empty string or string 'null' to null for parent_id
        $input = $request->all();
        if (array_key_exists('parent_id', $input) && ($input['parent_id'] === '' || $input['parent_id'] === 'null')) {
            $input['parent_id'] = null;
        }
        $validated = validator($input, $this->categoryValidationRules())->validate();

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
                'data' => $category->load('parent')
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

    public function edit(Category $category)
    {
        $this->authorize('update', $category);
        $category = Category::with('parent')->findOrFail($category->id);

        return response()->json([
            'data' => $category
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $this->authorize('update', $category);
        $category = Category::findOrFail($category->id);

        // Convert empty string or string 'null' to null for parent_id
        $input = $request->all();
        if (array_key_exists('parent_id', $input) && ($input['parent_id'] === '' || $input['parent_id'] === 'null')) {
            $input['parent_id'] = null;
        }
        $validated = validator($input, $this->categoryValidationRules($category->id))->validate();

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
                'data' => $category->load('parent')
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

    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);
        $category = Category::findOrFail($category->id);

        // Optional: check for children or associations before deleting

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully.'
        ]);
    }
}