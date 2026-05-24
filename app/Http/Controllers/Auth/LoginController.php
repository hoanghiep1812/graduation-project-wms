<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard.index');
        }
        return view('auth.login');
    }

     public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required'],
        ], [
            'username.required' => 'Vui lòng nhập Tên đăng nhập.',
            'password.required' => 'Vui lòng nhập Mật khẩu.',
        ]);
        
        $user = User::where('username', $request->username)->first();
        
        if (!$user) {
            return back()->withErrors([
                'username' => 'Tài khoản không tồn tại.',
            ])->onlyInput('username');
        }

        
        if (!$user->is_active) {
            return back()->withErrors([
                'username' => 'Tài khoản đã bị khóa.',
            ])->onlyInput('username');
        }

        
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'Mật khẩu không chính xác.',
            ])->onlyInput('username');
        }
        
        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->intended('admin/dashboard')
            ->with('success', 'Đăng nhập thành công.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
