<?php

namespace App\Http\Controllers\Api\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryUnit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    // GET /api/inventory/units
    public function index(Request $request)
    {
        $units = InventoryUnit::query()
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('name')
            ->paginate($request->per_page ?? 15);

        return response()->json($units);
    }

    // POST /api/inventory/units
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $unit = InventoryUnit::create($validated);

        return response()->json([
            'message' => 'Unit created successfully.',
            'data'    => $unit,
        ], 201);
    }

    // GET /api/inventory/units/{id}
    public function show(int $id)
    {
        $unit = InventoryUnit::findOrFail($id);

        return response()->json(['data' => $unit]);
    }

    // PUT /api/inventory/units/{id}
    public function update(Request $request, int $id)
    {
        $unit = InventoryUnit::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $unit->update($validated);

        return response()->json([
            'message' => 'Unit updated successfully.',
            'data'    => $unit,
        ]);
    }

    // DELETE /api/inventory/units/{id}
    public function destroy(int $id)
    {
        $unit = InventoryUnit::findOrFail($id);
        $unit->delete();

        return response()->json(['message' => 'Unit deleted successfully.']);
    }
}