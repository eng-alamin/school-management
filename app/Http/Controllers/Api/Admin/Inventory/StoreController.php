<?php

namespace App\Http\Controllers\Api\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $perPage = (int) $request->query('per_page', 10);
        $search  = trim((string) $request->query('search', ''));

        $stores = InventoryStore::query()
            ->where('institution_id', $institutionId)
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                       ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage);

        return response()->json([
            'message' => 'Stores fetched successfully',
            'data'    => $stores->items(),
            'meta'    => [
                'current_page' => $stores->currentPage(),
                'last_page'    => $stores->lastPage(),
                'per_page'     => $stores->perPage(),
                'total'        => $stores->total(),
            ],
        ]);
    }

    /**
     * POST /api/admin/inventory/stores
     * Create notun store.
     */
    public function store(Request $request): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $validator = Validator::make($request->all(), [
            'name'        => ['required', 'string', 'max:255'],
            'code'        => [
                'nullable', 'string', 'max:255',
                Rule::unique('inventory_stores', 'code')
                    ->where('institution_id', $institutionId),
            ],
            'mobile'      => ['nullable', 'string', 'max:20'],
            'address'     => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['institution_id'] = $institutionId;

        $store = InventoryStore::create($data);

        // Code na dile auto-generate kora hocche (Livewire version-er moto)
        if (empty($store->code)) {
            $store->update([
                'code' => Str::slug($store->name) . '-' . $store->id,
            ]);
        }

        return response()->json([
            'message' => 'Store created successfully',
            'data'    => $store->fresh(),
        ], 201);
    }

    /**
     * GET /api/admin/inventory/stores/{id}
     */
    public function show(int $id): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $store = InventoryStore::query()
            ->where('institution_id', $institutionId)
            ->find($id);

        if (! $store) {
            return response()->json([
                'message' => 'Store not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Store fetched successfully',
            'data'    => $store,
        ]);
    }

    /**
     * PUT/PATCH /api/admin/inventory/stores/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $store = InventoryStore::query()
            ->where('institution_id', $institutionId)
            ->find($id);

        if (! $store) {
            return response()->json([
                'message' => 'Store not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'        => ['required', 'string', 'max:255'],
            'code'        => [
                'nullable', 'string', 'max:255',
                Rule::unique('inventory_stores', 'code')
                    ->where('institution_id', $institutionId)
                    ->ignore($store->id),
            ],
            'mobile'      => ['nullable', 'string', 'max:20'],
            'address'     => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        if (empty($data['code'])) {
            $data['code'] = Str::slug($data['name']) . '-' . $store->id;
        }

        $store->update($data);

        return response()->json([
            'message' => 'Store updated successfully',
            'data'    => $store->fresh(),
        ]);
    }

    /**
     * DELETE /api/admin/inventory/stores/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $store = InventoryStore::query()
            ->where('institution_id', $institutionId)
            ->find($id);

        if (! $store) {
            return response()->json([
                'message' => 'Store not found',
            ], 404);
        }

        $store->delete();

        return response()->json([
            'message' => 'Store deleted successfully',
        ]);
    }
}