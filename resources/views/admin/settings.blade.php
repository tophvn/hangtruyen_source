@extends('layouts.admin')

@section('title', 'Cài đặt')
@section('page-title', 'Cài đặt')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#social" type="button" role="tab">
                            Liên kết mạng xã hội
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#gtag" type="button" role="tab">
                            Google Tag
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="social" role="tabpanel">
                        <form id="social-form">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="facebook_url" class="form-label">Facebook URL:</label>
                                    <input type="url" class="form-control" id="facebook_url" name="facebook_url" value="{{ $settings['facebook_url'] }}" placeholder="https://facebook.com/...">
                                    <small class="form-text text-muted">Link Facebook hiển thị ở footer và trang Về chúng tôi</small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="twitter_url" class="form-label">X (Twitter) URL:</label>
                                    <input type="url" class="form-control" id="twitter_url" name="twitter_url" value="{{ $settings['twitter_url'] }}" placeholder="https://x.com/...">
                                    <small class="form-text text-muted">Link X/Twitter hiển thị ở footer và trang Về chúng tôi</small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="gmail_url" class="form-label">Gmail:</label>
                                    <input type="email" class="form-control" id="gmail_url" name="gmail_url" value="{{ $settings['gmail_url'] }}" placeholder="example@gmail.com">
                                    <small class="form-text text-muted">Email liên hệ hiển thị ở trang Về chúng tôi</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Lưu cấu hình
                                    </button>
                                    <button type="button" class="btn btn-danger" id="clear-social-btn">
                                        <i class="bi bi-trash"></i> Xóa tất cả
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <div class="tab-pane fade" id="gtag" role="tabpanel">
                        <form id="gtag-form">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="gtag_code" class="form-label">Google Tag (gtag.js) Code:</label>
                                    <textarea class="form-control" id="gtag_code" name="gtag_code" rows="10" placeholder="Dán code Google Tag Manager hoặc Google Analytics vào đây...">{{ $settings['gtag_code'] ?? '' }}</textarea>
                                    <small class="form-text text-muted">
                                        Dán toàn bộ code Google Tag (gtag.js) vào đây. Ví dụ:<br>
                                        <code>&lt;script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXX"&gt;&lt;/script&gt;<br>
                                        &lt;script&gt;...&lt;/script&gt;</code>
                                    </small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Lưu Google Tag
                                    </button>
                                    <button type="button" class="btn btn-danger" id="clear-gtag-btn">
                                        <i class="bi bi-trash"></i> Xóa
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#social-form').on('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                facebook_url: $('#facebook_url').val(),
                twitter_url: $('#twitter_url').val(),
                gmail_url: $('#gmail_url').val(),
                _token: '{{ csrf_token() }}'
            };
            
            $.ajax({
                url: '/admin/settings/social',
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.status === 'success') {
                        alert('Đã lưu cấu hình thành công');
                    } else {
                        alert('Có lỗi xảy ra');
                    }
                },
                error: function() {
                    alert('Có lỗi xảy ra');
                }
            });
        });
        
        $('#clear-social-btn').on('click', function() {
            if (confirm('Bạn chắc chắn muốn xóa tất cả cấu hình liên kết mạng xã hội?')) {
                $.ajax({
                    url: '/admin/settings/social/clear',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#facebook_url').val('');
                            $('#twitter_url').val('');
                            $('#gmail_url').val('');
                            alert('Đã xóa cấu hình thành công');
                        } else {
                            alert('Có lỗi xảy ra');
                        }
                    },
                    error: function() {
                        alert('Có lỗi xảy ra');
                    }
                });
            }
        });
        
        $('#gtag-form').on('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                gtag_code: $('#gtag_code').val(),
                _token: '{{ csrf_token() }}'
            };
            
            $.ajax({
                url: '/admin/settings/gtag',
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.status === 'success') {
                        alert('Đã lưu Google Tag thành công');
                    } else {
                        alert('Có lỗi xảy ra');
                    }
                },
                error: function() {
                    alert('Có lỗi xảy ra');
                }
            });
        });
        
        $('#clear-gtag-btn').on('click', function() {
            if (confirm('Bạn chắc chắn muốn xóa Google Tag?')) {
                $.ajax({
                    url: '/admin/settings/gtag/clear',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#gtag_code').val('');
                            alert('Đã xóa Google Tag thành công');
                        } else {
                            alert('Có lỗi xảy ra');
                        }
                    },
                    error: function() {
                        alert('Có lỗi xảy ra');
                    }
                });
            }
        });
    });
</script>
@endpush
