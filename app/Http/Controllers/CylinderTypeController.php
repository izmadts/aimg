<?php

namespace App\Http\Controllers;

use App\Models\Cylinder;
use App\Models\CylinderType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CylinderTypeController extends Controller
{
    public function index()
    {
        $cylinderTypes = CylinderType::orderBy('name')->get()->map(function ($type) {
            $type->usage_count = Cylinder::where('type', $type->name)->count();
            return $type;
        });

        return view('cylinder-types.index', compact('cylinderTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:cylinder_types,name',
            'capacity' => 'required|numeric|min:0.01',
            'price_premium' => 'nullable|numeric|min:0',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);

        CylinderType::create($validated);

        return redirect()->route('cylinder-types.index')->with('success', "Cylinder type '{$validated['name']}' created successfully!");
    }

    public function update(Request $request, CylinderType $cylinderType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:cylinder_types,name,' . $cylinderType->id,
            'capacity' => 'required|numeric|min:0.01',
            'price_premium' => 'nullable|numeric|min:0',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);

        $oldName = $cylinderType->name;

        DB::transaction(function () use ($cylinderType, $validated, $oldName) {
            $cylinderType->update($validated);

            if ($oldName !== $validated['name']) {
                Cylinder::where('type', $oldName)->update(['type' => $validated['name']]);
            }
        });

        return redirect()->route('cylinder-types.index')->with('success', 'Cylinder type updated successfully!');
    }

    public function destroy(CylinderType $cylinderType)
    {
        if (Cylinder::where('type', $cylinderType->name)->exists()) {
            return redirect()->route('cylinder-types.index')
                ->with('error', "Cannot delete '{$cylinderType->name}' — it is used by one or more cylinders.");
        }

        $cylinderType->delete();

        return redirect()->route('cylinder-types.index')->with('success', 'Cylinder type deleted successfully!');
    }
}
