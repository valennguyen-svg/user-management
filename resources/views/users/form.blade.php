@php
    $isEdit = isset($user);
    $currentRole = old('role', $isEdit ? $user->getRoleNames()->first() : 'staff');
    $currentStatus = (string) old('status', $isEdit ? (int) $user->status : 1);
@endphp

<div class="mb-3">
    <label for="name" class="form-label">Họ tên</label>
    <input type="text" name="name" id="name" value="{{ old('name', $user->name ?? '') }}"
           class="form-control @error('name') is-invalid @enderror">
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input type="email" name="email" id="email" value="{{ old('email', $user->email ?? '') }}"
           class="form-control @error('email') is-invalid @enderror">
    @error('email')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="password" class="form-label">
            {{ $isEdit ? 'Mật khẩu mới' : 'Mật khẩu' }}
        </label>
        <input type="password" name="password" id="password"
               class="form-control @error('password') is-invalid @enderror">
        @if ($isEdit)
            <div class="form-text">Để trống nếu không đổi mật khẩu.</div>
        @endif
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="password_confirmation" class="form-label">Xác nhận mật khẩu</label>
        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="role" class="form-label">Vai trò</label>
        <select name="role" id="role" class="form-select @error('role') is-invalid @enderror">
            @foreach ($roles as $role)
                <option value="{{ $role }}" @selected($currentRole === $role)>{{ $role }}</option>
            @endforeach
        </select>
        @error('role')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="status" class="form-label">Trạng thái</label>
        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
            <option value="1" @selected($currentStatus === '1')>Hoạt động</option>
            <option value="0" @selected($currentStatus === '0')>Không hoạt động</option>
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>