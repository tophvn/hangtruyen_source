@extends('layouts.admin')

@section('title', 'Quản lý Users')
@section('page-title', 'Quản lý Users')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Danh sách Users</h3>
                <div class="card-tools">
                    <form method="GET" action="{{ route('admin.users') }}" class="d-flex">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Tìm theo email/tên..." value="{{ $search }}" style="width: 250px; margin-right: 10px;">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-search"></i> Tìm
                        </button>
                        @if($search)
                            <a href="{{ route('admin.users') }}" class="btn btn-sm btn-secondary ms-2">
                                <i class="bi bi-x"></i> Xóa
                            </a>
                        @endif
                    </form>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 50px">ID</th>
                            <th>Tên</th>
                            <th>Email</th>
                            <th>Quyền</th>
                            <th>Trạng thái</th>
                            <th>Bị ban đến</th>
                            <th>Đăng nhập cuối</th>
                            <th style="width: 200px">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            @php
                                $isBanned = $user->isBanned();
                                $isActive = $user->isActive();
                                $isOnline = $user->isOnline();
                                $isCurrentUser = $user->id === auth()->id();
                            @endphp
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>
                                    {{ $user->name }}
                                    @if($isOnline)
                                        <span class="badge text-bg-success ms-1">Online</span>
                                    @endif
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <select class="form-select form-select-sm change-role" data-user-id="{{ $user->id }}" style="width: auto; display: inline-block;">
                                        <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                </td>
                                <td>
                                    @if($isBanned)
                                        <span class="badge text-bg-danger">Bị ban</span>
                                    @elseif($isActive)
                                        <span class="badge text-bg-success">Hoạt động</span>
                                    @else
                                        <span class="badge text-bg-secondary">Không hoạt động</span>
                                    @endif
                                </td>
                                <td>
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
                                <td>
                                    {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Chưa đăng nhập' }}
                                </td>
                                <td>
                                    @if(!$isCurrentUser)
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                Ban
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item ban-user" href="#" data-user-id="{{ $user->id }}" data-days="1">Ban 1 ngày</a></li>
                                                <li><a class="dropdown-item ban-user" href="#" data-user-id="{{ $user->id }}" data-days="7">Ban 7 ngày</a></li>
                                                <li><a class="dropdown-item ban-user" href="#" data-user-id="{{ $user->id }}" data-days="30">Ban 30 ngày</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item ban-user" href="#" data-user-id="{{ $user->id }}" data-days="999999">Ban vĩnh viễn</a></li>
                                                @if($isBanned)
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item unban-user text-success" href="#" data-user-id="{{ $user->id }}">Bỏ ban</a></li>
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
            @if($users->hasPages() || $users->total() > 10)
                <div class="card-footer">
                    @if($users->hasPages())
                        {{ $users->links() }}
                    @endif
                    @if($users->total() > 10)
                        <div class="text-muted text-center mt-2">
                            Hiển thị {{ $users->firstItem() }} - {{ $users->lastItem() }} trong tổng số {{ $users->total() }} users
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
    $('.change-role').on('change', function() {
        const userId = $(this).data('user-id');
        const role = $(this).val();
        
        $.ajax({
            url: '/admin/users/' + userId + '/change-role',
            method: 'POST',
            data: {
                role: role,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.status === 'success') {
                    alert('Đổi quyền thành công');
                    location.reload();
                } else {
                    alert('Có lỗi xảy ra');
                }
            },
            error: function() {
                alert('Có lỗi xảy ra');
            }
        });
    });
    
    // Ban user
    $('.ban-user').on('click', function(e) {
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
                success: function(response) {
                    if (response.status === 'success') {
                        alert('Ban user thành công');
                        location.reload();
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
    
    // Unban user
    $('.unban-user').on('click', function(e) {
        e.preventDefault();
        const userId = $(this).data('user-id');
        
        if (confirm('Bạn chắc chắn muốn bỏ ban user này?')) {
            $.ajax({
                url: '/admin/users/' + userId + '/unban',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status === 'success') {
                        alert('Bỏ ban user thành công');
                        location.reload();
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
</script>
@endpush
