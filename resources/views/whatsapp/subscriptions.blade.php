@extends('layouts.master')

@section('title', 'My Subscriptions - WhatsApp Campaign Platform')
@section('page-title', 'My Subscriptions')
@section('page-title-ar', 'Ø§Ø´ØªØ±Ø§ÙƒØ§ØªÙŠ')

@php
    $statusStyles = [
        'active' => ['badge' => 'bg-success', 'text' => 'Active'],
        'expired' => ['badge' => 'bg-danger', 'text' => 'Expired'],
        'pending' => ['badge' => 'bg-warning text-dark', 'text' => 'Pending'],
        'cancelled' => ['badge' => 'bg-secondary', 'text' => 'Cancelled'],
    ];

    $summaryCards = [
        ['label' => 'Total', 'value' => $summary['total'], 'class' => ''],
        ['label' => 'Active', 'value' => $summary['active'], 'class' => 'bg-success bg-opacity-10'],
        ['label' => 'Expired', 'value' => $summary['expired'], 'class' => 'bg-danger bg-opacity-10'],
        ['label' => 'Pending', 'value' => $summary['pending'], 'class' => 'bg-warning bg-opacity-10'],
        ['label' => 'Trial', 'value' => $summary['trial'], 'class' => 'bg-info bg-opacity-10'],
        ['label' => 'Business', 'value' => $summary['business'], 'class' => 'bg-secondary bg-opacity-10'],
    ];
@endphp

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Error:</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="dashboard-card mb-3">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <h4 class="mb-1">My Subscriptions</h4>
                <p class="text-muted mb-0 small">Track your active and previous subscription plans.</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#choosePlanModal">
                <i class="bi bi-stars me-2"></i>Subscribe Now
            </button>
        </div>
    </div>

    <div class="dashboard-card mb-4">
        <h5 class="mb-3">Subscription Summary</h5>
        <div class="row g-3">
            @foreach($summaryCards as $card)
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="stat-box {{ $card['class'] }}">
                        <div class="text-muted small mb-1">{{ $card['label'] }}</div>
                        <h3 class="mb-0">{{ $card['value'] }}</h3>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="dashboard-card mb-3">
        <ul class="nav nav-tabs border-0" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#personalTab" type="button">
                    Personal ({{ $personalSubscriptions->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#businessTab" type="button">
                    Business ({{ $businessSubscriptions->count() }})
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="personalTab">
            @include('whatsapp.partials.subscriptions-table', ['items' => $personalSubscriptions, 'statusStyles' => $statusStyles, 'emptyText' => 'No personal subscriptions yet.'])
        </div>

        <div class="tab-pane fade" id="businessTab">
            @include('whatsapp.partials.subscriptions-table', ['items' => $businessSubscriptions, 'statusStyles' => $statusStyles, 'emptyText' => 'No business subscriptions yet.'])
        </div>
    </div>
@endsection

@section('modals')
    <div class="modal fade" id="choosePlanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-1">Choose Your Plan</h5>
                        <p class="text-muted mb-0 small">Select a package, then we will take you to the payment step.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        @forelse($plans as $plan)
                            <div class="col-lg-4">
                                <div class="dashboard-card h-100 border-0 shadow-none subscription-plan-card">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <span class="badge bg-{{ $plan->color ?: 'primary' }} mb-2">{{ strtoupper($plan->name) }}</span>
                                            <h4 class="mb-1">{{ $plan->name }}</h4>
                                            <p class="text-muted small mb-0">{{ $plan->description ?: 'Subscription plan' }}</p>
                                        </div>
                                    </div>

                                    <div class="border rounded-3 p-3 mb-3 bg-light-subtle">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">Monthly</span>
                                            <strong>${{ number_format((float) ($plan->monthly_price ?? 0), 2) }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Yearly</span>
                                            <strong>${{ number_format((float) ($plan->yearly_price ?? 0), 2) }}</strong>
                                        </div>
                                    </div>

                                    <ul class="list-unstyled small text-muted mb-4">
                                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>{{ $plan->max_instances }} instances</li>
                                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>{{ number_format((int) $plan->max_messages) }} messages/month</li>
                                        <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>{{ $plan->max_campaigns > 0 ? $plan->max_campaigns . ' campaigns' : 'Unlimited campaigns' }}</li>
                                        @foreach(collect($plan->features)->take(3) as $feature)
                                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>{{ $feature }}</li>
                                        @endforeach
                                    </ul>

                                    <div class="d-grid gap-2 mt-auto">
                                        <form method="POST" action="{{ route('subscriptions.checkout') }}">
                                            @csrf
                                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                            <input type="hidden" name="billing_cycle" value="monthly">
                                            <button class="btn btn-primary w-100">Choose Monthly</button>
                                        </form>
                                        <form method="POST" action="{{ route('subscriptions.checkout') }}">
                                            @csrf
                                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                            <input type="hidden" name="billing_cycle" value="yearly">
                                            <button class="btn btn-outline-primary w-100">Choose Yearly</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="empty-state">
                                    <i class="bi bi-box-seam fs-1 text-muted"></i>
                                    <p class="text-muted mt-2 mb-0">No active plans are available right now.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
