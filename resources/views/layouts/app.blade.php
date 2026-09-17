<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Quản lý người dùng')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

@auth
    <nav class="navbar navbar-expand bg-white border-bottom">
        <div class="container">
            <span class="navbar-brand">User Management</span>

            <ul class="navbar-nav me-auto">
                {{-- Spatie - Blade directives: @can('edit articles') ... @endcan --}}
                @can('users.view')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('users.index') }}">Quản lý người dùng</a>
                    </li>
                @endcan
            </ul>

            <span class="me-3 text-muted">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm">Đăng xuất</button>
            </form>
        </div>
    </nav>
@endauth

<main class="container py-4">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @yield('content')
</main>

</body>
</html>
