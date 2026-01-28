@extends('layouts.admin')

@section('title', 'Quản lý Users')
@section('page-title', 'Quản lý Users')

@section('content')
<div class="row mb-4">
    <div class="col-lg-3 col-6">
        <div class="small-box text-bg-primary">
            <div class="inner">
                <h3>{{ $stats['online_now'] }}</h3>
                <p>Đang Online</p>
            </div>
            <div class="small-box-icon">
                <i class="bi bi-person-check-fill"></i>
            </div>
            <a href="#" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-100-hover">
                Chi tiết <i class="bi bi-arrow-right-circle-fill"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box text-bg-success">
            <div class="inner">
                <h3>{{ $stats['new_today'] }}</h3>
                <p>Users mới hôm nay</p>
            </div>
            <div class="small-box-icon">
                <i class="bi bi-person-plus-fill"></i>
            </div>
            <a href="#" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-100-hover">
                Tháng này: {{ $stats['new_month'] }} <i class="bi bi-arrow-right-circle-fill"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box text-bg-info">
            <div class="inner">
                <h3>{{ $stats['online_today'] }}</h3>
                <p>Truy cập hôm nay</p>
            </div>
            <div class="small-box-icon">
                <i class="bi bi-activity"></i>
            </div>
            <a href="#" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-100-hover">
                Xem thống kê <i class="bi bi-arrow-right-circle-fill"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box text-bg-danger">
            <div class="inner">
                <h3>{{ $stats['banned'] }}</h3>
                <p>Bị khóa (Banned)</p>
            </div>
            <div class="small-box-icon">
                <i class="bi bi-person-x-fill"></i>
            </div>
            <a href="#" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-100-hover">
                Danh sách đen <i class="bi bi-arrow-right-circle-fill"></i>
            </a>
        </div>
    </div>
