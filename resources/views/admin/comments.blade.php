@extends('layouts.admin')

@section('title', 'Quản lý Bình luận')
@section('page-title', 'Quản lý Bình luận')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Danh sách Bình luận</h3>
                    <div class="card-tools">
                        <form method="GET" action="{{ route('admin.comments') }}" class="d-flex gap-2">
                            <input type="text" name="search" class="form-control form-control-sm"
                                placeholder="Tìm theo nội dung..." value="{{ $search }}" style="width: 200px;">
                            <input type="text" name="user_search" class="form-control form-control-sm"
                                placeholder="Tìm theo user..." value="{{ $userSearch }}" style="width: 200px;">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="bi bi-search"></i> Tìm
                            </button>
                            @if($search || $userSearch)
                                <a href="{{ route('admin.comments') }}" class="btn btn-sm btn-secondary">
                                    <i class="bi bi-x"></i> Xóa
                                </a>
                            @endif
                        </form>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th style="width: 50px">ID</th>
                                <th>Nội dung</th>
                                <th style="width: 150px">User</th>
                                <th style="width: 150px">Truyện</th>
                                <th style="width: 120px">Chapter</th>
                                <th style="width: 80px">Likes</th>
                                <th style="width: 100px">Replies</th>
                                <th style="width: 150px">Ngày đăng</th>
                                <th style="width: 100px">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($comments as $comment)
                                @php
                                    $isReply = $comment->parent_id !== null;
                                @endphp
                                <tr>
                                    <td>{{ $comment->id }}</td>
                                    <td>
                                        <div style="max-width: 400px; overflow: hidden; text-overflow: ellipsis;">
                                            @if($isReply)
                                                <span class="badge text-bg-info me-1">Reply</span>
                                            @endif
                                            {{ Str::limit($comment->content, 100) }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($comment->user)
                                            <div>
                                                <strong>{{ $comment->user->name }}</strong><br>
                                                <small class="text-muted">{{ $comment->user->email }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">User đã xóa</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($comment->manga)
                                            <a href="{{ route('manga.detail', ['slug' => $comment->manga->slug]) }}" target="_blank"
                                                class="text-decoration-none">
                                                {{ Str::limit($comment->manga->title, 30) }}
                                            </a>
                                        @else
                                            <span class="text-muted">Truyện đã xóa</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($comment->chapter && !empty($comment->chapter->slug))
                                            <a href="{{ route('manga.chapter', ['mangaSlug' => $comment->manga->slug ?? '', 'chapterSlug' => $comment->chapter->slug]) }}"
                                                target="_blank" class="text-decoration-none">
                                                {{ $comment->chapter->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge text-bg-primary">{{ $comment->likes_count ?? 0 }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge text-bg-secondary">{{ $comment->replies()->count() }}</span>
                                    </td>
                                    <td>
                                        {{ $comment->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger delete-comment"
                                            data-comment-id="{{ $comment->id }}"
                                            data-comment-content="{{ Str::limit($comment->content, 50) }}">
                                            <i class="bi bi-trash"></i> Xóa
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <span class="text-muted">Không có bình luận nào</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($comments->hasPages() || $comments->total() > 20)
                    <div class="card-footer">
                        @if($comments->hasPages())
                            {{ $comments->links() }}
                        @endif
                        @if($comments->total() > 20)
                            <div class="text-muted text-center mt-2">
                                Hiển thị {{ $comments->firstItem() }} - {{ $comments->lastItem() }} trong tổng số
                                {{ $comments->total() }} bình luận
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('.delete-comment').on('click', function (e) {
                e.preventDefault();
                const commentId = $(this).data('comment-id');
                const commentContent = $(this).data('comment-content');

                if (confirm('Bạn chắc chắn muốn xóa bình luận này?\n\nNội dung: ' + commentContent + '\n\n(Lưu ý: Tất cả replies và likes của bình luận này cũng sẽ bị xóa)')) {
                    const $btn = $(this);
                    $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Đang xóa...');

                    $.ajax({
                        url: '/admin/comments/' + commentId + '/delete',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (response) {
                            if (response.status === 'success') {
                                showToast('Xóa bình luận thành công');
                                setTimeout(() => location.reload(), 1000);
                            } else {
                                showToast('Có lỗi xảy ra: ' + (response.message || ''), 'danger');
                                $btn.prop('disabled', false).html('<i class="bi bi-trash"></i> Xóa');
                            }
                        },
                        error: function (xhr) {
                            let errorMsg = 'Có lỗi xảy ra';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            showToast(errorMsg, 'danger');
                            $btn.prop('disabled', false).html('<i class="bi bi-trash"></i> Xóa');
                        }
                    });
                }
            });
        });
    </script>
@endpush