<?php

namespace App\Http\Controllers;

use App\Models\GasProduct;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::orderBy('name')->get()->map(function ($unit) {
            $unit->usage_count = GasProduct::where('uom', $unit->name)->count();
            return $unit;
        });

        return view('units.index', compact('units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:units,name',
            'description' => 'nullable|string|max:255',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);

        Unit::create($validated);

        return redirect()->route('units.index')->with('success', "Unit '{$validated['name']}' created successfully!");
    }

    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:units,name,' . $unit->id,
            'description' => 'nullable|string|max:255',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);

        $oldName = $unit->name;

        DB::transaction(function () use ($unit, $validated, $oldName) {
            $unit->update($validated);

            if ($oldName !== $validated['name']) {
                GasProduct::where('uom', $oldName)->update(['uom' => $validated['name']]);
            }
        });

        return redirect()->route('units.index')->with('success', 'Unit updated successfully!');
    }

    public function destroy(Unit $unit)
    {
        if (GasProduct::where('uom', $unit->name)->exists()) {
            return redirect()->route('units.index')
                ->with('error', "Cannot delete '{$unit->name}' — it is used by one or more gas products.");
        }

        $unit->delete();

        return redirect()->route('units.index')->with('success', 'Unit deleted successfully!');
    }
}
