<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryProduct;
use App\Models\InventoryCategory;
use App\Models\InventoryUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    // GET /api/inventory/products
    public function index(Request $request)
    {
        $products = InventoryProduct::query()
            ->with(['category', 'purchaseUnit', 'salesUnit'])
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")
                                                  ->orWhere('code', 'like', "%{$request->search}%"))
            ->orderBy('name')
            ->paginate($request->per_page ?? 15);

        return response()->json($products);
    }

    // GET /api/inventory/products/form-data
    // Dropdown-এর জন্য category ও unit লিস্ট একসাথে পাঠায়
    public function formData()
    {
        return response()->json([
            'categories' => InventoryCategory::orderBy('name')->get(['id', 'name']),
            'units'      => InventoryUnit::orderBy('name')->get(['id', 'name']),
        ]);
    }

    // POST /api/inventory/products
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'code'             => 'nullable|string|max:255|unique:inventory_products,code',
            'category_id'      => 'required|exists:inventory_categories,id',
            'purchase_unit_id' => 'required|exists:inventory_units,id',
            'sales_unit_id'    => 'required|exists:inventory_units,id',
            'unit_ratio'       => 'required|numeric|min:0',
            'purchase_price'   => 'required|numeric|min:0',
            'sales_price'      => 'required|numeric|min:0',
            'remarks'          => 'nullable|string',
        ]);

        $product = InventoryProduct::create($validated);

        if (empty($validated['code'])) {
            $product->update(['code' => Str::slug($validated['name']) . '-' . $product->id]);
        }

        return response()->json([
            'message' => 'Product created successfully.',
            'data'    => $product->load(['category', 'purchaseUnit', 'salesUnit']),
        ], 201);
    }

    // GET /api/inventory/products/{id}
    public function show(int $id)
    {
        $product = InventoryProduct::with(['category', 'purchaseUnit', 'salesUnit'])->findOrFail($id);

        return response()->json(['data' => $product]);
    }

    // PUT /api/inventory/products/{id}
    public function update(Request $request, int $id)
    {
        $product = InventoryProduct::findOrFail($id);

        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'code'             => 'nullable|string|max:255|unique:inventory_products,code,' . $id,
            'category_id'      => 'required|exists:inventory_categories,id',
            'purchase_unit_id' => 'required|exists:inventory_units,id',
            'sales_unit_id'    => 'required|exists:inventory_units,id',
            'unit_ratio'       => 'required|numeric|min:0',
            'purchase_price'   => 'required|numeric|min:0',
            'sales_price'      => 'required|numeric|min:0',
            'remarks'          => 'nullable|string',
        ]);

        if (empty($validated['code'])) {
            $validated['code'] = Str::slug($validated['name']) . '-' . $product->id;
        }

        $product->update($validated);

        return response()->json([
            'message' => 'Product updated successfully.',
            'data'    => $product->load(['category', 'purchaseUnit', 'salesUnit']),
        ]);
    }

    // DELETE /api/inventory/products/{id}
    public function destroy(int $id)
    {
        $product = InventoryProduct::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully.']);
    }
}