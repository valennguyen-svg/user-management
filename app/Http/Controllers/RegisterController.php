<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Services\UserService;

class RegisterController extends Controller
{
    public function __construct(private UserService $userService) {}

    public function show()
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request)
    {
        $data = $request->validated();
        $data['role'] = 'staff';
        $data['status'] = false;

        $this->userService->create($data);

        return redirect()->route('login')
            ->with('success', 'Đăng ký thành công.');
    }
}
