@extends('layouts.admin')

@section('title', 'Thống kê & Phân tích')
@section('page-title', 'Thống kê & Phân tích')

@section('content')
    <!-- Overview Charts -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h3 class="card-title fw-bold m-0">
                        <i class="bi bi-graph-up text-primary me-2"></i>Lượt xem 30 ngày qua
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="viewsChart" style="min-height: 300px; width: 100%;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h3 class="card-title fw-bold m-0">
                        <i class="bi bi-trophy text-warning me-2"></i>Top 10 Truyện tháng này
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($topMangas as $index => $stat)
                            <div class="list-group-item d-flex align-items-center py-3">
                                <div class="me-3 fs-4 fw-bold text-muted" style="width: 30px;">{{ $index + 1 }}</div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="fw-bold text-dark text-truncate">{{ $stat->manga->title ?? 'N/A' }}</div>
                                    <div class="small text-muted">{{ number_format($stat->total_views) }} lượt xem</div>
                                </div>
                                <div class="ms-2">
                                    <span
                                        class="badge bg-primary-subtle text-primary rounded-pill">+{{ round(($stat->total_views / max(1, $dailyStats->sum('total_views'))) * 100, 1) }}%</span>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted">Chưa có dữ liệu</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h3 class="card-title fw-bold m-0">
                        <i class="bi bi-table text-info me-2"></i>Chi tiết lượt xem theo ngày
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-uppercase small fw-bold">
                                <tr>
                                    <th class="ps-3">Ngày</th>
                                    <th class="text-center">Tổng lượt xem</th>
                                    <th class="text-center">So với ngày trước</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $prevViews = 0; @endphp
                                @foreach($dailyStats->reverse() as $stat)
                                    <tr>
                                        <td class="ps-3 fw-bold">{{ $stat->view_date->format('d/m/Y') }}</td>
                                        <td class="text-center">{{ number_format($stat->total_views) }}</td>
                                        <td class="text-center">
                                            @if($prevViews > 0)
                                                @php $diff = $stat->total_views - $prevViews; @endphp
                                                @if($diff > 0)
                                                    <span class="text-success"><i
                                                            class="bi bi-caret-up-fill me-1"></i>{{ number_format($diff) }}</span>
                                                @elseif($diff < 0)
                                                    <span class="text-danger"><i
                                                            class="bi bi-caret-down-fill me-1"></i>{{ number_format($diff) }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @php $prevViews = $stat->total_views; @endphp
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function () {
            const ctx = document.getElementById('viewsChart').getContext('2d');
            const labels = {!! json_encode($dailyStats->pluck('view_date')->map(fn($d) => $d->format('d/m'))->toArray()) !!};
            const data = {!! json_encode($dailyStats->pluck('total_views')->toArray()) !!};

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Lượt xem',
                        data: data,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#0d6efd',
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { borderDash: [5, 5] }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        });
    </script>
@endpush