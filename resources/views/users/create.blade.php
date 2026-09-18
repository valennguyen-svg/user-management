@extends('layouts.app')

@section('title', 'Tạo người dùng mới')

@section('content')
    <h1 class="h4 mb-3">Tạo người dùng mới</h1>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf

                @include('users.form')

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary">Tạo mới</button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Quay lại</a>
                </div>
            </form>
        </div>
    </div>
@endsection