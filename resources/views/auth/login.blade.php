@extends('layouts.app')
 
@section('title', 'Đăng nhập')
 
@section('content')
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 mb-4 text-center">Đăng nhập</h1>
 
                    <form method="POST" action="{{ route('login.attempt') }}">
                        @csrf
 
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                   class="form-control @error('email') is-invalid @enderror" autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
 
                        <div class="mb-3">
                            <label for="password" class="form-label">Mật khẩu</label>
                            <input type="password" name="password" id="password"
                                   class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
 
                        <div class="form-check mb-3">
                            <input type="checkbox" name="remember" value="1" id="remember" class="form-check-input">
                            <label for="remember" class="form-check-label">Ghi nhớ đăng nhập</label>
                        </div>
 
                        <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
                    </form>
 
                    <p class="text-center text-muted small mt-3 mb-0">
                        Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection