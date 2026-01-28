@extends('layouts.admin')

@section('title', 'Quản lý Truyện')
@section('page-title', 'Quản lý Truyện')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#overview" type="button"
                                role="tab">
                                Tổng quan
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#update" type="button" role="tab">
                                Update truyện
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#trending" type="button"
                                role="tab">
                                Top thịnh hành
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Tab 1: Tổng quan -->
                        <div class="tab-pane fade show active" id="overview" role="tabpanel">
                            <h5 class="mb-3">10 truyện mới update vào CSDL</h5>
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th style="width: 50px">ID</th>
                                        <th>Tiêu đề</th>
                                        <th>Slug</th>
                                        <th>Số chapter</th>
                                        <th>Trạng thái</th>
                                        <th>Update lúc</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentMangas as $manga)
                                        <tr>
                                            <td>{{ $manga->id }}</td>
                                            <td>{{ $manga->title }}</td>
                                            <td>{{ $manga->slug }}</td>
                                            <td>{{ $manga->chapters_count }}</td>
                                            <td>
                                                @if($manga->status === 'completed')
                                                    <span class="badge text-bg-success">Hoàn thành</span>
                                                @elseif($manga->status === 'ongoing')
                                                    <span class="badge text-bg-primary">Đang phát hành</span>
                                                @else
                                                    <span class="badge text-bg-secondary">{{ $manga->status }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $manga->updated_at->format('d/m/Y H:i:s') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">Chưa có truyện nào</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Tab 2: Update truyện -->
                        <div class="tab-pane fade" id="update" role="tabpanel">
                            <form id="crawl-form">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="list-type" class="form-label">Chọn loại truyện cần crawl:</label>
                                        <select class="form-select" id="list-type" name="list_type" required>
                                            <option value="truyen-moi">Truyện mới</option>
                                            <option value="sap-ra-mat">Sắp ra mắt</option>
                                            <option value="dang-phat-hanh">Đang phát hành</option>
                                            <option value="hoan-thanh">Hoàn thành</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="custom-pages"
                                                name="custom_pages">
                                            <label class="form-check-label" for="custom-pages">
                                                Tùy chỉnh số trang
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3" id="pages-config" style="display: none;">
                                    <div class="col-md-4">
                                        <label for="pages-input" class="form-label">Số trang (ví dụ: 1, 1-5, 1,3,5):</label>
                                        <input type="text" class="form-control" id="pages-input" name="pages"
                                            placeholder="1" disabled>
                                    </div>
                                    <div class="col-md-8 d-flex align-items-end">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="all-pages" name="all_pages">
                                            <label class="form-check-label" for="all-pages">
                                                Crawl toàn bộ (tự động chuyển trang)
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary" id="start-crawl-btn">
                                            <i class="bi bi-play-fill"></i> Bắt đầu crawl
                                        </button>
                                        <button type="button" class="btn btn-secondary" id="stop-crawl-btn"
                                            style="display: none;">
                                            <i class="bi bi-stop-fill"></i> Dừng
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <div id="crawl-logs" style="display: none;" class="mt-4">
                                <h5>Logs crawl:</h5>
                                <div class="card">
                                    <div class="card-body"
                                        style="max-height: 500px; overflow-y: auto; background-color: #1e1e1e; color: #d4d4d4; font-family: monospace; font-size: 12px;">
                                        <div id="log-content"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 3: Top thịnh hành -->
                        <div class="tab-pane fade" id="trending" role="tabpanel">
                            <h5 class="mb-3">Chọn 8 truyện hiển thị ở Top thịnh hành</h5>
                            <form id="trending-form">
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="trending-search" class="form-label">Tìm truyện:</label>
                                        <input type="text" class="form-control" id="trending-search"
                                            placeholder="Nhập tên hoặc slug truyện...">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <div id="trending-search-results"
                                            style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; display: none; background: #f8f9fa;">
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="form-label">Danh sách truyện đã chọn (tối đa 8):</label>
                                        <div id="trending-selected" class="border p-3"
                                            style="min-height: 100px; background: #f8f9fa;">
                                            <div class="alert alert-info mb-2">
                                                <i class="bi bi-info-circle"></i> Kéo và thả để thay đổi thứ tự hiển thị
                                            </div>
                                            @if(isset($trendingMangas) && $trendingMangas->count() > 0)
                                                <div id="sortable-mangas">
                                                    @foreach($trendingMangas as $manga)
                                                        <div class="d-flex align-items-center mb-2 p-2 border rounded selected-manga-item sortable-item"
                                                            data-slug="{{ $manga->slug }}" data-order="{{ $loop->index }}">
                                                            <div class="drag-handle me-2" style="cursor: move; color: #999;">
                                                                <i class="bi bi-grip-vertical"></i>
                                                            </div>
                                                            <img src="{{ $manga->cover_url ?: asset('images/pre-load1.png') }}"
                                                                style="width: 50px; height: 70px; object-fit: cover; margin-right: 10px;">
                                                            <div class="flex-grow-1">
                                                                <strong>{{ $manga->title }}</strong><br>
                                                                <small class="text-muted">{{ $manga->slug }}</small>
                                                            </div>
                                                            <button type="button"
                                                                class="btn btn-sm btn-danger remove-trending-manga"
                                                                data-slug="{{ $manga->slug }}">
                                                                <i class="bi bi-x"></i>
                                                            </button>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="text-muted">Chưa có truyện nào được chọn</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-save"></i> Lưu danh sách
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
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <style>
        .sortable-item {
            transition: all 0.3s ease;
        }

        .sortable-item:hover {
            background-color: #f0f0f0;
        }

        .sortable-ghost {
            opacity: 0.4;
            background-color: #e3f2fd;
        }

        .drag-handle {
            transition: color 0.2s ease;
        }

        .drag-handle:hover {
            color: #007bff !important;
        }

        .sortable-item.sortable-drag {
            background-color: #e3f2fd;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
    <script>
        $(document).ready(function () {
            let crawlRunning = false;
            let crawlInterval = null;
            $('#custom-pages').on('change', function () {
                if ($(this).is(':checked')) {
                    $('#pages-config').show();
                    $('#pages-input').prop('disabled', false);
                } else {
                    $('#pages-config').hide();
                    $('#pages-input').prop('disabled', true);
                    $('#all-pages').prop('checked', false);
                }
            });

            $('#all-pages').on('change', function () {
                if ($(this).is(':checked')) {
                    $('#pages-input').prop('disabled', true);
                } else {
                    $('#pages-input').prop('disabled', false);
                }
            });

            $('#crawl-form').on('submit', function (e) {
                e.preventDefault();
                e.stopPropagation();

                const updateTab = document.querySelector('#update');
                const updateTabButton = document.querySelector('button[data-bs-target="#update"]');
                if (updateTabButton && !updateTabButton.classList.contains('active')) {
                    const tab = new bootstrap.Tab(updateTabButton);
                    tab.show();
                }

                if (crawlRunning) {
                    return false;
                }

                const listType = $('#list-type').val();
                const customPages = $('#custom-pages').is(':checked');
                const allPages = $('#all-pages').is(':checked');
                const pages = $('#pages-input').val() || '1';

                const data = {
                    list_type: listType,
                    _token: '{{ csrf_token() }}'
                };

                if (customPages) {
                    if (allPages) {
                        data.all_pages = true;
                    } else {
                        data.pages = pages;
                    }
                } else {
                    data.pages = '1';
                }

                $('#crawl-logs').show();
                $('#log-content').html('');
                addLog('Bắt đầu crawl...', 'info');
                addLog(`Loại truyện: ${listType}`, 'info');
                if (allPages) {
                    addLog('Chế độ: Crawl toàn bộ', 'info');
                } else {
                    addLog(`Trang: ${pages}`, 'info');
                }

                $('#crawl-form input, #crawl-form select, #crawl-form button').prop('disabled', true);
                $('#start-crawl-btn').hide();
                $('#stop-crawl-btn').show();
                crawlRunning = true;

                startCrawl(data);
                return false;
            });

            function startCrawl(data) {
                $.ajax({
                    url: '/admin/mangas/crawl',
                    method: 'POST',
                    data: data,
                    success: function (response) {
                        if (response.status === 'success') {
                            addLog(`Job ID: ${response.job_id}`, 'success');
                            addLog('Đang crawl, vui lòng chờ...', 'info');

                            checkProgress(response.job_id);
                        } else {
                            addLog(`Lỗi: ${response.message}`, 'error');
                            stopCrawl();
                        }
                    },
                    error: function (xhr) {
                        console.error('Crawl error:', xhr);
                        const message = xhr.responseJSON?.message || xhr.statusText || 'Có lỗi xảy ra';
                        addLog(`Lỗi: ${message}`, 'error');
                        stopCrawl();
                    }
                });
            }

            function checkProgress(jobId) {
                if (!crawlRunning) return;

                crawlInterval = setInterval(function () {
                    $.ajax({
                        url: `/admin/mangas/crawl/progress/${jobId}`,
                        method: 'GET',
                        success: function (response) {
                            if (response.logs && response.logs.length > 0) {
                                response.logs.forEach(function (log) {
                                    addLog(log.message, log.type);
                                });
                            }

                            if (response.status === 'completed') {
                                addLog('Crawl hoàn tất!', 'success');
                                stopCrawl();
                            } else if (response.status === 'failed') {
                                addLog(`Lỗi: ${response.error || 'Unknown error'}`, 'error');
                                stopCrawl();
                            }
                        },
                        error: function (xhr) {
                            console.error('Progress check error:', xhr);
                        }
                    });
                }, 2000);
            }

            function stopCrawl() {
                crawlRunning = false;
                if (crawlInterval) {
                    clearInterval(crawlInterval);
                    crawlInterval = null;
                }
                $('#crawl-form input, #crawl-form select, #crawl-form button').prop('disabled', false);
                $('#start-crawl-btn').show();
                $('#stop-crawl-btn').hide();
            }

            $('#stop-crawl-btn').on('click', function () {
                addLog('Đã dừng crawl', 'warning');
                stopCrawl();
            });

            function addLog(message, type = 'info') {
                const timestamp = new Date().toLocaleTimeString('vi-VN');
                const color = {
                    'info': '#4dabf7',
                    'success': '#51cf66',
                    'error': '#ff6b6b',
                    'warning': '#ffd43b'
                }[type] || '#d4d4d4';

                const logLine = `<div style="color: ${color};">[${timestamp}] ${message}</div>`;
                $('#log-content').append(logLine);

                const logContainer = $('#crawl-logs .card-body');
                if (logContainer.length && logContainer[0]) {
                    logContainer.scrollTop(logContainer[0].scrollHeight);
                }
            }

            let selectedMangas = [];
            @if(isset($trendingMangas) && $trendingMangas->count() > 0)
                selectedMangas = @json($trendingMangas->pluck('slug')->toArray());
            @endif

            let searchTimeout = null;
            $('#trending-search').on('input', function () {
                const query = $(this).val();
                const resultsDiv = $('#trending-search-results');

                if (query.length < 2) {
                    resultsDiv.hide();
                    return;
                }

                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function () {
                    $.ajax({
                        url: '/admin/mangas/search',
                        method: 'GET',
                        data: { q: query },
                        success: function (response) {
                            if (response.length === 0) {
                                resultsDiv.html('<p class="text-muted">Không tìm thấy truyện nào</p>').show();
                                return;
                            }

                            let html = '';
                            response.forEach(function (manga) {
                                const isSelected = selectedMangas.includes(manga.slug);
                                const disabled = isSelected ? 'disabled' : '';
                                const btnText = isSelected ? 'Đã chọn' : 'Chọn';
                                const btnClass = isSelected ? 'btn-secondary' : 'btn-primary';

                                html += `
                                    <div class="d-flex align-items-center mb-2 p-2 border rounded">
                                        <img src="${manga.cover_url || '/images/pre-load1.png'}" style="width: 40px; height: 56px; object-fit: cover; margin-right: 10px;">
                                        <div class="flex-grow-1">
                                            <strong>${manga.title}</strong><br>
                                            <small class="text-muted">${manga.slug}</small>
                                        </div>
                                        <button type="button" class="btn btn-sm ${btnClass} select-trending-manga" data-slug="${manga.slug}" data-title="${manga.title}" data-cover="${manga.cover_url || '/images/pre-load1.png'}" ${disabled}>
                                            ${btnText}
                                        </button>
                                    </div>
                                `;
                            });
                            resultsDiv.html(html).show();
                        }
                    });
                }, 300);
            });

            $(document).on('click', '.select-trending-manga', function () {
                if (selectedMangas.length >= 8) {
                    showToast('Chỉ được chọn tối đa 8 truyện', 'danger');
                    return;
                }

                const slug = $(this).data('slug');
                const title = $(this).data('title');
                const cover = $(this).data('cover');

                if (selectedMangas.includes(slug)) {
                    return;
                }

                selectedMangas.push(slug);
                updateSelectedList();
                $('#trending-search').trigger('input');
            });

            $(document).on('click', '.remove-trending-manga', function () {
                const slug = $(this).data('slug');
                selectedMangas = selectedMangas.filter(s => s !== slug);
                updateSelectedList();
                $('#trending-search').trigger('input');
            });

            function updateSelectedList() {
                const selectedDiv = $('#trending-selected');

                if (selectedMangas.length === 0) {
                    selectedDiv.html('<div class="alert alert-info mb-2"><i class="bi bi-info-circle"></i> Kéo và thả để thay đổi thứ tự hiển thị</div><p class="text-muted">Chưa có truyện nào được chọn</p>');
                    return;
                }

                $.ajax({
                    url: '/admin/mangas/search',
                    method: 'GET',
                    data: { q: selectedMangas.join(',') },
                    success: function (response) {
                        let html = '<div class="alert alert-info mb-2"><i class="bi bi-info-circle"></i> Kéo và thả để thay đổi thứ tự hiển thị</div><div id="sortable-mangas">';
                        selectedMangas.forEach(function (slug, index) {
                            const manga = response.find(m => m.slug === slug);
                            if (manga) {
                                html += `
                                    <div class="d-flex align-items-center mb-2 p-2 border rounded selected-manga-item sortable-item" data-slug="${manga.slug}" data-order="${index}">
                                        <div class="drag-handle me-2" style="cursor: move; color: #999;">
                                            <i class="bi bi-grip-vertical"></i>
                                        </div>
                                        <img src="${manga.cover_url || '/images/pre-load1.png'}" style="width: 50px; height: 70px; object-fit: cover; margin-right: 10px;">
                                        <div class="flex-grow-1">
                                            <strong>${manga.title}</strong><br>
                                            <small class="text-muted">${manga.slug}</small>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-danger remove-trending-manga" data-slug="${manga.slug}">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                `;
                            }
                        });
                        html += '</div>';
                        selectedDiv.html(html);

                        initializeSortable();
                    }
                });
            }

            function initializeSortable() {
                const sortableEl = document.getElementById('sortable-mangas');
                if (sortableEl) {
                    new Sortable(sortableEl, {
                        animation: 150,
                        handle: '.drag-handle',
                        onEnd: function (evt) {
                            const items = sortableEl.querySelectorAll('.sortable-item');
                            const newOrder = [];
                            items.forEach(function (item) {
                                newOrder.push(item.dataset.slug);
                            });
                            selectedMangas = newOrder;

                            console.log('New order:', selectedMangas);
                        }
                    });
                }
            }

            $(document).ready(function () {
                setTimeout(function () {
                    initializeSortable();
                }, 100);
            });

            $('#trending-form').on('submit', function (e) {
                e.preventDefault();

                if (selectedMangas.length === 0) {
                    showToast('Vui lòng chọn ít nhất 1 truyện', 'danger');
                    return;
                }

                if (selectedMangas.length > 8) {
                    showToast('Chỉ được chọn tối đa 8 truyện', 'danger');
                    return;
                }

                $.ajax({
                    url: '/admin/mangas/trending',
                    method: 'POST',
                    data: {
                        slugs: selectedMangas,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                            showToast('Đã lưu danh sách Top thịnh hành thành công');
                        } else {
                            showToast('Có lỗi xảy ra', 'danger');
                        }
                    },
                    error: function (xhr) {
                        const message = xhr.responseJSON?.message || 'Có lỗi xảy ra';
                        showToast(message, 'danger');
                    }
                });
            });
        });
    </script>
@endpush