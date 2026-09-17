@extends('layouts.app')

@section('title', 'Danh sách người dùng')

@section('content')
    @php
        $canAct = auth()->user()->canany(['users.update', 'users.delete']);
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Danh sách người dùng</h1>

        <div class="d-flex gap-2">
            {{-- Spatie - Blade directives: @can('edit articles') ... @endcan --}}
            @can('users.export')
                {{-- BR-09: gắn bộ lọc hiện tại vào link export --}}
                <a href="{{ route('users.export', request()->only(['keyword', 'role', 'status', 'sort'])) }}"
                   class="btn btn-outline-success">Export Excel</a>
            @endcan

            @can('users.create')
                <a href="{{ route('users.create') }}" class="btn btn-primary">Tạo người dùng</a>
            @endcan
        </div>
    </div>

    {{-- Spatie - Blade directives: @cannot --}}
    @cannot('users.create')
        <p class="text-muted small">Tài khoản của bạn chỉ có quyền xem danh sách.</p>
    @endcannot

    {{-- Import --}}
    @can('users.import')
        <div class="card mb-3">
            <div class="card-body">
                <form method="POST" action="{{ route('users.import') }}" enctype="multipart/form-data"
                      class="row g-2 align-items-end">
                    @csrf
                    <div class="col-md-6">
                        <label for="file" class="form-label">Import người dùng (xlsx, xls, csv — tối đa 2 MB)</label>
                        <input type="file" name="file" id="file" accept=".xlsx,.xls,.csv"
                               class="form-control @error('file') is-invalid @enderror">
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-secondary">Import</button>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('users.template') }}" class="btn btn-link">Tải tệp mẫu</a>
                    </div>
                </form>

                {{-- UC-07 bước 5: kết quả import --}}
                @if (session('import_result'))
                    @php($result = session('import_result'))
                    <div class="alert {{ $result['failed'] > 0 ? 'alert-warning' : 'alert-success' }} mt-3 mb-0">
                        Tổng: {{ $result['total'] }} dòng —
                        thành công: {{ $result['success'] }} —
                        thất bại: {{ $result['failed'] }}.

                        @if (! empty($result['errors']))
                            <ul class="mb-0 mt-2">
                                @foreach ($result['errors'] as $error)
                                    <li>Dòng {{ $error['row'] }}: {{ $error['message'] }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endcan

    {{-- FR-04: tìm kiếm, lọc, sắp xếp --}}
    <form method="GET" action="{{ route('users.index') }}" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="text" name="keyword" value="{{ request('keyword') }}"
                   class="form-control" placeholder="Tìm theo tên hoặc email">
        </div>
        <div class="col-md-2">
            <select name="role" class="form-select">
                <option value="">Tất cả vai trò</option>
                @foreach ($roles as $role)
                    <option value="{{ $role }}" @selected(request('role') === $role)>{{ $role }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">Tất cả trạng thái</option>
                <option value="1" @selected(request('status') === '1')>Hoạt động</option>
                <option value="0" @selected(request('status') === '0')>Không hoạt động</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="sort" class="form-select">
                <option value="desc" @selected(request('sort', 'desc') === 'desc')>Mới nhất</option>
                <option value="asc" @selected(request('sort') === 'asc')>Cũ nhất</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-dark w-100">Lọc</button>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Xóa lọc</a>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Họ tên</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    {{-- Spatie - Blade directives: dùng @canany thay cho @hasanypermission --}}
                    @canany(['users.update', 'users.delete'])
                        <th class="text-end">Thao tác</th>
                    @endcanany
                </tr>
                </thead>
                <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        {{-- Spatie - Basic Usage: getRoleNames() --}}
                        <td>{{ $user->getRoleNames()->implode(', ') }}</td>
                        <td>
                            @if ($user->status)
                                <span class="badge text-bg-success">Hoạt động</span>
                            @else
                                <span class="badge text-bg-secondary">Không hoạt động</span>
                            @endif
                        </td>
                        <td>{{ $user->created_at?->format('d/m/Y H:i') }}</td>

                        @canany(['users.update', 'users.delete'])
                            <td class="text-end">
                                @can('users.update')
                                    <a href="{{ route('users.edit', $user) }}"
                                       class="btn btn-sm btn-outline-primary">Sửa</a>
                                @endcan

                                {{-- Spatie - Blade directives:
                                     @if(auth()->user()->can('edit articles') && $some_other_condition) --}}
                                {{-- BR-04: không hiện nút Xóa ở chính tài khoản đang đăng nhập --}}
                                @if (auth()->user()->can('users.delete') && $user->id !== auth()->id())
                                    {{-- NFR-06: xác nhận trước khi xóa --}}
                                    <form method="POST" action="{{ route('users.destroy', $user) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('Xóa người dùng {{ $user->email }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                                    </form>
                                @endif
                            </td>
                        @endcanany
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $canAct ? 7 : 6 }}" class="text-center text-muted py-4">
                            Không có người dùng phù hợp. Thử đổi từ khóa hoặc bỏ bớt bộ lọc.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $users->links() }}
    </div>
@endsection
