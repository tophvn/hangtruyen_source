@extends('layouts.admin')

@section('title', 'Quản lý Tin tức')
@section('page-title', 'Quản lý Tin tức')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Danh sách Tin tức</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.posts.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Tạo bài viết mới
                    </a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 50px">ID</th>
                            <th>Tiêu đề</th>
                            <th>Slug</th>
                            <th>Nổi bật</th>
                            <th>Trạng thái</th>
                            <th>Ngày đăng</th>
                            <th style="width: 150px">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($posts as $post)
                            <tr>
                                <td>{{ $post->id }}</td>
                                <td>{{ $post->title }}</td>
                                <td><small class="text-muted">{{ $post->slug }}</small></td>
                                <td>
                                    @if($post->is_featured)
                                        <span class="badge text-bg-warning">Nổi bật</span>
                                    @else
                                        <span class="badge text-bg-secondary">Bình thường</span>
                                    @endif
                                </td>
                                <td>
                                    @if($post->is_active)
                                        <span class="badge text-bg-success">Đã xuất bản</span>
                                    @else
                                        <span class="badge text-bg-danger">Nháp</span>
                                    @endif
                                </td>
                                <td>{{ $post->published_at ? $post->published_at->format('d/m/Y H:i') : ($post->created_at ? $post->created_at->format('d/m/Y H:i') : '-') }}</td>
                                <td>
                                    <a href="{{ route('admin.posts.edit', $post->id) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i> Sửa
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger delete-post" data-post-id="{{ $post->id }}">
                                        <i class="bi bi-trash"></i> Xóa
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Chưa có bài viết nào</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                
                <div class="mt-3">
                    {{ $posts->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.delete-post').on('click', function() {
            if (!confirm('Bạn có chắc chắn muốn xóa bài viết này?')) {
                return;
            }
            
            const postId = $(this).data('post-id');
            
            $.ajax({
                url: `/admin/posts/${postId}`,
                method: 'DELETE',
                success: function(response) {
                    if (response.status === 'success') {
                        alert('Đã xóa bài viết thành công');
                        location.reload();
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Có lỗi xảy ra';
                    alert(message);
                }
            });
        });
    });
</script>
@endpush
