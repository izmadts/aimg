<?php

namespace App\Http\Controllers;

use App\Models\GasProduct;
use App\Models\Cylinder;
use App\Models\Unit;
use App\Http\Requests\StoreGasProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GasProductController extends Controller
{
    /**
     * Display a listing of gas products.
     */
    public function index(Request $request)
    {
        $query = GasProduct::with(['cylinders' => function ($q) {
            $q->orderBy('capacity');
        }]);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status == 'active');
        }

        // Sort
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $products = $query->paginate(15)->withQueryString();

        // Get statistics
        $stats = [
            'total' => GasProduct::count(),
            'active' => GasProduct::where('is_active', true)->count(),
            'inactive' => GasProduct::where('is_active', false)->count(),
            'total_stock_value' => GasProduct::where('is_active', true)->get()->sum(function ($p) {
                return $p->current_stock * $p->purchase_price;
            }),
            'total_cylinders' => Cylinder::count(),
        ];

        return view('gas-products.index', compact('products', 'stats'));
    }

    /**
     * Show the form for creating a new gas product.
     */
    public function create()
    {
        $units = Unit::active()->orderBy('name')->get();

        return view('gas-products.create', compact('units'));
    }

    /**
     * Store a newly created gas product in storage.
     */
    public function store(StoreGasProductRequest $request)
    {
        $validated = $request->validated();

        $validated['is_active'] = $request->has('is_active');
        $validated['current_stock'] = $validated['current_stock'] ?? 0;
        $validated['minimum_stock_level'] = $validated['minimum_stock_level'] ?? 0;

        $product = GasProduct::create($validated);

        return redirect()->route('gas-products.index')
            ->with('success', "Gas product '{$product->name}' created successfully!");
    }

    /**
     * Display the specified gas product.
     */
    public function show(GasProduct $gasProduct)
    {
        $gasProduct->load(['cylinders' => function ($q) {
            $q->limit(20);
        }]);

        // Get statistics
        $stats = [
            'total_cylinders' => $gasProduct->cylinders()->sum('stock_quantity'),
            'issued_cylinders' => $gasProduct->cylinders()->sum('issued_quantity'),
            'in_house_cylinders' => $gasProduct->cylinders()->whereIn('status', ['in_house', 'partial_issued'])->count(),
            'stock_value' => $gasProduct->current_stock * $gasProduct->purchase_price,
        ];

        return view('gas-products.show', compact('gasProduct', 'stats'));
    }

    /**
     * Show the form for editing the specified gas product.
     */
    public function edit(GasProduct $gasProduct)
    {
        $units = Unit::active()->orderBy('name')->get();

        // Keep the product's current unit selectable even if it was since deactivated/renamed away.
        if ($gasProduct->uom && ! $units->contains('name', $gasProduct->uom)) {
            $units->push(new Unit(['name' => $gasProduct->uom]));
        }

        return view('gas-products.edit', compact('gasProduct', 'units'));
    }

    /**
     * Update the specified gas product in storage.
     */
    public function update(StoreGasProductRequest $request, GasProduct $gasProduct)
    {
        $validated = $request->validated();

        $validated['is_active'] = $request->has('is_active');
        $validated['current_stock'] = $validated['current_stock'] ?? 0;
        $validated['minimum_stock_level'] = $validated['minimum_stock_level'] ?? 0;

        $gasProduct->update($validated);

        return redirect()->route('gas-products.index')
            ->with('success', "Gas product '{$gasProduct->name}' updated successfully!");
    }

    /**
     * Remove the specified gas product from storage.
     */
    public function destroy(GasProduct $gasProduct)
    {
        // Check if product has any cylinders
        if ($gasProduct->cylinders()->exists()) {
            return redirect()->route('gas-products.index')
                ->with('error', "Cannot delete '{$gasProduct->name}' because it has cylinders.");
        }

        $gasProduct->delete();

        return redirect()->route('gas-products.index')
            ->with('success', "Gas product '{$gasProduct->name}' deleted successfully!");
    }

    /**
     * Toggle product status.
     */
    public function toggleStatus(GasProduct $gasProduct)
    {
        $gasProduct->is_active = !$gasProduct->is_active;
        $gasProduct->save();

        $status = $gasProduct->is_active ? 'activated' : 'deactivated';

        return redirect()->route('gas-products.index')
            ->with('success', "Gas product '{$gasProduct->name}' {$status} successfully!");
    }

    /**
     * Export gas products to CSV.
     */
    public function export()
    {
        $products = GasProduct::where('is_active', true)->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="gas_products_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($products) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Code', 'UOM', 'Purchase Price', 'Sale Price', 'Current Stock', 'Min Stock', 'Status']);

            foreach ($products as $product) {
                fputcsv($file, [
                    $product->id,
                    $product->name,
                    $product->code,
                    $product->uom,
                    $product->purchase_price,
                    $product->sale_price,
                    $product->current_stock,
                    $product->minimum_stock_level,
                    $product->is_active ? 'Active' : 'Inactive'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Update stock via AJAX.
     */
    public function updateStock(Request $request, GasProduct $gasProduct)
    {
        $request->validate([
            'quantity' => 'required|numeric',
            'type' => 'required|in:add,subtract'
        ]);

        if ($request->type === 'add') {
            $gasProduct->increment('current_stock', $request->quantity);
        } else {
            if ($gasProduct->current_stock < $request->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock!'
                ], 400);
            }
            $gasProduct->decrement('current_stock', $request->quantity);
        }

        return response()->json([
            'success' => true,
            'message' => 'Stock updated successfully!',
            'current_stock' => $gasProduct->current_stock
        ]);
    }
}