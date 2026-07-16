<?php

namespace App\Http\Controllers\Api\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventorySale;
use App\Models\InventorySaleItem;
use App\Models\InventoryCategory;
use App\Models\InventoryProduct;
use App\Models\AcademicClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SaleController extends Controller
{
    /**
     * GET /api/admin/inventory/sales/form-data
     * Categories (with products), classes, role/class-filtered saleables +
     * notun bill_no suggestion.
     * IMPORTANT: ei route apiResource-er UPORE thakte hobe routes/api.php te.
     */
    public function formData(Request $request): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;
        $role          = (string) $request->query('role', '');
        $classId       = $request->query('class_id');

        return response()->json([
            'message'      => 'Form data fetched successfully',
            'categories'   => $this->categoriesWithProducts($institutionId),
            'classes'      => AcademicClass::where('institution_id', $institutionId)
                ->orderBy('name')->get(['id', 'name']),
            'saleables'    => $this->saleablesFor($institutionId, $role, $classId),
            'next_bill_no' => $this->generateBillNo($institutionId),
        ]);
    }

    /**
     * GET /api/admin/inventory/sales
     */
    public function index(Request $request): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $perPage = (int) $request->query('per_page', 10);
        $search  = trim((string) $request->query('search', ''));

        $sales = InventorySale::query()
            ->with(['saleable' => function ($q) {
                // NOTE: closure-based eager load used instead of the
                // 'saleable:id,name' shorthand. The shorthand column-select
                // syntax is unreliable on morphTo relations in Laravel —
                // it can silently return null / wrong rows because Laravel
                // does not automatically merge the correct key columns for
                // morphTo the way it does for belongsTo/hasMany. Using an
                // explicit closure avoids that.
                $q->select('id', 'name');
            }])
            ->where('institution_id', $institutionId)
            ->when($search !== '', function ($q) use ($search) {
                $q->where('bill_no', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%");
            })
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json([
            'message' => 'Sales fetched successfully',
            'data'    => $sales->items(),
            'meta'    => [
                'current_page' => $sales->currentPage(),
                'last_page'    => $sales->lastPage(),
                'per_page'     => $sales->perPage(),
                'total'        => $sales->total(),
            ],
        ]);
    }

    /**
     * GET /api/admin/inventory/sales/{id}
     */
    public function show(int $id): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $sale = InventorySale::with([
            'saleable' => function ($q) {
                $q->select('id', 'name');
            },
            'items.product' => function ($q) {
                $q->select('id', 'name');
            },
            'items.category' => function ($q) {
                $q->select('id', 'name');
            },
        ])
            ->where('institution_id', $institutionId)
            ->find($id);

        if (! $sale) {
            return response()->json(['message' => 'Sale not found'], 404);
        }

        // Student role-er khetre edit form-e Class dropdown pre-select
        // korte class_id lagbe, tai shathe pathiye deya hocche.
        $classId = null;
        if ($sale->role === 'student') {
            $classId = Student::where('institution_id', $institutionId)
                ->where('id', $sale->saleable_id)
                ->value('class_id');
        }

        $data = $sale->toArray();
        $data['class_id'] = $classId;

        return response()->json([
            'message' => 'Sale fetched successfully',
            'data'    => $data,
        ]);
    }

    /**
     * POST /api/admin/inventory/sales
     */
    public function store(Request $request): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $validator = $this->validateSale($request, $institutionId);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $items     = $validated['items'];
        unset($validated['items'], $validated['class_id']);

        $sale = DB::transaction(function () use ($validated, $items, $institutionId) {
            $subTotal      = collect($items)->sum(fn ($i) => (float) ($i['unit_price'] ?? 0) * (int) ($i['quantity'] ?? 1));
            $totalDiscount = collect($items)->sum(fn ($i) => (float) ($i['discount'] ?? 0));
            $netPayable    = max(0, $subTotal - $totalDiscount);
            $received      = (float) ($validated['received_amount'] ?? 0);

            $sale = InventorySale::create([
                ...$validated,
                'institution_id'  => $institutionId,
                // NOTE: saleable_type always resolves to User::class, even
                // for role = 'student' (where saleable_id actually points to
                // the students table). Preserved from the original Livewire
                // components as-is to avoid breaking existing data.
                'saleable_type'   => User::class,
                'sub_total'       => $subTotal,
                'discount'        => $totalDiscount,
                'net_payable'     => $netPayable,
                'received_amount' => $received,
                'due_amount'      => max(0, $netPayable - $received),
                'payment_status'  => $this->paymentStatus($received, $netPayable),
            ]);

            foreach ($items as $item) {
                InventorySaleItem::create([
                    'sale_id'     => $sale->id,
                    'category_id' => $item['category_id'] ?? null,
                    'product_id'  => $item['product_id'],
                    'unit_price'  => $item['unit_price'],
                    'quantity'    => $item['quantity'],
                    'discount'    => $item['discount'] ?? 0,
                    'total_price' => $this->rowTotal($item),
                ]);
            }

            return $sale;
        });

        return response()->json([
            'message' => 'Sale created successfully',
            'data'    => $sale->load([
                'saleable' => function ($q) { $q->select('id', 'name'); },
                'items.product' => function ($q) { $q->select('id', 'name'); },
                'items.category' => function ($q) { $q->select('id', 'name'); },
            ]),
        ], 201);
    }

    /**
     * PUT/PATCH /api/admin/inventory/sales/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $sale = InventorySale::where('institution_id', $institutionId)->find($id);

        if (! $sale) {
            return response()->json(['message' => 'Sale not found'], 404);
        }

        $validator = $this->validateSale($request, $institutionId, $sale->id);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $items     = $validated['items'];
        unset($validated['items'], $validated['class_id']);

        DB::transaction(function () use ($sale, $validated, $items) {
            $subTotal      = collect($items)->sum(fn ($i) => (float) ($i['unit_price'] ?? 0) * (int) ($i['quantity'] ?? 1));
            $totalDiscount = collect($items)->sum(fn ($i) => (float) ($i['discount'] ?? 0));
            $netPayable    = max(0, $subTotal - $totalDiscount);
            $received      = (float) ($validated['received_amount'] ?? 0);

            $sale->update([
                ...$validated,
                'saleable_type'   => User::class, // see note in store()
                'sub_total'       => $subTotal,
                'discount'        => $totalDiscount,
                'net_payable'     => $netPayable,
                'received_amount' => $received,
                'due_amount'      => max(0, $netPayable - $received),
                'payment_status'  => $this->paymentStatus($received, $netPayable),
            ]);

            $keptIds = collect($items)->pluck('id')->filter()->values()->toArray();

            InventorySaleItem::where('sale_id', $sale->id)
                ->whereNotIn('id', $keptIds)
                ->delete();

            foreach ($items as $item) {
                $data = [
                    'category_id' => $item['category_id'] ?? null,
                    'product_id'  => $item['product_id'],
                    'unit_price'  => $item['unit_price'],
                    'quantity'    => $item['quantity'],
                    'discount'    => $item['discount'] ?? 0,
                    'total_price' => $this->rowTotal($item),
                ];

                if (! empty($item['id'])) {
                    InventorySaleItem::where('id', $item['id'])
                        ->where('sale_id', $sale->id)
                        ->update($data);
                } else {
                    InventorySaleItem::create([...$data, 'sale_id' => $sale->id]);
                }
            }
        });

        return response()->json([
            'message' => 'Sale updated successfully',
            'data'    => $sale->fresh([
                'saleable' => function ($q) { $q->select('id', 'name'); },
                'items.product' => function ($q) { $q->select('id', 'name'); },
                'items.category' => function ($q) { $q->select('id', 'name'); },
            ]),
        ]);
    }

    /**
     * DELETE /api/admin/inventory/sales/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $sale = InventorySale::where('institution_id', $institutionId)->find($id);

        if (! $sale) {
            return response()->json(['message' => 'Sale not found'], 404);
        }

        $sale->delete(); // items cascade via FK / model event dhore newa hoyeche

        return response()->json(['message' => 'Sale deleted successfully']);
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    /**
     * BUG FIX: The previous version mutated the already-loaded `products`
     * relation via `$category->products = $category->products->map(...)`.
     * Eloquent's `Model::toArray()` runs
     *   array_merge($this->attributesToArray(), $this->relationsToArray())
     * — relationsToArray() is merged LAST, so it silently overwrote the
     * custom array back with the raw (unmapped) relation data. That means
     * the intended `price = sales_price ?? price` fallback NEVER actually
     * reached the JSON response, and the API could return inconsistent /
     * unexpected shapes for `products`, which is what broke parsing on the
     * Flutter side ("Data load korte parlam na").
     *
     * Fix: build a completely fresh, plain array structure instead of
     * mutating the relation property.
     */
    private function categoriesWithProducts(int $institutionId): array
    {
        // BUG FIX: `inventory_products` table only has a `sales_price`
        // column — there is NO `price` column. Selecting a non-existent
        // column threw:
        //   SQLSTATE[42S22]: Column not found: 1054 Unknown column 'price'
        // Fixed by only selecting columns that actually exist.
        $categories = InventoryCategory::where('institution_id', $institutionId)
            ->with(['products' => function ($q) {
                $q->select('id', 'category_id', 'name', 'sales_price');
            }])
            ->orderBy('name')
            ->get(['id', 'name']);

        return $categories->map(function ($category) {
            return [
                'id'       => $category->id,
                'name'     => $category->name,
                'products' => $category->products->map(function ($product) {
                    return [
                        'id'    => $product->id,
                        'name'  => $product->name,
                        'price' => $product->sales_price ?? 0,
                    ];
                })->values()->all(),
            ];
        })->values()->all();
    }

    private function saleablesFor(int $institutionId, string $role, $classId)
    {
        return match ($role) {
            'student' => Student::where('institution_id', $institutionId)
                ->when($classId, fn ($q) => $q->where('class_id', $classId))
                ->orderBy('name')
                ->get(['id', 'name']),
            'teacher' => User::where('institution_id', $institutionId)
                ->where('role', 'teacher')
                ->orderBy('name')
                ->get(['id', 'name']),
            'staff' => User::where('institution_id', $institutionId)
                ->where('role', 'staff')
                ->orderBy('name')
                ->get(['id', 'name']),
            default => collect(),
        };
    }

    private function rowTotal(array $item): float
    {
        $price    = (float) ($item['unit_price'] ?? 0);
        $qty      = (int)   ($item['quantity']   ?? 0);
        $discount = (float) ($item['discount']   ?? 0);

        return max(0, ($price * $qty) - $discount);
    }

    private function paymentStatus(float $received, float $netPayable): string
    {
        if ($received <= 0)           return 'due';
        if ($received >= $netPayable) return 'paid';
        return 'partial';
    }

    private function generateBillNo(int $institutionId): string
    {
        $last = InventorySale::where('institution_id', $institutionId)
            ->latest('id')
            ->value('bill_no');

        $next = $last ? ((int) preg_replace('/\D/', '', $last)) + 1 : 1;

        return 'BILL-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function validateSale(Request $request, int $institutionId, ?int $ignoreId = null)
    {
        return Validator::make($request->all(), [
            'role' => ['required', 'string', Rule::in(['student', 'teacher', 'staff', 'other'])],
            'class_id' => ['nullable', 'integer'],
            'saleable_id' => ['required', 'integer'],
            'bill_no' => [
                'required', 'string', 'max:255',
                Rule::unique('inventory_sales', 'bill_no')
                    ->where('institution_id', $institutionId)
                    ->ignore($ignoreId),
            ],
            'date'            => ['required', 'date'],
            'received_amount' => ['nullable', 'numeric', 'min:0'],
            'pay_via'         => ['nullable', 'string', 'max:100'],
            'remarks'         => ['nullable', 'string', 'max:1000'],
            'items'           => ['required', 'array', 'min:1'],
            'items.*.id'          => ['nullable', 'integer'],
            'items.*.category_id' => [
                'nullable', 'integer',
                Rule::exists('inventory_categories', 'id')->where('institution_id', $institutionId),
            ],
            'items.*.product_id' => [
                'required', 'integer',
                Rule::exists('inventory_products', 'id')->where('institution_id', $institutionId),
            ],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity'   => ['required', 'integer', 'min:1'],
            'items.*.discount'   => ['nullable', 'numeric', 'min:0'],
        ], [
            'role.required'                => 'Role is required.',
            'saleable_id.required'         => 'Please select a sale target.',
            'items.required'               => 'At least one item is required.',
            'items.min'                    => 'At least one item is required.',
            'items.*.product_id.required'  => 'Product is required.',
            'items.*.unit_price.required'  => 'Unit price is required.',
            'items.*.quantity.required'    => 'Quantity is required.',
            'items.*.quantity.min'         => 'Quantity must be at least 1.',
        ]);
    }
}