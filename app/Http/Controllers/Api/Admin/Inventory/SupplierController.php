<?php

namespace App\Http\Controllers\Api\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventorySupplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
{
    /**
     * GET /api/admin/inventory/suppliers
     * List + search + pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $perPage = (int) $request->query('per_page', 10);
        $search  = trim((string) $request->query('search', ''));

        $suppliers = InventorySupplier::query()
            ->where('institution_id', $institutionId)
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                       ->orWhere('mobile', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage);

        return response()->json([
            'message' => 'Suppliers fetched successfully',
            'data'    => $suppliers->items(),
            'meta'    => [
                'current_page' => $suppliers->currentPage(),
                'last_page'    => $suppliers->lastPage(),
                'per_page'     => $suppliers->perPage(),
                'total'        => $suppliers->total(),
            ],
        ]);
    }

    /**
     * POST /api/admin/inventory/suppliers
     * Create notun supplier.
     */
    public function store(Request $request): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $validator = Validator::make($request->all(), [
            'name'    => ['required', 'string', 'max:255'],
            'mobile'  => ['nullable', 'string', 'max:20'],
            'email'   => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['institution_id'] = $institutionId;

        $supplier = InventorySupplier::create($data);

        return response()->json([
            'message' => 'Supplier created successfully',
            'data'    => $supplier,
        ], 201);
    }

    /**
     * GET /api/admin/inventory/suppliers/{id}
     */
    public function show(int $id): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $supplier = InventorySupplier::query()
            ->where('institution_id', $institutionId)
            ->find($id);

        if (! $supplier) {
            return response()->json([
                'message' => 'Supplier not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Supplier fetched successfully',
            'data'    => $supplier,
        ]);
    }

    /**
     * PUT/PATCH /api/admin/inventory/suppliers/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $supplier = InventorySupplier::query()
            ->where('institution_id', $institutionId)
            ->find($id);

        if (! $supplier) {
            return response()->json([
                'message' => 'Supplier not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'    => ['required', 'string', 'max:255'],
            'mobile'  => ['nullable', 'string', 'max:20'],
            'email'   => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $supplier->update($validator->validated());

        return response()->json([
            'message' => 'Supplier updated successfully',
            'data'    => $supplier->fresh(),
        ]);
    }

    /**
     * DELETE /api/admin/inventory/suppliers/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $supplier = InventorySupplier::query()
            ->where('institution_id', $institutionId)
            ->find($id);

        if (! $supplier) {
            return response()->json([
                'message' => 'Supplier not found',
            ], 404);
        }

        $supplier->delete();

        return response()->json([
            'message' => 'Supplier deleted successfully',
        ]);
    }
}