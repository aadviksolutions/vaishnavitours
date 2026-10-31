@extends('layouts.admin')

@section('title', 'Edit Vehicle: ' . $vehicle->name)
@section('page_title', 'Edit Fleet Vehicle')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.vehicles.show', $vehicle->id) }}" style="font-size: 0.875rem; color: var(--slate-600); font-weight: 600;">
        ← Back to Vehicle Details
    </a>
    <h1 style="font-size: 1.65rem; font-weight: 800; margin: 4px 0 0 0;">Edit {{ $vehicle->name }}</h1>
    <p style="color: var(--slate-500); font-size: 0.875rem; margin: 2px 0 0 0;">Update vehicle registration, capacity, rates, and operational status.</p>
</div>

@if($errors->any())
    <div class="alert alert-danger mb-4">
        <ul style="margin: 0; padding-left: 1.25rem;">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('admin.vehicles.update', $vehicle->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="grid grid-3 gap-4">
        <!-- Left 2 Cols: Specs -->
        <div style="grid-column: span 2;">
            <div class="card mb-4">
                <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 8px;">
                    <x-icon name="car" :size="18" /> Vehicle Specification
                </h3>

                <div class="grid grid-2 gap-3 mb-3">
                    <div class="form-group">
                        <label for="name" class="form-label">Vehicle Make & Model Name *</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $vehicle->name) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="registration_number" class="form-label">Commercial RTO Number *</label>
                        <input type="text" name="registration_number" id="registration_number" class="form-control" value="{{ old('registration_number', $vehicle->registration_number) }}" required>
                    </div>
                </div>

                <div class="grid grid-2 gap-3 mb-3">
                    <div class="form-group">
                        <div class="d-flex justify-between align-center mb-1">
                            <label for="vehicle_category_id" class="form-label mb-0">Vehicle Category *</label>
                            <button type="button" onclick="openQuickCategoryModal()" style="background: none; border: none; color: #2563EB; font-weight: 700; font-size: 11px; cursor: pointer; text-decoration: underline; padding: 0;">
                                + Add Category
                            </button>
                        </div>
                        <select name="vehicle_category_id" id="vehicle_category_id" class="form-select" onchange="onAdminCategoryChange()" required>
                            <option value="">-- Select Category --</option>
                            <option value="new_category" style="font-weight: 800; color: #2563EB; background: #EFF6FF;">➕ + Add New Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" 
                                        data-name="{{ $cat->name }}" 
                                        data-capacity="{{ $cat->default_seating_capacity }}" 
                                        data-min="{{ $cat->min_seating_capacity }}" 
                                        data-max="{{ $cat->max_seating_capacity }}"
                                        data-configurable="{{ $cat->is_capacity_configurable ? '1' : '0' }}"
                                        {{ old('vehicle_category_id', $vehicle->vehicle_category_id) == $cat->id || old('vehicle_type', $vehicle->vehicle_type) == $cat->name ? 'selected' : '' }}>
                                    {{ $cat->name }} ({{ $cat->is_capacity_configurable ? 'Configurable '.$cat->min_seating_capacity.'-'.$cat->max_seating_capacity.' Seats' : $cat->default_seating_capacity.' Seats' }})
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="vehicle_type" id="vehicle_type" value="{{ old('vehicle_type', $vehicle->vehicle_type) }}">
                    </div>

                    <div class="form-group">
                        <div class="d-flex justify-between align-center mb-1">
                            <label for="vehicle_model_id" class="form-label mb-0">Vehicle Model Option</label>
                            <button type="button" onclick="openQuickModelModal()" style="background: none; border: none; color: #2563EB; font-weight: 700; font-size: 11px; cursor: pointer; text-decoration: underline; padding: 0;">
                                + Add Model
                            </button>
                        </div>
                        <select name="vehicle_model_id" id="vehicle_model_id" class="form-select" onchange="onAdminModelChange()">
                            <option value="">-- Optional / Other Model --</option>
                            <option value="new_model" style="font-weight: 800; color: #2563EB; background: #EFF6FF;">➕ + Add New Model</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-2 gap-3 mb-3">
                    <div class="form-group">
                        <label for="seating_capacity" class="form-label">
                            Passenger Seating Capacity (Seats) *
                            <span id="capacityBadge" class="badge" style="background: #E0E7FF; color: #3730A3; font-size: 10px; margin-left: 5px;">Configurable</span>
                        </label>
                        <input type="number" name="seating_capacity" id="seating_capacity" class="form-control" value="{{ old('seating_capacity', $vehicle->seating_capacity) }}" min="1" max="70" required>
                        <small id="capacityHelp" style="color: var(--slate-500); font-size: 11px; display: block; margin-top: 2px;">
                            Configurable seating capacity: adjust if this vehicle has a specific certified passenger configuration.
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="ac_non_ac" class="form-label">Air Conditioning *</label>
                        <select name="ac_non_ac" id="ac_non_ac" class="form-select" required>
                            <option value="AC" {{ old('ac_non_ac', $vehicle->ac_non_ac) == 'AC' ? 'selected' : '' }}>AC (Air Conditioned)</option>
                            <option value="Non-AC" {{ old('ac_non_ac', $vehicle->ac_non_ac) == 'Non-AC' ? 'selected' : '' }}>Non-AC</option>
                        </select>
                    </div>
                </div>

                <div class="card mb-4" style="background: var(--slate-50); border: 1px solid var(--slate-200);">
                    <h3 style="font-size: 1.05rem; font-weight: 800; margin-bottom: 0.75rem;">📑 Compliance & Document Records</h3>
                    <p style="font-size: 0.8rem; color: var(--slate-500); margin-top: -0.5rem; margin-bottom: 1rem;">Set document expiry dates for automated compliance reminders.</p>

                    <div class="grid grid-2 gap-3 mb-3">
                        <div class="form-group">
                            <label for="insurance_number" class="form-label">Insurance Policy Number</label>
                            <input type="text" name="insurance_number" id="insurance_number" class="form-control" value="{{ old('insurance_number', $vehicle->insurance_number) }}" placeholder="e.g. POL-98765432">
                        </div>

                        <div class="form-group">
                            <label for="insurance_expiry" class="form-label">Insurance Expiry Date</label>
                            <input type="date" name="insurance_expiry" id="insurance_expiry" class="form-control" value="{{ old('insurance_expiry', $vehicle->insurance_expiry?->format('Y-m-d')) }}">
                        </div>
                    </div>

                    <div class="grid grid-3 gap-3 mb-2">
                        <div class="form-group">
                            <label for="fitness_expiry" class="form-label">Fitness Expiry</label>
                            <input type="date" name="fitness_expiry" id="fitness_expiry" class="form-control" value="{{ old('fitness_expiry', $vehicle->fitness_expiry?->format('Y-m-d')) }}">
                        </div>

                        <div class="form-group">
                            <label for="permit_expiry" class="form-label">Permit Expiry</label>
                            <input type="date" name="permit_expiry" id="permit_expiry" class="form-control" value="{{ old('permit_expiry', $vehicle->permit_expiry?->format('Y-m-d')) }}">
                        </div>

                        <div class="form-group">
                            <label for="puc_expiry" class="form-label">PUC Expiry</label>
                            <input type="date" name="puc_expiry" id="puc_expiry" class="form-control" value="{{ old('puc_expiry', $vehicle->puc_expiry?->format('Y-m-d')) }}">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="notes" class="form-label">Maintenance Notes / Special Equipment</label>
                    <textarea name="notes" id="notes" class="form-control" rows="2">{{ old('notes', $vehicle->notes) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Right 1 Col: Fleet Status -->
        <div>
            <div class="card mb-4">
                <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1.25rem;">Fleet Status</h3>

                <div class="form-group mb-4">
                    <label for="status" class="form-label">Operational Status *</label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="Available" {{ old('status', $vehicle->status) == 'Available' ? 'selected' : '' }}>Available for Booking</option>
                        <option value="On Trip" {{ old('status', $vehicle->status) == 'On Trip' ? 'selected' : '' }}>On Active Trip</option>
                        <option value="Maintenance" {{ old('status', $vehicle->status) == 'Maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                        <option value="Inactive" {{ old('status', $vehicle->status) == 'Inactive' ? 'selected' : '' }}>Inactive / Decommissioned</option>
                    </select>
                </div>

                <div class="d-flex justify-between gap-2">
                    <a href="{{ route('admin.vehicles.show', $vehicle->id) }}" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary flex-1">
                        💾 Update Vehicle
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Quick Add Category Modal -->
<div id="quickCategoryModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.55); z-index: 10000; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #fff; border-radius: 8px; width: 100%; max-width: 480px; padding: 1.5rem; box-shadow: 0 10px 25px rgba(0,0,0,0.25);">
        <div class="d-flex justify-between align-center mb-3">
            <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: var(--dark-900);">➕ Add New Vehicle Category</h3>
            <button type="button" onclick="closeQuickCategoryModal()" style="background: none; border: none; font-size: 18px; cursor: pointer; color: var(--slate-400);">✕</button>
        </div>
        <form id="quickCategoryForm" onsubmit="submitQuickCategory(event)">
            <div class="mb-3">
                <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Category Name *</label>
                <input type="text" id="qc_name" class="form-control form-control-sm" placeholder="e.g. Luxury SUV, Mini Bus" required>
            </div>
            <div class="grid grid-2 gap-2 mb-3">
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Default Seats *</label>
                    <input type="number" id="qc_default_capacity" class="form-control form-control-sm" value="4" min="1" max="70" required>
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Max Seats (if range)</label>
                    <input type="number" id="qc_max_capacity" class="form-control form-control-sm" value="4" min="1" max="70">
                </div>
            </div>
            <div class="mb-3">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 12px; font-weight: 600;">
                    <input type="checkbox" id="qc_configurable" value="1">
                    Allow Configurable Seating Capacity
                </label>
            </div>
            <div id="qc_error" style="display: none; color: #dc2626; font-size: 12px; margin-bottom: 10px;"></div>
            <div class="d-flex justify-end gap-2">
                <button type="button" onclick="closeQuickCategoryModal()" class="btn btn-outline btn-sm">Cancel</button>
                <button type="submit" id="qc_submit_btn" class="btn btn-primary btn-sm">Save & Select Category</button>
            </div>
        </form>
    </div>
</div>

<!-- Quick Add Model Modal -->
<div id="quickModelModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.55); z-index: 10000; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #fff; border-radius: 8px; width: 100%; max-width: 450px; padding: 1.5rem; box-shadow: 0 10px 25px rgba(0,0,0,0.25);">
        <div class="d-flex justify-between align-center mb-3">
            <h3 style="margin: 0; font-size: 16px; font-weight: 800; color: var(--dark-900);">➕ Add New Vehicle Model</h3>
            <button type="button" onclick="closeQuickModelModal()" style="background: none; border: none; font-size: 18px; cursor: pointer; color: var(--slate-400);">✕</button>
        </div>
        <form id="quickModelForm" onsubmit="submitQuickModel(event)">
            <div class="mb-3">
                <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Model Name *</label>
                <input type="text" id="qm_name" class="form-control form-control-sm" placeholder="e.g. Innova Hycross, Kia Carens" required>
            </div>
            <div class="grid grid-2 gap-2 mb-3">
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Brand / Make</label>
                    <input type="text" id="qm_brand" class="form-control form-control-sm" placeholder="e.g. Toyota, Kia">
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 700; display: block; margin-bottom: 2px;">Default Seats</label>
                    <input type="number" id="qm_capacity" class="form-control form-control-sm" min="1" max="70" placeholder="Optional">
                </div>
            </div>
            <div id="qm_error" style="display: none; color: #dc2626; font-size: 12px; margin-bottom: 10px;"></div>
            <div class="d-flex justify-end gap-2">
                <button type="button" onclick="closeQuickModelModal()" class="btn btn-outline btn-sm">Cancel</button>
                <button type="submit" id="qm_submit_btn" class="btn btn-primary btn-sm">Save & Select Model</button>
            </div>
        </form>
    </div>
</div>

<script>
    let vehicleCategoriesData = @json($categories);
    const initialModelId = @json(old('vehicle_model_id', $vehicle->vehicle_model_id));
    let initialLoad = true;
    let lastValidCategory = @json(old('vehicle_category_id', $vehicle->vehicle_category_id));
    let lastValidModel = initialModelId;

    function openQuickCategoryModal() {
        const modal = document.getElementById('quickCategoryModal');
        modal.style.display = 'flex';
        document.getElementById('qc_name').focus();
    }

    function closeQuickCategoryModal() {
        const modal = document.getElementById('quickCategoryModal');
        modal.style.display = 'none';
        document.getElementById('qc_error').style.display = 'none';
        const catSelect = document.getElementById('vehicle_category_id');
        if (catSelect.value === 'new_category') {
            catSelect.value = lastValidCategory;
        }
    }

    function openQuickModelModal() {
        const catSelect = document.getElementById('vehicle_category_id');
        if (!catSelect.value || catSelect.value === 'new_category') {
            alert('Please select a valid Vehicle Category first.');
            return;
        }
        const modal = document.getElementById('quickModelModal');
        modal.style.display = 'flex';
        document.getElementById('qm_name').focus();
    }

    function closeQuickModelModal() {
        const modal = document.getElementById('quickModelModal');
        modal.style.display = 'none';
        document.getElementById('qm_error').style.display = 'none';
        const modelSelect = document.getElementById('vehicle_model_id');
        if (modelSelect.value === 'new_model') {
            modelSelect.value = lastValidModel;
        }
    }

    async function submitQuickCategory(e) {
        e.preventDefault();
        const btn = document.getElementById('qc_submit_btn');
        const err = document.getElementById('qc_error');
        btn.disabled = true;
        btn.textContent = 'Saving...';
        err.style.display = 'none';

        const name = document.getElementById('qc_name').value.trim();
        const cap = parseInt(document.getElementById('qc_default_capacity').value) || 4;
        const maxCap = parseInt(document.getElementById('qc_max_capacity').value) || cap;
        const conf = document.getElementById('qc_configurable').checked ? 1 : 0;

        try {
            const resp = await fetch('{{ route('admin.vehicle-categories.quick') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    name: name,
                    default_seating_capacity: cap,
                    min_seating_capacity: cap,
                    max_seating_capacity: maxCap,
                    is_capacity_configurable: conf
                })
            });
            const data = await resp.json();
            if (data.success && data.category) {
                const cat = data.category;
                vehicleCategoriesData.push(cat);
                const catSelect = document.getElementById('vehicle_category_id');
                const opt = document.createElement('option');
                opt.value = cat.id;
                opt.dataset.name = cat.name;
                opt.dataset.capacity = cat.default_seating_capacity;
                opt.dataset.min = cat.min_seating_capacity;
                opt.dataset.max = cat.max_seating_capacity;
                opt.dataset.configurable = cat.is_capacity_configurable ? '1' : '0';
                opt.textContent = `${cat.name} (${cat.is_capacity_configurable ? 'Configurable ' + cat.min_seating_capacity + '-' + cat.max_seating_capacity + ' Seats' : cat.default_seating_capacity + ' Seats'})`;
                
                catSelect.appendChild(opt);
                catSelect.value = cat.id;
                lastValidCategory = cat.id;
                closeQuickCategoryModal();
                onAdminCategoryChange();
                alert(`✓ Category '${cat.name}' created and selected successfully!`);
            } else {
                err.textContent = data.message || 'Error creating category.';
                err.style.display = 'block';
            }
        } catch (ex) {
            err.textContent = 'Server error creating category: ' + ex.message;
            err.style.display = 'block';
        } finally {
            btn.disabled = false;
            btn.textContent = 'Save & Select Category';
        }
    }

    async function submitQuickModel(e) {
        e.preventDefault();
        const catSelect = document.getElementById('vehicle_category_id');
        const catId = catSelect.value;
        const btn = document.getElementById('qm_submit_btn');
        const err = document.getElementById('qm_error');
        btn.disabled = true;
        btn.textContent = 'Saving...';
        err.style.display = 'none';

        const name = document.getElementById('qm_name').value.trim();
        const brand = document.getElementById('qm_brand').value.trim();
        const cap = document.getElementById('qm_capacity').value;

        try {
            const resp = await fetch('{{ route('admin.vehicle-categories.models.quick') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    vehicle_category_id: catId,
                    name: name,
                    brand: brand,
                    default_seating_capacity: cap ? parseInt(cap) : null
                })
            });
            const data = await resp.json();
            if (data.success && data.model) {
                const m = data.model;
                const modelSelect = document.getElementById('vehicle_model_id');
                const opt = document.createElement('option');
                opt.value = m.id;
                opt.textContent = m.name + (m.brand ? ` (${m.brand})` : '');
                opt.dataset.name = m.name;
                opt.dataset.capacity = m.default_seating_capacity || '';
                modelSelect.appendChild(opt);
                modelSelect.value = m.id;
                lastValidModel = m.id;
                closeQuickModelModal();
                onAdminModelChange();
                alert(`✓ Model '${m.name}' created and selected successfully!`);
            } else {
                err.textContent = data.message || 'Error creating model.';
                err.style.display = 'block';
            }
        } catch (ex) {
            err.textContent = 'Server error creating model: ' + ex.message;
            err.style.display = 'block';
        } finally {
            btn.disabled = false;
            btn.textContent = 'Save & Select Model';
        }
    }

    function onAdminCategoryChange() {
        const catSelect = document.getElementById('vehicle_category_id');
        if (catSelect.value === 'new_category') {
            openQuickCategoryModal();
            return;
        }
        lastValidCategory = catSelect.value;
        const modelSelect = document.getElementById('vehicle_model_id');
        const typeHidden = document.getElementById('vehicle_type');
        const capInput = document.getElementById('seating_capacity');
        const capBadge = document.getElementById('capacityBadge');
        const capHelp = document.getElementById('capacityHelp');

        const selectedOpt = catSelect.options[catSelect.selectedIndex];
        if (!selectedOpt || !selectedOpt.value) return;

        const catId = parseInt(selectedOpt.value);
        const catName = selectedOpt.dataset.name;
        const defaultCap = parseInt(selectedOpt.dataset.capacity) || 4;
        const isConfigurable = selectedOpt.dataset.configurable === '1';
        const minCap = parseInt(selectedOpt.dataset.min) || 1;
        const maxCap = parseInt(selectedOpt.dataset.max) || 70;

        typeHidden.value = catName;

        if (isConfigurable) {
            capBadge.textContent = `Configurable (${minCap}-${maxCap} Seats)`;
            capBadge.style.background = '#FEF3C7';
            capBadge.style.color = '#92400E';
            capHelp.textContent = `Enter custom seating capacity for this ${catName} (certified passenger seats between ${minCap} and ${maxCap}).`;
        } else {
            capBadge.textContent = `${defaultCap} Seats Standard`;
            capBadge.style.background = '#E0E7FF';
            capBadge.style.color = '#3730A3';
            capHelp.textContent = `Standard ${defaultCap}-seater capacity. Adjust if RTO commercial registration specifies otherwise.`;
        }

        capInput.min = minCap;
        capInput.max = maxCap;

        if (!initialLoad) {
            capInput.value = defaultCap;
        }

        // Populate models
        const catObj = vehicleCategoriesData.find(c => c.id === catId);
        const currentModelVal = initialLoad ? initialModelId : modelSelect.value;
        modelSelect.innerHTML = '<option value="">-- Optional / Other Model --</option><option value="new_model" style="font-weight: 800; color: #2563EB; background: #EFF6FF;">➕ + Add New Model</option>';
        if (catObj && catObj.active_models && catObj.active_models.length > 0) {
            catObj.active_models.forEach(m => {
                const opt = document.createElement('option');
                opt.value = m.id;
                opt.textContent = m.name + (m.brand ? ` (${m.brand})` : '');
                opt.dataset.name = m.name;
                opt.dataset.capacity = m.default_seating_capacity || defaultCap;
                if (currentModelVal == m.id) opt.selected = true;
                modelSelect.appendChild(opt);
            });
        }
        initialLoad = false;
    }

    function onAdminModelChange() {
        const modelSelect = document.getElementById('vehicle_model_id');
        if (modelSelect.value === 'new_model') {
            openQuickModelModal();
            return;
        }
        lastValidModel = modelSelect.value;
    }

    document.addEventListener('DOMContentLoaded', () => {
        onAdminCategoryChange();
    });
</script>
@endsection
