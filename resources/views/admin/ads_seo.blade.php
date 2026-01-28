@extends('layouts.admin')

@section('title', 'Quản lý Quảng cáo')
@section('page-title', 'Quản lý Quảng cáo')

@section('content')
<form id="ads-form">
    @csrf
    <div class="row">
        <!-- Ad Management -->
        <div class="col-lg-8">
            <!-- Global Configuration -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h3 class="card-title fw-bold m-0">
                        <i class="bi bi-gear-fill text-primary me-2"></i>Cấu hình Chung
                    </h3>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center p-3 bg-light rounded border">
                        <div class="form-check form-switch flex-grow-1">
                            <input class="form-check-input" type="checkbox" name="ads_enabled" value="1" 
                                {{ ($settings['ads_enabled'] ?? '') == '1' ? 'checked' : '' }} style="transform: scale(1.2);">
                            <div class="ms-2">
                                <label class="form-check-label fw-bold d-block">Kích hoạt Quảng cáo toàn trang</label>
                                <span class="text-muted small">Bật hoặc tắt toàn bộ quảng cáo trên mọi trang web chỉ với 1 click.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ad Units -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h3 class="card-title fw-bold m-0">
                        <i class="bi bi-megaphone text-primary me-2"></i>Chi tiết các đơn vị Quảng cáo
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="accordion accordion-flush" id="adsAccordion">
                        
                        <!-- Popunder Ads -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePopunder">
                                    <div class="d-flex align-items-center w-100">
                                        <i class="bi bi-window-stack fs-5 text-indigo me-3"></i>
                                        <div class="flex-grow-1">
                                            <span class="fw-bold d-block">Popunder (Cửa sổ bật ngầm)</span>
                                            <span class="small text-muted">Mở cửa sổ mới khi người dùng click vào trang</span>
                                        </div>
                                        <div class="form-check form-switch me-3" onclick="event.stopPropagation()">
                                            <input class="form-check-input" type="checkbox" name="ads_popunder_enabled" value="1" 
                                                {{ ($settings['ads_popunder_enabled'] ?? '') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapsePopunder" class="accordion-collapse collapse" data-bs-parent="#adsAccordion">
                                <div class="accordion-body bg-light">
                                    <textarea name="ads_popunder_code" class="form-control text-monospace" rows="4" placeholder="Dán mã script Popunder vào đây...">{{ $settings['ads_popunder_code'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Push Notifications -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePush">
                                    <div class="d-flex align-items-center w-100">
                                        <i class="bi bi-bell-fill fs-5 text-warning me-3"></i>
                                        <div class="flex-grow-1">
                                            <span class="fw-bold d-block">Thông báo đẩy (Push Notifications)</span>
                                            <span class="small text-muted">Mời người dùng đăng ký nhận thông báo trình duyệt</span>
                                        </div>
                                        <div class="form-check form-switch me-3" onclick="event.stopPropagation()">
                                            <input class="form-check-input" type="checkbox" name="ads_push_enabled" value="1" 
                                                {{ ($settings['ads_push_enabled'] ?? '') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapsePush" class="accordion-collapse collapse" data-bs-parent="#adsAccordion">
                                <div class="accordion-body bg-light">
                                    <textarea name="ads_push_code" class="form-control text-monospace" rows="4" placeholder="Dán mã script Push Notification vào đây...">{{ $settings['ads_push_code'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Sticky Banners -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSticky">
                                    <div class="d-flex align-items-center w-100">
                                        <i class="bi bi-layout-sidebar-inset fs-5 text-info me-3"></i>
                                        <div class="flex-grow-1">
                                            <span class="fw-bold d-block">Biểu ngữ dính (Sticky Banner)</span>
                                            <span class="small text-muted">Cố định ở mép màn hình (Thường ở dưới cùng)</span>
                                        </div>
                                        <div class="form-check form-switch me-3" onclick="event.stopPropagation()">
                                            <input class="form-check-input" type="checkbox" name="ads_sticky_enabled" value="1" 
                                                {{ ($settings['ads_sticky_enabled'] ?? '') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapseSticky" class="accordion-collapse collapse" data-bs-parent="#adsAccordion">
                                <div class="accordion-body bg-light">
                                    <textarea name="ads_sticky_code" class="form-control text-monospace" rows="4" placeholder="Dán mã script Sticky Banner vào đây...">{{ $settings['ads_sticky_code'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Top Banner -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTop">
                                    <div class="d-flex align-items-center w-100">
                                        <i class="bi bi-arrow-up-square-fill fs-5 text-success me-3"></i>
                                        <div class="flex-grow-1">
                                            <span class="fw-bold d-block">Banner Đầu trang (Header)</span>
                                            <span class="small text-muted">Hiển thị ngay trên phần đầu của website</span>
                                        </div>
                                        <div class="form-check form-switch me-3" onclick="event.stopPropagation()">
                                            <input class="form-check-input" type="checkbox" name="ads_top_enabled" value="1" 
                                                {{ ($settings['ads_top_enabled'] ?? '') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapseTop" class="accordion-collapse collapse" data-bs-parent="#adsAccordion">
                                <div class="accordion-body bg-light">
                                    <textarea name="ad_banner_top" class="form-control text-monospace" rows="4" placeholder="Dán mã banner đầu trang vào đây...">{{ $settings['ad_banner_top'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Bottom Banner -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBottom">
                                    <div class="d-flex align-items-center w-100">
                                        <i class="bi bi-arrow-down-square-fill fs-5 text-danger me-3"></i>
                                        <div class="flex-grow-1">
                                            <span class="fw-bold d-block">Banner Cuối trang (Footer)</span>
                                            <span class="small text-muted">Hiển thị ở chân trang web</span>
                                        </div>
                                        <div class="form-check form-switch me-3" onclick="event.stopPropagation()">
                                            <input class="form-check-input" type="checkbox" name="ads_bottom_enabled" value="1" 
                                                {{ ($settings['ads_bottom_enabled'] ?? '') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapseBottom" class="accordion-collapse collapse" data-bs-parent="#adsAccordion">
                                <div class="accordion-body bg-light">
                                    <textarea name="ad_banner_bottom" class="form-control text-monospace" rows="4" placeholder="Dán mã banner cuối trang vào đây...">{{ $settings['ad_banner_bottom'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Chapter Banner -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseChapter">
                                    <div class="d-flex align-items-center w-100">
                                        <i class="bi bi-book-half fs-5 text-primary me-3"></i>
                                        <div class="flex-grow-1">
                                            <span class="fw-bold d-block">Banner Giữa trang đọc (Chapters)</span>
                                            <span class="small text-muted">Hiển thị lồng vào giữa nội dung chương truyện</span>
                                        </div>
                                        <div class="form-check form-switch me-3" onclick="event.stopPropagation()">
                                            <input class="form-check-input" type="checkbox" name="ads_chapter_enabled" value="1" 
                                                {{ ($settings['ads_chapter_enabled'] ?? '') == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapseChapter" class="accordion-collapse collapse" data-bs-parent="#adsAccordion">
                                <div class="accordion-body bg-light">
                                    <textarea name="ad_chapter_middle" class="form-control text-monospace" rows="4" placeholder="Dán mã banner giữa chương vào đây...">{{ $settings['ad_chapter_middle'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- General Tracking / Script Head -->
                        <div class="accordion-item border-bottom-0">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHead">
                                    <div class="d-flex align-items-center w-100">
                                        <i class="bi bi-code-slash fs-5 text-muted me-3"></i>
                                        <div class="flex-grow-1">
                                            <span class="fw-bold d-block">Mã Script bổ sung (&lt;head&gt;)</span>
                                            <span class="small text-muted">Mã theo dõi hoặc script quảng cáo chung khác</span>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapseHead" class="accordion-collapse collapse" data-bs-parent="#adsAccordion">
                                <div class="accordion-body bg-light">
                                    <textarea name="ad_script_header" class="form-control text-monospace" rows="4" placeholder="Mã script sẽ được chèn vào thẻ <head>...">{{ $settings['ad_script_header'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Actions -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4 sticky-top" style="top: 20px;">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-3"><i class="bi bi-shield-check me-2"></i>Hành động</h4>
                    <p class="text-muted small">Cấu hình quảng cáo sẽ được áp dụng ngay sau khi bạn nhấn nút lưu bên dưới.</p>
                    <button type="submit" class="btn btn-primary w-100 py-2 shadow-sm fs-5">
                        <i class="bi bi-save2 me-2"></i> Lưu cấu hình
                    </button>
                    
                    <div class="mt-4 p-3 border rounded bg-light">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-info-circle-fill text-info me-2"></i>
                            <span class="fw-bold small">Trạng thái hiện tại:</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Banner Top:</span>
                            <span class="badge {{ ($settings['ads_top_enabled'] ?? '') == '1' ? 'bg-success' : 'bg-secondary' }}">{{ ($settings['ads_top_enabled'] ?? '') == '1' ? 'Bật' : 'Tắt' }}</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Sticky Banner:</span>
                            <span class="badge {{ ($settings['ads_sticky_enabled'] ?? '') == '1' ? 'bg-success' : 'bg-secondary' }}">{{ ($settings['ads_sticky_enabled'] ?? '') == '1' ? 'Bật' : 'Tắt' }}</span>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span>Popunder:</span>
                            <span class="badge {{ ($settings['ads_popunder_enabled'] ?? '') == '1' ? 'bg-success' : 'bg-secondary' }}">{{ ($settings['ads_popunder_enabled'] ?? '') == '1' ? 'Bật' : 'Tắt' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<style>
    .accordion-button:not(.collapsed) {
        background-color: #f8f9fa;
        color: #000;
        box-shadow: none;
    }
    .accordion-button::after {
        margin-left: 0;
    }
    .text-indigo { color: #6610f2; }
    .form-check-input:checked {
        background-color: #198754;
        border-color: #198754;
    }
</style>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#ads-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const btn = form.find('button[type="submit"]');
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Đang cập nhật...');
        
        // Collect all data including checkboxes
        let formData = form.serializeArray();
        
        // Add checkboxes that are UNCHECKED (serializeArray only picks up checked ones)
        form.find('input[type="checkbox"]').each(function() {
            if (!this.checked) {
                formData.push({ name: this.name, value: '0' });
            }
        });
        
        $.ajax({
            url: `{{ route('admin.ads-seo.save') }}`,
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.status === 'success') {
                    showToast(response.message);
                    setTimeout(() => window.location.reload(), 1000); // Reload để cập nhật badge ở hành động
                } else {
                    showToast(response.message, 'danger');
                }
                btn.prop('disabled', false).html('<i class="bi bi-save2 me-2"></i> Lưu cấu hình');
            },
            error: function() {
                showToast('Có lỗi xảy ra', 'danger');
                btn.prop('disabled', false).html('<i class="bi bi-save2 me-2"></i> Lưu cấu hình');
            }
        });
    });
});
</script>
@endpush
