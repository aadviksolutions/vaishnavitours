<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleCategory;
use App\Models\VehicleModel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminVehicleCategoryController extends Controller
{
    public function index()
    {
        $categories = VehicleCategory::with(['models', 'vehicles'])->orderBy('sort_order')->get();

        $stats = [
            'total_categories' => $categories->count(),
            'active_categories' => $categories->where('is_active', true)->count(),
            'total_models' => VehicleModel::count(),
            'active_models' => VehicleModel::where('is_active', true)->count(),
        ];

        return view('admin.vehicle-categories.index', compact('categories', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:50|unique:vehicle_categories,code',
            'display_name' => 'nullable|string|max:150',
            'description' => 'nullable|string',
            'default_seating_capacity' => 'required|integer|min:1|max:70',
            'min_seating_capacity' => 'nullable|integer|min:1|max:70',
            'max_seating_capacity' => 'nullable|integer|min:1|max:70',
            'is_capacity_configurable' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if (empty($data['code'])) {
            $data['code'] = Str::slug($data['name'], '_');
        }

        $data['is_capacity_configurable'] = $request->boolean('is_capacity_configurable');
        $data['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        VehicleCategory::create($data);

        return redirect()->route('admin.vehicle-categories.index')
            ->with('success', "Vehicle category '{$data['name']}' created successfully!");
    }

    public function update(Request $request, VehicleCategory $vehicle_category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:vehicle_categories,code,'.$vehicle_category->id,
            'display_name' => 'nullable|string|max:150',
            'description' => 'nullable|string',
            'default_seating_capacity' => 'required|integer|min:1|max:70',
            'min_seating_capacity' => 'nullable|integer|min:1|max:70',
            'max_seating_capacity' => 'nullable|integer|min:1|max:70',
            'is_capacity_configurable' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $data['is_capacity_configurable'] = $request->boolean('is_capacity_configurable');
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        $vehicle_category->update($data);

        return redirect()->route('admin.vehicle-categories.index')
            ->with('success', "Vehicle category '{$vehicle_category->name}' updated successfully!");
    }

    public function toggleStatus(VehicleCategory $vehicle_category)
    {
        $vehicle_category->is_active = ! $vehicle_category->is_active;
        $vehicle_category->save();

        $statusStr = $vehicle_category->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.vehicle-categories.index')
            ->with('success', "Vehicle category '{$vehicle_category->name}' {$statusStr} successfully!");
    }

    public function destroy(VehicleCategory $vehicle_category)
    {
        if ($vehicle_category->vehicles()->exists()) {
            return back()->with('error', "Cannot delete category '{$vehicle_category->name}' because {$vehicle_category->vehicles()->count()} vehicles are currently assigned to it. Deactivate it instead.");
        }

        $vehicle_category->models()->delete();
        $vehicle_category->delete();

        return redirect()->route('admin.vehicle-categories.index')
            ->with('success', "Vehicle category '{$vehicle_category->name}' deleted successfully.");
    }

    public function storeModel(Request $request, VehicleCategory $vehicle_category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'brand' => 'nullable|string|max:60',
            'default_seating_capacity' => 'nullable|integer|min:1|max:70',
            'sort_order' => 'nullable|integer',
        ]);

        $data['vehicle_category_id'] = $vehicle_category->id;
        $data['is_active'] = true;
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        VehicleModel::create($data);

        return redirect()->route('admin.vehicle-categories.index')
            ->with('success', "Model '{$data['name']}' added to {$vehicle_category->name} successfully!");
    }

    public function updateModel(Request $request, VehicleCategory $vehicle_category, VehicleModel $vehicle_model)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'brand' => 'nullable|string|max:60',
            'default_seating_capacity' => 'nullable|integer|min:1|max:70',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : $vehicle_model->is_active;
        $data['sort_order'] = (int) ($data['sort_order'] ?? $vehicle_model->sort_order);

        $vehicle_model->update($data);

        return redirect()->route('admin.vehicle-categories.index')
            ->with('success', "Model '{$vehicle_model->name}' updated successfully!");
    }

    public function toggleModelStatus(VehicleCategory $vehicle_category, VehicleModel $vehicle_model)
    {
        $vehicle_model->is_active = ! $vehicle_model->is_active;
        $vehicle_model->save();

        $statusStr = $vehicle_model->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.vehicle-categories.index')
            ->with('success', "Model '{$vehicle_model->name}' {$statusStr} successfully!");
    }

    public function destroyModel(VehicleCategory $vehicle_category, VehicleModel $vehicle_model)
    {
        if ($vehicle_model->vehicles()->exists()) {
            return back()->with('error', "Cannot delete model '{$vehicle_model->name}' because vehicles are currently using it. Deactivate it instead.");
        }

        $name = $vehicle_model->name;
        $vehicle_model->delete();

        return redirect()->route('admin.vehicle-categories.index')
            ->with('success', "Vehicle model '{$name}' deleted successfully.");
    }
}
