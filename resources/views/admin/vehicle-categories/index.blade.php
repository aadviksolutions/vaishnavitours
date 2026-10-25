@extends('layouts.admin')

@section('title', 'Vehicle Categories & Models - Vaishnavi Tours')
@section('page_title', 'Vehicle Categories & Fleet Models')

@section('content')
<div class="d-flex justify-between align-center flex-wrap gap-2 mb-3">
    <div>
        <h1 style="font-size: 19px; font-weight: 800; margin: 0; color: var(--dark-900); display: flex; align-items: center; gap: 8px;">
            <x-icon name="car" :size="20" style="color: var(--primary);" />
            Fleet Vehicle Categories & Models
        </h1>
        <p style="color: var(--slate-500); margin: 3px 0 0 0; font-size: 13px;">
            Configure commercial vehicle categories (Hatchback, Sedan, SUV/MUV, Traveller, Urbania, Bus) and manage customer model options.
        </p>
    </div>
    <div class="d-flex align-center gap-2">
        <button type="button" onclick="openAddCategoryModal()" class="btn btn-primary btn-sm" style="display: flex; align-items: center; gap: 5px;">
            <x-icon name="plus" size="14" /> Add Vehicle Category
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-3" style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; padding: 0.75rem 1rem; border-radius: 6px; font-size: 13px;">
        ✓ {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger mb-3" style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; padding: 0.75rem 1rem; border-radius: 6px; font-size: 13px;">
        ⚠️ {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger mb-3" style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; padding: 0.75rem 1rem; border-radius: 6px; font-size: 13px;">
        <ul style="margin: 0; padding-left: 1.25rem;">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Stats Row -->
<div class="grid grid-4 gap-3 mb-4">
    <div class="card" style="padding: 1rem; background: #fff; border-left: 4px solid var(--primary); border-radius: 8px;">
        <div style="font-size: 11px; text-transform: uppercase; color: var(--slate-500); font-weight: 700;">Vehicle Categories</div>
        <div style="font-size: 1.6rem; font-weight: 800; color: var(--dark-900); margin-top: 2px;">{{ $stats['total_categories'] }}</div>
        <div style="font-size: 11.5px; color: var(--slate-500);">{{ $stats['active_categories'] }} Active for Booking</div>
    </div>
    <div class="card" style="padding: 1rem; background: #fff; border-left: 4px solid #10B981; border-radius: 8px;">
        <div style="font-size: 11px; text-transform: uppercase; color: var(--slate-500); font-weight: 700;">Vehicle Models</div>
        <div style="font-size: 1.6rem; font-weight: 800; color: var(--dark-900); margin-top: 2px;">{{ $stats['total_models'] }}</div>
        <div style="font-size: 11.5px; color: #059669;">{{ $stats['active_models'] }} Customer Selectable</div>
    </div>
    <div class="card" style="padding: 1rem; background: #fff; border-left: 4px solid #F59E0B; border-radius: 8px;">
        <div style="font-size: 11px; text-transform: uppercase; color: var(--slate-500); font-weight: 700;">Configurable Capacity</div>
        <div style="font-size: 1.6rem; font-weight: 800; color: var(--dark-900); margin-top: 2px;">
            {{ $categories->where('is_capacity_configurable', true)->count() }}
        </div>
        <div style="font-size: 11.5px; color: var(--slate-500);">Traveller, Urbania, Bus</div>
    </div>
    <div class="card" style="padding: 1rem; background: #fff; border-left: 4px solid #6366F1; border-radius: 8px;">
        <div style="font-size: 11px; text-transform: uppercase; color: var(--slate-500); font-weight: 700;">Fleet Vehicles Linked</div>
        <div style="font-size: 1.6rem; font-weight: 800; color: var(--dark-900); margin-top: 2px;">
            {{ $categories->sum(fn($c) => $c->vehicles->count()) }}
        </div>
        <div style="font-size: 11.5px; color: var(--slate-500);">Commercial Cabs & Buses</div>
    </div>
</div>

<!-- Categories & Models Detailed Listing -->
<div style="display: flex; flex-direction: column; gap: 1.25rem;">
    @forelse($categories as $category)
        <div class="card" style="background: #fff; border: 1px solid var(--slate-200); border-radius: 8px; overflow: hidden; box-shadow: var(--shadow-sm);">
            <!-- Category Header -->
            <div style="padding: 1rem 1.25rem; background: {{ $category->is_active ? 'var(--slate-50)' : '#FFF1F2' }}; border-bottom: 1px solid var(--slate-200); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 38px; height: 38px; border-radius: 8px; background: {{ $category->is_active ? '#EFF6FF' : '#FFE4E6' }}; color: {{ $category->is_active ? 'var(--primary)' : '#E11D48' }}; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px;">
                        #{{ $category->sort_order ?: $category->id }}
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <h2 style="margin: 0; font-size: 16px; font-weight: 800; color: var(--dark-900);">
                                {{ $category->name }}
                            </h2>
                            <code style="background: var(--slate-200); padding: 1px 6px; border-radius: 4px; font-size: 11px; font-weight: 600; color: #1E40AF;">
                                {{ $category->code }}
                            </code>
                            @if($category->is_capacity_configurable)
                                <span class="badge" style="background: #FEF3C7; color: #92400E; font-size: 10.5px; font-weight: 700;">
                                    Configurable Capacity ({{ $category->min_seating_capacity }}-{{ $category->max_seating_capacity }} Seats)
                                </span>
                            @else
                                <span class="badge" style="background: #E0E7FF; color: #3730A3; font-size: 10.5px; font-weight: 700;">
                                    {{ $category->default_seating_capacity }} Seater
                                </span>
                            @endif

                            <span class="badge {{ $category->is_active ? 'badge-success' : 'badge-danger' }}" style="font-size: 10.5px;">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div style="font-size: 12px; color: var(--slate-500); margin-top: 3px;">
                            {{ $category->description ?: 'No category notes.' }}
                            @if($category->vehicles->count() > 0)
                                &bull; <strong style="color: var(--dark-800);">{{ $category->vehicles->count() }}</strong> commercial vehicles registered in fleet
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Category Actions -->
                <div style="display: flex; align-items: center; gap: 6px;">
                    <button type="button" onclick="openAddModelModal({{ $category->id }}, '{{ addslashes($category->name) }}', {{ $category->default_seating_capacity }})" class="btn btn-outline btn-xs" style="font-size: 11px; padding: 3px 8px; color: #059669; border-color: #A7F3D0; background: #ECFDF5;">
                        <x-icon name="plus" size="12" /> Add Model
                    </button>

                    <button type="button" onclick="openEditCategoryModal({{ json_encode($category) }})" class="btn btn-outline btn-xs" style="font-size: 11px; padding: 3px 8px; color: #2563EB; border-color: #BFDBFE; background: #EFF6FF;">
                        <x-icon name="edit" size="12" /> Edit Category
                    </button>

                    <form action="{{ route('admin.vehicle-categories.toggle', $category->id) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-xs" style="font-size: 11px; padding: 3px 8px; color: {{ $category->is_active ? '#D97706' : '#059669' }}; border-color: {{ $category->is_active ? '#FDE68A' : '#A7F3D0' }}; background: {{ $category->is_active ? '#FFFBEB' : '#ECFDF5' }};">
                            {{ $category->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>

                    @if($category->vehicles->isEmpty())
                        <form action="{{ route('admin.vehicle-categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Delete category {{ addslashes($category->name) }} and its associated models?');" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline btn-xs" style="font-size: 11px; padding: 3px 8px; color: #DC2626; border-color: #FECACA; background: #FEF2F2;">
                                Delete
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Associated Models Table -->
            <div style="padding: 0.75rem 1.25rem;">
                <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--slate-400); letter-spacing: 0.5px; margin-bottom: 0.5rem; display: flex; align-items: center; justify-content: space-between;">
                    <span>Associated Vehicle Models ({{ $category->models->count() }})</span>
                    <span style="font-weight: normal; color: var(--slate-500); text-transform: none;">Shown to customers on website booking forms when selecting this category</span>
                </div>

                @if($category->models->isEmpty())
                    <div style="padding: 1rem; background: var(--slate-50); border: 1px dashed var(--slate-200); border-radius: 6px; text-align: center; color: var(--slate-500); font-size: 12px;">
                        No models configured for {{ $category->name }} yet.
                        <button type="button" onclick="openAddModelModal({{ $category->id }}, '{{ addslashes($category->name) }}', {{ $category->default_seating_capacity }})" style="color: var(--primary); background: none; border: none; font-weight: 700; cursor: pointer; text-decoration: underline;">
                            Add the first vehicle model
                        </button>
                    </div>
                @else
                    <div class="table-responsive">
                        <table style="width: 100%; font-size: 12px; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 1px solid var(--slate-200); color: var(--slate-600); text-align: left;">
                                    <th style="padding: 6px 8px; font-weight: 600;">Model Name</th>
                                    <th style="padding: 6px 8px; font-weight: 600;">Brand / Make</th>
                                    <th style="padding: 6px 8px; font-weight: 600;">Default Capacity</th>
                                    <th style="padding: 6px 8px; font-weight: 600;">Order</th>
                                    <th style="padding: 6px 8px; font-weight: 600;">Status</th>
                                    <th style="padding: 6px 8px; font-weight: 600; text-align: right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($category->models as $model)
                                    <tr style="border-bottom: 1px solid var(--slate-100);">
                                        <td style="padding: 7px 8px;">
                                            <strong style="color: var(--dark-900); font-size: 12.5px;">{{ $model->name }}</strong>
                                        </td>
                                        <td style="padding: 7px 8px; color: var(--slate-600);">
                                            {{ $model->brand ?: '-' }}
                                        </td>
                                        <td style="padding: 7px 8px; color: var(--slate-700);">
                                            <span style="display: inline-flex; align-items: center; gap: 4px;">
                                                <x-icon name="users" size="12" style="color: var(--slate-400);" />
                                                {{ $model->default_seating_capacity ?: $category->default_seating_capacity }} Seats
                                            </span>
                                        </td>
                                        <td style="padding: 7px 8px; color: var(--slate-500);">
                                            {{ $model->sort_order }}
                                        </td>
                                        <td style="padding: 7px 8px;">
                                            <span class="badge {{ $model->is_active ? 'badge-success' : 'badge-danger' }}" style="font-size: 10px; padding: 2px 6px;">
                                                {{ $model->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td style="padding: 7px 8px; text-align: right;">
                                            <div style="display: inline-flex; align-items: center; gap: 4px;">
                                                <button type="button" onclick="openEditModelModal({{ $category->id }}, {{ json_encode($model) }})" class="btn btn-outline btn-xs" style="font-size: 10px; padding: 2px 6px; color: #2563EB; border-color: #BFDBFE;">
                                                    Edit
                                                </button>

                                                <form action="{{ route('admin.vehicle-categories.models.toggle', [$category->id, $model->id]) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline btn-xs" style="font-size: 10px; padding: 2px 6px; color: {{ $model->is_active ? '#D97706' : '#059669' }}; border-color: {{ $model->is_active ? '#FDE68A' : '#A7F3D0' }};">
                                                        {{ $model->is_active ? 'Disable' : 'Enable' }}
                                                    </button>
                                                </form>

                                                <form action="{{ route('admin.vehicle-categories.models.destroy', [$category->id, $model->id]) }}" method="POST" onsubmit="return confirm('Delete model {{ addslashes($model->name) }}?');" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline btn-xs" style="font-size: 10px; padding: 2px 6px; color: #DC2626; border-color: #FECACA;">
                                                        ✕
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="card" style="padding: 3rem; text-align: center; color: var(--slate-500);">
            No vehicle categories found. Click "+ Add Vehicle Category" to initialize.
        </div>
    @endforelse
</div>

<!-- Modal 1: Add New Category -->
<div id="addCategoryModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #fff; border-radius: 8px; width: 100%; max-width: 520px; padding: 1.5rem; box-shadow: 0 10px 25px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto;">
        <div class="d-flex justify-between align-center mb-3">
            <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: var(--dark-900);">Add New Vehicle Category</h3>
            <button type="button" onclick="closeModal('addCategoryModal')" style="background: none; border: none; font-size: 18px; cursor: pointer; color: var(--slate-400);">✕</button>
        </div>
        <form action="{{ route('admin.vehicle-categories.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Category Name *</label>
                <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Luxury SUV, Electric Cab" required>
            </div>

            <div class="grid grid-2 gap-2 mb-3">
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Code / Key (Optional)</label>
                    <input type="text" name="code" class="form-control form-control-sm" placeholder="Auto-generated if blank">
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Display Name</label>
                    <input type="text" name="display_name" class="form-control form-control-sm" placeholder="e.g. Luxury 7-Seater SUV">
                </div>
            </div>

            <div class="grid grid-3 gap-2 mb-3">
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Default Seats *</label>
                    <input type="number" name="default_seating_capacity" class="form-control form-control-sm" value="4" min="1" max="70" required>
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Min Seats</label>
                    <input type="number" name="min_seating_capacity" class="form-control form-control-sm" value="4" min="1" max="70">
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Max Seats</label>
                    <input type="number" name="max_seating_capacity" class="form-control form-control-sm" value="5" min="1" max="70">
                </div>
            </div>

            <div class="mb-3" style="padding: 0.6rem 0.75rem; background: var(--slate-50); border: 1px solid var(--slate-200); border-radius: 6px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 12.5px; font-weight: 600; color: var(--dark-900);">
                    <input type="checkbox" name="is_capacity_configurable" value="1">
                    Allow Configurable Seating Capacity (For Traveller, Urbania, Bus)
                </label>
                <div style="font-size: 11px; color: var(--slate-500); margin-left: 24px; margin-top: 2px;">
                    When checked, vehicles added under this category allow any seating capacity within the min-max range rather than a fixed number.
                </div>
            </div>

            <div class="grid grid-2 gap-2 mb-3">
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Display Sort Order</label>
                    <input type="number" name="sort_order" class="form-control form-control-sm" value="0">
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Status</label>
                    <select name="is_active" class="form-select form-select-sm">
                        <option value="1" selected>Active (Available for Booking)</option>
                        <option value="0">Inactive (Hidden from Booking)</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Description</label>
                <textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Brief description of vehicle category..."></textarea>
            </div>

            <div class="d-flex justify-between gap-2">
                <button type="button" onclick="closeModal('addCategoryModal')" class="btn btn-outline btn-sm">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm flex-1">Save Category</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Edit Category -->
<div id="editCategoryModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #fff; border-radius: 8px; width: 100%; max-width: 520px; padding: 1.5rem; box-shadow: 0 10px 25px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto;">
        <div class="d-flex justify-between align-center mb-3">
            <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: var(--dark-900);">Edit Vehicle Category</h3>
            <button type="button" onclick="closeModal('editCategoryModal')" style="background: none; border: none; font-size: 18px; cursor: pointer; color: var(--slate-400);">✕</button>
        </div>
        <form id="editCategoryForm" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Category Name *</label>
                <input type="text" name="name" id="edit_cat_name" class="form-control form-control-sm" required>
            </div>

            <div class="grid grid-2 gap-2 mb-3">
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Code / Key *</label>
                    <input type="text" name="code" id="edit_cat_code" class="form-control form-control-sm" required>
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Display Name</label>
                    <input type="text" name="display_name" id="edit_cat_display_name" class="form-control form-control-sm">
                </div>
            </div>

            <div class="grid grid-3 gap-2 mb-3">
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Default Seats *</label>
                    <input type="number" name="default_seating_capacity" id="edit_cat_default_seats" class="form-control form-control-sm" min="1" max="70" required>
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Min Seats</label>
                    <input type="number" name="min_seating_capacity" id="edit_cat_min_seats" class="form-control form-control-sm" min="1" max="70">
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Max Seats</label>
                    <input type="number" name="max_seating_capacity" id="edit_cat_max_seats" class="form-control form-control-sm" min="1" max="70">
                </div>
            </div>

            <div class="mb-3" style="padding: 0.6rem 0.75rem; background: var(--slate-50); border: 1px solid var(--slate-200); border-radius: 6px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 12.5px; font-weight: 600; color: var(--dark-900);">
                    <input type="checkbox" name="is_capacity_configurable" id="edit_cat_is_configurable" value="1">
                    Allow Configurable Seating Capacity (For Traveller, Urbania, Bus)
                </label>
            </div>

            <div class="grid grid-2 gap-2 mb-3">
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Display Sort Order</label>
                    <input type="number" name="sort_order" id="edit_cat_sort_order" class="form-control form-control-sm">
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Status</label>
                    <select name="is_active" id="edit_cat_is_active" class="form-select form-select-sm">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Description</label>
                <textarea name="description" id="edit_cat_description" class="form-control form-control-sm" rows="2"></textarea>
            </div>

            <div class="d-flex justify-between gap-2">
                <button type="button" onclick="closeModal('editCategoryModal')" class="btn btn-outline btn-sm">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm flex-1">Update Category</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Add Vehicle Model -->
<div id="addModelModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #fff; border-radius: 8px; width: 100%; max-width: 460px; padding: 1.5rem; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div class="d-flex justify-between align-center mb-3">
            <div>
                <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: var(--dark-900);">Add Model to <span id="add_model_cat_name" style="color: var(--primary);"></span></h3>
            </div>
            <button type="button" onclick="closeModal('addModelModal')" style="background: none; border: none; font-size: 18px; cursor: pointer; color: var(--slate-400);">✕</button>
        </div>
        <form id="addModelForm" method="POST">
            @csrf
            <div class="mb-3">
                <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Vehicle Model Name *</label>
                <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Maruti Suzuki WagonR, Force Traveller 17S" required>
            </div>

            <div class="grid grid-2 gap-2 mb-3">
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Brand / Make</label>
                    <input type="text" name="brand" class="form-control form-control-sm" placeholder="e.g. Maruti Suzuki, Force">
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Default Seating Capacity</label>
                    <input type="number" name="default_seating_capacity" id="add_model_capacity" class="form-control form-control-sm" min="1" max="70">
                </div>
            </div>

            <div class="mb-3">
                <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Sort Order</label>
                <input type="number" name="sort_order" class="form-control form-control-sm" value="0">
            </div>

            <div class="d-flex justify-between gap-2">
                <button type="button" onclick="closeModal('addModelModal')" class="btn btn-outline btn-sm">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm flex-1">Save Model</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 4: Edit Vehicle Model -->
<div id="editModelModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #fff; border-radius: 8px; width: 100%; max-width: 460px; padding: 1.5rem; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div class="d-flex justify-between align-center mb-3">
            <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: var(--dark-900);">Edit Vehicle Model</h3>
            <button type="button" onclick="closeModal('editModelModal')" style="background: none; border: none; font-size: 18px; cursor: pointer; color: var(--slate-400);">✕</button>
        </div>
        <form id="editModelForm" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Vehicle Model Name *</label>
                <input type="text" name="name" id="edit_model_name" class="form-control form-control-sm" required>
            </div>

            <div class="grid grid-2 gap-2 mb-3">
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Brand / Make</label>
                    <input type="text" name="brand" id="edit_model_brand" class="form-control form-control-sm">
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Default Seating Capacity</label>
                    <input type="number" name="default_seating_capacity" id="edit_model_capacity" class="form-control form-control-sm" min="1" max="70">
                </div>
            </div>

            <div class="grid grid-2 gap-2 mb-3">
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Sort Order</label>
                    <input type="number" name="sort_order" id="edit_model_sort_order" class="form-control form-control-sm">
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Status</label>
                    <select name="is_active" id="edit_model_is_active" class="form-select form-select-sm">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="d-flex justify-between gap-2">
                <button type="button" onclick="closeModal('editModelModal')" class="btn btn-outline btn-sm">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm flex-1">Update Model</button>
            </div>
        </form>
    </div>
</div>

<script>
    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    function openAddCategoryModal() {
        document.getElementById('addCategoryModal').style.display = 'flex';
    }

    function openEditCategoryModal(cat) {
        const form = document.getElementById('editCategoryForm');
        form.action = `/admin/vehicle-categories/${cat.id}`;
        document.getElementById('edit_cat_name').value = cat.name || '';
        document.getElementById('edit_cat_code').value = cat.code || '';
        document.getElementById('edit_cat_display_name').value = cat.display_name || '';
        document.getElementById('edit_cat_default_seats').value = cat.default_seating_capacity || 4;
        document.getElementById('edit_cat_min_seats').value = cat.min_seating_capacity || 1;
        document.getElementById('edit_cat_max_seats').value = cat.max_seating_capacity || 60;
        document.getElementById('edit_cat_is_configurable').checked = !!cat.is_capacity_configurable;
        document.getElementById('edit_cat_sort_order').value = cat.sort_order || 0;
        document.getElementById('edit_cat_is_active').value = cat.is_active ? '1' : '0';
        document.getElementById('edit_cat_description').value = cat.description || '';
        document.getElementById('editCategoryModal').style.display = 'flex';
    }

    function openAddModelModal(categoryId, categoryName, defaultCapacity) {
        const form = document.getElementById('addModelForm');
        form.action = `/admin/vehicle-categories/${categoryId}/models`;
        document.getElementById('add_model_cat_name').textContent = categoryName;
        document.getElementById('add_model_capacity').value = defaultCapacity || '';
        document.getElementById('addModelModal').style.display = 'flex';
    }

    function openEditModelModal(categoryId, model) {
        const form = document.getElementById('editModelForm');
        form.action = `/admin/vehicle-categories/${categoryId}/models/${model.id}`;
        document.getElementById('edit_model_name').value = model.name || '';
        document.getElementById('edit_model_brand').value = model.brand || '';
        document.getElementById('edit_model_capacity').value = model.default_seating_capacity || '';
        document.getElementById('edit_model_sort_order').value = model.sort_order || 0;
        document.getElementById('edit_model_is_active').value = model.is_active ? '1' : '0';
        document.getElementById('editModelModal').style.display = 'flex';
    }
</script>
@endsection
