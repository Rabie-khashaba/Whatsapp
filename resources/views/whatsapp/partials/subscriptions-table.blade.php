<div class="dashboard-card">
    @if($items->isEmpty())
        <div class="empty-state">
            <i class="bi bi-credit-card fs-1 text-muted"></i>
            <p class="text-muted mt-2 mb-0">{{ $emptyText }}</p>
        </div>
    @else
        <div class="table-responsive p-0 shadow-none">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>PLAN NAME</th>
                        <th>TYPE</th>
                        <th>STATUS</th>
                        <th>PERIOD</th>
                        <th>START DATE</th>
                        <th>END DATE</th>
                        <th>PRICE</th>
                        <th>TAGS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $subscription)
                        @php
                            $status = $statusStyles[$subscription->display_status] ?? ['badge' => 'bg-secondary', 'text' => ucfirst($subscription->display_status)];
                            $planName = $subscription->plan?->name ?? ($customer->plan ?: 'Plan');
                            $tag = $subscription->billing_cycle ? ucfirst($subscription->billing_cycle) : '-';
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $planName }}</div>
                                <small class="text-muted">#{{ $subscription->id }}</small>
                            </td>
                            <td>{{ $subscription->display_type }}</td>
                            <td>
                                <span class="badge {{ $status['badge'] }}">{{ $status['text'] }}</span>
                            </td>
                            <td>{{ $subscription->display_period }}</td>
                            <td>{{ $subscription->start_date?->format('d M Y') }}</td>
                            <td>{{ $subscription->end_date?->format('d M Y') }}</td>
                            <td>{{ number_format((float) $subscription->price, 2) }} {{ $subscription->display_currency }}</td>
                            <td>{{ $tag }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
