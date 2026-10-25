@extends('layouts.admin')

@section('title', 'Booking Categories Configuration - Vaishnavi Tours')
@section('page_title', 'Booking Categories')

@section('content')
<div class="d-flex justify-between align-center flex-wrap gap-2 mb-3">
    <div>
        <h1 style="font-size: 18px; font-weight: 700; margin: 0; color: var(--dark-900);">Configurable Booking Categories & Workflows</h1>
        <p style="color: var(--slate-500); margin: 2px 0 0 0; font-size: 12.5px;">
            Dynamic booking types supported across admin dispatch (ONE WAY, ROUND TRIP, LOCAL 4H/8H, AMBULANCE, CORPORATE, and custom categories).
        </p>
    </div>
    <div class="d-flex align-center gap-2">
        <button type="button" onclick="document.getElementById('addCategoryModal').style.display='flex'" class="btn btn-primary btn-sm">
            + Add New Category
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-3" style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; padding: 0.75rem 1rem; border-radius: 6px;">
        ✓ {{ session('success') }}
    </div>
@endif

<div class="card" style="background: #fff; border: 1px solid var(--slate-200); border-radius: 8px;">
    <div class="table-responsive">
        <table class="data-table" style="width: 100%; font-size: 12.5px;">
            <thead>
                <tr>
                    <th>Category Name</th>
                    <th>Code / Identifier</th>
                    <th>Description</th>
                    <th>Category Mode</th>
                    <th>Status</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $cat)
                    <tr>
                        <td>
                            <strong style="color: var(--dark-900); font-size: 13px;">{{ $cat->name }}</strong>
                        </td>
                        <td>
                            <code style="background: var(--slate-100); padding: 2px 6px; border-radius: 4px; font-weight: 700; color: #2563EB;">
                                {{ $cat->code }}
                            </code>
                        </td>
                        <td>{{ $cat->description ?: '-' }}</td>
                        <td>
                            <span class="badge badge-secondary" style="font-size: 10.5px;">
                                {{ $cat->is_system ? 'Core System' : 'Custom Category' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $cat->is_active ? 'badge-success' : 'badge-danger' }}">
                                {{ $cat->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <button type="button" class="btn btn-outline btn-sm" style="padding: 2px 8px; font-size: 11px; margin-right: 4px;" onclick='openEditCategoryModal(@json($cat))'>
                                Edit
                            </button>
                            @if(! $cat->is_system)
                                <form action="{{ route('admin.booking-categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('Delete this category?');" style="display: inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline btn-sm text-red-600 border-red-300" style="padding: 2px 6px; font-size: 11px;">
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2.5rem; color: var(--slate-500);">
                            No categories registered.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($categories->hasPages())
        <div style="padding: 0.75rem 1rem; border-top: 1px solid var(--slate-200);">
            {{ $categories->links() }}
        </div>
    @endif
</div>

<!-- Modal: Add New Category -->
<div id="addCategoryModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #fff; border-radius: 8px; width: 100%; max-width: 480px; padding: 1.5rem; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div class="d-flex justify-between align-center mb-3">
            <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--dark-900);">Add New Booking Category</h3>
            <button type="button" onclick="document.getElementById('addCategoryModal').style.display='none'" style="background: none; border: none; font-size: 18px; cursor: pointer; color: var(--slate-400);">✕</button>
        </div>
        <form action="{{ route('admin.booking-categories.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">Category Name *</label>
                <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Wedding Escort, Luxury Van, Corporate Monthly" required>
            </div>
            <div class="mb-3">
                <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">Category Code / Slug *</label>
                <input type="text" name="code" class="form-control form-control-sm" placeholder="e.g. WEDDING_ESCORT, LUXURY_VAN" required>
            </div>
            <div class="mb-3">
                <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">Description & Notes</label>
                <textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Operational rules, vehicle types allowed..."></textarea>
            </div>
            <div class="mb-3">
                <label style="font-size: 12px; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                    <input type="checkbox" name="is_active" value="1" checked>
                    <span style="font-weight: 600;">Active & ready for admin dispatch</span>
                </label>
            </div>
            <div class="d-flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('addCategoryModal').style.display='none'" class="btn btn-secondary btn-sm">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Create Category</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Category -->
<div id="editCategoryModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #fff; border-radius: 8px; width: 100%; max-width: 480px; padding: 1.5rem; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div class="d-flex justify-between align-center mb-3">
            <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--dark-900);">Edit Booking Category</h3>
            <button type="button" onclick="document.getElementById('editCategoryModal').style.display='none'" style="background: none; border: none; font-size: 18px; cursor: pointer; color: var(--slate-400);">✕</button>
        </div>
        <form id="editCategoryForm" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">Category Name *</label>
                <input type="text" name="name" id="editCategoryName" class="form-control form-control-sm" required>
            </div>
            <div class="mb-3">
                <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">Category Code / Identifier</label>
                <input type="text" name="code" id="editCategoryCode" class="form-control form-control-sm">
                <small id="editCategoryCodeNote" style="display: none; color: var(--slate-500); font-size: 11px;">Core system codes cannot be renamed to prevent dispatch issues.</small>
            </div>
            <div class="mb-3">
                <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 2px;">Description & Notes</label>
                <textarea name="description" id="editCategoryDescription" class="form-control form-control-sm" rows="2"></textarea>
            </div>
            <div class="mb-3">
                <label style="font-size: 12px; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                    <input type="checkbox" name="is_active" id="editCategoryActive" value="1">
                    <span style="font-weight: 600;">Active & available for bookings</span>
                </label>
            </div>
            <div class="d-flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('editCategoryModal').style.display='none'" class="btn btn-secondary btn-sm">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditCategoryModal(cat) {
    const form = document.getElementById('editCategoryForm');
    form.action = "{{ url('admin/booking-categories') }}/" + cat.id;

    document.getElementById('editCategoryName').value = cat.name || '';
    const codeInput = document.getElementById('editCategoryCode');
    const codeNote = document.getElementById('editCategoryCodeNote');

    codeInput.value = cat.code || '';
    if (cat.is_system) {
        codeInput.setAttribute('readonly', 'readonly');
        codeInput.style.backgroundColor = 'var(--slate-100)';
        codeNote.style.display = 'block';
    } else {
        codeInput.removeAttribute('readonly');
        codeInput.style.backgroundColor = '#fff';
        codeNote.style.display = 'none';
    }

    document.getElementById('editCategoryDescription').value = cat.description || '';
    document.getElementById('editCategoryActive').checked = !!cat.is_active;

    document.getElementById('editCategoryModal').style.display = 'flex';
}
</script>
@endsection
