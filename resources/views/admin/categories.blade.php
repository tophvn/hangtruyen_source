@extends('layouts.admin')

@section('title', 'Quản lý Thể loại')
@section('page-title', 'Quản lý Thể loại')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h3 class="card-title fw-bold m-0">
                    <i class="bi bi-tags text-primary me-2"></i>Danh sách Thể loại
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-uppercase small fw-bold">
                            <tr>
                                <th class="ps-3" style="width: 80px">Thứ tự</th>
                                <th>Tên thể loại</th>
                                <th>Slug</th>
                                <th class="text-center">Số truyện</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="text-center pe-3" style="width: 150px">Cập nhật</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                            <tr>
                                <form class="update-category-form" data-id="{{ $category->id }}">
                                    @csrf
                                    <td class="ps-3">
                                        <input type="number" name="sort_order" value="{{ $category->sort_order }}" 
                                            class="form-control form-control-sm text-center" style="width: 60px;">
                                    </td>
                                    <td>
                                        <input type="text" name="name" value="{{ $category->name }}" 
                                            class="form-control form-control-sm fw-bold">
                                    </td>
                                    <td>
                                        <code class="small text-muted">{{ $category->slug }}</code>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border">{{ number_format($category->manga_count) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="is_active" 
                                                {{ $category->is_active ? 'checked' : '' }}>
                                        </div>
                                    </td>
                                    <td class="text-center pe-3">
                                        <button type="submit" class="btn btn-sm btn-primary px-3">
                                            <i class="bi bi-save me-1"></i> Lưu
                                        </button>
                                    </td>
                                </form>
                            </tr>
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
<script>
$(document).ready(function() {
    $('.update-category-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const id = form.data('id');
        const btn = form.find('button[type="submit"]');
        
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Đang lưu');
        
        $.ajax({
            url: `/admin/categories/${id}/update`,
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.status === 'success') {
                    showToast(response.message);
                } else {
                    showToast(response.message, 'danger');
                }
                btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Lưu');
            },
            error: function() {
                showToast('Có lỗi xảy ra', 'danger');
                btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Lưu');
            }
        });
    });
});
</script>
@endpush
