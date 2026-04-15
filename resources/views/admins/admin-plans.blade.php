@extends('admins.layouts.master')

@section('title', 'Subscription Plans - Admin Panel')
@section('page_title', 'Subscription Plans')
@section('page_subtitle', 'Manage subscription plans and pricing')

@section('content')
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="row g-4" id="plansContainer">
    <div class="col-lg-4">
        <div class="dashboard-card h-100 d-flex align-items-center justify-content-center text-center border border-dashed"
             style="border-style: dashed !important; min-height: 420px; cursor: pointer;"
             data-bs-toggle="modal"
             data-bs-target="#createPlanModal">
            <div>
                <div class="rounded-circle border border-4 border-success d-inline-flex align-items-center justify-content-center"
                     style="width:74px;height:74px;">
                    <i class="bi bi-plus-lg text-success fs-1"></i>
                </div>
                <h4 class="mt-3 mb-2">Add New Plan</h4>
                <p class="text-muted mb-0">Create a new subscription plan</p>
            </div>
        </div>
    </div>

    @foreach($plans as $plan)
        <div class="col-lg-4">
            <div class="dashboard-card h-100 {{ !$plan['active'] ? 'opacity-75' : '' }}">
                @if(!$plan['active'])
                    <div class="position-absolute top-0 end-0 m-3">
                        <span class="badge bg-secondary">Inactive</span>
                    </div>
                @endif

                <div class="text-center mb-3">
                    <span class="badge bg-{{ $plan['color'] }} mb-2 px-3 py-2">{{ strtoupper($plan['name']) }}</span>
                    <h4 class="text-muted mb-2">{{ $plan['description'] }}</h4>
                    <h2 class="fw-bold mb-0">EGP {{ number_format($plan['monthly_price'], 2) }}<small class="text-muted fs-6">/month</small></h2>
                    <small class="text-muted">or EGP {{ number_format($plan['yearly_price'], 2) }}/year</small>
                </div>

                <div class="border rounded p-3 mb-3">
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <div class="fw-bold text-primary fs-4">{{ $plan['max_instances'] }}</div>
                            <small class="text-muted">Instances</small>
                        </div>
                        <div class="col-6">
                            <div class="fw-bold text-primary fs-4">{{ $plan['subscribers'] }}</div>
                            <small class="text-muted">Subscribers</small>
                        </div>
                    </div>
                </div>

                <ul class="list-unstyled mb-3">
                    @foreach($plan['features'] as $feature)
                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>{{ $feature }}</li>
                    @endforeach
                </ul>

                <div class="d-grid gap-2 mt-auto">
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editPlanModal{{ $plan['id'] }}">
                        <i class="bi bi-pencil me-2"></i>Edit Plan
                    </button>
                    <form method="POST" action="{{ route('admin.plans.toggle', $plan['id']) }}">
                        @csrf
                        <button class="btn btn-outline-{{ $plan['active'] ? 'warning' : 'success' }} w-100">
                            <i class="bi bi-{{ $plan['active'] ? 'pause' : 'play' }}-circle me-2"></i>
                            {{ $plan['active'] ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="editPlanModal{{ $plan['id'] }}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Plan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('admin.plans.update', $plan['id']) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Plan Name</label>
                                    <input type="text" class="form-control" name="name" value="{{ $plan['name'] }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Monthly Price (EGP)</label>
                                    <input type="number" class="form-control" name="monthly_price" step="0.01" value="{{ $plan['monthly_price'] }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Yearly Price (EGP)</label>
                                    <input type="number" class="form-control" name="yearly_price" step="0.01" value="{{ $plan['yearly_price'] }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Max Instances</label>
                                    <input type="number" class="form-control" name="max_instances" min="1" value="{{ $plan['max_instances'] }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Max Messages/Month</label>
                                    <input type="number" class="form-control" name="max_messages" min="0" value="{{ $plan['max_messages'] }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Max Campaigns</label>
                                    <input type="number" class="form-control" name="max_campaigns" min="0" value="{{ $plan['max_campaigns'] }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Plan Color</label>
                                    <select class="form-select" name="color" required>
                                        <option value="secondary" {{ $plan['color'] === 'secondary' ? 'selected' : '' }}>Secondary (Gray)</option>
                                        <option value="primary" {{ $plan['color'] === 'primary' ? 'selected' : '' }}>Primary (Blue)</option>
                                        <option value="success" {{ $plan['color'] === 'success' ? 'selected' : '' }}>Success (Green)</option>
                                        <option value="warning" {{ $plan['color'] === 'warning' ? 'selected' : '' }}>Warning (Yellow)</option>
                                        <option value="danger" {{ $plan['color'] === 'danger' ? 'selected' : '' }}>Danger (Red)</option>
                                        <option value="info" {{ $plan['color'] === 'info' ? 'selected' : '' }}>Info (Cyan)</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" rows="3">{{ $plan['description'] }}</textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Features (one per line)</label>
                                    <textarea class="form-control" name="features" rows="4">{{ $plan['features_text'] }}</textarea>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $plan['active'] ? 'checked' : '' }}>
                                        <label class="form-check-label">Active Plan</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Plan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="modal fade" id="createPlanModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.plans.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Plan Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Monthly Price (EGP)</label>
                            <input type="number" class="form-control" name="monthly_price" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Yearly Price (EGP)</label>
                            <input type="number" class="form-control" name="yearly_price" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Max Instances</label>
                            <input type="number" class="form-control" name="max_instances" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Max Messages/Month</label>
                            <input type="number" class="form-control" name="max_messages" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Max Campaigns</label>
                            <input type="number" class="form-control" name="max_campaigns" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Plan Color</label>
                            <select class="form-select" name="color" required>
                                <option value="secondary">Secondary (Gray)</option>
                                <option value="primary">Primary (Blue)</option>
                                <option value="success">Success (Green)</option>
                                <option value="warning">Warning (Yellow)</option>
                                <option value="danger">Danger (Red)</option>
                                <option value="info">Info (Cyan)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Features (one per line)</label>
                            <textarea class="form-control" name="features" rows="4">Feature 1
                             Feature 2
                             Feature 3</textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                                <label class="form-check-label">Active Plan</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
