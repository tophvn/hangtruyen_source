@extends('layouts.admin')

@section('title', 'Quản lý Báo lỗi')
@section('page-title', 'Quản lý Báo lỗi')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-0 border-bottom-0">
                    <div class="d-flex align-items-center justify-content-between py-3">
                        <h3 class="card-title fw-bold m-0">
                            <i class="bi bi-exclamation-triangle text-danger me-2"></i>Danh sách Báo lỗi
                        </h3>
                    </div>
                    <!-- Tabs -->
                    <ul class="nav nav-tabs border-bottom-0">
                        <li class="nav-item">
                            <a class="nav-link {{ $tab === 'pending' ? 'active fw-bold border-bottom-3 border-primary' : 'text-muted' }}"
                                href="{{ route('admin.reports', ['tab' => 'pending']) }}">
                                <i class="bi bi-clock-history me-1"></i> Chưa xử lý
                                @if($tab === 'pending' && $reports->total() > 0)
                                    <span class="badge rounded-pill bg-danger ms-1">{{ $reports->total() }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $tab === 'resolved' ? 'active fw-bold border-bottom-3 border-primary' : 'text-muted' }}"
                                href="{{ route('admin.reports', ['tab' => 'resolved']) }}">
                                <i class="bi bi-check2-all me-1"></i> Đã xử lý
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="min-height: 450px;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-uppercase small fw-bold">
                                <tr>
                                    <th class="ps-3" style="width: 60px">ID</th>
                                    <th>Truyện / Chapter</th>
                                    <th>Người báo / Nội dung</th>
                                    <th class="text-center">Trạng thái</th>
                                    <th class="d-none d-md-table-cell">Ngày gửi</th>
                                    <th class="text-center pe-3" style="width: 150px">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reports as $report)
                                    <tr data-report-id="{{ $report->id }}">
                                        <td class="ps-3 text-muted">#{{ $report->id }}</td>
                                        <td>
                                            <div class="fw-bold text-dark">
                                                @if($report->manga)
                                                    <a href="{{ route('manga.detail', $report->manga->slug) }}" target="_blank"
                                                        class="text-decoration-none">
                                                        {{ $report->manga->title }}
                                                    </a>
                                                @else
                                                    <span class="text-danger">Manga ID: {{ $report->manga_id }}</span>
                                                @endif
                                            </div>
                                            @if($report->chapter_slug)
                                                <div class="small text-muted">
                                                    <span class="badge bg-light text-dark border fw-normal">Chap:
                                                        {{ $report->chapter_slug }}</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="small fw-bold text-primary mb-1">
                                                <i class="bi bi-person-circle me-1"></i>
                                                {{ $report->user ? $report->user->name : 'Khách' }}
                                            </div>
                                            <div class="text-wrap" style="max-width: 400px; font-size: 0.9rem;">
                                                {{ $report->content }}</div>
                                        </td>
                                        <td class="text-center">
                                            <select
                                                class="form-select form-select-sm status-select @if($report->status == 'pending') border-danger text-danger @elseif($report->status == 'resolved') border-success text-success @endif"
                                                data-id="{{ $report->id }}" style="width: 130px; margin: 0 auto;">
                                                <option value="pending" {{ $report->status == 'pending' ? 'selected' : '' }}>Chờ
                                                    xử lý</option>
                                                <option value="resolved" {{ $report->status == 'resolved' ? 'selected' : '' }}>Đã
                                                    xong</option>
                                                <option value="ignored" {{ $report->status == 'ignored' ? 'selected' : '' }}>Bỏ
                                                    qua</option>
                                            </select>
                                        </td>
                                        <td class="d-none d-md-table-cell">
                                            <div class="small text-muted">
                                                {{ $report->created_at->format('H:i d/m/Y') }}
                                            </div>
                                        </td>
                                        <td class="text-center pe-3">
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-report"
                                                data-id="{{ $report->id }}" title="Xóa">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                                Không có báo lỗi nào trong mục này
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white py-3 border-top">
                    {{ $reports->links() }}
                </div>
            </div>
        </div>
    </div>

    <style>
        .nav-tabs .nav-link.active {
            border-top: none;
            border-left: none;
            border-right: none;
            border-bottom: 3px solid var(--bs-primary);
            color: var(--bs-primary) !important;
        }

        .status-select {
            font-weight: 500;
        }
    </style>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Update status
            $('.status-select').on('change', function () {
                const id = $(this).data('id');
                const status = $(this).val();
                const select = $(this);
                const row = $(`tr[data-report-id="${id}"]`);
                const currentTab = '{{ $tab }}';

                select.prop('disabled', true);

                $.ajax({
                    url: `/admin/reports/${id}/status`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        status: status
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                            showToast(response.message);

                            // Nếu đang ở tab 'Chưa xử lý' mà chọn 'Đã xong' hoặc 'Bỏ qua' -> Xóa dòng
                            // Nếu đang ở tab 'Đã xử lý' mà chọn 'Chờ xử lý' -> Xóa dòng
                            if (currentTab === 'pending' && status !== 'pending') {
                                row.fadeOut(300, function () { $(this).remove(); });
                            } else if (currentTab === 'resolved' && status === 'pending') {
                                row.fadeOut(300, function () { $(this).remove(); });
                            }
                        } else {
                            showToast(response.message, 'danger');
                        }
                        select.prop('disabled', false);
                    },
                    error: function () {
                        showToast('Có lỗi xảy ra', 'danger');
                        select.prop('disabled', false);
                    }
                });
            });

            // Delete report
            $('.delete-report').on('click', function () {
                if (!confirm('Bạn có chắc chắn muốn xóa báo lỗi này?')) {
                    return;
                }

                const id = $(this).data('id');
                const row = $(`tr[data-report-id="${id}"]`);

                $.ajax({
                    url: `/admin/reports/${id}`,
                    method: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                            showToast(response.message);
                            row.fadeOut(300, function () { $(this).remove(); });
                        } else {
                            showToast(response.message, 'danger');
                        }
                    },
                    error: function () {
                        showToast('Có lỗi xảy ra', 'danger');
                    }
                });
            });
        });
    </script>
@endpush