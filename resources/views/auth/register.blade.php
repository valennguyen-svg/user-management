@extends('layouts.app')
 
@section('title', 'Đăng ký tài khoản')
 
@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 mb-1 text-center">Đăng ký tài khoản</h1>
                   
                    <form method="POST" action="{{ route('register.store') }}">
                        @csrf
 
                        <div class="mb-3">
                            <label for="name" class="form-label">Họ tên</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}"
                                   class="form-control @error('name') is-invalid @enderror" autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
 
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                   class="form-control @error('email') is-invalid @enderror">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
 
                        <div class="mb-3">
                            <label for="password" class="form-label">Mật khẩu</label>
                            <input type="password" name="password" id="password"
                                   class="form-control @error('password') is-invalid @enderror">
                            <div class="form-text">Tối thiểu 8 ký tự.</div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
 
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Nhập lại mật khẩu</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="form-control">
                        </div>
 
                        <button type="submit" class="btn btn-primary w-100">Đăng ký</button>
                    </form>
 
                    <p class="text-center text-muted small mt-3 mb-0">
                        Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection