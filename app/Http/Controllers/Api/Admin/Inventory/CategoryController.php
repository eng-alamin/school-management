<?php

namespace App\Http\Controllers\Api\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // GET /api/inventory/categories
    public function index(Request $request)
    {
        $categories = InventoryCategory::query()
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('name')
            ->paginate($request->per_page ?? 15);

        return response()->json($categories);
    }

    // POST /api/inventory/categories
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = InventoryCategory::create($validated);

        return response()->json([
            'message' => 'Category created successfully.',
            'data'    => $category,
        ], 201);
    }

    // GET /api/inventory/categories/{id}
    public function show(int $id)
    {
        $category = InventoryCategory::findOrFail($id);

        return response()->json(['data' => $category]);
    }

    // PUT /api/inventory/categories/{id}
    public function update(Request $request, int $id)
    {
        $category = InventoryCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category->update($validated);

        return response()->json([
            'message' => 'Category updated successfully.',
            'data'    => $category,
        ]);
    }

    // DELETE /api/inventory/categories/{id}
    public function destroy(int $id)
    {
        $category = InventoryCategory::findOrFail($id);
        $category->delete();

        return response()->json(['message' => 'Category deleted successfully.']);
    }
}