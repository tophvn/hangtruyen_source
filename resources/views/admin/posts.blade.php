@extends('layouts.admin')

@section('title', 'Quản lý Tin tức')
@section('page-title', 'Quản lý Tin tức')

@section('content')
    <!-- Statistics Bar -->
    <div class="row mb-4 g-3">
        <div class="col-6 col-md-4 col-lg">
            <div class="small-box text-bg-primary shadow-sm h-100 mb-0">
                <div class="inner p-3">
                    <h3 class="fw-bold mb-1">{{ number_format($stats['total'] ?? 0) }}</h3>
                    <p class="mb-0 opacity-75 text-nowrap">Tổng bài viết</p>
                </div>
                <div class="small-box-icon">
                    <i class="bi bi-journal-text"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="small-box text-bg-success shadow-sm h-100 mb-0">
                <div class="inner p-3">
                    <h3 class="fw-bold mb-1">{{ number_format($stats['published'] ?? 0) }}</h3>
                    <p class="mb-0 opacity-75 text-nowrap">Đã xuất bản</p>
                </div>
                <div class="small-box-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="small-box text-bg-danger shadow-sm h-100 mb-0">
                <div class="inner p-3">
                    <h3 class="fw-bold mb-1">{{ number_format($stats['draft'] ?? 0) }}</h3>
                    <p class="mb-0 opacity-75 text-nowrap">Bản nháp/Ẩn</p>
                </div>
                <div class="small-box-icon">
                    <i class="bi bi-eye-slash"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="small-box text-bg-warning shadow-sm h-100 mb-0">
                <div class="inner p-3">
                    <h3 class="fw-bold mb-1">{{ number_format($stats['featured'] ?? 0) }}</h3>
                    <p class="mb-0 opacity-75 text-nowrap">Nổi bật</p>
                </div>
                <div class="small-box-icon">
                    <i class="bi bi-star"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="small-box text-bg-info shadow-sm h-100 mb-0">
                <div class="inner p-3">
                    <h3 class="fw-bold mb-1">{{ number_format($stats['new_today'] ?? 0) }}</h3>
                    <p class="mb-0 opacity-75 text-nowrap">Mới hôm nay</p>
                </div>
                <div class="small-box-icon">
                    <i class="bi bi-plus-lg"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h3 class="card-title fw-bold m-0">
                            <i class="bi bi-list-ul me-2 text-primary"></i>Danh sách Tin tức
                        </h3>
                        <a href="{{ route('admin.posts.create') }}" class="btn btn-primary px-3 shadow-sm">
                            <i class="bi bi-plus-circle me-1"></i> Tạo bài viết
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="min-height: 400px;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-uppercase small fw-bold">
                                <tr>
                                    <th class="ps-3 text-center" style="width: 60px">ID</th>
                                    <th>Tiêu đề</th>
                                    <th class="text-center d-none d-lg-table-cell">Loại</th>
                                    <th class="text-center">Trạng thái</th>
                                    <th class="d-none d-md-table-cell">Ngày đăng</th>
                                    <th class="text-center pe-3" style="width: 180px">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($posts as $post)
                                    <tr>
                                        <td class="ps-3 text-center text-muted">#{{ $post->id }}</td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $post->title }}</div>
                                            <div class="small text-muted d-none d-sm-block">{{ Str::limit($post->slug, 50) }}</div>
                                        </td>
                                        <td class="text-center d-none d-lg-table-cell">
                                            @if ($post->is_featured)
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2">Nổi bật</span>
                                            @else
                                                <span class="badge bg-light text-muted border px-2">Thường</span>
                                            @endif
                                        </td>
                                        <td class="text-center" id="status-badge-{{ $post->id }}">
                                            @if ($post->is_active)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2">Công khai</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2">Tạm ẩn</span>
                                            @endif
                                        </td>
                                        <td class="d-none d-md-table-cell">
                                            <div class="small text-muted">
                                                <i class="bi bi-clock me-1"></i>
                                                {{ $post->published_at ? $post->published_at->format('d/m/Y H:i') : ($post->created_at ? $post->created_at->format('d/m/Y H:i') : '-') }}
                                            </div>
                                        </td>
                                        <td class="text-center pe-3">
                                            <div class="btn-group shadow-sm border rounded">
                                                <a href="{{ route('admin.posts.edit', $post->id) }}" class="btn btn-sm btn-white border-0" title="Sửa">
                                                    <i class="bi bi-pencil text-primary"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-white border-0 border-start toggle-status" 
                                                    data-id="{{ $post->id }}" 
                                                    id="toggle-btn-{{ $post->id }}"
                                                    title="{{ $post->is_active ? 'Ẩn bài' : 'Hiện bài' }}">
                                                    <i class="bi {{ $post->is_active ? 'bi-eye-slash-fill text-secondary' : 'bi-eye-fill text-success' }}"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-white border-0 border-start delete-post"
                                                    data-post-id="{{ $post->id }}" title="Xóa">
                                                    <i class="bi bi-trash text-danger"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">Chưa có bài viết nào</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top-0 py-3">
                    {{ $posts->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Delete post
            $('.delete-post').on('click', function () {
                if (!confirm('Bạn có chắc chắn muốn xóa bài viết này?')) {
                    return;
                }

                const postId = $(this).data('post-id');
                const row = $(this).closest('tr');

                $.ajax({
                    url: `/admin/posts/${postId}`,
                    method: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                            showToast(response.message || 'Đã xóa bài viết thành công');
                            row.fadeOut(300, function() { $(this).remove(); });
                        }
                    },
                    error: function (xhr) {
                        const message = xhr.responseJSON?.message || 'Có lỗi xảy ra';
                        showToast(message, 'danger');
                    }
                });
            });

            // Toggle status (Hide/Show)
            $('.toggle-status').on('click', function() {
                const id = $(this).data('id');
                const btn = $(this);
                const badge = $(`#status-badge-${id}`);
                const icon = btn.find('i');
                
                btn.prop('disabled', true);

                $.ajax({
                    url: `/admin/posts/${id}/toggle-status`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            showToast(response.message);
                            
                            if (response.is_active) {
                                badge.html('<span class="badge bg-success-subtle text-success border border-success-subtle px-2">Công khai</span>');
                                icon.removeClass('bi-eye-fill text-success').addClass('bi-eye-slash-fill text-secondary');
                                btn.attr('title', 'Ẩn bài');
                            } else {
                                badge.html('<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2">Tạm ẩn</span>');
                                icon.removeClass('bi-eye-slash-fill text-secondary').addClass('bi-eye-fill text-success');
                                btn.attr('title', 'Hiện bài');
                            }
                        }
                        btn.prop('disabled', false);
                    },
                    error: function() {
                        showToast('Có lỗi xảy ra', 'danger');
                        btn.prop('disabled', false);
                    }
                });
            });
        });
    </script>
@endpush