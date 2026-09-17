@php
    $isEdit = isset($user);
    $selectedRole = old('role', $isEdit ? $user->getRoleNames()->first() : '');
    $selectedStatus = (string) old('status', $isEdit ? (int) $user->status : 1);
@endphp
 
<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label">Họ tên <span class="text-danger">*</span></label>
        <input type="text" name="name" id="name"
               class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $user->name ?? '') }}" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
 
    <div class="col-md-6">
        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" name="email" id="email"
               class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $user->email ?? '') }}" required>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
 
    <div class="col-md-6">
        <label for="password" class="form-label">
            Mật khẩu {{ $isEdit ? 'mới' : '' }}
            @unless ($isEdit)
                <span class="text-danger">*</span>
            @endunless
        </label>
        <input type="password" name="password" id="password"
               class="form-control @error('password') is-invalid @enderror"
               autocomplete="new-password" {{ $isEdit ? '' : 'required' }}>
        @if ($isEdit)
            {{-- để trống thì giữ nguyên mật khẩu cũ --}}
            <div class="form-text">Để trống nếu không đổi mật khẩu.</div>
        @else
            <div class="form-text">Tối thiểu 8 ký tự.</div>
        @endif
    </div>
 
    <div class="col-md-6">
        <label for="password_confirmation" class="form-label">
            Xác nhận mật khẩu
            @unless ($isEdit)
                <span class="text-danger">*</span>
            @endunless
        </label>
        <input type="password" name="password_confirmation" id="password_confirmation"
               class="form-control @error('password') is-invalid @enderror"
               autocomplete="new-password" {{ $isEdit ? '' : 'required' }}>
        {{-- Lỗi của trường password (bao gồm "xác nhận không khớp") hiển thị tại đây --}}
        @error('password')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
 
    <div class="col-md-6">
        <label for="role" class="form-label">Vai trò <span class="text-danger">*</span></label>
        <select name="role" id="role"
                class="form-select text-capitalize @error('role') is-invalid @enderror" required>
            <option value="">-- Chọn vai trò --</option>
            @foreach ($roles as $role)
                @php($roleName = is_object($role) ? $role->name : $role)
                <option value="{{ $roleName }}" @selected($selectedRole === $roleName)>
                    {{ ucfirst($roleName) }}
                </option>
            @endforeach
        </select>
        @error('role')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
 
    <div class="col-md-6">
        <label for="status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
        <select name="status" id="status"
                class="form-select @error('status') is-invalid @enderror" required>
            <option value="1" @selected($selectedStatus === '1')>Hoạt động</option>
            <option value="0" @selected($selectedStatus === '0')>Không hoạt động</option>
        </select>
        @if ($isEdit && $user->id === auth()->id())
            <div class="form-text">Bạn không thể tự chuyển tài khoản của mình sang Không hoạt động.</div>
        @endif
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>