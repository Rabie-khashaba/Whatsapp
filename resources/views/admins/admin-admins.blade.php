@extends('admins.layouts.master')

@section('title', 'Admin Users - Admin Panel')
@section('page_title', 'Admin Users')
@section('page_subtitle', 'Manage admin accounts and permissions')

@section('content')
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="dashboard-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="mb-1">Admin Users</h4>
            <p class="text-muted mb-0 small">Manage admin accounts and permissions</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminModal">
            <i class="bi bi-plus-circle me-2"></i>Add Admin
        </button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="dashboard-card stats-card">
            <div class="stats-icon" style="background: rgba(59, 130, 246, 0.1); color: #3B82F6;">
                <i class="bi bi-shield-check"></i>
            </div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">Total Admins</p>
                <h3 class="mb-0 fw-bold">{{ $totalAdmins }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dashboard-card stats-card">
            <div class="stats-icon" style="background: rgba(16, 185, 129, 0.1); color: #10B981;">
                <i class="bi bi-person-check"></i>
            </div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">Active</p>
                <h3 class="mb-0 fw-bold">{{ $activeAdmins }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dashboard-card stats-card">
            <div class="stats-icon" style="background: rgba(168, 85, 247, 0.1); color: #A855F7;">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="flex-grow-1">
                <p class="text-muted mb-1">Latest Admin</p>
                <h3 class="mb-0 fw-bold" style="font-size: 18px;">{{ $latestAdmin?->name ?? '-' }}</h3>
                <small class="text-muted">{{ $latestAdmin?->created_at?->diffForHumans() ?? 'No admins found' }}</small>
            </div>
        </div>
    </div>
</div>

<div class="dashboard-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Admin</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($admins as $admin)
                    @php
                        $roleClass = $admin->display_role === 'super admin' ? 'danger' : 'success';
                    @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-light border d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    {{ $admin->initials }}
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $admin->name }}</div>
                                    <small class="text-muted">#{{ $admin->id }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $admin->email ?? '-' }}</td>
                        <td>{{ $admin->phone ?? '-' }}</td>
                        <td><span class="badge bg-{{ $roleClass }}">{{ $admin->display_role }}</span></td>
                        <td><span class="badge bg-success">active</span></td>
                        <td>{{ $admin->created_at?->diffForHumans() ?? '-' }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editAdminModal{{ $admin->id }}">
                                    <i class="bi bi-pencil me-1"></i>Edit
                                </button>
                                @if((int) $admin->id !== (int) auth()->id())
                                    <form method="POST" action="{{ route('admin.admins.delete', $admin->id) }}" onsubmit="return confirm('Delete this admin?')" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash me-1"></i>Delete
                                        </button>
                                    </form>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled>
                                        <i class="bi bi-trash me-1"></i>Delete
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No admin users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('modals')
<div class="modal fade" id="addAdminModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.admins.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" name="phone" value="{{ old('phone') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Country Code</label>
                        <input type="text" class="form-control" name="country_code" value="{{ old('country_code', '20') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Admin</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($admins as $admin)
<div class="modal fade" id="editAdminModal{{ $admin->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Admin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.admins.update', $admin->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="name" value="{{ old('name', $admin->name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" value="{{ old('email', $admin->email) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" name="phone" value="{{ old('phone', $admin->phone) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Country Code</label>
                        <input type="text" class="form-control" name="country_code" value="{{ old('country_code', $admin->country_code) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-control" name="password" placeholder="Leave empty to keep current password">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Admin</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection

@section('scripts')
@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalElement = document.getElementById('addAdminModal');
        if (modalElement) {
            new bootstrap.Modal(modalElement).show();
        }
    });
</script>
@endif
@endsection
