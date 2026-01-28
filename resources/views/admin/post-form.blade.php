@extends('layouts.admin')

@section('title', $post ? 'Sửa Tin tức' : 'Tạo Tin tức mới')
@section('page-title', $post ? 'Sửa Tin tức' : 'Tạo Tin tức mới')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $post ? 'Sửa bài viết' : 'Tạo bài viết mới' }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.posts') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form id="post-form">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="title" class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title"
                                    value="{{ $post->title ?? '' }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="description" class="form-label">Mô tả ngắn</label>
                                <textarea class="form-control" id="description" name="description"
                                    rows="3">{{ $post->description ?? '' }}</textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="image" class="form-label">Ảnh đại diện (URL)</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="image" name="image"
                                        value="{{ $post->image ?? '' }}">
                                    <button type="button" class="btn btn-secondary" id="upload-image-btn">
                                        <i class="bi bi-upload"></i> Upload ảnh
                                    </button>
                                </div>
                                @if($post && $post->image)
                                    <img src="{{ $post->image }}" alt="Preview" class="img-thumbnail mt-2"
                                        style="max-width: 300px;">
                                @endif
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="author" class="form-label">Tác giả</label>
                                <input type="text" class="form-control" id="author" name="author"
                                    value="{{ $post->author ?? auth()->user()->name }}">
                            </div>
                            <div class="col-md-6">
                                <label for="published_at" class="form-label">Ngày đăng</label>
                                <input type="datetime-local" class="form-control" id="published_at" name="published_at"
                                    value="{{ $post && $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i') }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" {{ ($post && $post->is_featured) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_featured">
                                        Tin nổi bật
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" {{ ($post && $post->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Hiển thị
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="content" class="form-label">Nội dung <span class="text-danger">*</span></label>
                                <textarea id="content" name="content" class="form-control" rows="15"
                                    style="min-height: 400px;">{{ $post->content ?? '' }}</textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> {{ $post ? 'Cập nhật' : 'Tạo mới' }}
                                </button>
                                <a href="{{ route('admin.posts') }}" class="btn btn-secondary">Hủy</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <input type="file" id="image-upload-input" style="display: none;" accept="image/*">
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#content').summernote({
                height: 500,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                callbacks: {
                    onImageUpload: function (files) {
                        const formData = new FormData();
                        formData.append('image', files[0]);

                        $.ajax({
                            url: '/admin/posts/upload-image',
                            method: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function (response) {
                                if (response.status === 'success') {
                                    $('#content').summernote('insertImage', response.url);
                                } else {
                                    showToast(response.message || 'Upload thất bại', 'danger');
                                }
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'Upload thất bại';
                                showToast(message, 'danger');
                            }
                        });
                    }
                }
            });

            $('#upload-image-btn').on('click', function () {
                $('#image-upload-input').click();
            });

            $('#image-upload-input').on('change', function (e) {
                const file = e.target.files[0];
                if (!file) return;

                const formData = new FormData();
                formData.append('image', file);

                $.ajax({
                    url: '/admin/posts/upload-image',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.status === 'success') {
                            $('#image').val(response.url);
                            if ($('#image').next('img').length) {
                                $('#image').next('img').attr('src', response.url);
                            } else {
                                $('#image').after('<img src="' + response.url + '" alt="Preview" class="img-thumbnail mt-2" style="max-width: 300px;">');
                            }
                        } else {
                            showToast(response.message || 'Upload thất bại', 'danger');
                        }
                    },
                    error: function (xhr) {
                        const message = xhr.responseJSON?.message || 'Upload thất bại';
                        showToast(message, 'danger');
                    }
                });
            });

            $('#post-form').on('submit', function (e) {
                e.preventDefault();

                if (!$('#title').val().trim()) {
                    showToast('Vui lòng nhập tiêu đề', 'danger');
                    return;
                }

                let content = '';
                try {
                    if ($('#content').summernote('isEmpty')) {
                        content = '';
                    } else {
                        content = $('#content').summernote('code');
                    }
                } catch (err) {
                    console.error('Summernote error:', err);
                    content = $('#content').val() || '';
                }

                const formData = {
                    title: $('#title').val().trim(),
                    description: $('#description').val() || '',
                    content: content,
                    image: $('#image').val() || '',
                    author: $('#author').val() || '{{ auth()->user()->name }}',
                    published_at: $('#published_at').val() || '',
                    is_featured: $('#is_featured').is(':checked') ? 1 : 0,
                    is_active: $('#is_active').is(':checked') ? 1 : 0,
                };

                @if($post)
                    const url = '/admin/posts/{{ $post->id }}';
                    const method = 'PUT';
                @else
                                const url = '/admin/posts';
                    const method = 'POST';
                @endif

                console.log('Submitting:', { url, method, formData });

                formData._token = '{{ csrf_token() }}';
                if (method === 'PUT') {
                    formData._method = 'PUT';
                }

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function (response) {
                        console.log('Success:', response);
                        if (response.status === 'success') {
                            showToast(response.message);
                            setTimeout(() => {
                                window.location.href = '/admin/posts';
                            }, 1000);
                        } else {
                            showToast(response.message || 'Có lỗi xảy ra', 'danger');
                        }
                    },
                    error: function (xhr) {
                        console.error('Error:', xhr);
                        console.error('Status:', xhr.status);
                        console.error('Response:', xhr.responseText);
                        let message = 'Có lỗi xảy ra';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        } else if (xhr.responseText) {
                            try {
                                const error = JSON.parse(xhr.responseText);
                                message = error.message || message;
                            } catch (e) {
                                message = xhr.responseText.substring(0, 200);
                            }
                        }
                        showToast('Lỗi: ' + message, 'danger');
                    }
                });
            });
        });
    </script>
@endpush