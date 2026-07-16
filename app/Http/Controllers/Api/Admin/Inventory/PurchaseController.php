<?php

namespace App\Http\Controllers\Api\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryPurchase;
use App\Models\InventoryPurchaseItem;
use App\Models\InventorySupplier;
use App\Models\InventoryStore;
use App\Models\InventoryProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PurchaseController extends Controller
{
    /**
     * GET /api/admin/inventory/purchases/form-data
     * Suppliers, Stores, Products dropdown + notun bill_no suggestion.
     * IMPORTANT: ei route apiResource-er UPORE thakte hobe routes/api.php te.
     */
    public function formData(): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        return response()->json([
            'message' => 'Form data fetched successfully',
            'suppliers' => InventorySupplier::where('institution_id', $institutionId)
                ->orderBy('name')->get(['id', 'name']),
            'stores' => InventoryStore::where('institution_id', $institutionId)
                ->orderBy('name')->get(['id', 'name']),
            'products' => InventoryProduct::where('institution_id', $institutionId)
                ->orderBy('name')->get(['id', 'name', 'purchase_price']),
            'next_bill_no' => $this->generateBillNo($institutionId),
        ]);
    }

    /**
     * GET /api/admin/inventory/purchases
     */
    public function index(Request $request): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $perPage = (int) $request->query('per_page', 10);
        $search  = trim((string) $request->query('search', ''));

        $purchases = InventoryPurchase::query()
            ->with(['supplier:id,name', 'store:id,name'])
            ->where('institution_id', $institutionId)
            ->when($search !== '', function ($q) use ($search) {
                $q->where('bill_no', 'like', "%{$search}%")
                  ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$search}%"));
            })
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json([
            'message' => 'Purchases fetched successfully',
            'data'    => $purchases->items(),
            'meta'    => [
                'current_page' => $purchases->currentPage(),
                'last_page'    => $purchases->lastPage(),
                'per_page'     => $purchases->perPage(),
                'total'        => $purchases->total(),
            ],
        ]);
    }

    /**
     * GET /api/admin/inventory/purchases/{id}
     */
    public function show(int $id): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $purchase = InventoryPurchase::with(['supplier:id,name', 'store:id,name', 'items.product:id,name'])
            ->where('institution_id', $institutionId)
            ->find($id);

        if (! $purchase) {
            return response()->json(['message' => 'Purchase not found'], 404);
        }

        return response()->json([
            'message' => 'Purchase fetched successfully',
            'data'    => $purchase,
        ]);
    }

    /**
     * POST /api/admin/inventory/purchases
     */
    public function store(Request $request): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $validator = $this->validatePurchase($request, $institutionId);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $items     = $validated['items'];
        unset($validated['items']);

        $purchase = DB::transaction(function () use ($validated, $items, $institutionId) {
            $netTotal = collect($items)->sum(fn ($i) => $this->rowTotal($i));

            $purchase = InventoryPurchase::create([
                ...$validated,
                'institution_id' => $institutionId,
                'net_total'      => $netTotal,
            ]);

            foreach ($items as $item) {
                InventoryPurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id'  => $item['product_id'],
                    'unit_price'  => $item['unit_price'],
                    'quantity'    => $item['quantity'],
                    'discount'    => $item['discount'] ?? 0,
                    'total_price' => $this->rowTotal($item),
                ]);
            }

            return $purchase;
        });

        return response()->json([
            'message' => 'Purchase created successfully',
            'data'    => $purchase->load(['supplier:id,name', 'store:id,name', 'items.product:id,name']),
        ], 201);
    }

    /**
     * PUT/PATCH /api/admin/inventory/purchases/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $purchase = InventoryPurchase::where('institution_id', $institutionId)->find($id);

        if (! $purchase) {
            return response()->json(['message' => 'Purchase not found'], 404);
        }

        $validator = $this->validatePurchase($request, $institutionId, $purchase->id);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $items     = $validated['items'];
        unset($validated['items']);

        DB::transaction(function () use ($purchase, $validated, $items) {
            $netTotal = collect($items)->sum(fn ($i) => $this->rowTotal($i));

            $purchase->update([
                ...$validated,
                'net_total' => $netTotal,
            ]);

            $keptIds = collect($items)->pluck('id')->filter()->values()->toArray();

            InventoryPurchaseItem::where('purchase_id', $purchase->id)
                ->whereNotIn('id', $keptIds)
                ->delete();

            foreach ($items as $item) {
                $data = [
                    'product_id'  => $item['product_id'],
                    'unit_price'  => $item['unit_price'],
                    'quantity'    => $item['quantity'],
                    'discount'    => $item['discount'] ?? 0,
                    'total_price' => $this->rowTotal($item),
                ];

                if (! empty($item['id'])) {
                    InventoryPurchaseItem::where('id', $item['id'])
                        ->where('purchase_id', $purchase->id)
                        ->update($data);
                } else {
                    InventoryPurchaseItem::create([...$data, 'purchase_id' => $purchase->id]);
                }
            }
        });

        return response()->json([
            'message' => 'Purchase updated successfully',
            'data'    => $purchase->fresh(['supplier:id,name', 'store:id,name', 'items.product:id,name']),
        ]);
    }

    /**
     * DELETE /api/admin/inventory/purchases/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $purchase = InventoryPurchase::where('institution_id', $institutionId)->find($id);

        if (! $purchase) {
            return response()->json(['message' => 'Purchase not found'], 404);
        }

        $purchase->delete(); // items cascade via FK / model event dhore newa hoyeche

        return response()->json(['message' => 'Purchase deleted successfully']);
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private function rowTotal(array $item): float
    {
        $price    = (float) ($item['unit_price'] ?? 0);
        $qty      = (int)   ($item['quantity']   ?? 0);
        $discount = (float) ($item['discount']   ?? 0);

        return max(0, ($price * $qty) - $discount);
    }

    private function generateBillNo(int $institutionId): string
    {
        $last = InventoryPurchase::where('institution_id', $institutionId)
            ->latest('id')
            ->value('bill_no');

        $next = $last ? ((int) preg_replace('/\D/', '', $last)) + 1 : 1;

        return 'BILL-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function validatePurchase(Request $request, int $institutionId, ?int $ignoreId = null)
    {
        return Validator::make($request->all(), [
            'supplier_id' => [
                'required', 'integer',
                Rule::exists('inventory_suppliers', 'id')->where('institution_id', $institutionId),
            ],
            'store_id' => [
                'required', 'integer',
                Rule::exists('inventory_stores', 'id')->where('institution_id', $institutionId),
            ],
            'bill_no' => [
                'required', 'string', 'max:255',
                Rule::unique('inventory_purchases', 'bill_no')
                    ->where('institution_id', $institutionId)
                    ->ignore($ignoreId),
            ],
            'purchase_status' => ['required', Rule::in(['pending', 'ordered', 'completed', 'received', 'cancelled'])],
            'date'        => ['required', 'date'],
            'remarks'     => ['nullable', 'string', 'max:1000'],
            'items'       => ['required', 'array', 'min:1'],
            'items.*.id'          => ['nullable', 'integer'],
            'items.*.product_id'  => [
                'required', 'integer',
                Rule::exists('inventory_products', 'id')->where('institution_id', $institutionId),
            ],
            'items.*.unit_price'  => ['required', 'numeric', 'min:0'],
            'items.*.quantity'    => ['required', 'integer', 'min:1'],
            'items.*.discount'    => ['nullable', 'numeric', 'min:0'],
        ], [
            'items.required'              => 'At least one purchase item is required.',
            'items.min'                   => 'At least one purchase item is required.',
            'items.*.product_id.required' => 'Product is required.',
            'items.*.unit_price.required' => 'Unit price is required.',
            'items.*.quantity.required'   => 'Quantity is required.',
            'items.*.quantity.min'        => 'Quantity must be at least 1.',
        ]);
    }
}