</div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between">
                    <h3 class="card-title mb-3 mb-md-0">Danh sách Users</h3>
                    <div class="card-tools w-100 w-md-auto">
                        <form method="GET" action="{{ route('admin.users') }}" class="d-flex w-100">
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control"
                                    placeholder="Tìm theo email/tên..." value="{{ $search }}">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> <span class="d-none d-sm-inline">Tìm</span>
                                </button>
                                @if($search)
                                    <a href="{{ route('admin.users') }}" class="btn btn-secondary">
                                        <i class="bi bi-x"></i> <span class="d-none d-sm-inline">Xóa</span>
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="min-height: 300px;">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 50px">ID</th>
                                    <th>Tên</th>
                                    <th class="d-none d-md-table-cell">Email</th>
                                    <th>Quyền</th>
                                    <th>Tình trạng</th>
                                    <th class="d-none d-lg-table-cell">Bị ban đến</th>
                                    <th class="d-none d-md-table-cell">Hoạt động cuối</th>
                                    <th style="width: 120px">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    @php
                                        $isBanned = $user->isBanned();
                                        $isOnline = $user->isOnline();
                                        $isActive = $user->isActive();
                                        $isCurrentUser = $user->id === auth()->id();
                                    @endphp
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td>
                                            <strong>{{ $user->name }}</strong>
                                            <div class="d-md-none mt-1">
                                                <small class="text-muted d-block">{{ $user->email }}</small>
                                            </div>
                                        </td>
                                        <td class="d-none d-md-table-cell">{{ $user->email }}</td>
                                        <td>
                                            <select class="form-select form-select-sm change-role" data-user-id="{{ $user->id }}"
                                                style="width: auto; display: inline-block;">
                                                <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                            </select>
                                        </td>
                                        <td>
                                            @if($isBanned)
                                                <span class="badge text-bg-danger">Bị ban</span>
                                            @else
                                                @if($isOnline)
                                                    <span class="badge rounded-pill bg-success">
                                                        <i class="bi bi-circle-fill" style="font-size: 0.5rem; vertical-align: middle;"></i> Online
                                                    </span>
                                                @else
                                                    <span class="badge rounded-pill bg-secondary">Offline</span>
                                                @endif
                                            @endif

                                            <div class="d-md-none mt-1">
                                                @if($user->last_seen_at)
                                                    <small class="text-muted">{{ $user->last_seen_at->diffForHumans() }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="d-none d-lg-table-cell">
                                            @if($user->banned_until)
                                                @if($isBanned)
                                                    <span class="text-danger">{{ $user->banned_until->format('d/m/Y H:i') }}</span>
                                                @else
                                                    <span class="text-muted">Đã hết hạn</span>
                                                @endif
                                            @else
                                                <span class="text-success">Không bị ban</span>
                                            @endif
                                        </td>
                                        <td class="d-none d-md-table-cell">
                                            @if($user->last_seen_at)
                                                <span title="{{ $user->last_seen_at->format('d/m/Y H:i:s') }}">
                                                    {{ $user->last_seen_at->diffForHumans() }}
                                                </span>
                                            @elseif($user->last_login_at)
                                                <span title="{{ $user->last_login_at->format('d/m/Y H:i:s') }}">
                                                    {{ $user->last_login_at->diffForHumans() }}
                                                </span>
                                            @else
                                                <span class="text-muted">Chưa rõ</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!$isCurrentUser)
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        Hành động
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow">
                                                        <li><h6 class="dropdown-header">Xử lý tài khoản</h6></li>
                                                        <li><a class="dropdown-item ban-user" href="#" data-user-id="{{ $user->id }}"
                                                                data-days="1">Ban 1 ngày</a></li>
                                                        <li><a class="dropdown-item ban-user" href="#" data-user-id="{{ $user->id }}"
                                                                data-days="7">Ban 7 ngày</a></li>
                                                        <li><a class="dropdown-item ban-user" href="#" data-user-id="{{ $user->id }}"
                                                                data-days="30">Ban 30 ngày</a></li>
                                                        <li><a class="dropdown-item ban-user" href="#" data-user-id="{{ $user->id }}"
                                                                data-days="999999">Ban vĩnh viễn</a></li>
                                                        @if($isBanned)
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><a class="dropdown-item unban-user text-success" href="#"
                                                                    data-user-id="{{ $user->id }}">
                                                                    <i class="bi bi-unlock"></i> Bỏ ban
                                                                </a></li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($users->hasPages() || $users->total() > 10)
                    <div class="card-footer">
                        @if($users->hasPages())
                            {{ $users->links() }}
                        @endif
                        @if($users->total() > 10)
                            <div class="text-muted text-center mt-2">
                                Hiển thị {{ $users->firstItem() }} - {{ $users->lastItem() }} trong tổng số {{ $users->total() }}
                                users
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
        // Change role
        function updateRole(userId, role) {
            $.ajax({
                url: '/admin/users/' + userId + '/change-role',
                method: 'POST',
                data: {
                    role: role,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    if (response.status === 'success') {
                        showToast('Đổi quyền thành công');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast('Có lỗi xảy ra', 'danger');
                    }
                },
                error: function () {
                    showToast('Có lỗi xảy ra', 'danger');
                }
            });
        }

        $('.change-role').on('change', function () {
            updateRole($(this).data('user-id'), $(this).val());
        });

        $('.change-role-btn').on('click', function (e) {
            e.preventDefault();
            updateRole($(this).data('user-id'), $(this).data('role'));
        });

        // Ban user
        $('.ban-user').on('click', function (e) {
            e.preventDefault();
            const userId = $(this).data('user-id');
            const days = $(this).data('days');

            if (confirm('Bạn chắc chắn muốn ban user này?')) {
                $.ajax({
                    url: '/admin/users/' + userId + '/ban',
                    method: 'POST',
                    data: {
                        days: days,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                        showToast('Ban user thành công');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast('Có lỗi xảy ra', 'danger');
                    }
                },
                error: function () {
                    showToast('Có lỗi xảy ra', 'danger');
                }
                });
            }
        });

        // Unban user
        $('.unban-user').on('click', function (e) {
            e.preventDefault();
            const userId = $(this).data('user-id');

            if (confirm('Bạn chắc chắn muốn bỏ ban user này?')) {
                $.ajax({
                    url: '/admin/users/' + userId + '/unban',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                        showToast('Bỏ ban user thành công');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast('Có lỗi xảy ra', 'danger');
                    }
                },
                error: function () {
                    showToast('Có lỗi xảy ra', 'danger');
                }
                });
            }
        });
    </script>
@endpush