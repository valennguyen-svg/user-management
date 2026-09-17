@extends('layouts.app')

@section('title', 'Tạo người dùng')

@section('content')
    <h1 class="h4 mb-3">Tạo người dùng</h1>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf

                @include('users.form')

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Lưu</button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Quay lại</a>
                </div>
            </form>
        </div>
    </div>
@endsection