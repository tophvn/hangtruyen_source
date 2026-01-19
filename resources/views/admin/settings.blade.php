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
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#site" type="button" role="tab">
                            Website / Tổng quan
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#effects" type="button" role="tab">
                            Hiệu ứng
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#social" type="button" role="tab">
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
                    <div class="tab-pane fade show active" id="site" role="tabpanel">
                        <form id="site-form" enctype="multipart/form-data">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="site_name" class="form-label">Tên trang (Site name)</label>
                                    <input type="text" class="form-control" id="site_name" name="site_name" value="{{ $settings['site_name'] ?? '' }}" placeholder="HangTruyen">
                                    <small class="form-text text-muted">Dùng làm cấu hình mặc định cho phần meta/head (sẽ dùng ở các trang sau).</small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="site_description" class="form-label">Description</label>
                                    @php
                                        $siteNameForDefault = !empty($settings['site_name']) ? $settings['site_name'] : 'HangTruyen';
                                        $defaultSiteDescription = $siteNameForDefault . ' - Website đọc truyện tranh online miễn phí hàng đầu Việt Nam. Cập nhật truyện manga, manhua, manhwa mới nhất mỗi ngày. Đọc truyện full, không quảng cáo, chất lượng cao. Hàng nghìn truyện tranh hot trending đang chờ bạn khám phá.';
                                    @endphp
                                    <textarea class="form-control" id="site_description" name="site_description" rows="3" placeholder="{{ $defaultSiteDescription }}">{{ $settings['site_description'] ?? '' }}</textarea>
                                    <div class="d-flex align-items-center justify-content-between mt-2">
                                        <small class="form-text text-muted">Để trống sẽ dùng mặc định.</small>
                                        <button type="button" class="btn btn-sm btn-outline-danger js-reset-text" data-target="description">
                                            <i class="bi bi-arrow-counterclockwise"></i> Khôi phục mặc định
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="site_keywords" class="form-label">Keywords</label>
                                    @php
                                        $defaultSiteKeywords = 'đọc truyện tranh, truyện tranh online, manga online, manhua online, manhwa online, đọc truyện miễn phí, truyện tranh mới nhất, hangtruyen, đọc manga, đọc manhua, truyện full, truyện hot, truyện trending, truyện tranh việt nam';
                                    @endphp
                                    <textarea class="form-control" id="site_keywords" name="site_keywords" rows="2" placeholder="{{ $defaultSiteKeywords }}">{{ $settings['site_keywords'] ?? '' }}</textarea>
                                    <div class="d-flex align-items-center justify-content-between mt-2">
                                        <small class="form-text text-muted">Để trống sẽ dùng mặc định.</small>
                                        <button type="button" class="btn btn-sm btn-outline-danger js-reset-text" data-target="keywords">
                                            <i class="bi bi-arrow-counterclockwise"></i> Khôi phục mặc định
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-12">
                                    <div class="alert alert-info mb-0">
                                        Ảnh upload sẽ được tự động “convert” về đúng kích thước mẫu: <code>favicon/mini-logo: 49x54</code>, <code>logo/logo-dark: 300x108</code>.
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Favicon (favicon.png)</label>
                                    <div class="d-flex align-items-center gap-3">
                                        <img id="preview-favicon" src="{{ $settings['favicon_path'] ?? '/images/favicon.png' }}" alt="favicon" style="width: 48px; height: 48px; object-fit: contain; border: 1px solid #ddd; border-radius: 6px;">
                                        <input type="file" class="form-control" id="favicon" name="favicon" accept="image/png">
                                    </div>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-danger js-reset-image" data-target="favicon">
                                            <i class="bi bi-x-circle"></i> Xóa ảnh (về mặc định)
                                        </button>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Mini logo (mini-logo.png)</label>
                                    <div class="d-flex align-items-center gap-3">
                                        <img id="preview-mini-logo" src="{{ $settings['mini_logo_path'] ?? '/images/mini-logo.png' }}" alt="mini-logo" style="width: 48px; height: 48px; object-fit: contain; border: 1px solid #ddd; border-radius: 6px;">
                                        <input type="file" class="form-control" id="mini_logo" name="mini_logo" accept="image/png">
                                    </div>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-danger js-reset-image" data-target="mini_logo">
                                            <i class="bi bi-x-circle"></i> Xóa ảnh (về mặc định)
                                        </button>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Logo (logo.png)</label>
                                    <div class="d-flex align-items-center gap-3">
                                        <img id="preview-logo" src="{{ $settings['logo_path'] ?? '/images/logo.png' }}" alt="logo" style="width: 120px; height: 48px; object-fit: contain; border: 1px solid #ddd; border-radius: 6px;">
                                        <input type="file" class="form-control" id="logo" name="logo" accept="image/png">
                                    </div>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-danger js-reset-image" data-target="logo">
                                            <i class="bi bi-x-circle"></i> Xóa ảnh (về mặc định)
                                        </button>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Logo dark (logo-dark.png)</label>
                                    <div class="d-flex align-items-center gap-3">
                                        <img id="preview-logo-dark" src="{{ $settings['logo_dark_path'] ?? '/images/logo-dark.png' }}" alt="logo-dark" style="width: 120px; height: 48px; object-fit: contain; border: 1px solid #ddd; border-radius: 6px;">
                                        <input type="file" class="form-control" id="logo_dark" name="logo_dark" accept="image/png">
                                    </div>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-danger js-reset-image" data-target="logo_dark">
                                            <i class="bi bi-x-circle"></i> Xóa ảnh (về mặc định)
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Lưu cấu hình Website
                                    </button>
                                    <button type="button" class="btn btn-outline-danger ms-2 js-reset-image" data-target="all">
                                        <i class="bi bi-arrow-counterclockwise"></i> Khôi phục ảnh
                                    </button>
                                    <button type="button" class="btn btn-outline-danger ms-2 js-reset-text" data-target="all">
                                        <i class="bi bi-arrow-counterclockwise"></i> Khôi phục Description/Keywords
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="social" role="tabpanel">
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
                                    <label for="youtube_url" class="form-label">YouTube URL:</label>
                                    <input type="url" class="form-control" id="youtube_url" name="youtube_url" value="{{ $settings['youtube_url'] ?? '' }}" placeholder="https://youtube.com/...">
                                    <small class="form-text text-muted">Link YouTube hiển thị ở footer</small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="github_url" class="form-label">GitHub URL:</label>
                                    <input type="url" class="form-control" id="github_url" name="github_url" value="{{ $settings['github_url'] ?? '' }}" placeholder="https://github.com/...">
                                    <small class="form-text text-muted">Link GitHub hiển thị ở footer</small>
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
                    
                    <div class="tab-pane fade" id="effects" role="tabpanel">
                        <form id="effect-form">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="site_effect" class="form-label">Hiệu ứng cho website</label>
                                    <select class="form-select" id="site_effect" name="site_effect">
                                        @foreach($effects ?? [] as $key => $label)
                                            <option value="{{ $key }}" {{ ($currentEffect ?? 'none') === $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted d-block mt-2">
                                        Hiệu ứng sẽ áp dụng trên toàn bộ website<br>
                                    </small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Lưu hiệu ứng
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
        const resizedFiles = {};
        const imageConfigs = {
            favicon: { width: 49, height: 54, preview: '#preview-favicon', filename: 'favicon.png' },
            mini_logo: { width: 49, height: 54, preview: '#preview-mini-logo', filename: 'mini-logo.png' },
            logo: { width: 300, height: 108, preview: '#preview-logo', filename: 'logo.png' },
            logo_dark: { width: 300, height: 108, preview: '#preview-logo-dark', filename: 'logo-dark.png' },
        };

        function fileToDataURL(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(reader.result);
                reader.onerror = reject;
                reader.readAsDataURL(file);
            });
        }

        async function resizePngContain(file, targetW, targetH) {
            const dataUrl = await fileToDataURL(file);
            const img = new Image();
            img.src = dataUrl;

            await new Promise((resolve, reject) => {
                img.onload = resolve;
                img.onerror = reject;
            });

            const canvas = document.createElement('canvas');
            canvas.width = targetW;
            canvas.height = targetH;
            const ctx = canvas.getContext('2d');
            if (!ctx) {
                throw new Error('Canvas is not supported');
            }

            ctx.clearRect(0, 0, targetW, targetH);

            const srcW = img.naturalWidth || img.width;
            const srcH = img.naturalHeight || img.height;
            if (!srcW || !srcH) {
                throw new Error('Invalid image');
            }

            const scale = Math.min(targetW / srcW, targetH / srcH);
            const drawW = Math.round(srcW * scale);
            const drawH = Math.round(srcH * scale);
            const dx = Math.round((targetW - drawW) / 2);
            const dy = Math.round((targetH - drawH) / 2);
            ctx.drawImage(img, dx, dy, drawW, drawH);

            const blob = await new Promise((resolve) => {
                canvas.toBlob((b) => resolve(b), 'image/png');
            });

            if (!blob) {
                throw new Error('Failed to export PNG');
            }

            return { blob, previewUrl: canvas.toDataURL('image/png') };
        }

        async function handleResizeInput(inputName) {
            const input = document.querySelector(`input[name="${inputName}"]`);
            if (!input || !input.files || !input.files[0]) {
                return;
            }

            const file = input.files[0];
            if (file.type !== 'image/png') {
                alert('Vui lòng chọn ảnh PNG');
                input.value = '';
                return;
            }

            const cfg = imageConfigs[inputName];
            if (!cfg) return;

            try {
                const { blob, previewUrl } = await resizePngContain(file, cfg.width, cfg.height);
                resizedFiles[inputName] = blob;
                $(cfg.preview).attr('src', previewUrl);
            } catch (e) {
                console.warn('Resize failed, using original file', e);
                delete resizedFiles[inputName];
            }
        }

        Object.keys(imageConfigs).forEach((name) => {
            const input = document.querySelector(`input[name="${name}"]`);
            if (input) {
                input.addEventListener('change', () => handleResizeInput(name));
            }
        });

        $('#site-form').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData();
            formData.append('site_name', $('#site_name').val() || '');
            formData.append('site_description', $('#site_description').val() || '');
            formData.append('site_keywords', $('#site_keywords').val() || '');

            Object.keys(imageConfigs).forEach((name) => {
                const input = document.querySelector(`input[name="${name}"]`);
                if (!input) return;
                const cfg = imageConfigs[name];
                if (resizedFiles[name]) {
                    formData.append(name, resizedFiles[name], cfg.filename);
                } else if (input.files && input.files[0]) {
                    formData.append(name, input.files[0]);
                }
            });

            formData.append('_token', '{{ csrf_token() }}');

            $.ajax({
                url: '/admin/settings/site',
                method: 'POST',
                data: formData,
                headers: {
                    'Accept': 'application/json'
                },
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.status === 'success') {
                        const t = Date.now();
                        $('#preview-favicon').attr('src', '{{ $settings['favicon_path'] ?? '/images/favicon.png' }}' + '?t=' + t);
                        $('#preview-logo').attr('src', '{{ $settings['logo_path'] ?? '/images/logo.png' }}' + '?t=' + t);
                        $('#preview-logo-dark').attr('src', '{{ $settings['logo_dark_path'] ?? '/images/logo-dark.png' }}' + '?t=' + t);
                        $('#preview-mini-logo').attr('src', '{{ $settings['mini_logo_path'] ?? '/images/mini-logo.png' }}' + '?t=' + t);
                        alert(response.message || 'Đã lưu cấu hình Website thành công');
                    } else {
                        alert(response.message || 'Có lỗi xảy ra');
                    }
                },
                error: function(xhr) {
                    if (xhr && xhr.responseJSON) {
                        const message = xhr.responseJSON.message || 'Có lỗi xảy ra';
                        const errors = xhr.responseJSON.errors || null;
                        if (errors) {
                            const firstKey = Object.keys(errors)[0];
                            const firstMsg = (errors[firstKey] && errors[firstKey][0]) ? errors[firstKey][0] : message;
                            alert(firstMsg);
                            return;
                        }
                        alert(message);
                        return;
                    }
                    alert('Có lỗi xảy ra');
                }
            });
        });

        $(document).on('click', '.js-reset-image', function() {
            const target = $(this).data('target');
            if (!target) return;

            const confirmMsg = target === 'all'
                ? 'Bạn chắc chắn muốn khôi phục lại 4 ảnh mặc định?'
                : 'Bạn chắc chắn muốn xóa ảnh này và khôi phục ảnh mặc định?';

            if (!confirm(confirmMsg)) {
                return;
            }

            $.ajax({
                url: '/admin/settings/site/images/reset',
                method: 'POST',
                data: {
                    target: target,
                    _token: '{{ csrf_token() }}'
                },
                headers: {
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.status === 'success') {
                        ['favicon', 'logo', 'logo_dark', 'mini_logo'].forEach((k) => {
                            delete resizedFiles[k];
                            const input = document.querySelector(`input[name="${k}"]`);
                            if (input) input.value = '';
                        });

                        const t = Date.now();
                        $('#preview-favicon').attr('src', '/images/favicon.png?t=' + t);
                        $('#preview-logo').attr('src', '/images/logo.png?t=' + t);
                        $('#preview-logo-dark').attr('src', '/images/logo-dark.png?t=' + t);
                        $('#preview-mini-logo').attr('src', '/images/mini-logo.png?t=' + t);

                        alert(response.message || 'Đã khôi phục ảnh mặc định');
                    } else {
                        alert(response.message || 'Có lỗi xảy ra');
                    }
                },
                error: function(xhr) {
                    if (xhr && xhr.responseJSON) {
                        alert(xhr.responseJSON.message || 'Có lỗi xảy ra');
                        return;
                    }
                    alert('Có lỗi xảy ra');
                }
            });
        });

        $(document).on('click', '.js-reset-text', function() {
            const target = $(this).data('target');
            if (!target) return;

            const confirmMsg = target === 'all'
                ? 'Bạn chắc chắn muốn khôi phục Description/Keywords về mặc định?'
                : 'Bạn chắc chắn muốn khôi phục về mặc định?';

            if (!confirm(confirmMsg)) {
                return;
            }

            $.ajax({
                url: '/admin/settings/site/text/reset',
                method: 'POST',
                data: {
                    target: target,
                    _token: '{{ csrf_token() }}'
                },
                headers: {
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.status === 'success') {
                        $('#site_description').val('');
                        $('#site_keywords').val('');
                        alert(response.message || 'Đã khôi phục mặc định');
                    } else {
                        alert(response.message || 'Có lỗi xảy ra');
                    }
                },
                error: function(xhr) {
                    if (xhr && xhr.responseJSON) {
                        alert(xhr.responseJSON.message || 'Có lỗi xảy ra');
                        return;
                    }
                    alert('Có lỗi xảy ra');
                }
            });
        });

        $('#social-form').on('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                facebook_url: $('#facebook_url').val(),
                twitter_url: $('#twitter_url').val(),
                youtube_url: $('#youtube_url').val(),
                github_url: $('#github_url').val(),
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

        $('#effect-form').on('submit', function(e) {
            e.preventDefault();

            const formData = {
                site_effect: $('#site_effect').val(),
                _token: '{{ csrf_token() }}'
            };

            $.ajax({
                url: '/admin/settings/effect',
                method: 'POST',
                data: formData,
                headers: {
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message || 'Đã lưu cấu hình hiệu ứng thành công');
                    } else {
                        alert(response.message || 'Có lỗi xảy ra');
                    }
                },
                error: function(xhr) {
                    if (xhr && xhr.responseJSON) {
                        const message = xhr.responseJSON.message || 'Có lỗi xảy ra';
                        alert(message);
                        return;
                    }
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
                            $('#youtube_url').val('');
                            $('#github_url').val('');
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
