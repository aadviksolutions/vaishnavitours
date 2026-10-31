@extends('layouts.admin')

@section('title', 'Add New Vehicle')
@section('page_title', 'Register Fleet Vehicle')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.vehicles.index') }}" style="font-size: 0.875rem; color: var(--slate-600); font-weight: 600;">
        ← Back to Fleet Management
    </a>
    <h1 style="font-size: 1.65rem; font-weight: 800; margin: 4px 0 0 0;">Add Vehicle to Fleet</h1>
    <p style="color: var(--slate-500); font-size: 0.875rem; margin: 2px 0 0 0;">Register new commercial cab or tourist traveller into Vaishnavi Tours fleet.</p>
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

<form action="{{ route('admin.vehicles.store') }}" method="POST">
    @csrf

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
                        <input type="text" name="name" id="name" class="form-control" placeholder="e.g. Maruti Suzuki Dzire VXI" value="{{ old('name') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="registration_number" class="form-label">Commercial RTO Number *</label>
                        <input type="text" name="registration_number" id="registration_number" class="form-control" placeholder="e.g. CG 10 AB 4501" value="{{ old('registration_number') }}" required>
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
                                        {{ old('vehicle_category_id', $loop->first ? $cat->id : null) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }} ({{ $cat->is_capacity_configurable ? 'Configurable '.$cat->min_seating_capacity.'-'.$cat->max_seating_capacity.' Seats' : $cat->default_seating_capacity.' Seats' }})
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="vehicle_type" id="vehicle_type" value="{{ old('vehicle_type', $categories->first()?->name ?? 'Sedan') }}">
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
                        <input type="number" name="seating_capacity" id="seating_capacity" class="form-control" value="{{ old('seating_capacity', 4) }}" min="1" max="70" required>
                        <small id="capacityHelp" style="color: var(--slate-500); font-size: 11px; display: block; margin-top: 2px;">
                            Configurable seating capacity: enter certified passenger seats for this vehicle.
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="ac_non_ac" class="form-label">Air Conditioning *</label>
                        <select name="ac_non_ac" id="ac_non_ac" class="form-select" required>
                            <option value="AC" {{ old('ac_non_ac') == 'AC' ? 'selected' : '' }}>AC (Air Conditioned)</option>
                            <option value="Non-AC" {{ old('ac_non_ac') == 'Non-AC' ? 'selected' : '' }}>Non-AC</option>
                        </select>
                    </div>
                </div>

                <div class="card mb-4" id="complianceCard" style="background: var(--slate-50); border: 1px solid var(--slate-200);">
                    <div class="d-flex justify-between align-center mb-2 flex-wrap gap-2">
                        <h3 style="font-size: 1.05rem; font-weight: 800; margin: 0;">📑 Compliance & Document Records</h3>
                        <span id="docNoticeBadge" class="badge" style="background: {{ old('ownership_type') === 'third_party' ? '#FEF3C7' : '#E0E7FF' }}; color: {{ old('ownership_type') === 'third_party' ? '#92400E' : '#3730A3' }}; font-size: 11px;">
                            {{ old('ownership_type') === 'third_party' ? 'Optional for Third-Party Partner' : 'Mandatory for Owned Fleet' }}
                        </span>
                    </div>
                    <p id="complianceHelpText" style="font-size: 0.8rem; color: var(--slate-500); margin-top: -0.25rem; margin-bottom: 1rem;">
                        {{ old('ownership_type') === 'third_party' ? 'Documents are not required for Travel Partner vehicles. Only vehicle number is mandatory.' : 'Insurance and Pollution Under Control (PUC) expiry dates are mandatory for company-owned fleet.' }}
                    </p>

                    <div class="grid grid-2 gap-3 mb-3">
                        <div class="form-group">
                            <label for="insurance_number" class="form-label">Insurance Policy Number</label>
                            <input type="text" name="insurance_number" id="insurance_number" class="form-control" value="{{ old('insurance_number') }}" placeholder="e.g. POL-98765432">
                        </div>

                        <div class="form-group">
                            <label for="insurance_expiry" class="form-label" id="lblInsuranceExpiry">
                                Insurance Expiry Date <span id="insuranceReqMark" style="color: #DC2626; display: {{ old('ownership_type') === 'third_party' ? 'none' : 'inline' }};">*</span>
                            </label>
                            <input type="date" name="insurance_expiry" id="insurance_expiry" class="form-control" value="{{ old('insurance_expiry') }}" {{ old('ownership_type') === 'third_party' ? '' : 'required' }}>
                        </div>
                    </div>

                    <div class="grid grid-3 gap-3 mb-2">
                        <div class="form-group">
                            <label for="puc_expiry" class="form-label" id="lblPucExpiry">
                                PUC Expiry Date <span id="pucReqMark" style="color: #DC2626; display: {{ old('ownership_type') === 'third_party' ? 'none' : 'inline' }};">*</span>
                            </label>
                            <input type="date" name="puc_expiry" id="puc_expiry" class="form-control" value="{{ old('puc_expiry') }}" {{ old('ownership_type') === 'third_party' ? '' : 'required' }}>
                        </div>

                        <div class="form-group">
                            <label for="fitness_expiry" class="form-label">Fitness Expiry</label>
                            <input type="date" name="fitness_expiry" id="fitness_expiry" class="form-control" value="{{ old('fitness_expiry') }}">
                        </div>

                        <div class="form-group">
                            <label for="permit_expiry" class="form-label">Commercial Permit Expiry</label>
                            <input type="date" name="permit_expiry" id="permit_expiry" class="form-control" value="{{ old('permit_expiry') }}">
                        </div>
                    </div>
                </div>

                <!-- Third-Party Vehicle Vendor Profile (Requirement: Section 3) -->
                <div class="card mb-4" style="background: #fff; border: 1px solid var(--slate-200); border-radius: 8px; padding: 1.25rem;">
                    <h3 style="font-size: 1.05rem; font-weight: 800; margin-bottom: 0.75rem;">🤝 Ownership & Vendor Tracking</h3>
                    <div class="grid grid-2 gap-3 mb-3">
                        <div class="form-group">
                            <label class="form-label">Ownership Type *</label>
                            <select name="ownership_type" id="ownership_type" class="form-select" onchange="toggleVendorFields(this.value)" required>
                                <option value="owned" {{ old('ownership_type', 'owned') === 'owned' ? 'selected' : '' }}>Company Owned Fleet</option>
                                <option value="third_party" {{ old('ownership_type') === 'third_party' ? 'selected' : '' }}>Third-Party / Vendor Attached Vehicle</option>
                            </select>
                        </div>
                    </div>

                    <div id="vendorFieldsCard" style="display: {{ old('ownership_type') === 'third_party' ? 'block' : 'none' }}; background: #FFFBEB; border: 1px solid #FDE68A; padding: 1rem; border-radius: 6px;">
                        <div style="font-weight: 700; font-size: 13px; color: #92400E; margin-bottom: 0.5rem;">
                            Vendor / Vehicle Owner Details (Travel Partner)
                        </div>
                        <div class="grid grid-3 gap-2 mb-2">
                            <div>
                                <label style="font-size: 11px; font-weight: 600; color: #92400E; display: block; margin-bottom: 2px;">Owner / Vendor Name</label>
                                <input type="text" name="vendor_name" id="vendor_name" class="form-control form-control-sm" placeholder="e.g. Maa Sharda Travels" value="{{ old('vendor_name') }}">
                            </div>
                            <div>
                                <label style="font-size: 11px; font-weight: 600; color: #92400E; display: block; margin-bottom: 2px;">Vendor Phone</label>
                                <input type="tel" name="vendor_phone" class="form-control form-control-sm" placeholder="e.g. 9827100000" value="{{ old('vendor_phone') }}">
                            </div>
                            <div>
                                <label style="font-size: 11px; font-weight: 600; color: #92400E; display: block; margin-bottom: 2px;">Agreed Hire Cost (₹)</label>
                                <input type="number" step="0.01" name="agreed_vendor_amount" class="form-control form-control-sm" placeholder="0.00" value="{{ old('agreed_vendor_amount') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                function toggleVendorFields(val) {
                    const card = document.getElementById('vendorFieldsCard');
                    const vName = document.getElementById('vendor_name');
                    const isTp = (val === 'third_party');
                    if (card) card.style.display = isTp ? 'block' : 'none';

                    // Third-party vehicles do not require compliance documents (only vehicle number is mandatory)
                    const ins = document.getElementById('insurance_expiry');
                    const puc = document.getElementById('puc_expiry');
                    const insMark = document.getElementById('insuranceReqMark');
                    const pucMark = document.getElementById('pucReqMark');
                    const badge = document.getElementById('docNoticeBadge');
                    const helpText = document.getElementById('complianceHelpText');

                    if (ins) ins.required = !isTp;
                    if (puc) puc.required = !isTp;
                    if (insMark) insMark.style.display = isTp ? 'none' : 'inline';
                    if (pucMark) pucMark.style.display = isTp ? 'none' : 'inline';
                    if (badge) {
                        badge.textContent = isTp ? 'Optional for Third-Party Partner' : 'Mandatory for Owned Fleet';
                        badge.style.background = isTp ? '#FEF3C7' : '#E0E7FF';
                        badge.style.color = isTp ? '#92400E' : '#3730A3';
                    }
                    if (helpText) {
                        helpText.textContent = isTp 
                            ? 'Documents are not required for Travel Partner vehicles. Only vehicle number is mandatory.'
                            : 'Insurance and Pollution Under Control (PUC) expiry dates are mandatory for company-owned fleet.';
                    }
                }
                </script>

                <div class="form-group">
                    <label for="notes" class="form-label">Maintenance Notes / Special Equipment</label>
                    <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="GPS installed, commercial permit valid till 2028, first-aid kit... ">{{ old('notes') }}</textarea>
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
                        <option value="Available" {{ old('status', 'Available') == 'Available' ? 'selected' : '' }}>Available for Booking</option>
                        <option value="On Trip" {{ old('status') == 'On Trip' ? 'selected' : '' }}>On Active Trip</option>
                        <option value="Maintenance" {{ old('status') == 'Maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                        <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>Inactive / Decommissioned</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    💾 Save Vehicle
                </button>
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
    let lastValidCategory = '';
    let lastValidModel = '';

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
        const nameInput = document.getElementById('name');

        const selectedOpt = catSelect.options[catSelect.selectedIndex];
        if (!selectedOpt || !selectedOpt.value) return;

        const catId = parseInt(selectedOpt.value);
        const catName = selectedOpt.dataset.name;
        const defaultCap = parseInt(selectedOpt.dataset.capacity) || 4;
        const isConfigurable = selectedOpt.dataset.configurable === '1';
        const minCap = parseInt(selectedOpt.dataset.min) || 1;
        const maxCap = parseInt(selectedOpt.dataset.max) || 60;

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

        capInput.value = defaultCap;
        capInput.min = minCap;
        capInput.max = maxCap;

        // Populate models
        const catObj = vehicleCategoriesData.find(c => c.id === catId);
        const currentModelVal = modelSelect.value;
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
    }

    function onAdminModelChange() {
        const modelSelect = document.getElementById('vehicle_model_id');
        if (modelSelect.value === 'new_model') {
            openQuickModelModal();
            return;
        }
        lastValidModel = modelSelect.value;
        const nameInput = document.getElementById('name');
        const capInput = document.getElementById('seating_capacity');

        const selectedOpt = modelSelect.options[modelSelect.selectedIndex];
        if (selectedOpt && selectedOpt.value) {
            if (!nameInput.value || nameInput.value.trim() === '') {
                nameInput.value = selectedOpt.dataset.name;
            }
            if (selectedOpt.dataset.capacity) {
                capInput.value = selectedOpt.dataset.capacity;
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        onAdminCategoryChange();
    });
</script>
@endsection
