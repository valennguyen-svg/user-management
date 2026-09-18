@extends('layouts.app')

@section('title', 'Cập nhật người dùng')

@section('content')
    <h1 class="h4 mb-3">Cập nhật người dùng #{{ $user->id }}</h1>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('users.update', $user) }}">
                @csrf
                @method('PUT')

                @include('users.form', ['user' => $user])

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Quay lại</a>
                </div>
            </form>
        </div>
    </div>
@endsection